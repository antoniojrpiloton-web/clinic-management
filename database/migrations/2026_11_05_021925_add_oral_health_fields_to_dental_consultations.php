<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dental_consultations', function (Blueprint $table): void {
            $table->text('previous_dental_treatment')->nullable()->after('oral_hygiene_status');
            $table->string('dental_treatment_frequency', 30)->nullable()->after('previous_dental_treatment');
            $table->date('last_dental_visit')->nullable()->after('dental_treatment_frequency');
            $table->boolean('dental_fears')->default(false)->after('last_dental_visit');
            $table->text('dental_concerns')->nullable()->after('dental_fears');

            $table->boolean('has_toothache')->default(false)->after('dental_concerns');
            $table->boolean('has_bleeding_gums')->default(false)->after('has_toothache');
            $table->boolean('has_bad_breath')->default(false)->after('has_bleeding_gums');
            $table->boolean('has_tooth_sensitivity')->default(false)->after('has_bad_breath');
            $table->boolean('has_jaw_pain')->default(false)->after('has_tooth_sensitivity');
            $table->boolean('has_loose_teeth')->default(false)->after('has_jaw_pain');
            $table->boolean('has_swelling')->default(false)->after('has_loose_teeth');

            $table->boolean('systemic_diabetes')->default(false)->after('has_swelling');
            $table->boolean('systemic_hypertension')->default(false)->after('systemic_diabetes');
            $table->boolean('systemic_heart_disease')->default(false)->after('systemic_hypertension');
            $table->boolean('systemic_bleeding_disorder')->default(false)->after('systemic_heart_disease');
            $table->boolean('systemic_allergies')->default(false)->after('systemic_bleeding_disorder');
            $table->string('systemic_allergy_details', 200)->nullable()->after('systemic_allergies');

            $table->string('soft_tissue_lips', 50)->nullable()->after('systemic_allergy_details');
            $table->string('soft_tissue_cheeks', 50)->nullable()->after('soft_tissue_lips');
            $table->string('soft_tissue_tongue', 50)->nullable()->after('soft_tissue_cheeks');
            $table->string('soft_tissue_palate', 50)->nullable()->after('soft_tissue_tongue');
            $table->string('soft_tissue_gingiva', 50)->nullable()->after('soft_tissue_palate');
            $table->string('soft_tissue_floor_of_mouth', 50)->nullable()->after('soft_tissue_gingiva');

            $table->string('periodontal_status', 30)->nullable()->after('soft_tissue_floor_of_mouth');
            $table->string('calculus_index', 20)->nullable()->after('periodontal_status');
            $table->string('gingival_index', 20)->nullable()->after('calculus_index');
            $table->string('plaque_index', 20)->nullable()->after('gingival_index');

            $table->tinyInteger('dmft_decayed')->unsigned()->nullable()->after('plaque_index');
            $table->tinyInteger('dmft_missing')->unsigned()->nullable()->after('dmft_decayed');
            $table->tinyInteger('dmft_filled')->unsigned()->nullable()->after('dmft_missing');
            $table->tinyInteger('dmfs_decayed')->unsigned()->nullable()->after('dmft_filled');
            $table->tinyInteger('dmfs_missing')->unsigned()->nullable()->after('dmfs_decayed');
            $table->tinyInteger('dmfs_filled')->unsigned()->nullable()->after('dmfs_missing');

            $table->text('treatment_plan')->nullable()->after('dmfs_filled');
            $table->string('priority_level', 20)->nullable()->after('treatment_plan');
            $table->json('primary_tooth_chart')->nullable()->after('priority_level');
        });
    }

    public function down(): void
    {
        Schema::table('dental_consultations', function (Blueprint $table): void {
            $table->dropColumn([
                'previous_dental_treatment',
                'dental_treatment_frequency',
                'last_dental_visit',
                'dental_fears',
                'dental_concerns',
                'has_toothache',
                'has_bleeding_gums',
                'has_bad_breath',
                'has_tooth_sensitivity',
                'has_jaw_pain',
                'has_loose_teeth',
                'has_swelling',
                'systemic_diabetes',
                'systemic_hypertension',
                'systemic_heart_disease',
                'systemic_bleeding_disorder',
                'systemic_allergies',
                'systemic_allergy_details',
                'soft_tissue_lips',
                'soft_tissue_cheeks',
                'soft_tissue_tongue',
                'soft_tissue_palate',
                'soft_tissue_gingiva',
                'soft_tissue_floor_of_mouth',
                'periodontal_status',
                'calculus_index',
                'gingival_index',
                'plaque_index',
                'dmft_decayed',
                'dmft_missing',
                'dmft_filled',
                'dmfs_decayed',
                'dmfs_missing',
                'dmfs_filled',
                'treatment_plan',
                'priority_level',
                'primary_tooth_chart',
            ]);
        });
    }
};
