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
        Schema::create('product_out_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_out_id')
                ->constrained('product_outs')
                ->cascadeOnDelete();
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();
            $table->integer('quantity');
            $table->decimal('price', 12, 2);
            $table->decimal('total_price', 12, 2);
            $table->boolean('isDeleted')->default(false);
            $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
            Schema::dropIfExists('product_out_details');
    }
};
