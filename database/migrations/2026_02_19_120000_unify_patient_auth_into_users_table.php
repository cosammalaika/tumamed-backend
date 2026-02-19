<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('phone', 32)->nullable()->after('email');
            });
        }

        if (! Schema::hasColumn('patients', 'user_id')) {
            Schema::table('patients', function (Blueprint $table): void {
                $table->uuid('user_id')->nullable()->after('id')->index();
            });
        }

        Role::firstOrCreate(['name' => User::ROLE_PATIENT, 'guard_name' => 'web']);

        $patients = DB::table('patients')
            ->whereNotNull('email')
            ->whereNotNull('password')
            ->get(['id', 'name', 'email', 'password', 'phone', 'user_id']);

        foreach ($patients as $patient) {
            $email = strtolower(trim((string) $patient->email));
            if ($email === '') {
                continue;
            }

            $existingUser = DB::table('users')->where('email', $email)->first(['id', 'role']);

            if ($existingUser) {
                DB::table('patients')->where('id', $patient->id)->update(['user_id' => $existingUser->id]);

                if (strtoupper((string) $existingUser->role) !== User::ROLE_PATIENT) {
                    continue;
                }

                DB::table('model_has_roles')
                    ->where('model_type', User::class)
                    ->where('model_id', $existingUser->id)
                    ->delete();

                DB::table('model_has_roles')->insertOrIgnore([
                    'role_id' => Role::query()->where('name', User::ROLE_PATIENT)->value('id'),
                    'model_type' => User::class,
                    'model_id' => $existingUser->id,
                ]);

                continue;
            }

            $userId = (string) Str::uuid();

            DB::table('users')->insert([
                'id' => $userId,
                'name' => (string) ($patient->name ?? 'Patient User'),
                'email' => $email,
                'phone' => $patient->phone ? (string) $patient->phone : null,
                'password' => (string) $patient->password,
                'role' => User::ROLE_PATIENT,
                'is_active' => true,
                'pharmacy_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('patients')->where('id', $patient->id)->update(['user_id' => $userId]);

            DB::table('model_has_roles')->insertOrIgnore([
                'role_id' => Role::query()->where('name', User::ROLE_PATIENT)->value('id'),
                'model_type' => User::class,
                'model_id' => $userId,
            ]);
        }

        Schema::table('patients', function (Blueprint $table): void {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('phone');
        });
    }
};
