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
        Schema::create('disease_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('name', 255);
            $table->string('classification', 30)->default('non_communicable')->index();
            $table->text('keywords')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::table('medical_consultations', function (Blueprint $table) {
            $table->foreignId('disease_category_id')
                ->nullable()
                ->after('clinic_visit_id')
                ->constrained('disease_categories')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medical_consultations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('disease_category_id');
        });

        Schema::dropIfExists('disease_categories');
    }
};
