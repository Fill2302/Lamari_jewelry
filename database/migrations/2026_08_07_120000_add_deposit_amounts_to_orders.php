<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->unsignedBigInteger('prepaid_amount')->default(0)->after('total_amount');
            $table->unsignedBigInteger('cod_amount')->default(0)->after('prepaid_amount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn(['prepaid_amount', 'cod_amount']));
    }
};
