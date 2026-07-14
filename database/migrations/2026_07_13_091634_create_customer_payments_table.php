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
        Schema::create('customer_payments', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique(); //
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete(); //
            $table->date('payment_date'); //
            $table->decimal('amount_received', 12, 2); //
            $table->enum('payment_mode', ['cash', 'bank transfer', 'cheque'])->default('cash'); //
            $table->text('notes')->nullable(); //
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_payments');
    }
};
