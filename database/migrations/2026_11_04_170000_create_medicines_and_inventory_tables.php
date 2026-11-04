<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicine_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 30)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->foreignId('category_id')->constrained('medicine_categories');
            $table->string('generic_name', 150)->index();
            $table->string('brand_name', 150)->nullable()->index();
            $table->string('dosage_form', 60);
            $table->string('strength', 60)->nullable();
            $table->string('unit', 40)->default('tablet');
            $table->unsignedInteger('reorder_level')->default(50);
            $table->text('description')->nullable();
            $table->text('side_effects')->nullable();
            $table->string('storage_conditions', 150)->nullable();
            $table->boolean('is_prescription_required')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('medicine_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained('medicines')->cascadeOnDelete();
            $table->string('batch_number', 80)->index();
            $table->string('supplier', 150)->nullable();
            $table->date('expiration_date')->index();
            $table->unsignedInteger('quantity_received');
            $table->unsignedInteger('quantity_remaining')->default(0)->index();
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->date('received_date');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('medicine_dispensings', function (Blueprint $table) {
            $table->id();
            $table->string('dispensing_number', 60)->unique();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('clinician_id')->nullable()->constrained('clinicians')->nullOnDelete();
            $table->foreignId('dispensed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('dispensed_date')->index();
            $table->string('diagnosis', 255)->nullable();
            $table->text('instructions')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedSmallInteger('total_items')->default(1);
            $table->timestamps();
        });

        Schema::create('medicine_dispensing_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispensing_id')->constrained('medicine_dispensings')->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained('medicines');
            $table->foreignId('batch_id')->nullable()->constrained('medicine_batches')->nullOnDelete();
            $table->unsignedInteger('quantity');
            $table->string('dosage_instruction', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number', 60)->unique();
            $table->foreignId('medicine_id')->constrained('medicines')->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('medicine_batches')->nullOnDelete();
            $table->string('type', 40)->index();
            $table->integer('quantity');
            $table->integer('quantity_before');
            $table->integer('quantity_after');
            $table->string('reference_number', 80)->nullable();
            $table->string('reason', 255);
            $table->foreignId('patient_id')->nullable()->constrained('patients')->nullOnDelete();
            $table->foreignId('clinician_id')->nullable()->constrained('clinicians')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('transaction_date')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('medicine_dispensing_items');
        Schema::dropIfExists('medicine_dispensings');
        Schema::dropIfExists('medicine_batches');
        Schema::dropIfExists('medicines');
        Schema::dropIfExists('medicine_categories');
    }
};

