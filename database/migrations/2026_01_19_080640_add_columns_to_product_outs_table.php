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
        Schema::table('product_outs', function (Blueprint $table) {
            $table->decimal('money_received', 12, 2)->after('total');
            $table->decimal('discount', 12, 2)->after('money_received');
            $table->decimal('return', 12, 2)->after('discount');
            $table->string('payment_method')->after('return');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_outs', function (Blueprint $table) {
            $table->dropColumn(['money_received', 'discount', 'return', 'payment_method']);
        });
    }
};
