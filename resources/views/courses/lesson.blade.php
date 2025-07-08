@extends('layouts.app')

@push('styles')
<style>
    .sidebar {
        height: calc(100vh - 150px);
        overflow-y: auto;
    }
    .lesson-link {
        color: inherit;
        text-decoration: none;
    }
    .lesson-link:hover {
        color: #0d6efd;
    }
    .lesson-active {
        background-color: #e9ecef;
    }
    .video-container {
        position: relative;
        padding-bottom: 56.25%; /* 16:9 */
        height: 0;
        overflow: hidden;
    }
    .video-container video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="card sidebar">
                <div class="card-header">
                    <h5 class="card-title mb-0">Daftar Materi</h5>
                </div>
                <div class="card-body p-0">
                    <div class="accordion" id="moduleAccordion">
                        @foreach($course->modules as $module)
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button {{ $lesson->module_id === $module->id ? '' : 'collapsed' }}" 
                                            type="button" 
                                            data-bs-toggle="collapse" 
                                            data-bs-target="#module{{ $module->id }}">
                                        {{ $module->title }}
                                    </button>
                                </h2>
                                <div id="module{{ $module->id }}" 
                                     class="accordion-collapse collapse {{ $lesson->module_id === $module->id ? 'show' : '' }}" 
                                     data-bs-parent="#moduleAccordion">
                                    <div class="accordion-body p-0">
                                        <div class="list-group list-group-flush">
                                            @foreach($module->lessons as $moduleLesson)
                                                <a href="{{ route('courses.lessons.show', [$course, $moduleLesson]) }}" 
                                                   class="lesson-link list-group-item list-group-item-action {{ $lesson->id === $moduleLesson->id ? 'lesson-active' : '' }}">
                                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                                        <div>
                                                            <i class="fas fa-play-circle me-2"></i>
                                                            {{ $moduleLesson->title }}
                                                        </div>
                                                        @if($moduleLesson->duration)
                                                            <small class="text-muted">
                                                                {{ gmdate('i:s', $moduleLesson->duration) }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-body">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">Kursus</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.show', $course) }}">{{ $course->title }}</a></li>
                            <li class="breadcrumb-item">{{ $lesson->module->title }}</li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $lesson->title }}</li>
                        </ol>
                    </nav>

                    <h1 class="mb-4">{{ $lesson->title }}</h1>

                    @if($lesson->video_url)
                        <div class="video-container mb-4">
                            <video controls>
                                <source src="{{ Storage::url($lesson->video_url) }}" type="video/mp4">
                                Browser Anda tidak mendukung pemutaran video.
                            </video>
                        </div>
                    @endif

                    @if($lesson->description)
                        <div class="lesson-description">
                            {{ $lesson->description }}
                        </div>
                    @endif

                    <div class="mt-4 d-flex justify-content-between">
                        @php
                            $prevLesson = $course->modules()
                                ->where('order', '<=', $lesson->module->order)
                                ->with(['lessons' => function($query) use ($lesson) {
                                    $query->where('order', '<', $lesson->order)
                                        ->orderBy('order', 'desc');
                                }])
                                ->get()
                                ->flatMap->lessons
                                ->first();

                            $nextLesson = $course->modules()
                                ->where('order', '>=', $lesson->module->order)
                                ->with(['lessons' => function($query) use ($lesson) {
                                    $query->where('order', '>', $lesson->order)
                                        ->orderBy('order');
                                }])
                                ->get()
                                ->flatMap->lessons
                                ->first();
                        @endphp

                        @if($prevLesson)
                            <a href="{{ route('courses.lessons.show', [$course, $prevLesson]) }}" class="btn btn-outline-primary">
                                <i class="fas fa-chevron-left me-2"></i>
                                Sebelumnya
                            </a>
                        @endif

                        @if($nextLesson)
                            <a href="{{ route('courses.lessons.show', [$course, $nextLesson]) }}" class="btn btn-primary">
                                Selanjutnya
                                <i class="fas fa-chevron-right ms-2"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 