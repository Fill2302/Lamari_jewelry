<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_destination')->default('unassigned')->after('payment_method')->index();
        });
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('payment_destination')->default('unassigned')->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['payment_destination']);
            $table->dropColumn('payment_destination');
        });
        Schema::table('order_items', fn (Blueprint $table) => $table->dropColumn('payment_destination'));
    }
};
