<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_card_settings', function (Blueprint $table) {
            $table->text('care_text')->default('Зберігайте прикраси окремо в сухому місці. Уникайте контакту з парфумами, косметикою та побутовою хімією. Після носіння протирайте виріб м’якою сухою серветкою.');
        });
    }

    public function down(): void
    {
        Schema::table('product_card_settings', function (Blueprint $table) {
            $table->dropColumn('care_text');
        });
    }
};
