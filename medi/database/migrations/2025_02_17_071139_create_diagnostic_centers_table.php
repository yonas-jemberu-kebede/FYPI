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
        Schema::create('diagnostic_centers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('address');
            // Email for receiving test requests and communication
            $table->string('email')->unique();
            $table->unsignedBigInteger('hospital_id');
            // Phone number for diagnostic center contact
            $table->string('phone_number');

            $table->timestamps();

            $table->foreign('hospital_id')->references('id')->on('hospitals')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diagnostic_centers');
    }
};
