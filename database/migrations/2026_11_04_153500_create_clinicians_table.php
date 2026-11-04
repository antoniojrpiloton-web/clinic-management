<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinicians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name', 120);
            $table->string('type', 30)->default('Doctor')->index();
            $table->string('title', 100)->default('School Physician');
            $table->string('license_number', 60)->nullable()->index();
            $table->string('ptr_number', 60)->nullable();
            $table->string('specialization', 120)->nullable();
            $table->string('phone', 35)->nullable();
            $table->string('email', 120)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinicians');
    }
};
