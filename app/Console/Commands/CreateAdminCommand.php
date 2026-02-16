<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tumamed:create-admin {email} {password} {--name=Admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');
        $name = $this->option('name') ?? 'Admin';

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => $password,
                'role' => User::ROLE_ADMIN,
            ]
        );

        $user->syncRoles([User::ROLE_ADMIN]);

        app(\App\Services\AuditLogger::class)->log('ADMIN_CREATE_USER', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => User::ROLE_ADMIN,
        ], $user, ['type' => 'SYSTEM', 'name' => 'Console']);

        $this->info("Admin user ready: {$user->email}");
    }
}
