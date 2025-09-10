<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Course;
use App\Models\Module;
use App\Models\Lesson;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UITest extends TestCase
{

    /** @test */
    public function home_page_displays_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/home');
        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $response->assertSee('You are logged in!');
    }

    /** @test */
    public function courses_index_displays_correctly()
    {
        $user = User::factory()->create();
        $user->assignRole('member');
        $this->actingAs($user);

        $course = Course::factory()->create([
            'title' => 'Test Course',
            'description' => 'This is a test course description',
            'is_published' => true,
        ]);

        $response = $this->get('/courses');
        $response->assertStatus(200);
        $response->assertSee('Daftar Kursus');
        $response->assertSee('Test Course');
        $response->assertSee('This is a test course description');
        $response->assertSee('Mulai Belajar');
    }

    /** @test */
    public function courses_index_shows_empty_state()
    {
        $user = User::factory()->create();
        $user->assignRole('member');
        $this->actingAs($user);

        $response = $this->get('/courses');
        $response->assertStatus(200);
        $response->assertSee('Belum ada kursus yang tersedia');
    }

    /** @test */
    public function course_detail_page_displays_correctly()
    {
        $user = User::factory()->create();
        $user->assignRole('member');
        $this->actingAs($user);

        $course = Course::factory()->create([
            'title' => 'Test Course',
            'description' => 'This is a detailed course description',
            'is_published' => true,
        ]);

        $module = Module::factory()->create([
            'course_id' => $course->id,
            'title' => 'Module 1',
        ]);

        $lesson = Lesson::factory()->create([
            'module_id' => $module->id,
            'title' => 'Lesson 1',
            'description' => 'First lesson description',
        ]);

        $response = $this->get("/courses/{$course->id}");
        $response->assertStatus(200);
        $response->assertSee('Test Course');
        $response->assertSee('This is a detailed course description');
        $response->assertSee('Module 1');
        $response->assertSee('Lesson 1');
    }

    /** @test */
    public function lesson_page_displays_correctly()
    {
        $user = User::factory()->create();
        $user->assignRole('member');
        $this->actingAs($user);

        $course = Course::factory()->create(['is_published' => true]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $lesson = Lesson::factory()->create([
            'module_id' => $module->id,
            'title' => 'Test Lesson',
            'description' => 'This is a test lesson description',
            'video_url' => 'https://example.com/video.mp4',
            'duration' => '10:30',
        ]);

        $response = $this->get("/courses/{$course->id}/lessons/{$lesson->id}");
        $response->assertStatus(200);
        $response->assertSee('Test Lesson');
        $response->assertSee('This is a test lesson description');
    }

    /** @test */
    public function admin_courses_index_displays_correctly()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $course = Course::factory()->create([
            'title' => 'Admin Test Course',
            'is_published' => true,
        ]);

        $response = $this->get('/admin/courses');
        $response->assertStatus(200);
        $response->assertSee('Admin Test Course');
    }

    /** @test */
    public function admin_course_create_form_displays_correctly()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $response = $this->get('/admin/courses/create');
        $response->assertStatus(200);
        $response->assertSee('form');
        $response->assertSee('title');
        $response->assertSee('description');
    }

    /** @test */
    public function admin_course_edit_form_displays_correctly()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $course = Course::factory()->create([
            'title' => 'Test Course',
            'description' => 'Test Description',
        ]);

        $response = $this->get("/admin/courses/{$course->id}/edit");
        $response->assertStatus(200);
        $response->assertSee('Test Course');
        $response->assertSee('Test Description');
    }

    /** @test */
    public function navigation_displays_correctly_for_authenticated_user()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/home');
        $response->assertStatus(200);
        $response->assertSee('Kursus');
        $response->assertSee($user->name);
    }

    /** @test */
    public function navigation_displays_correctly_for_admin()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $response = $this->get('/home');
        $response->assertStatus(200);
        $response->assertSee('Kursus');
        $response->assertSee('Admin Panel');
        $response->assertSee($admin->name);
    }

    /** @test */
    public function navigation_displays_correctly_for_guest()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Login');
        $response->assertSee('Register');
    }

    /** @test */
    public function course_card_displays_thumbnail_when_available()
    {
        Storage::fake('public');
        
        $user = User::factory()->create();
        $user->assignRole('member');
        $this->actingAs($user);

        $file = UploadedFile::fake()->image('thumbnail.jpg');
        $course = Course::factory()->create([
            'title' => 'Test Course',
            'thumbnail' => $file->store('thumbnails', 'public'),
            'is_published' => true,
        ]);

        $response = $this->get('/courses');
        $response->assertStatus(200);
        $response->assertSee('Test Course');
        // Note: In actual testing, you might want to check for the image src attribute
    }

    /** @test */
    public function course_card_displays_placeholder_when_no_thumbnail()
    {
        $user = User::factory()->create();
        $user->assignRole('member');
        $this->actingAs($user);

        $course = Course::factory()->create([
            'title' => 'Test Course',
            'thumbnail' => null,
            'is_published' => true,
        ]);

        $response = $this->get('/courses');
        $response->assertStatus(200);
        $response->assertSee('Test Course');
        $response->assertSee('fa-video'); // Font Awesome video icon
    }

    /** @test */
    public function pagination_works_correctly()
    {
        $user = User::factory()->create();
        $user->assignRole('member');
        $this->actingAs($user);

        // Create more than 12 courses (default pagination limit)
        Course::factory()->count(15)->create(['is_published' => true]);

        $response = $this->get('/courses');
        $response->assertStatus(200);
        $response->assertSee('pagination');
    }

    /** @test */
    public function error_pages_display_correctly()
    {
        $user = User::factory()->create();
        $user->assignRole('member');
        $this->actingAs($user);

        // Test 404 for non-existent course
        $response = $this->get('/courses/99999');
        $response->assertStatus(404);

        // Test 404 for unpublished course
        $course = Course::factory()->create(['is_published' => false]);
        $response = $this->get("/courses/{$course->id}");
        $response->assertStatus(404);
    }

    /** @test */
    public function form_validation_displays_errors()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        // Test course creation without required fields
        $response = $this->post('/admin/courses', []);
        $response->assertSessionHasErrors(['title']);

        // Test module creation without required fields
        $course = Course::factory()->create();
        $response = $this->post("/admin/courses/{$course->id}/modules", []);
        $response->assertSessionHasErrors(['title']);

        // Test lesson creation without required fields
        $module = Module::factory()->create(['course_id' => $course->id]);
        $response = $this->post("/admin/modules/{$module->id}/lessons", []);
        $response->assertSessionHasErrors(['title']);
    }

    /** @test */
    public function success_messages_display_correctly()
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
        $response->assertSessionHas('success', 'Kursus berhasil dibuat.');
    }

    /** @test */
    public function course_ordering_works_correctly()
    {
        $user = User::factory()->create();
        $user->assignRole('member');
        $this->actingAs($user);

        $course = Course::factory()->create(['is_published' => true]);
        
        // Create modules with specific order
        $module1 = Module::factory()->create([
            'course_id' => $course->id,
            'title' => 'First Module',
            'order' => 1,
        ]);
        
        $module2 = Module::factory()->create([
            'course_id' => $course->id,
            'title' => 'Second Module',
            'order' => 2,
        ]);

        $response = $this->get("/courses/{$course->id}");
        $response->assertStatus(200);
        
        // Check that modules appear in correct order
        $content = $response->getContent();
        $firstModulePos = strpos($content, 'First Module');
        $secondModulePos = strpos($content, 'Second Module');
        
        $this->assertLessThan($secondModulePos, $firstModulePos);
    }

    /** @test */
    public function lesson_ordering_works_correctly()
    {
        $user = User::factory()->create();
        $user->assignRole('member');
        $this->actingAs($user);

        $course = Course::factory()->create(['is_published' => true]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        
        // Create lessons with specific order
        $lesson1 = Lesson::factory()->create([
            'module_id' => $module->id,
            'title' => 'First Lesson',
            'order' => 1,
        ]);
        
        $lesson2 = Lesson::factory()->create([
            'module_id' => $module->id,
            'title' => 'Second Lesson',
            'order' => 2,
        ]);

        $response = $this->get("/courses/{$course->id}");
        $response->assertStatus(200);
        
        // Check that lessons appear in correct order
        $content = $response->getContent();
        $firstLessonPos = strpos($content, 'First Lesson');
        $secondLessonPos = strpos($content, 'Second Lesson');
        
        $this->assertLessThan($secondLessonPos, $firstLessonPos);
    }
}