<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

#[Signature('app:backup {--keep=30 : How many backups to keep; older ones are deleted}')]
#[Description('Save a consistent copy of the SQLite database to storage/app/backups')]
class BackupDatabase extends Command
{
    public function handle(): int
    {
        if (DB::getDriverName() !== 'sqlite') {
            $this->error('app:backup only handles SQLite. For MySQL, use mysqldump instead.');

            return self::FAILURE;
        }

        $dir = storage_path('app/backups');
        File::ensureDirectoryExists($dir);

        $path = $dir.'/database-'.now()->format('Y-m-d-His').'.sqlite';

        // VACUUM INTO writes a complete, consistent snapshot even while the app is in use.
        DB::statement('VACUUM INTO ?', [$path]);

        $this->info("Backup saved to {$path}");

        $keep = max(1, (int) $this->option('keep'));
        collect(File::glob($dir.'/database-*.sqlite'))
            ->sortDesc()
            ->slice($keep)
            ->each(fn (string $old) => File::delete($old));

        return self::SUCCESS;
    }
}
