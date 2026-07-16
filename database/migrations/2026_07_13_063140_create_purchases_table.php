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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
             $table->string('lot_number')->unique(); // e.g. LOT-105
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->date('purchase_date');
            $table->decimal('lot_price', 12, 2)->default(0); // total amount agreed with supplier
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('total_lot_item_quantity', 12, 2)->default(0);
            $table->decimal('balance_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            // $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
             $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
