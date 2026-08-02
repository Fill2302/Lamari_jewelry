<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_routing_settings', function (Blueprint $table) {
            $table->id();
            $table->string('mixed_cart_destination')->default('privat');
            $table->timestamps();
        });

        DB::table('payment_routing_settings')->insert([
            'mixed_cart_destination' => 'privat',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_routing_settings');
    }
};
