@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Daftar Kursus</h1>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        @forelse ($courses as $course)
            <div class="col">
                <div class="card h-100">
                    @if ($course->thumbnail)
                        <img src="{{ Storage::url($course->thumbnail) }}" class="card-img-top" alt="{{ $course->title }}">
                    @else
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i class="fas fa-video fa-3x text-muted"></i>
                        </div>
                    @endif
                    <div class="card-body">
                        <h5 class="card-title">{{ $course->title }}</h5>
                        <p class="card-text">{{ Str::limit($course->description, 100) }}</p>
                    </div>
                    <div class="card-footer bg-white border-top-0">
                        <a href="{{ route('courses.show', $course) }}" class="btn btn-primary w-100">
                            Mulai Belajar
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">
                    Belum ada kursus yang tersedia.
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $courses->links() }}
    </div>
</div>
@endsection 