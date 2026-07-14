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
        Schema::create('lot_items', function (Blueprint $table) {
            $table->id();
             $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->string('item');
            $table->string('brand')->nullable();
            $table->string('description')->nullable(); // e.g. "Mall waala"
            $table->unsignedInteger('qty_purchased');
            $table->unsignedInteger('qty_available'); // decremented on sale, incremented on return
            $table->decimal('cost_price', 10, 2); // cost per unit
            $table->decimal('retail_price', 10, 2); // default selling price per unit
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lot_items');
    }
};
