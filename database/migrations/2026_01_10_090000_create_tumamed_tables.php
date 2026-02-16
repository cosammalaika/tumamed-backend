<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hospitals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('type')->nullable()->index();
            $table->string('town')->nullable()->index();
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('pharmacies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->string('town')->nullable()->index();
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_verified')->default(false)->index();
            $table->boolean('is_open')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('hospital_pharmacies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('hospital_id');
            $table->uuid('pharmacy_id');
            $table->unsignedInteger('priority')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['hospital_id', 'pharmacy_id']);
            $table->foreign('hospital_id')->references('id')->on('hospitals')->cascadeOnDelete();
            $table->foreign('pharmacy_id')->references('id')->on('pharmacies')->cascadeOnDelete();
        });

        Schema::create('patients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('phone')->unique();
            $table->timestamps();
        });

        Schema::create('medicine_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('patient_id');
            $table->uuid('hospital_id');
            $table->uuid('current_pharmacy_id')->nullable();
            $table->string('status')->default('PENDING')->index();
            $table->text('request_text');
            $table->string('prescription_image_path')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->foreign('patient_id')->references('id')->on('patients')->cascadeOnDelete();
            $table->foreign('hospital_id')->references('id')->on('hospitals')->cascadeOnDelete();
            $table->foreign('current_pharmacy_id')->references('id')->on('pharmacies')->nullOnDelete();
            $table->index(['hospital_id', 'status']);
        });

        Schema::create('request_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('medicine_request_id');
            $table->uuid('pharmacy_id');
            $table->unsignedInteger('attempt_no');
            $table->string('status')->default('SENT')->index();
            $table->text('reason')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['medicine_request_id', 'attempt_no']);
            $table->index(['medicine_request_id', 'pharmacy_id']);
            $table->foreign('medicine_request_id')->references('id')->on('medicine_requests')->cascadeOnDelete();
            $table->foreign('pharmacy_id')->references('id')->on('pharmacies')->cascadeOnDelete();
        });

        Schema::create('request_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('medicine_request_id');
            $table->string('type')->index();
            $table->text('details')->nullable();
            $table->uuid('from_pharmacy_id')->nullable();
            $table->uuid('to_pharmacy_id')->nullable();
            $table->uuid('actor_user_id')->nullable();
            $table->timestamps();

            $table->index(['medicine_request_id', 'type']);
            $table->foreign('medicine_request_id')->references('id')->on('medicine_requests')->cascadeOnDelete();
            $table->foreign('from_pharmacy_id')->references('id')->on('pharmacies')->nullOnDelete();
            $table->foreign('to_pharmacy_id')->references('id')->on('pharmacies')->nullOnDelete();
            $table->foreign('actor_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_events');
        Schema::dropIfExists('request_assignments');
        Schema::dropIfExists('medicine_requests');
        Schema::dropIfExists('patients');
        Schema::dropIfExists('hospital_pharmacies');
        Schema::dropIfExists('pharmacies');
        Schema::dropIfExists('hospitals');
    }
};
