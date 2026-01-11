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
        Schema::create('product_outs', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->date('date');
            $table->string('invoice')->unique();
            $table->decimal('total', 12, 2);
            $table->text('remark')->nullable();
            $table->string('casher');
            $table->boolean('isDeleted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_outs');
    }
};
