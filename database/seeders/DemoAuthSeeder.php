<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoAuthSeeder extends Seeder
{
    /**
     * Seed demo credentials for local development.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@tumamed.com'],
            [
                'name' => 'TumaMed Admin',
                'password' => 'Admin@12345',
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
            ]
        );

        $admin->syncRoles([User::ROLE_ADMIN]);

        Patient::updateOrCreate(
            ['email' => 'patient@tumamed.com'],
            [
                'name' => 'Demo Patient',
                'password' => 'Patient@12345',
                'phone' => '+250700000001',
            ]
        );
    }
}
