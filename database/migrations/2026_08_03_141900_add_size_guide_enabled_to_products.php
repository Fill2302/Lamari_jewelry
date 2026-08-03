<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->boolean('size_guide_enabled')->default(false)->after('size_guide_type');
        });

        DB::table('products')
            ->whereNotNull('size_guide_type')
            ->update(['size_guide_enabled' => true]);

        DB::table('products')
            ->where('slug', 'crystal-pearl-necklace')
            ->whereNull('size_guide_type')
            ->update([
                'size_guide_type' => 'necklace',
                'size_guide_enabled' => true,
            ]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn('size_guide_enabled');
        });
    }
};
