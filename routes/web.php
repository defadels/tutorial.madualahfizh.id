<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\LessonController;

Route::get('/', function () {
    return redirect()->route('home');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

// Member Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
    Route::get('/courses/{course}/lessons/{lesson}', [CourseController::class, 'showLesson'])->name('courses.lessons.show');
});

// Admin Routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Courses
    Route::resource('courses', AdminCourseController::class);
    Route::post('courses/{course}/publish', [AdminCourseController::class, 'publish'])->name('courses.publish');
    
    // Modules
    Route::post('courses/{course}/modules', [ModuleController::class, 'store'])->name('modules.store');
    Route::put('modules/{module}', [ModuleController::class, 'update'])->name('modules.update');
    Route::delete('modules/{module}', [ModuleController::class, 'destroy'])->name('modules.destroy');
    Route::post('courses/{course}/modules/reorder', [ModuleController::class, 'reorder'])->name('modules.reorder');
    
    // Lessons
    Route::post('modules/{module}/lessons', [LessonController::class, 'store'])->name('lessons.store');
    Route::put('lessons/{lesson}', [LessonController::class, 'update'])->name('lessons.update');
    Route::delete('lessons/{lesson}', [LessonController::class, 'destroy'])->name('lessons.destroy');
    Route::post('modules/{module}/lessons/reorder', [LessonController::class, 'reorder'])->name('lessons.reorder');
});
