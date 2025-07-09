@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Daftar Kursus</h1>
        <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Kursus
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Thumbnail</th>
                            <th>Judul</th>
                            <th>Status</th>
                            <th>Jumlah Materi</th>
                            <th>Tanggal Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($courses as $course)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    @if ($course->thumbnail)
                                        <img src="{{ Storage::url($course->thumbnail) }}" 
                                             alt="{{ $course->title }}" 
                                             class="img-thumbnail"
                                             style="max-width: 100px;">
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center" 
                                             style="width: 100px; height: 60px;">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $course->title }}</td>
                                <td>
                                    <form action="{{ route('admin.courses.publish', $course) }}" 
                                          method="POST" 
                                          class="d-inline">
                                        @csrf
                                        <button type="submit" 
                                                class="btn btn-sm {{ $course->is_published ? 'btn-success' : 'btn-secondary' }}">
                                            {{ $course->is_published ? 'Dipublikasi' : 'Draft' }}
                                        </button>
                                    </form>
                                </td>
                                <td>{{ $course->modules->count() }} Modul</td>
                                <td>{{ $course->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.courses.edit', $course) }}" 
                                       class="btn btn-sm btn-info">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.courses.destroy', $course) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus kursus ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Belum ada kursus</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $courses->links() }}
            </div>
        </div>
    </div>
</div>
@endsection 