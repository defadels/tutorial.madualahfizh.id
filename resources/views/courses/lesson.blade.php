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
        background: #000;
        border-radius: 8px;
    }
    .video-container video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }
    .lesson-description {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-top: 20px;
    }
    .progress-container {
        position: relative;
        height: 5px;
        background: #e9ecef;
        margin-bottom: 10px;
        border-radius: 5px;
    }
    .progress-bar {
        position: absolute;
        height: 100%;
        background: #0d6efd;
        border-radius: 5px;
        transition: width 0.3s ease;
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
                            <li class="breadcrumb-item active">{{ $lesson->title }}</li>
                        </ol>
                    </nav>

                    <h1 class="h2 mb-4">{{ $lesson->title }}</h1>

                    @if($lesson->video_url)
                        <div class="video-container mb-4">
                            <div class="progress-container">
                                <div class="progress-bar" style="width: 0%"></div>
                            </div>
                            <video id="lessonVideo" controls controlsList="nodownload">
                                <source src="{{ Storage::url($lesson->video_url) }}" type="video/mp4">
                                Browser Anda tidak mendukung pemutaran video.
                            </video>
                        </div>
                    @endif

                    @if($lesson->description)
                        <div class="lesson-description">
                            <h5 class="mb-3">Tentang Pelajaran Ini</h5>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const video = document.getElementById('lessonVideo');
    if (video) {
        const progressBar = document.querySelector('.progress-bar');
        
        video.addEventListener('timeupdate', function() {
            const progress = (video.currentTime / video.duration) * 100;
            progressBar.style.width = progress + '%';
        });

        // Simpan posisi terakhir video
        video.addEventListener('pause', function() {
            localStorage.setItem(`video_progress_${video.querySelector('source').src}`, video.currentTime);
        });

        // Muat posisi terakhir video
        const lastPosition = localStorage.getItem(`video_progress_${video.querySelector('source').src}`);
        if (lastPosition) {
            video.currentTime = parseFloat(lastPosition);
        }
    }
});
</script>
@endpush
@endsection 