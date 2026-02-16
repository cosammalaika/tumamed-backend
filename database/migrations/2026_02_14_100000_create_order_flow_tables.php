<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pharmacies', function (Blueprint $table) {
            $table->decimal('rating_avg', 3, 2)->default(0)->after('longitude');
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('hospital_id')->constrained('hospitals');
            $table->decimal('user_lat', 10, 7)->nullable();
            $table->decimal('user_lng', 10, 7)->nullable();
            $table->boolean('is_self_patient')->default(false);
            $table->string('patient_name');
            $table->string('patient_phone', 32);
            $table->string('status', 40)->index();
            $table->unsignedInteger('search_radius_km')->default(5);
            $table->foreignUuid('matched_pharmacy_id')->nullable()->constrained('pharmacies')->nullOnDelete();
            $table->timestamps();

            $table->index(['hospital_id', 'status']);
            $table->index(['matched_pharmacy_id', 'status']);
        });

        Schema::create('order_prescriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('mime', 100);
            $table->timestamps();
        });

        Schema::create('order_pharmacy_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignUuid('pharmacy_id')->constrained('pharmacies')->cascadeOnDelete();
            $table->float('score');
            $table->string('status', 40)->index();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['order_id', 'pharmacy_id']);
            $table->index(['order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_pharmacy_requests');
        Schema::dropIfExists('order_prescriptions');
        Schema::dropIfExists('orders');

        Schema::table('pharmacies', function (Blueprint $table) {
            $table->dropColumn('rating_avg');
        });
    }
};
