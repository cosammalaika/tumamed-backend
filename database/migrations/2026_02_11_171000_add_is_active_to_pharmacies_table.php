<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pharmacies', function (Blueprint $table): void {
            if (! Schema::hasColumn('pharmacies', 'is_active')) {
                $table->boolean('is_active')->default(true)->index()->after('longitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pharmacies', function (Blueprint $table): void {
            if (Schema::hasColumn('pharmacies', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};

