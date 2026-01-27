<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

public function up(): void
{
    Schema::table('product_outs', function (Blueprint $table) {
        $table->unsignedBigInteger('user_id')->nullable();
        $table->unsignedBigInteger('session_cashier_id')->nullable();
    });

    // Isi data existing dengan user_id default (misalnya user pertama) jika diperlukan
    // Contoh: DB::table('product_outs')->whereNull('user_id')->update(['user_id' => 1]);

    Schema::table('product_outs', function (Blueprint $table) {
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        $table->foreign('session_cashier_id')->references('id')->on('cashier_sessions')->onDelete('cascade');
    });
}

public function down(): void
{
    Schema::table('product_outs', function (Blueprint $table) {
        $table->dropForeign(['user_id']);
        $table->dropForeign(['session_cashier_id']);
        $table->dropColumn(['user_id', 'session_cashier_id']);
    });
}
};
