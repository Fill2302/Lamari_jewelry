<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $role = Role::firstOrCreate([
            'name' => 'Адмін',
            'guard_name' => 'web',
        ]);

        $role->syncPermissions(
            Permission::query()
                ->where('guard_name', 'web')
                ->where('name', 'not like', '%:User')
                ->where('name', 'not like', '%:Role')
                ->pluck('id'),
        );
    }

    public function down(): void
    {
        Role::query()
            ->where('name', 'Адмін')
            ->where('guard_name', 'web')
            ->delete();
    }
};
