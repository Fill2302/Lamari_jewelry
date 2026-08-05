<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('salesdrive_order_id')->nullable()->unique();
            $table->timestamp('salesdrive_created_at')->nullable();
            $table->timestamp('salesdrive_paid_at')->nullable();
            $table->text('salesdrive_sync_error')->nullable();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('salesdrive_payment_id')->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('payments', fn (Blueprint $table) => $table->dropColumn('salesdrive_payment_id'));
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['salesdrive_order_id', 'salesdrive_created_at', 'salesdrive_paid_at', 'salesdrive_sync_error']);
        });
    }
};
