<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:admin {email : Email address to sign in with} {--name=Administrator : Display name for a new account}')]
#[Description('Create an administrator, or make an existing user an administrator with a new password')]
class MakeAdmin extends Command
{
    public function handle(): int
    {
        $email = (string) $this->argument('email');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error("\"{$email}\" is not a valid email address.");

            return self::FAILURE;
        }

        $password = (string) $this->secret('Password (at least 8 characters)');

        if (mb_strlen($password) < 8) {
            $this->error('The password must be at least 8 characters.');

            return self::FAILURE;
        }

        $user = User::firstOrNew(['email' => $email]);
        $existed = $user->exists;

        $user->fill([
            'name' => $user->name ?? (string) $this->option('name'),
            'password' => $password,
            'role' => UserRole::Admin,
        ])->save();

        $this->info($existed
            ? "{$email} is now an administrator with the new password."
            : "Administrator {$email} created.");

        return self::SUCCESS;
    }
}
