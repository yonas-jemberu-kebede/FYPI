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
        Schema::create('pharmacists', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            // Each pharmacist works at a specific pharmacy
            $table->unsignedBigInteger('pharmacy_id');
            // Gender of the pharmacist
            $table->enum('gender', ['Male', 'Female']);
            $table->string('phone_number');
            $table->date('date_of_birth');
            $table->string('shift_day');
            $table->time('shift_start');
            $table->time('shift_end');
            $table->timestamps();

            $table->foreign('pharmacy_id')->references('id')->on('pharmacies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacists');
    }
};
