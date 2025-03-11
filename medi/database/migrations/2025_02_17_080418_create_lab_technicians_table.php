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
        Schema::create('lab_technicians', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            // Each lab technician is assigned to a diagnostic center
            $table->unsignedBigInteger('diagnostic_center_id');
            $table->enum('gender', ['Male', 'Female']);
            // Phone number for lab technician contact
            $table->string('phone_number');
            $table->date('date_of_birth');
            $table->string('shift_day');
            $table->time('shift_start');
            $table->time('shift_end');
            $table->timestamps();

            $table->foreign('diagnostic_center_id')->references('id')->on('diagnostic_centers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_technicians');
    }
};
