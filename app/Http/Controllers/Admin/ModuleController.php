<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:create modules', ['only' => ['store']]);
        $this->middleware('permission:edit modules', ['only' => ['update']]);
        $this->middleware('permission:delete modules', ['only' => ['destroy']]);
        $this->middleware('permission:reorder modules', ['only' => ['reorder']]);
    }

    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $order = $course->modules()->max('order') + 1;
        $course->modules()->create([
            'title' => $validated['title'],
            'order' => $order,
        ]);

        return redirect()->route('admin.courses.edit', $course)
            ->with('success', 'Modul berhasil ditambahkan.');
    }

    public function update(Request $request, Module $module)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $module->update($validated);

        return redirect()->route('admin.courses.edit', $module->course)
            ->with('success', 'Modul berhasil diperbarui.');
    }

    public function destroy(Module $module)
    {
        $course = $module->course;
        $module->delete();

        return redirect()->route('admin.courses.edit', $course)
            ->with('success', 'Modul berhasil dihapus.');
    }

    public function reorder(Request $request, Course $course)
    {
        $request->validate([
            'modules' => 'required|array',
            'modules.*' => 'exists:modules,id',
        ]);

        foreach ($request->modules as $index => $moduleId) {
            Module::where('id', $moduleId)->update(['order' => $index]);
        }

        return response()->json(['message' => 'Urutan modul berhasil diperbarui.']);
    }
} 