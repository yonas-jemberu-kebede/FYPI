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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('tx_ref')->unique();
            $table->decimal('amount', 8, 2);
            $table->string('currency')->default('ETB');
            $table->string('status')->default('pending');
            $table->string('payable_type');
            $table->unsignedBigInteger('payable_id')->nullable();
            $table->string('checkout_url')->nullable();
            $table->foreignId('patient_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
