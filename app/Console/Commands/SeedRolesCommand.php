<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SeedRolesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tumamed:seed-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed base roles and permissions for TumaMed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->call('db:seed', [
            '--class' => \Database\Seeders\RolesAndPermissionsSeeder::class,
        ]);

        $this->info('Roles and permissions seeded.');
    }
}
