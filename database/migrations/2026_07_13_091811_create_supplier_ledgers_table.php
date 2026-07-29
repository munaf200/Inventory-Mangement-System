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
        Schema::create('supplier_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->date('transaction_date');
            $table->string('voucher_no')->nullable(); // Invoice # or Payment Voucher #
            $table->string('description');
            $table->enum('type', ['opening_balance', 'purchase', 'payment', 'purchase_return']);
            
            // Debit = Payment Given / Udhaar Kam Hua (-)
            $table->decimal('debit', 12, 2)->default(0); 
            
            // Credit = Bill / Udhaar Barha (+)
            $table->decimal('credit', 12, 2)->default(0); 
            
            // Running balance after this transaction
            $table->decimal('balance', 12, 2); 
            
            // Polymorphic link to Purchases or SupplierPayments
            $table->nullableMorphs('reference'); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_ledgers');
    }
};
