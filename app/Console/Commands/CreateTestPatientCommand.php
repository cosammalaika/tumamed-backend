<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class CreateTestPatientCommand extends Command
{
    protected $signature = 'patient:create-test
        {--name=Demo Patient : Patient full name}
        {--email=patient@tumamed.com : Patient email}
        {--password=Patient@12345 : Patient password}
        {--phone=+250700000001 : Patient phone number}';

    protected $description = 'Create or update a default test patient account in users table';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->option('email')));

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => (string) $this->option('name'),
                'password' => (string) $this->option('password'),
                'role' => User::ROLE_PATIENT,
                'is_active' => true,
                'pharmacy_id' => null,
                'phone' => (string) $this->option('phone'),
            ]
        );

        Role::firstOrCreate(['name' => User::ROLE_PATIENT, 'guard_name' => 'web']);
        $user->syncRoles([User::ROLE_PATIENT]);

        $this->info('Test patient is ready.');
        $this->line('ID: '.$user->id);
        $this->line('Email: '.$user->email);
        $this->line('Role: '.$user->role);

        return self::SUCCESS;
    }
}
