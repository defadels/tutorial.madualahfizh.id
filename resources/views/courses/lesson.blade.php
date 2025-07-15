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
        width: 100%;
        height: 0;
        padding-bottom: 56.25%; /* 16:9 aspect ratio */
        background: #000;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .video-container iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: none;
        border-radius: 8px;
    }
    .lesson-description {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-top: 20px;
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
                    <h5 class="card-title mb-0">{{ $course->title }}</h5>
                </div>
                <div class="card-body p-0">
                    <div class="accordion" id="moduleAccordion">
                        @foreach($course->modules->sortBy('order') as $module)
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
                                            @foreach($module->lessons->sortBy('order') as $moduleLesson)
                                                <a href="{{ route('courses.lessons.show', [$course, $moduleLesson]) }}" 
                                                   class="lesson-link list-group-item list-group-item-action {{ $lesson->id === $moduleLesson->id ? 'lesson-active' : '' }}">
                                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                                        <div>
                                                            @if($lesson->id === $moduleLesson->id)
                                                                <i class="fas fa-play-circle text-primary me-2"></i>
                                                            @else
                                                                <i class="fas fa-play-circle text-muted me-2"></i>
                                                            @endif
                                                            {{ $moduleLesson->title }}
                                                        </div>
                                                        @if($moduleLesson->duration)
                                                            <small class="text-muted">
                                                                {{ $moduleLesson->duration }}
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
                            <li class="breadcrumb-item active">{{ $lesson->title }}</li>
                        </ol>
                    </nav>

                    <h1 class="h2 mb-4">{{ $lesson->title }}</h1>

                    @if($lesson->video_url)
                        <div class="video-container">
                            @php
                                // Extract YouTube video ID
                                $videoId = null;
                                if (preg_match('/youtu\.be\/([^#\&\?]+)/', $lesson->video_url, $matches)) {
                                    $videoId = $matches[1];
                                } elseif (preg_match('/youtube\.com\/watch\?v=([^#\&\?]+)/', $lesson->video_url, $matches)) {
                                    $videoId = $matches[1];
                                }
                            @endphp
                            
                            @if($videoId)
                                <iframe src="https://www.youtube.com/embed/{{ $videoId }}" 
                                        allowfullscreen 
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
                                </iframe>
                            @else
                                <div class="d-flex align-items-center justify-content-center h-100">
                                    <div class="text-center text-white">
                                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                                        <p>URL video tidak valid</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Video belum tersedia untuk pelajaran ini.
                        </div>
                    @endif

                    @if($lesson->description)
                        <div class="lesson-description">
                            <h5 class="mb-3">Tentang Pelajaran Ini</h5>
                            {!! nl2br(e($lesson->description)) !!}
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
                            <a href="{{ route('courses.lessons.show', [$course, $prevLesson]) }}" 
                               class="btn btn-outline-primary">
                                <i class="fas fa-chevron-left me-2"></i>
                                {{ Str::limit($prevLesson->title, 30) }}
                            </a>
                        @else
                            <div></div>
                        @endif

                        @if($nextLesson)
                            <a href="{{ route('courses.lessons.show', [$course, $nextLesson]) }}" 
                               class="btn btn-primary">
                                {{ Str::limit($nextLesson->title, 30) }}
                                <i class="fas fa-chevron-right ms-2"></i>
                            </a>
                        @else
                            <a href="{{ route('courses.show', $course) }}" 
                               class="btn btn-success">
                                Selesai
                                <i class="fas fa-check ms-2"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 