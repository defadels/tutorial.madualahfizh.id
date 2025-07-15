<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'video_url' => 'nullable|string|url',
            'duration' => 'nullable|string|max:100',
        ], [
            'video_url.url' => 'URL video tidak valid.',
            'duration.max' => 'Durasi tidak boleh lebih dari 100 karakter.',
        ]);

        try {
            DB::beginTransaction();

            $order = $module->lessons()->max('order') + 1;
            $lesson = $module->lessons()->create([
                'module_id' => $module->id,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'video_url' => $validated['video_url'],
                'duration' => $validated['duration'],
                'order' => $order,
            ]);

            DB::commit();

            return redirect()->route('admin.courses.edit', $module->course_id)
                ->with('success', 'Pelajaran berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menambahkan pelajaran. Silakan coba lagi.')
                ->withInput();
        }
    }

    public function update(Request $request, Lesson $lesson)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video_url' => 'nullable|string|url',
            'duration' => 'nullable|string|max:100',
        ], [
            'video_url.url' => 'URL video tidak valid.',
            'duration.max' => 'Durasi tidak boleh lebih dari 100 karakter.',
        ]);

        try {
            DB::beginTransaction();

            $lesson->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'video_url' => $validated['video_url'],
                'duration' => $validated['duration'],
            ]);

            DB::commit();

            return redirect()->route('admin.courses.edit', $lesson->module->course)
                ->with('success', 'Pelajaran berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memperbarui pelajaran. Silakan coba lagi.')
                ->withInput();
        }
    }

    public function destroy(Lesson $lesson)
    {
        try {
            $course = $lesson->module->course;
            $lesson->delete();

            return redirect()->route('admin.courses.edit', $course)
                ->with('success', 'Pelajaran berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus pelajaran. Silakan coba lagi.');
        }
    }

    public function reorder(Request $request, Module $module)
    {
        $request->validate([
            'lessons' => 'required|array',
            'lessons.*.id' => 'required|exists:lessons,id',
            'lessons.*.order' => 'required|integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->lessons as $lessonData) {
                Lesson::where('id', $lessonData['id'])
                    ->where('module_id', $module->id)
                    ->update(['order' => $lessonData['order']]);
            }

            DB::commit();

            return response()->json(['message' => 'Urutan pelajaran berhasil diperbarui.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Terjadi kesalahan saat mengatur urutan.'], 500);
        }
    }
} 