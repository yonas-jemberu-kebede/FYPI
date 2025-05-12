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
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('image');
            $table->integer('experience');
            $table->string('specialization')->default('General');
            $table->string('email')->unique();
            // Each doctor is linked to a hospital
            $table->unsignedBigInteger('hospital_id');
            // Gender of the doctor
            $table->enum('gender', ['Male', 'Female']);
            $table->string('phone_number');
            $table->date('date_of_birth');
            $table->timestamps();

            $table->foreign('hospital_id')->references('id')->on('hospitals')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
