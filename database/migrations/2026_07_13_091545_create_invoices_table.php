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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); //
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete(); //
            $table->date('invoice_date'); //
            $table->enum('payment_mode', ['cash', 'bank', 'credit'])->default('credit'); //
            $table->decimal('sub_total', 12, 2)->default(0); //
            $table->decimal('discount', 12, 2)->default(0); //
            $table->decimal('grand_total', 12, 2)->default(0); //
            $table->decimal('amount_paid', 12, 2)->default(0); //
            $table->string('ref_no')->nullable();
            $table->enum('status', ['unpaid', 'partial', 'paid'])->default('unpaid'); //
            $table->text('notes')->nullable(); //
            // $table->foreignId('created_by')->nullable()->constrained('users'); //
            $table->timestamps();
            $table->softDeletes(); //

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
