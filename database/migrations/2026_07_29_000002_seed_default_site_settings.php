<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $settings = [
            ['group' => 'general', 'label' => 'Назва магазину', 'key' => 'store_name', 'type' => 'text', 'value' => 'Lamari'],
            ['group' => 'general', 'label' => 'Логотип', 'key' => 'logo', 'type' => 'image', 'value' => null],
            ['group' => 'contacts', 'label' => 'Телефон', 'key' => 'phone', 'type' => 'text', 'value' => null],
            ['group' => 'contacts', 'label' => 'Email', 'key' => 'email', 'type' => 'text', 'value' => null],
            ['group' => 'contacts', 'label' => 'Instagram', 'key' => 'instagram', 'type' => 'text', 'value' => null],
            ['group' => 'homepage', 'label' => 'Головний заголовок', 'key' => 'home_title', 'type' => 'text', 'value' => 'LAMARI'],
            ['group' => 'homepage', 'label' => 'Головний банер', 'key' => 'home_banner', 'type' => 'image', 'value' => null],
            ['group' => 'delivery', 'label' => 'Умови доставки', 'key' => 'delivery_text', 'type' => 'textarea', 'value' => null],
            ['group' => 'payment', 'label' => 'Умови оплати', 'key' => 'payment_text', 'type' => 'textarea', 'value' => null],
        ];

        DB::table('site_settings')->insert(array_map(
            fn (array $setting) => $setting + ['is_public' => true, 'created_at' => $now, 'updated_at' => $now],
            $settings,
        ));
    }

    public function down(): void
    {
        DB::table('site_settings')->whereIn('key', [
            'store_name', 'logo', 'phone', 'email', 'instagram', 'home_title',
            'home_banner', 'delivery_text', 'payment_text',
        ])->delete();
    }
};
