<?php

namespace App\Console\Commands;

use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigratePatientsToUsersCommand extends Command
{
    protected $signature = 'migrate:patients-to-users {--dry-run : Show what would change without writing data}';

    protected $description = 'Copy patient auth accounts into users table as PATIENT role and sync role assignments';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        Role::firstOrCreate(['name' => User::ROLE_PATIENT, 'guard_name' => 'web']);

        $patients = Patient::query()
            ->whereNotNull('email')
            ->whereNotNull('password')
            ->get(['id', 'name', 'email', 'password', 'phone']);

        $created = 0;
        $linked = 0;
        $updatedRole = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            foreach ($patients as $patient) {
                $email = strtolower(trim((string) $patient->email));
                if ($email === '') {
                    $skipped++;
                    continue;
                }

                $existing = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

                if ($existing) {
                    if (strtoupper((string) $existing->role) !== User::ROLE_PATIENT) {
                        if (! $dryRun) {
                            $existing->update(['role' => User::ROLE_PATIENT]);
                        }
                        $updatedRole++;
                    }

                    if (! $dryRun) {
                        $existing->syncRoles([User::ROLE_PATIENT]);
                    }

                    if (DB::getSchemaBuilder()->hasColumn('patients', 'user_id')) {
                        if (! $dryRun) {
                            $patient->forceFill(['user_id' => $existing->id])->save();
                        }
                        $linked++;
                    }

                    continue;
                }

                if (! $dryRun) {
                    $user = User::query()->create([
                        'name' => (string) ($patient->name ?: 'Patient User'),
                        'email' => $email,
                        'phone' => $patient->phone,
                        'password' => (string) $patient->password,
                        'role' => User::ROLE_PATIENT,
                        'is_active' => true,
                        'pharmacy_id' => null,
                    ]);
                    $user->syncRoles([User::ROLE_PATIENT]);

                    if (DB::getSchemaBuilder()->hasColumn('patients', 'user_id')) {
                        $patient->forceFill(['user_id' => $user->id])->save();
                        $linked++;
                    }
                }

                $created++;
            }

            if ($dryRun) {
                DB::rollBack();
            } else {
                DB::commit();
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Migration failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info($dryRun ? 'Dry run complete.' : 'Migration complete.');
        $this->line('Patients processed: '.$patients->count());
        $this->line('Users created: '.$created);
        $this->line('Users role-normalized to PATIENT: '.$updatedRole);
        $this->line('Patients linked to users: '.$linked);
        $this->line('Skipped: '.$skipped);

        return self::SUCCESS;
    }
}

