<?php

use Illuminate\Support\Facades\Schedule;

// Daily database backup, kept for 30 days. Runs when `php artisan schedule:work`
// (the "scheduler" service in docker-compose.yml) is running.
Schedule::command('app:backup --keep=30')->dailyAt('01:00');
