<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('appointment_number', 40)->unique();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('clinician_id')->nullable()->constrained('clinicians')->nullOnDelete();
            $table->foreignId('booked_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('clinic_visit_id')->nullable()->constrained('clinic_visits')->nullOnDelete();
            $table->date('appointment_date')->index();
            $table->string('appointment_time', 20)->default('09:00 AM');
            $table->string('service_type', 40)->default('medical')->index();
            $table->string('priority_level', 20)->default('normal')->index();
            $table->string('reason', 255);
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('scheduled')->index();
            $table->string('cancellation_reason', 255)->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['appointment_date', 'status']);
            $table->index(['clinician_id', 'appointment_date']);
            $table->index(['patient_id', 'appointment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
