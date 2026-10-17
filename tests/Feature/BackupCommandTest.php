<?php

namespace Tests\Feature;

use App\Models\Staff;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Runs against a real SQLite file: VACUUM INTO can't run inside RefreshDatabase's transaction.
 */
class BackupCommandTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dir = sys_get_temp_dir().'/attendance-backup-test-'.uniqid();
        File::ensureDirectoryExists($this->dir);
        touch($this->dir.'/database.sqlite');

        config(['database.connections.sqlite.database' => $this->dir.'/database.sqlite']);
        DB::purge('sqlite');
        $this->app->useStoragePath($this->dir.'/storage');

        Artisan::call('migrate', ['--force' => true]);
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');
        File::deleteDirectory($this->dir);

        parent::tearDown();
    }

    public function test_backup_writes_a_complete_copy_and_prunes_old_ones(): void
    {
        Staff::factory()->count(3)->create();
        $backups = $this->dir.'/storage/app/backups';
        File::ensureDirectoryExists($backups);
        foreach (['2026-01-01-000000', '2026-01-02-000000'] as $stamp) {
            touch("{$backups}/database-{$stamp}.sqlite");
        }

        $this->artisan('app:backup', ['--keep' => 2])->assertSuccessful();

        $files = File::glob($backups.'/database-*.sqlite');
        $this->assertCount(2, $files);
        $this->assertNotContains("{$backups}/database-2026-01-01-000000.sqlite", $files);

        $copy = new \PDO('sqlite:'.collect($files)->sort()->last());
        $this->assertSame(3, (int) $copy->query('select count(*) from staff')->fetchColumn());
    }
}
