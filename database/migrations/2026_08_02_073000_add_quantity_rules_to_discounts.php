<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discounts', function (Blueprint $table) {
            $table->string('mode')->default('standard')->after('percentage');
            $table->json('quantity_rules')->nullable()->after('mode');
        });
    }

    public function down(): void
    {
        Schema::table('discounts', function (Blueprint $table) {
            $table->dropColumn(['mode', 'quantity_rules']);
        });
    }
};
