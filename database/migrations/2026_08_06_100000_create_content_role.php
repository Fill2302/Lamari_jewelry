<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $role = Role::firstOrCreate([
            'name' => 'Контент',
            'guard_name' => 'web',
        ]);

        $role->syncPermissions(
            Permission::query()
                ->where('guard_name', 'web')
                ->whereIn('name', [
                    'ViewAny:Product',
                    'View:Product',
                    'Create:Product',
                    'Update:Product',
                    'ViewAny:HomepageSetting',
                    'View:HomepageSetting',
                    'Update:HomepageSetting',
                ])
                ->pluck('id'),
        );
    }

    public function down(): void
    {
        Role::query()
            ->where('name', 'Контент')
            ->where('guard_name', 'web')
            ->delete();
    }
};
