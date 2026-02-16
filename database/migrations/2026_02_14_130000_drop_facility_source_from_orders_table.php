<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'facility_source')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('facility_source');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders') && ! Schema::hasColumn('orders', 'facility_source')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('facility_source', 12)->nullable()->after('hospital_id');
            });
        }
    }
};
