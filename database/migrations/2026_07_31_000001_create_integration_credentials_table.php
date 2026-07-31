<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->unique();
            $table->text('api_key')->nullable();
            $table->boolean('is_active')->default(false);
            $table->string('sender_name')->nullable();
            $table->string('sender_phone', 20)->nullable();
            $table->string('sender_settlement')->nullable();
            $table->string('sender_warehouse')->nullable();
            $table->decimal('package_weight', 6, 2)->nullable();
            $table->decimal('package_length', 6, 1)->nullable();
            $table->decimal('package_width', 6, 1)->nullable();
            $table->decimal('package_height', 6, 1)->nullable();
            $table->string('delivery_payer')->default('Recipient');
            $table->timestamps();
        });

        DB::table('integration_credentials')->insert([
            'provider' => 'nova_poshta',
            'sender_name' => 'ФОП Попова Владислава Ігорівна',
            'sender_phone' => '+380505617887',
            'sender_settlement' => 'Говтвянчик',
            'sender_warehouse' => '1',
            'package_weight' => 0.5,
            'package_length' => 25.5,
            'package_width' => 17,
            'package_height' => 1,
            'delivery_payer' => 'Recipient',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_credentials');
    }
};
