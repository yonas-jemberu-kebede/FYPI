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
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->foreignId('pharmacist_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('test_id')->nullable()->constrained('tests')->onDelete('cascade');
            $table->foreignId('hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->foreignId('pharmacy_id')->constrained('pharmacies')->onDelete('cascade');

            $table->string('status')->default('pending');
            $table->json('medications'); // e.g., [{"name": "Aspirin", "dosage": "500mg", "frequency": "daily"}]
            $table->text('instructions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
