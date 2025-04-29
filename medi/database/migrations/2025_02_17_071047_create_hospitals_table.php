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
        Schema::create('hospitals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Official email for hospital communication
            $table->string('email')->unique();
            $table->text('address');
            $table->string('phone_number');
            $table->string('account');

            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->decimal('latitude', 8, 2)->nullable();
            $table->decimal('longitude', 8, 2)->nullable();

            $table->integer('icu_capacity')->nullable();
            $table->integer('established_year')->nullable();
            $table->string('operating_hours')->nullable();
            $table->string('hospital_type')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospitals');
    }
};
