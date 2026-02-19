<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('patients')
            ->whereNull('email')
            ->orderBy('id')
            ->get(['id'])
            ->each(function ($patient): void {
                DB::table('patients')
                    ->where('id', $patient->id)
                    ->update([
                        'email' => 'patient-'.$patient->id.'@placeholder.local',
                    ]);
            });

        DB::table('patients')
            ->whereNull('password')
            ->orderBy('id')
            ->get(['id'])
            ->each(function ($patient): void {
                DB::table('patients')
                    ->where('id', $patient->id)
                    ->update([
                        'password' => Hash::make(Str::random(40)),
                    ]);
            });

        DB::statement('ALTER TABLE patients ALTER COLUMN email SET NOT NULL');
        DB::statement('ALTER TABLE patients ALTER COLUMN password SET NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE patients ALTER COLUMN email DROP NOT NULL');
        DB::statement('ALTER TABLE patients ALTER COLUMN password DROP NOT NULL');
    }
};
