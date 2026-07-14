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
        Schema::create('customer_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete(); //
            $table->date('transaction_date'); //
            $table->string('description'); //
            $table->enum('type', ['opening_balance', 'invoice', 'payment']); //
            $table->decimal('debit', 12, 2)->default(0); //
            $table->decimal('credit', 12, 2)->default(0); //
            $table->decimal('balance', 12, 2); //
            $table->nullableMorphs('reference'); //
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_ledgers');
    }
};
