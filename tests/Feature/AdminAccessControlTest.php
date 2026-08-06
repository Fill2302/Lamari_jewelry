<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminAccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_role_only_manages_products_and_homepage_banners(): void
    {
        Role::query()->where('name', 'Контент')->delete();

        foreach ([
            'ViewAny:Product',
            'View:Product',
            'Create:Product',
            'Update:Product',
            'Delete:Product',
            'ViewAny:HomepageSetting',
            'View:HomepageSetting',
            'Create:HomepageSetting',
            'Update:HomepageSetting',
            'ViewAny:Order',
            'ViewAny:User',
            'ViewAny:Role',
        ] as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        (require database_path('migrations/2026_08_06_100000_create_content_role.php'))->up();

        $user = User::factory()->create();
        $user->assignRole('Контент');

        $this->assertTrue($user->can('ViewAny:Product'));
        $this->assertTrue($user->can('View:Product'));
        $this->assertTrue($user->can('Create:Product'));
        $this->assertTrue($user->can('Update:Product'));
        $this->assertTrue($user->can('ViewAny:HomepageSetting'));
        $this->assertTrue($user->can('View:HomepageSetting'));
        $this->assertTrue($user->can('Update:HomepageSetting'));

        $this->assertFalse($user->can('Delete:Product'));
        $this->assertFalse($user->can('Create:HomepageSetting'));
        $this->assertFalse($user->can('ViewAny:Order'));
        $this->assertFalse($user->can('ViewAny:User'));
        $this->assertFalse($user->can('ViewAny:Role'));
    }

    public function test_admin_role_has_every_permission_except_user_and_role_management(): void
    {
        Role::query()->where('name', 'Адмін')->delete();

        foreach ([
            'ViewAny:Product',
            'Create:Order',
            'Update:SiteSetting',
            'ViewAny:Activity',
            'ViewAny:User',
            'Create:User',
            'Update:User',
            'Delete:User',
            'ViewAny:Role',
            'Create:Role',
            'Update:Role',
            'Delete:Role',
        ] as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        (require database_path('migrations/2026_08_06_094900_create_admin_role.php'))->up();

        $user = User::factory()->create();
        $user->assignRole('Адмін');

        $this->assertTrue($user->can('ViewAny:Product'));
        $this->assertTrue($user->can('Create:Order'));
        $this->assertTrue($user->can('Update:SiteSetting'));
        $this->assertTrue($user->can('ViewAny:Activity'));

        $this->assertFalse($user->can('ViewAny:User'));
        $this->assertFalse($user->can('Create:User'));
        $this->assertFalse($user->can('Update:User'));
        $this->assertFalse($user->can('Delete:User'));
        $this->assertFalse($user->can('ViewAny:Role'));
        $this->assertFalse($user->can('Create:Role'));
        $this->assertFalse($user->can('Update:Role'));
        $this->assertFalse($user->can('Delete:Role'));
    }

    public function test_limited_user_only_sees_permitted_resources(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'Контент-менеджер', 'guard_name' => 'web']);
        $permission = Permission::create(['name' => 'ViewAny:Product', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);
        $user->assignRole($role);

        $this->actingAs($user)->get('/admin/products')->assertOk();
        $this->actingAs($user)->get('/admin/users')->assertForbidden();
        $this->actingAs($user)->get('/admin/shield/roles')->assertForbidden();
        $this->actingAs($user)->get('/admin/activity-logs')->assertForbidden();
    }

    public function test_admin_changes_record_the_user_and_changed_fields(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = Category::create(['name' => 'Тест', 'slug' => 'test-audit']);
        $category->update(['name' => 'Оновлений тест']);

        $activity = Activity::query()->where('subject_type', Category::class)->latest('id')->firstOrFail();
        $this->assertSame($user->id, $activity->causer_id);
        $this->assertSame('updated', $activity->event);
        $this->assertSame('Оновлений тест', $activity->properties->get('attributes')['name']);
        $this->assertSame('Тест', $activity->properties->get('old')['name']);
    }
}
