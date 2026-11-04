<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->unique()->constrained('patients')->cascadeOnDelete();
            $table->json('past_medical_history')->nullable();
            $table->text('past_medical_history_notes')->nullable();
            $table->json('family_medical_history')->nullable();
            $table->text('family_medical_history_notes')->nullable();
            $table->text('allergies_food')->nullable();
            $table->text('allergies_drugs')->nullable();
            $table->text('allergies_others')->nullable();
            $table->text('existing_conditions')->nullable();
            $table->text('current_medications')->nullable();
            $table->json('hospitalization_history')->nullable();
            $table->json('surgical_history')->nullable();
            $table->string('smoking_status', 30)->nullable();
            $table->string('alcohol_status', 30)->nullable();
            $table->json('immunizations')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('physical_examinations', function (Blueprint $table) {
            $table->id();
            $table->string('pe_number', 50)->unique();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignId('examiner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('clinician_id')->nullable()->constrained('clinicians')->nullOnDelete();
            $table->string('examiner_name', 120)->nullable();
            $table->string('examiner_license_number', 50)->nullable();
            $table->date('exam_date');
            $table->string('purpose', 80)->default('Annual Physical Exam');
            $table->unsignedSmallInteger('systolic')->nullable();
            $table->unsignedSmallInteger('diastolic')->nullable();
            $table->unsignedSmallInteger('pulse_rate')->nullable();
            $table->unsignedSmallInteger('respiratory_rate')->nullable();
            $table->decimal('temperature', 4, 1)->nullable();
            $table->unsignedSmallInteger('oxygen_saturation')->nullable();
            $table->decimal('height_cm', 5, 2)->nullable();
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->decimal('bmi', 4, 2)->nullable();
            $table->string('bmi_category', 30)->nullable();
            $table->string('visual_acuity_right', 20)->nullable()->default('20/20');
            $table->string('visual_acuity_left', 20)->nullable()->default('20/20');
            $table->boolean('visual_acuity_is_corrected')->default(false);
            $table->string('color_vision', 30)->nullable()->default('Normal');
            $table->string('hearing', 30)->nullable()->default('Normal');
            $table->string('general_appearance', 120)->nullable()->default('Conscious, coherent, ambulatory, well-nourished');
            $table->json('skin')->nullable();
            $table->json('heent')->nullable();
            $table->json('neck')->nullable();
            $table->json('chest_lungs')->nullable();
            $table->json('heart')->nullable();
            $table->json('abdomen')->nullable();
            $table->json('musculoskeletal')->nullable();
            $table->json('neurological')->nullable();
            $table->json('dental')->nullable();
            $table->string('chest_xray_findings', 190)->nullable();
            $table->date('chest_xray_date')->nullable();
            $table->text('laboratory_notes')->nullable();
            $table->text('clinical_findings')->nullable();
            $table->text('medical_recommendations')->nullable();
            $table->string('fitness_assessment', 30)->nullable()->default('Class A');
            $table->json('fitness_activities')->nullable();
            $table->string('status', 20)->default('completed');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('physical_examinations');
        Schema::dropIfExists('medical_profiles');
    }
};
