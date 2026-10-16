<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\Movements\Pages\ListMovements;
use App\Filament\Resources\Staff\Pages\ListStaff;
use App\Filament\Widgets\CurrentlyOut;
use App\Livewire\Board;
use App\Models\Movement;
use App\Models\MovementReason;
use App\Models\Staff;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => UserRole::Admin]);
    }

    private function clerk(): User
    {
        return User::factory()->create(['role' => UserRole::Clerk]);
    }

    public function test_guests_are_sent_to_the_login_page(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/staff')->assertRedirect('/admin/login');
        $this->get('/board')->assertRedirect('/admin/login');
    }

    public function test_clerks_cannot_manage_master_data(): void
    {
        $this->actingAs($this->clerk());

        $this->get('/admin/users')->assertForbidden();
        $this->get('/admin/units')->assertForbidden();
        $this->get('/admin/movement-reasons')->assertForbidden();

        $this->get('/admin')->assertOk();
        $this->get('/admin/staff')->assertOk();
        $this->get('/admin/movements')->assertOk();
    }

    public function test_only_admins_can_correct_movement_history(): void
    {
        $movement = Movement::factory()->create();

        $this->actingAs($this->clerk())->get("/admin/movements/{$movement->id}/edit")->assertForbidden();
        $this->actingAs($this->admin())->get("/admin/movements/{$movement->id}/edit")->assertOk();
    }

    public function test_admins_can_open_every_page(): void
    {
        $this->actingAs($this->admin());

        foreach (['/admin', '/admin/staff', '/admin/staff/create', '/admin/movements', '/admin/units', '/admin/movement-reasons', '/admin/users', '/board'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_staff_can_be_checked_out_and_in_from_the_staff_list(): void
    {
        $this->actingAs($this->clerk());
        $staff = Staff::factory()->create();
        $reason = MovementReason::factory()->create();

        Livewire::test(ListStaff::class)
            ->callAction(TestAction::make('checkOut')->table($staff), [
                'movement_reason_id' => $reason->id,
                'out_at' => now()->subHour()->format('Y-m-d H:i'),
                'expected_back_at' => now()->addHour()->format('Y-m-d H:i'),
                'note' => 'Meeting at the state office',
            ])
            ->assertHasNoFormErrors()
            ->assertNotified("{$staff->name} checked out");

        $movement = $staff->currentMovement()->first();
        $this->assertSame('Meeting at the state office', $movement->note);

        Livewire::test(ListStaff::class)
            ->assertActionHidden(TestAction::make('checkOut')->table($staff))
            ->callAction(TestAction::make('checkIn')->table($staff), ['in_at' => now()->format('Y-m-d H:i')])
            ->assertNotified("{$staff->name} checked in");

        $this->assertFalse($staff->isOut());
    }

    public function test_dashboard_checks_out_a_chosen_staff_member(): void
    {
        $this->actingAs($this->clerk());
        $staff = Staff::factory()->create();

        Livewire::test(CurrentlyOut::class)
            ->callAction(TestAction::make('checkOut')->table(), [
                'staff_id' => $staff->id,
                'movement_reason_id' => MovementReason::factory()->create()->id,
                'out_at' => now()->format('Y-m-d H:i'),
            ])
            ->assertHasNoFormErrors();

        $this->assertTrue($staff->isOut());
    }

    public function test_someone_already_out_cannot_be_checked_out_again(): void
    {
        $this->actingAs($this->clerk());
        $movement = Movement::factory()->open()->create();

        Livewire::test(CurrentlyOut::class)
            ->callAction(TestAction::make('checkOut')->table(), [
                'staff_id' => $movement->staff_id,
                'movement_reason_id' => $movement->movement_reason_id,
                'out_at' => now()->format('Y-m-d H:i'),
            ]);

        // The staff picker only offers people who are in, so the form rejects the choice.
        $this->assertSame(1, Movement::count());
    }

    public function test_csv_export_neutralises_spreadsheet_formulas(): void
    {
        $this->actingAs($this->admin());
        Movement::factory()->create(['out_at' => now(), 'note' => '=HYPERLINK("http://evil")']);

        $response = Livewire::test(ListMovements::class)->callAction('exportCsv');

        $csv = $response->effects['download']['content'] ?? null;
        $this->assertNotNull($csv);
        $this->assertStringContainsString("'=HYPERLINK", base64_decode($csv));
    }

    public function test_board_lists_staff_with_their_status(): void
    {
        $this->actingAs($this->clerk());
        $out = Movement::factory()->open()->create(['note' => 'Site visit'])->staff;
        $in = Staff::factory()->for($out->unit)->create();
        $inactive = Staff::factory()->for($out->unit)->create(['is_active' => false]);

        Livewire::test(Board::class)
            ->assertSee($out->name)
            ->assertSee('Site visit')
            ->assertSee($in->name)
            ->assertDontSee($inactive->name)
            ->assertSee('1 in, 1 out');
    }
}
