<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Course;
use App\Models\Module;
use App\Models\Lesson;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CourseManagementTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user with permissions
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);
    }

    /** @test */
    public function admin_can_view_courses_index()
    {
        $response = $this->get('/admin/courses');
        $response->assertStatus(200);
        $response->assertViewIs('admin.courses.index');
    }

    /** @test */
    public function admin_can_create_course()
    {
        $courseData = [
            'title' => 'Test Course',
            'description' => 'This is a test course description',
            'is_published' => true,
        ];

        $response = $this->post('/admin/courses', $courseData);
        $response->assertRedirect('/admin/courses');
        
        $this->assertDatabaseHas('courses', [
            'title' => 'Test Course',
            'description' => 'This is a test course description',
            'is_published' => true,
        ]);
    }

    /** @test */
    public function admin_can_upload_course_thumbnail()
    {
        Storage::fake('public');
        
        $file = UploadedFile::fake()->image('thumbnail.jpg');
        
        $courseData = [
            'title' => 'Test Course with Thumbnail',
            'description' => 'Course with thumbnail',
            'thumbnail' => $file,
        ];

        $response = $this->post('/admin/courses', $courseData);
        $response->assertRedirect('/admin/courses');
        
        $course = Course::where('title', 'Test Course with Thumbnail')->first();
        $this->assertNotNull($course->thumbnail);
        Storage::disk('public')->assertExists($course->thumbnail);
    }

    /** @test */
    public function admin_can_edit_course()
    {
        $course = Course::factory()->create([
            'title' => 'Original Title',
            'description' => 'Original Description',
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated Description',
            'is_published' => true,
        ];

        $response = $this->put("/admin/courses/{$course->id}", $updateData);
        $response->assertRedirect('/admin/courses');
        
        $course->refresh();
        $this->assertEquals('Updated Title', $course->title);
        $this->assertEquals('Updated Description', $course->description);
        $this->assertTrue($course->is_published);
    }

    /** @test */
    public function admin_can_delete_course()
    {
        $course = Course::factory()->create();
        
        $response = $this->delete("/admin/courses/{$course->id}");
        $response->assertRedirect('/admin/courses');
        
        $this->assertSoftDeleted('courses', ['id' => $course->id]);
    }

    /** @test */
    public function admin_can_publish_unpublish_course()
    {
        $course = Course::factory()->create(['is_published' => false]);
        
        $response = $this->post("/admin/courses/{$course->id}/publish");
        $response->assertRedirect('/admin/courses');
        
        $course->refresh();
        $this->assertTrue($course->is_published);
        
        $response = $this->post("/admin/courses/{$course->id}/publish");
        $course->refresh();
        $this->assertFalse($course->is_published);
    }

    /** @test */
    public function admin_can_create_module()
    {
        $course = Course::factory()->create();
        
        $moduleData = [
            'title' => 'Test Module',
        ];

        $response = $this->post("/admin/courses/{$course->id}/modules", $moduleData);
        $response->assertRedirect("/admin/courses/{$course->id}/edit");
        
        $this->assertDatabaseHas('modules', [
            'course_id' => $course->id,
            'title' => 'Test Module',
        ]);
    }

    /** @test */
    public function admin_can_update_module()
    {
        $course = Course::factory()->create();
        $module = Module::factory()->create(['course_id' => $course->id]);
        
        $updateData = [
            'title' => 'Updated Module Title',
        ];

        $response = $this->put("/admin/modules/{$module->id}", $updateData);
        $response->assertRedirect("/admin/courses/{$course->id}/edit");
        
        $module->refresh();
        $this->assertEquals('Updated Module Title', $module->title);
    }

    /** @test */
    public function admin_can_delete_module()
    {
        $course = Course::factory()->create();
        $module = Module::factory()->create(['course_id' => $course->id]);
        
        $response = $this->delete("/admin/modules/{$module->id}");
        $response->assertRedirect("/admin/courses/{$course->id}/edit");
        
        $this->assertSoftDeleted('modules', ['id' => $module->id]);
    }

    /** @test */
    public function admin_can_reorder_modules()
    {
        $course = Course::factory()->create();
        $module1 = Module::factory()->create(['course_id' => $course->id, 'order' => 1]);
        $module2 = Module::factory()->create(['course_id' => $course->id, 'order' => 2]);
        
        $reorderData = [
            'modules' => [$module2->id, $module1->id],
        ];

        $response = $this->post("/admin/courses/{$course->id}/modules/reorder", $reorderData);
        $response->assertStatus(200);
        
        $module1->refresh();
        $module2->refresh();
        $this->assertEquals(1, $module2->order);
        $this->assertEquals(0, $module1->order);
    }

    /** @test */
    public function admin_can_create_lesson()
    {
        $course = Course::factory()->create();
        $module = Module::factory()->create(['course_id' => $course->id]);
        
        $lessonData = [
            'title' => 'Test Lesson',
            'description' => 'Test lesson description',
            'video_url' => 'https://example.com/video.mp4',
            'duration' => '10:30',
        ];

        $response = $this->post("/admin/modules/{$module->id}/lessons", $lessonData);
        $response->assertRedirect("/admin/courses/{$course->id}/edit");
        
        $this->assertDatabaseHas('lessons', [
            'module_id' => $module->id,
            'title' => 'Test Lesson',
            'video_url' => 'https://example.com/video.mp4',
        ]);
    }

    /** @test */
    public function admin_can_update_lesson()
    {
        $course = Course::factory()->create();
        $module = Module::factory()->create(['course_id' => $course->id]);
        $lesson = Lesson::factory()->create(['module_id' => $module->id]);
        
        $updateData = [
            'title' => 'Updated Lesson Title',
            'description' => 'Updated description',
            'video_url' => 'https://example.com/new-video.mp4',
            'duration' => '15:45',
        ];

        $response = $this->put("/admin/lessons/{$lesson->id}", $updateData);
        $response->assertRedirect("/admin/courses/{$course->id}/edit");
        
        $lesson->refresh();
        $this->assertEquals('Updated Lesson Title', $lesson->title);
        $this->assertEquals('https://example.com/new-video.mp4', $lesson->video_url);
    }

    /** @test */
    public function admin_can_delete_lesson()
    {
        $course = Course::factory()->create();
        $module = Module::factory()->create(['course_id' => $course->id]);
        $lesson = Lesson::factory()->create(['module_id' => $module->id]);
        
        $response = $this->delete("/admin/lessons/{$lesson->id}");
        $response->assertRedirect("/admin/courses/{$course->id}/edit");
        
        $this->assertSoftDeleted('lessons', ['id' => $lesson->id]);
    }

    /** @test */
    public function admin_can_reorder_lessons()
    {
        $course = Course::factory()->create();
        $module = Module::factory()->create(['course_id' => $course->id]);
        $lesson1 = Lesson::factory()->create(['module_id' => $module->id, 'order' => 1]);
        $lesson2 = Lesson::factory()->create(['module_id' => $module->id, 'order' => 2]);
        
        $reorderData = [
            'lessons' => [
                ['id' => $lesson2->id, 'order' => 0],
                ['id' => $lesson1->id, 'order' => 1],
            ],
        ];

        $response = $this->post("/admin/modules/{$module->id}/lessons/reorder", $reorderData);
        $response->assertStatus(200);
        
        $lesson1->refresh();
        $lesson2->refresh();
        $this->assertEquals(1, $lesson1->order);
        $this->assertEquals(0, $lesson2->order);
    }

    /** @test */
    public function member_can_view_published_courses()
    {
        $member = User::factory()->create();
        $member->assignRole('member');
        $this->actingAs($member);
        
        $publishedCourse = Course::factory()->create(['is_published' => true]);
        $unpublishedCourse = Course::factory()->create(['is_published' => false]);
        
        $response = $this->get('/courses');
        $response->assertStatus(200);
        $response->assertSee($publishedCourse->title);
        $response->assertDontSee($unpublishedCourse->title);
    }

    /** @test */
    public function member_can_view_course_details()
    {
        $member = User::factory()->create();
        $member->assignRole('member');
        $this->actingAs($member);
        
        $course = Course::factory()->create(['is_published' => true]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $lesson = Lesson::factory()->create(['module_id' => $module->id]);
        
        $response = $this->get("/courses/{$course->id}");
        $response->assertStatus(200);
        $response->assertSee($course->title);
    }

    /** @test */
    public function member_cannot_view_unpublished_course()
    {
        $member = User::factory()->create();
        $member->assignRole('member');
        $this->actingAs($member);
        
        $course = Course::factory()->create(['is_published' => false]);
        
        $response = $this->get("/courses/{$course->id}");
        $response->assertStatus(404);
    }

    /** @test */
    public function member_can_view_lesson()
    {
        $member = User::factory()->create();
        $member->assignRole('member');
        $this->actingAs($member);
        
        $course = Course::factory()->create(['is_published' => true]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $lesson = Lesson::factory()->create(['module_id' => $module->id]);
        
        $response = $this->get("/courses/{$course->id}/lessons/{$lesson->id}");
        $response->assertStatus(200);
        $response->assertSee($lesson->title);
    }

    /** @test */
    public function validation_works_for_course_creation()
    {
        $response = $this->post('/admin/courses', []);
        $response->assertSessionHasErrors(['title']);
    }

    /** @test */
    public function validation_works_for_module_creation()
    {
        $course = Course::factory()->create();
        
        $response = $this->post("/admin/courses/{$course->id}/modules", []);
        $response->assertSessionHasErrors(['title']);
    }

    /** @test */
    public function validation_works_for_lesson_creation()
    {
        $course = Course::factory()->create();
        $module = Module::factory()->create(['course_id' => $course->id]);
        
        $response = $this->post("/admin/modules/{$module->id}/lessons", []);
        $response->assertSessionHasErrors(['title']);
    }
}