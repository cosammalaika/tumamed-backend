<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PatientUsersSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => User::ROLE_PATIENT, 'guard_name' => 'web']);

        $users = [
            ['name' => 'Patient One', 'email' => 'patient1@tumamed.com'],
            ['name' => 'Patient Two', 'email' => 'patient2@tumamed.com'],
            ['name' => 'Patient Three', 'email' => 'patient3@tumamed.com'],
        ];

        foreach ($users as $row) {
            $user = User::query()->updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'password' => Hash::make('Patient@12345'),
                    'role' => User::ROLE_PATIENT,
                    'is_active' => true,
                    'pharmacy_id' => null,
                ]
            );

            $user->syncRoles([User::ROLE_PATIENT]);
        }
    }
}

