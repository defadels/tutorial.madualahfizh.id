<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'video' => 'nullable|file|mimes:mp4,mov,avi|max:102400', // max 100MB
            'duration' => 'nullable|integer|min:1',
        ], [
            'video.max' => 'Ukuran video tidak boleh lebih dari 100MB.',
            'video.mimes' => 'Format video harus MP4, MOV, atau AVI.',
            'duration.min' => 'Durasi harus lebih dari 0 detik.',
        ]);

        try {
            DB::beginTransaction();

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

            DB::commit();

            return redirect()->route('admin.courses.edit', $module->course)
                ->with('success', 'Pelajaran berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Hapus file video jika ada error
            if (isset($path)) {
                Storage::disk('public')->delete($path);
            }

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
            'video' => 'nullable|file|mimes:mp4,mov,avi|max:102400', // max 100MB
            'duration' => 'nullable|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $oldVideoUrl = $lesson->video_url;

            $lesson->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'duration' => $validated['duration'],
            ]);

            if ($request->hasFile('video')) {
                $path = $request->file('video')->store('videos', 'public');
                $lesson->update(['video_url' => $path]);

                // Hapus video lama jika ada
                if ($oldVideoUrl) {
                    Storage::disk('public')->delete($oldVideoUrl);
                }
            }

            DB::commit();

            return redirect()->route('admin.courses.edit', $lesson->module->course)
                ->with('success', 'Pelajaran berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Hapus file video baru jika ada error
            if (isset($path)) {
                Storage::disk('public')->delete($path);
            }

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memperbarui pelajaran. Silakan coba lagi.')
                ->withInput();
        }
    }

    public function destroy(Lesson $lesson)
    {
        try {
            $course = $lesson->module->course;
            
            if ($lesson->video_url) {
                Storage::disk('public')->delete($lesson->video_url);
            }
            
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