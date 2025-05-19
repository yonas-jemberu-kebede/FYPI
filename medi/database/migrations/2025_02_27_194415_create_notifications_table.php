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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('notifiable_type');
            $table->unsignedBigInteger('notifiable_id');
            $table->json('data');
            $table->datetime('read_at')->default(null)->nullable();
            $table->string('status')->default('pending');
            // Add a generated column for appointment_id
            $table->unsignedBigInteger('appointment_id')->nullable()->stored()->generatedAs('JSON_UNQUOTE(JSON_EXTRACT(data, "$.appointment_id"))');
            $table->timestamps();
            // Unique constraint including the generated column
            $table->unique(['notifiable_type', 'notifiable_id', 'type', 'appointment_id'], 'unique_notification');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
