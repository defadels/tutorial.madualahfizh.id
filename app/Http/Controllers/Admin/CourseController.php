<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view courses');
        $this->middleware('permission:create courses', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit courses', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete courses', ['only' => ['destroy']]);
        $this->middleware('permission:publish courses', ['only' => ['publish']]);
    }

    public function index()
    {
        $courses = Course::latest()->paginate(10);
        return view('admin.courses.index', compact('courses'));
    }

    public function create()
    {
        return view('admin.courses.create');
    }

    public function store(Request $request)
    {
        // dd($request->all());

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|image|max:2048',
            'is_published' => 'nullable|boolean',
        ]);

        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('thumbnails', 'public');
            $validated['thumbnail'] = $path;
        }

        Course::create($validated);

        return redirect()->route('admin.courses.index')
            ->with('success', 'Kursus berhasil dibuat.');
    }

    public function edit(Course $course)
    {
        return view('admin.courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|image|max:2048',
            'is_published' => 'nullable|boolean',
        ]);

        if ($request->hasFile('thumbnail')) {
            if ($course->thumbnail) {
                Storage::disk('public')->delete($course->thumbnail);
            }
            $path = $request->file('thumbnail')->store('thumbnails', 'public');
            $validated['thumbnail'] = $path;
        }

        $course->update($validated);

        return redirect()->route('admin.courses.index')
            ->with('success', 'Kursus berhasil diperbarui.');
    }

    public function destroy(Course $course)
    {
        if ($course->thumbnail) {
            Storage::disk('public')->delete($course->thumbnail);
        }
        $course->delete();

        return redirect()->route('admin.courses.index')
            ->with('success', 'Kursus berhasil dihapus.');
    }

    public function publish(Course $course)
    {
        $course->update(['is_published' => !$course->is_published]);
        $status = $course->is_published ? 'dipublikasikan' : 'diarsipkan';

        return redirect()->route('admin.courses.index')
            ->with('success', "Kursus berhasil {$status}.");
    }
} 