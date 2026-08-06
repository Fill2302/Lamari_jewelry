<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('merchant_accounts', function (Blueprint $table) {
            $table->string('payment_destination')->nullable()->after('code')->index();
        });

        DB::table('merchant_accounts')
            ->where('provider', 'mono')
            ->where('is_default', true)
            ->update(['payment_destination' => 'mono']);

        $fop3EntityId = DB::table('legal_entities')->where('name', 'ФОП-3')->value('id');
        if (! $fop3EntityId) {
            $fop3EntityId = DB::table('legal_entities')->insertGetId([
                'name' => 'ФОП-3',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('merchant_accounts')->updateOrInsert(['code' => 'mono-fop-3'], [
            'legal_entity_id' => $fop3EntityId,
            'provider' => 'mono',
            'code' => 'mono-fop-3',
            'payment_destination' => 'privat',
            'is_default' => true,
            'test_mode' => false,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('merchant_accounts')->where('code', 'mono-fop-3')->update(['is_active' => false]);

        Schema::table('merchant_accounts', function (Blueprint $table) {
            $table->dropIndex(['payment_destination']);
            $table->dropColumn('payment_destination');
        });
    }
};
