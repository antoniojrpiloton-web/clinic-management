<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('physical_exam_schedule_attendees');
        Schema::dropIfExists('first_aid_records');
        Schema::dropIfExists('emergency_records');
        Schema::dropIfExists('dental_consultations');
        Schema::dropIfExists('medical_consultations');
        Schema::dropIfExists('clinic_visits');
        Schema::dropIfExists('physical_exam_schedules');

        Schema::create('physical_exam_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('schedule_number', 50)->unique();
            $table->string('title', 150);
            $table->string('purpose', 80)->default('Annual Physical Exam');
            $table->string('target_classification', 30)->default('student')->index();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignId('college_id')->nullable()->constrained('colleges')->nullOnDelete();
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->string('year_level', 30)->nullable();
            $table->string('office_department', 120)->nullable();
            $table->date('scheduled_date')->index();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('venue', 150)->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->foreignId('clinician_id')->nullable()->constrained('clinicians')->nullOnDelete();
            $table->string('status', 20)->default('scheduled')->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('clinic_visits', function (Blueprint $table) {
            $table->id();
            $table->string('visit_number', 50)->unique();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->string('visit_type', 30)->index();
            $table->string('visit_category', 30)->default('walk_in');
            $table->string('patient_classification', 30)->index();
            $table->string('classification_label', 255)->nullable();
            $table->foreignId('college_id')->nullable()->constrained('colleges')->nullOnDelete();
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->string('year_level', 30)->nullable();
            $table->string('office_department', 120)->nullable();
            $table->dateTime('visit_date')->index();
            $table->dateTime('time_out')->nullable();
            $table->text('chief_complaint')->nullable();
            $table->unsignedSmallInteger('systolic')->nullable();
            $table->unsignedSmallInteger('diastolic')->nullable();
            $table->unsignedSmallInteger('pulse_rate')->nullable();
            $table->unsignedSmallInteger('respiratory_rate')->nullable();
            $table->decimal('temperature', 4, 1)->nullable();
            $table->unsignedSmallInteger('oxygen_saturation')->nullable();
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->decimal('height_cm', 5, 2)->nullable();
            $table->unsignedTinyInteger('pain_scale')->nullable();
            $table->foreignId('clinician_id')->nullable()->constrained('clinicians')->nullOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('disposition', 40)->nullable();
            $table->string('status', 20)->default('completed')->index();
            $table->text('remarks')->nullable();
            $table->foreignId('physical_examination_id')->nullable()->constrained('physical_examinations')->nullOnDelete();
            $table->foreignId('medicine_dispensing_id')->nullable()->constrained('medicine_dispensings')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('medical_consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_visit_id')->unique()->constrained('clinic_visits')->cascadeOnDelete();
            $table->text('history_of_present_illness')->nullable();
            $table->text('physical_findings')->nullable();
            $table->text('diagnosis');
            $table->string('icd_code', 20)->nullable();
            $table->text('treatment_plan')->nullable();
            $table->text('medications_prescribed')->nullable();
            $table->text('advice')->nullable();
            $table->boolean('medical_certificate_issued')->default(false);
            $table->unsignedSmallInteger('rest_days')->nullable();
            $table->string('referred_to', 150)->nullable();
            $table->text('referral_reason')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->timestamps();
        });

        Schema::create('dental_consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_visit_id')->unique()->constrained('clinic_visits')->cascadeOnDelete();
            $table->text('oral_examination_findings')->nullable();
            $table->json('tooth_chart')->nullable();
            $table->json('procedures')->nullable();
            $table->string('teeth_involved', 150)->nullable();
            $table->string('anesthesia_used', 120)->nullable();
            $table->text('diagnosis');
            $table->text('treatment_rendered')->nullable();
            $table->text('recommendations')->nullable();
            $table->string('oral_hygiene_status', 30)->nullable();
            $table->date('follow_up_date')->nullable();
            $table->timestamps();
        });

        Schema::create('emergency_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_visit_id')->unique()->constrained('clinic_visits')->cascadeOnDelete();
            $table->dateTime('incident_datetime')->nullable();
            $table->string('incident_location', 150)->nullable();
            $table->string('emergency_nature', 60);
            $table->string('triage_level', 20)->index();
            $table->string('level_of_consciousness', 20)->nullable();
            $table->unsignedTinyInteger('gcs_score')->nullable();
            $table->text('incident_description')->nullable();
            $table->json('interventions')->nullable();
            $table->text('intervention_notes')->nullable();
            $table->boolean('ambulance_called')->default(false);
            $table->boolean('transferred_to_hospital')->default(false);
            $table->string('hospital_name', 150)->nullable();
            $table->string('transport_mode', 60)->nullable();
            $table->dateTime('transferred_at')->nullable();
            $table->boolean('guardian_notified')->default(false);
            $table->string('notified_person', 120)->nullable();
            $table->dateTime('notified_at')->nullable();
            $table->string('incident_report_number', 60)->nullable();
            $table->string('outcome', 60)->nullable();
            $table->timestamps();
        });

        Schema::create('first_aid_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_visit_id')->unique()->constrained('clinic_visits')->cascadeOnDelete();
            $table->string('injury_type', 60);
            $table->string('body_part', 100)->nullable();
            $table->string('incident_location', 150)->nullable();
            $table->string('incident_cause', 190)->nullable();
            $table->json('first_aid_given')->nullable();
            $table->text('treatment_notes')->nullable();
            $table->text('supplies_used')->nullable();
            $table->boolean('was_referred')->default(false);
            $table->string('referred_to', 150)->nullable();
            $table->timestamps();
        });

        Schema::create('physical_exam_schedule_attendees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('physical_exam_schedule_id');
            $table->unsignedBigInteger('patient_id');
            $table->string('status', 20)->default('pending')->index();
            $table->unsignedBigInteger('physical_examination_id')->nullable();
            $table->unsignedBigInteger('clinic_visit_id')->nullable();
            $table->dateTime('attended_at')->nullable();
            $table->timestamps();

            $table->foreign('physical_exam_schedule_id', 'fk_pes_att_sched')->references('id')->on('physical_exam_schedules')->cascadeOnDelete();
            $table->foreign('patient_id', 'fk_pes_att_pat')->references('id')->on('patients')->cascadeOnDelete();
            $table->foreign('physical_examination_id', 'fk_pes_att_pe')->references('id')->on('physical_examinations')->nullOnDelete();
            $table->foreign('clinic_visit_id', 'fk_pes_att_vis')->references('id')->on('clinic_visits')->nullOnDelete();
            $table->unique(['physical_exam_schedule_id', 'patient_id'], 'pes_att_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('physical_exam_schedule_attendees');
        Schema::dropIfExists('first_aid_records');
        Schema::dropIfExists('emergency_records');
        Schema::dropIfExists('dental_consultations');
        Schema::dropIfExists('medical_consultations');
        Schema::dropIfExists('clinic_visits');
        Schema::dropIfExists('physical_exam_schedules');
    }
};
