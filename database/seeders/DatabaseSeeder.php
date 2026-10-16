<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\MovementReason;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the reference data every installation needs, plus a first admin account.
     */
    public function run(): void
    {
        // Units carried over from the old system; rename them in the admin panel.
        foreach (['BPKSP', 'UGT', 'UKNP', 'UKSP'] as $order => $code) {
            Unit::firstOrCreate(['code' => $code], ['name' => $code, 'sort_order' => $order]);
        }

        foreach (['Mesyuarat', 'Kursus / Latihan', 'Tugasan Luar', 'Lawatan Tapak', 'Urusan Peribadi', 'Cuti'] as $reason) {
            MovementReason::firstOrCreate(['name' => $reason]);
        }

        User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@example.com')],
            [
                'name' => 'Administrator',
                'password' => env('ADMIN_PASSWORD', 'password'),
                'role' => UserRole::Admin,
            ],
        );
    }
}
