<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('first_name', 60)->nullable()->after('customer_name');
            $table->string('last_name', 60)->nullable()->after('first_name');
        });

        DB::table('orders')->orderBy('id')->each(function (object $order): void {
            $parts = preg_split('/\s+/u', trim((string) $order->customer_name), 2);

            DB::table('orders')->where('id', $order->id)->update([
                'first_name' => $parts[0] ?? '',
                'last_name' => $parts[1] ?? '',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name']);
        });
    }
};
