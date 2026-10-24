<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class AdminPassword extends Command
{
    protected $signature = 'madeby:admin-password {email=admin@madeby.test}';

    protected $description = 'Set a password interactively for an existing MADEBY administrator';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->where('is_admin', true)->first();
        if (! $user) {
            $this->error('Existing administrator not found. No account was changed.');

            return self::FAILURE;
        }
        $password = $this->secret('New password (at least 12 characters)');
        $confirmation = $this->secret('Confirm new password');
        if (! is_string($password) || strlen($password) < 12 || $password !== $confirmation) {
            $this->error('Passwords must match and contain at least 12 characters.');

            return self::FAILURE;
        }
        $user->password = Hash::make($password);
        $user->save();
        $user->tokens()->delete();
        $this->info('Administrator password updated and existing API tokens revoked.');

        return self::SUCCESS;
    }
}
