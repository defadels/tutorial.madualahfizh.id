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
                                    <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#module{{ $module->id }}">
                                        {{ $module->title }}
                                    </button>
                                </h2>
                                <div id="module{{ $module->id }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#moduleAccordion">
                                    <div class="accordion-body p-0">
                                        <div class="list-group list-group-flush">
                                            @foreach($module->lessons as $lesson)
                                                <a href="{{ route('courses.lessons.show', [$course, $lesson]) }}" 
                                                   class="lesson-link list-group-item list-group-item-action {{ isset($currentLesson) && $currentLesson->id === $lesson->id ? 'lesson-active' : '' }}">
                                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                                        <div>
                                                            <i class="fas fa-play-circle me-2"></i>
                                                            {{ $lesson->title }}
                                                        </div>
                                                        @if($lesson->duration)
                                                            <small class="text-muted">
                                                                {{ gmdate('i:s', $lesson->duration) }}
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
                    <h1>{{ $course->title }}</h1>
                    <p class="lead">{{ $course->description }}</p>

                    @if($firstLesson)
                        <a href="{{ route('courses.lessons.show', [$course, $firstLesson]) }}" class="btn btn-primary">
                            Mulai Belajar
                        </a>
                    @else
                        <div class="alert alert-info">
                            Belum ada materi yang tersedia.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 