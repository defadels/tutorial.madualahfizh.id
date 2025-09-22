<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    //     $this->middleware('permission:view published courses');
    // }

    public function index()
    {
        $courses = Course::where('is_published', true)
            ->latest()
            ->paginate(12);
            
        return view('courses.index', compact('courses'));
    }

    public function show(Course $course)
    {
        abort_if(!$course->is_published, 404);
        
        $firstLesson = $course->modules()
            ->orderBy('order')
            ->first()
            ?->lessons()
            ->orderBy('order')
            ->first();
            
        return view('courses.show', compact('course', 'firstLesson'));
    }

    public function showLesson(Course $course, Lesson $lesson)
    {
        abort_if(!$course->is_published, 404);
        abort_if($lesson->module->course_id !== $course->id, 404);
        
        return view('courses.lesson', compact('course', 'lesson'));
    }
} 