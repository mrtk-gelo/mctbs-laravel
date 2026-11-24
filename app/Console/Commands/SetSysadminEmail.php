<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class SetSysadminEmail extends Command
{
    protected $signature = 'mctbs:set-sysadmin-email {email? : Defaults to MCTBS_SYSAD_EMAIL}';

    protected $description = 'Set the System Admin (Account Admin) account email to the official MCTBS email.';

    public function handle(): int
    {
        $email = (string) ($this->argument('email') ?: config('services.mctbs.sysad_email'));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Provide a valid email or set MCTBS_SYSAD_EMAIL.');

            return self::FAILURE;
        }

        $role = Role::where('name', 'Account Admin')->firstOrFail();
        $sysadmins = User::where('role_id', $role->role_id)->get();

        if ($sysadmins->count() !== 1) {
            $this->error("Expected exactly one Account Admin, found {$sysadmins->count()}.");

            return self::FAILURE;
        }

        if (User::where('email', $email)->whereKeyNot($sysadmins->first()->getKey())->exists()) {
            $this->error('That email already belongs to another account.');

            return self::FAILURE;
        }

        $sysadmins->first()->update(['email' => $email, 'email_verified_at' => now()]);
        $this->info("System Admin email set to {$email}.");

        return self::SUCCESS;
    }
}
