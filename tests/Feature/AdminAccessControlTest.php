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
