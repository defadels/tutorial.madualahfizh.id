<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Course;
use App\Models\Module;
use App\Models\Lesson;
use Tests\TestCase;

class PermissionTest extends TestCase
{

    /** @test */
    public function admin_has_all_permissions()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->assertTrue($admin->hasPermissionTo('view courses'));
        $this->assertTrue($admin->hasPermissionTo('create courses'));
        $this->assertTrue($admin->hasPermissionTo('edit courses'));
        $this->assertTrue($admin->hasPermissionTo('delete courses'));
        $this->assertTrue($admin->hasPermissionTo('publish courses'));
        $this->assertTrue($admin->hasPermissionTo('create modules'));
        $this->assertTrue($admin->hasPermissionTo('edit modules'));
        $this->assertTrue($admin->hasPermissionTo('delete modules'));
        $this->assertTrue($admin->hasPermissionTo('reorder modules'));
        $this->assertTrue($admin->hasPermissionTo('create lessons'));
        $this->assertTrue($admin->hasPermissionTo('edit lessons'));
        $this->assertTrue($admin->hasPermissionTo('delete lessons'));
        $this->assertTrue($admin->hasPermissionTo('reorder lessons'));
        $this->assertTrue($admin->hasPermissionTo('upload videos'));
    }

    /** @test */
    public function member_has_limited_permissions()
    {
        $member = User::factory()->create();
        $member->assignRole('member');

        $this->assertTrue($member->hasPermissionTo('view courses'));
        $this->assertFalse($member->hasPermissionTo('create courses'));
        $this->assertFalse($member->hasPermissionTo('edit courses'));
        $this->assertFalse($member->hasPermissionTo('delete courses'));
        $this->assertFalse($member->hasPermissionTo('publish courses'));
        $this->assertFalse($member->hasPermissionTo('create modules'));
        $this->assertFalse($member->hasPermissionTo('edit modules'));
        $this->assertFalse($member->hasPermissionTo('delete modules'));
        $this->assertFalse($member->hasPermissionTo('reorder modules'));
        $this->assertFalse($member->hasPermissionTo('create lessons'));
        $this->assertFalse($member->hasPermissionTo('edit lessons'));
        $this->assertFalse($member->hasPermissionTo('delete lessons'));
        $this->assertFalse($member->hasPermissionTo('reorder lessons'));
        $this->assertFalse($member->hasPermissionTo('upload videos'));
    }

    /** @test */
    public function admin_can_access_admin_panel()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $response = $this->get('/admin/courses');
        $response->assertStatus(200);
    }

    /** @test */
    public function member_cannot_access_admin_panel()
    {
        $member = User::factory()->create();
        $member->assignRole('member');
        $this->actingAs($member);

        $response = $this->get('/admin/courses');
        // Member will get 403 because they don't have 'view courses' permission
        $response->assertStatus(403);
    }

    /** @test */
    public function guest_cannot_access_admin_panel()
    {
        $response = $this->get('/admin/courses');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function admin_can_create_course()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $courseData = [
            'title' => 'Test Course',
            'description' => 'Test Description',
        ];

        $response = $this->post('/admin/courses', $courseData);
        $response->assertRedirect('/admin/courses');
        $this->assertDatabaseHas('courses', ['title' => 'Test Course']);
    }

    /** @test */
    public function member_cannot_create_course()
    {
        $member = User::factory()->create();
        $member->assignRole('member');
        $this->actingAs($member);

        $courseData = [
            'title' => 'Test Course',
            'description' => 'Test Description',
        ];

        $response = $this->post('/admin/courses', $courseData);
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_edit_course()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $course = Course::factory()->create();
        $updateData = [
            'title' => 'Updated Course',
            'description' => 'Updated Description',
        ];

        $response = $this->put("/admin/courses/{$course->id}", $updateData);
        $response->assertRedirect('/admin/courses');
        
        $course->refresh();
        $this->assertEquals('Updated Course', $course->title);
    }

    /** @test */
    public function member_cannot_edit_course()
    {
        $member = User::factory()->create();
        $member->assignRole('member');
        $this->actingAs($member);

        $course = Course::factory()->create();
        $updateData = [
            'title' => 'Updated Course',
            'description' => 'Updated Description',
        ];

        $response = $this->put("/admin/courses/{$course->id}", $updateData);
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_delete_course()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $course = Course::factory()->create();

        $response = $this->delete("/admin/courses/{$course->id}");
        $response->assertRedirect('/admin/courses');
        $this->assertSoftDeleted('courses', ['id' => $course->id]);
    }

    /** @test */
    public function member_cannot_delete_course()
    {
        $member = User::factory()->create();
        $member->assignRole('member');
        $this->actingAs($member);

        $course = Course::factory()->create();

        $response = $this->delete("/admin/courses/{$course->id}");
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_publish_course()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $course = Course::factory()->create(['is_published' => false]);

        $response = $this->post("/admin/courses/{$course->id}/publish");
        $response->assertRedirect('/admin/courses');
        
        $course->refresh();
        $this->assertTrue($course->is_published);
    }

    /** @test */
    public function member_cannot_publish_course()
    {
        $member = User::factory()->create();
        $member->assignRole('member');
        $this->actingAs($member);

        $course = Course::factory()->create(['is_published' => false]);

        $response = $this->post("/admin/courses/{$course->id}/publish");
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_manage_modules()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $course = Course::factory()->create();

        // Create module
        $moduleData = ['title' => 'Test Module'];
        $response = $this->post("/admin/courses/{$course->id}/modules", $moduleData);
        $response->assertRedirect("/admin/courses/{$course->id}/edit");

        $module = Module::where('course_id', $course->id)->first();
        $this->assertNotNull($module);

        // Update module
        $updateData = ['title' => 'Updated Module'];
        $response = $this->put("/admin/modules/{$module->id}", $updateData);
        $response->assertRedirect("/admin/courses/{$course->id}/edit");

        // Delete module
        $response = $this->delete("/admin/modules/{$module->id}");
        $response->assertRedirect("/admin/courses/{$course->id}/edit");
    }

    /** @test */
    public function member_cannot_manage_modules()
    {
        $member = User::factory()->create();
        $member->assignRole('member');
        $this->actingAs($member);

        $course = Course::factory()->create();
        $module = Module::factory()->create(['course_id' => $course->id]);

        // Cannot create module
        $moduleData = ['title' => 'Test Module'];
        $response = $this->post("/admin/courses/{$course->id}/modules", $moduleData);
        $response->assertStatus(403);

        // Cannot update module
        $updateData = ['title' => 'Updated Module'];
        $response = $this->put("/admin/modules/{$module->id}", $updateData);
        $response->assertStatus(403);

        // Cannot delete module
        $response = $this->delete("/admin/modules/{$module->id}");
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_manage_lessons()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $course = Course::factory()->create();
        $module = Module::factory()->create(['course_id' => $course->id]);

        // Create lesson
        $lessonData = [
            'title' => 'Test Lesson',
            'description' => 'Test Description',
            'video_url' => 'https://example.com/video.mp4',
            'duration' => '10:30',
        ];
        $response = $this->post("/admin/modules/{$module->id}/lessons", $lessonData);
        $response->assertRedirect("/admin/courses/{$course->id}/edit");

        $lesson = Lesson::where('module_id', $module->id)->first();
        $this->assertNotNull($lesson);

        // Update lesson
        $updateData = [
            'title' => 'Updated Lesson',
            'description' => 'Updated Description',
        ];
        $response = $this->put("/admin/lessons/{$lesson->id}", $updateData);
        $response->assertRedirect("/admin/courses/{$course->id}/edit");

        // Delete lesson
        $response = $this->delete("/admin/lessons/{$lesson->id}");
        $response->assertRedirect("/admin/courses/{$course->id}/edit");
    }

    /** @test */
    public function member_cannot_manage_lessons()
    {
        $member = User::factory()->create();
        $member->assignRole('member');
        $this->actingAs($member);

        $course = Course::factory()->create();
        $module = Module::factory()->create(['course_id' => $course->id]);
        $lesson = Lesson::factory()->create(['module_id' => $module->id]);

        // Cannot create lesson
        $lessonData = [
            'title' => 'Test Lesson',
            'description' => 'Test Description',
        ];
        $response = $this->post("/admin/modules/{$module->id}/lessons", $lessonData);
        $response->assertStatus(403);

        // Cannot update lesson
        $updateData = [
            'title' => 'Updated Lesson',
            'description' => 'Updated Description',
        ];
        $response = $this->put("/admin/lessons/{$lesson->id}", $updateData);
        $response->assertStatus(403);

        // Cannot delete lesson
        $response = $this->delete("/admin/lessons/{$lesson->id}");
        $response->assertStatus(403);
    }

    /** @test */
    public function user_without_role_has_no_permissions()
    {
        $user = User::factory()->create();
        // No role assigned

        $this->assertFalse($user->hasPermissionTo('view courses'));
        $this->assertFalse($user->hasPermissionTo('create courses'));
        $this->assertFalse($user->hasPermissionTo('edit courses'));
    }

    /** @test */
    public function admin_role_has_correct_permissions()
    {
        $adminRole = \Spatie\Permission\Models\Role::where('name', 'admin')->first();
        $this->assertNotNull($adminRole);
        
        $permissions = $adminRole->permissions->pluck('name')->toArray();
        
        $expectedPermissions = [
            'view courses', 'create courses', 'edit courses', 'delete courses', 'publish courses',
            'create modules', 'edit modules', 'delete modules', 'reorder modules',
            'create lessons', 'edit lessons', 'delete lessons', 'reorder lessons', 'upload videos'
        ];
        
        foreach ($expectedPermissions as $permission) {
            $this->assertContains($permission, $permissions);
        }
    }

    /** @test */
    public function member_role_has_correct_permissions()
    {
        $memberRole = \Spatie\Permission\Models\Role::where('name', 'member')->first();
        $this->assertNotNull($memberRole);
        
        $permissions = $memberRole->permissions->pluck('name')->toArray();
        
        $this->assertContains('view courses', $permissions);
        $this->assertCount(1, $permissions);
    }
}