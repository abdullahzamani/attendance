<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Http\Middleware\AuthorizeBoard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DeploymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_board_token_opens_the_board_without_a_login_and_hides_the_token(): void
    {
        config(['board.token' => 'tv-secret']);

        $this->get('/board?unit=UGT&token=tv-secret')
            ->assertRedirect('/board?unit=UGT')
            ->assertSessionHas(AuthorizeBoard::SESSION_KEY);

        $this->get('/board?unit=UGT')->assertOk();
    }

    public function test_wrong_or_missing_token_still_requires_a_login(): void
    {
        config(['board.token' => 'tv-secret']);

        $this->get('/board?token=guess')->assertRedirect('/admin/login');
        $this->get('/board')->assertRedirect('/admin/login');
    }

    public function test_board_requires_a_login_when_no_token_is_configured(): void
    {
        config(['board.token' => null]);

        $this->get('/board?token=')->assertRedirect('/admin/login');
    }

    public function test_changing_the_token_cuts_off_screens_using_the_old_link(): void
    {
        config(['board.token' => 'old-secret']);
        $this->get('/board?token=old-secret');
        $this->get('/board')->assertOk();

        config(['board.token' => 'new-secret']);

        $this->get('/board')->assertRedirect('/admin/login');
    }

    public function test_board_access_does_not_open_the_admin_panel(): void
    {
        config(['board.token' => 'tv-secret']);
        $this->get('/board?token=tv-secret');

        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_board_url_command_refuses_without_a_token(): void
    {
        config(['board.token' => null]);

        $this->artisan('app:board-url')->assertFailed();
    }

    public function test_board_url_command_prints_the_tv_link(): void
    {
        config(['board.token' => 'tv-secret']);

        $this->artisan('app:board-url', ['--unit' => 'UGT'])
            ->expectsOutputToContain('/board?unit=UGT&token=tv-secret')
            ->assertSuccessful();
    }

    public function test_admin_command_creates_an_administrator(): void
    {
        $this->artisan('app:admin', ['email' => 'it@office.gov.my', '--name' => 'IT Unit'])
            ->expectsQuestion('Password (at least 8 characters)', 'a-strong-pass')
            ->assertSuccessful();

        $user = User::where('email', 'it@office.gov.my')->first();
        $this->assertTrue($user->isAdmin());
        $this->assertTrue(Hash::check('a-strong-pass', $user->password));
    }

    public function test_admin_command_resets_an_existing_users_password_and_role(): void
    {
        $clerk = User::factory()->create(['email' => 'clerk@office.gov.my', 'role' => UserRole::Clerk]);

        $this->artisan('app:admin', ['email' => 'clerk@office.gov.my'])
            ->expectsQuestion('Password (at least 8 characters)', 'another-pass')
            ->assertSuccessful();

        $clerk->refresh();
        $this->assertTrue($clerk->isAdmin());
        $this->assertTrue(Hash::check('another-pass', $clerk->password));
    }

    public function test_admin_command_rejects_a_short_password(): void
    {
        $this->artisan('app:admin', ['email' => 'it@office.gov.my'])
            ->expectsQuestion('Password (at least 8 characters)', 'short')
            ->assertFailed();

        $this->assertFalse(User::where('email', 'it@office.gov.my')->exists());
    }

    public function test_seeder_only_runs_on_a_fresh_installation(): void
    {
        $this->seed();
        $this->assertSame(1, User::count());

        User::first()->update(['email' => 'boss@office.gov.my']);
        $this->seed();

        $this->assertSame(1, User::count());
        $this->assertFalse(User::where('email', 'admin@example.com')->exists());
    }
}
