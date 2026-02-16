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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('actor_type')->nullable();
            $table->uuid('actor_id')->nullable()->index();
            $table->string('actor_name')->nullable();
            $table->string('actor_phone')->nullable();
            $table->string('action')->index();
            $table->string('subject_type')->nullable()->index();
            $table->uuid('subject_id')->nullable()->index();
            $table->string('route')->nullable();
            $table->string('method')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->jsonb('meta')->nullable();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
