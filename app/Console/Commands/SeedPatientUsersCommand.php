<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SeedPatientUsersCommand extends Command
{
    protected $signature = 'patients:seed-defaults';

    protected $description = 'Seed default PATIENT users into users table';

    public function handle(): int
    {
        Role::firstOrCreate(['name' => User::ROLE_PATIENT, 'guard_name' => 'web']);

        $defaults = [
            ['name' => 'Patient One', 'email' => 'patient1@tumamed.com'],
            ['name' => 'Patient Two', 'email' => 'patient2@tumamed.com'],
            ['name' => 'Patient Three', 'email' => 'patient3@tumamed.com'],
        ];

        foreach ($defaults as $data) {
            $user = User::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('Patient@12345'),
                    'role' => User::ROLE_PATIENT,
                    'is_active' => true,
                    'pharmacy_id' => null,
                ]
            );

            $user->syncRoles([User::ROLE_PATIENT]);
        }

        $this->info('Seeded 3 PATIENT users in users table.');

        return self::SUCCESS;
    }
}

