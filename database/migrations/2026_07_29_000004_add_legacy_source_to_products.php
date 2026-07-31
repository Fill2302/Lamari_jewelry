<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->unsignedInteger('legacy_source_id')->nullable()->unique();
            $table->string('legacy_source_url')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropUnique(['legacy_source_id']);
            $table->dropColumn(['legacy_source_id', 'legacy_source_url']);
        });
    }
};
