<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LessonController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:create lessons', ['only' => ['store']]);
        $this->middleware('permission:edit lessons', ['only' => ['update']]);
        $this->middleware('permission:delete lessons', ['only' => ['destroy']]);
        $this->middleware('permission:reorder lessons', ['only' => ['reorder']]);
        $this->middleware('permission:upload videos', ['only' => ['uploadVideo']]);
    }

    public function store(Request $request, Module $module)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video' => 'nullable|file|mimes:mp4,mov,avi|max:102400', // max 100MB
            'duration' => 'nullable|integer',
        ]);

        $order = $module->lessons()->max('order') + 1;
        $lesson = $module->lessons()->create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'duration' => $validated['duration'],
            'order' => $order,
        ]);

        if ($request->hasFile('video')) {
            $path = $request->file('video')->store('videos', 'public');
            $lesson->update(['video_url' => $path]);
        }

        return redirect()->route('admin.courses.edit', $module->course)
            ->with('success', 'Pelajaran berhasil ditambahkan.');
    }

    public function update(Request $request, Lesson $lesson)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video' => 'nullable|file|mimes:mp4,mov,avi|max:102400', // max 100MB
            'duration' => 'nullable|integer',
        ]);

        if ($request->hasFile('video')) {
            if ($lesson->video_url) {
                Storage::disk('public')->delete($lesson->video_url);
            }
            $path = $request->file('video')->store('videos', 'public');
            $validated['video_url'] = $path;
        }

        $lesson->update($validated);

        return redirect()->route('admin.courses.edit', $lesson->module->course)
            ->with('success', 'Pelajaran berhasil diperbarui.');
    }

    public function destroy(Lesson $lesson)
    {
        $course = $lesson->module->course;
        
        if ($lesson->video_url) {
            Storage::disk('public')->delete($lesson->video_url);
        }
        
        $lesson->delete();

        return redirect()->route('admin.courses.edit', $course)
            ->with('success', 'Pelajaran berhasil dihapus.');
    }

    public function reorder(Request $request, Module $module)
    {
        $request->validate([
            'lessons' => 'required|array',
            'lessons.*' => 'exists:lessons,id',
        ]);

        foreach ($request->lessons as $index => $lessonId) {
            Lesson::where('id', $lessonId)->update(['order' => $index]);
        }

        return response()->json(['message' => 'Urutan pelajaran berhasil diperbarui.']);
    }
} 