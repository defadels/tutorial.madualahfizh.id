@extends('layouts.app')

@push('styles')
<style>
    .module-list {
        max-height: 500px;
        overflow-y: auto;
    }
    .sortable-ghost {
        opacity: 0.4;
    }
    .youtube-preview {
        position: relative;
        width: 100%;
        height: 0;
        padding-bottom: 56.25%; /* 16:9 aspect ratio */
        margin-top: 10px;
    }
    .youtube-preview iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: none;
        border-radius: 8px;
    }
    .youtube-preview.hidden {
        display: none;
    }
</style>
@endpush

@section('content')
<div class="container">
    <div class="row">
        <!-- Form Edit Kursus -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Edit Kursus</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.courses.update', $course) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="title" class="form-label">Judul Kursus</label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title', $course->title) }}" 
                                   required>
                            @error('title')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="4">{{ old('description', $course->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            @if($course->thumbnail)
                                <div class="mb-2">
                                    <img src="{{ Storage::url($course->thumbnail) }}" 
                                         alt="{{ $course->title }}" 
                                         class="img-thumbnail"
                                         style="max-width: 200px;">
                                </div>
                            @endif
                            <label for="thumbnail" class="form-label">Thumbnail Baru</label>
                            <input type="file" 
                                   class="form-control @error('thumbnail') is-invalid @enderror" 
                                   id="thumbnail" 
                                   name="thumbnail"
                                   accept="image/*">
                            @error('thumbnail')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" 
                                       class="form-check-input" 
                                       id="is_published" 
                                       name="is_published" 
                                       value="1" 
                                       {{ old('is_published', $course->is_published) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_published">
                                    Publikasikan
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Daftar Modul -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Daftar Modul</h5>
                    <button type="button" 
                            class="btn btn-primary btn-sm" 
                            data-bs-toggle="modal" 
                            data-bs-target="#addModuleModal">
                        <i class="fas fa-plus"></i> Tambah Modul
                    </button>
                </div>
                <div class="card-body">
                    <div class="module-list" id="moduleList">
                        @forelse($course->modules->sortBy('order') as $module)
                            <div class="card mb-3" data-module-id="{{ $module->id }}">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">{{ $module->title }}</h6>
                                    <div class="btn-group">
                                        <button type="button" 
                                                class="btn btn-sm btn-info"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editModuleModal{{ $module->id }}"
                                                data-module-id="{{ $module->id }}"
                                                data-module-title="{{ $module->title }}">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <!-- Modal Edit Modul -->
                                        <div class="modal fade" id="editModuleModal{{ $module->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST" action="{{ route('admin.modules.update', $module->id) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Edit Modul</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label for="editModuleTitle" class="form-label">Judul Modul</label>
                                                                <input type="text" value="{{ old('title', $module->title) }}" class="form-control" id="editModuleTitle" name="title" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-primary">Simpan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>


                                        <form action="{{ route('admin.modules.destroy', $module) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus modul ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0">Daftar Pelajaran</h6>
                                        <button type="button" 
                                                class="btn btn-primary btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#addLessonModal{{ $module->id }}"
                                                data-module-id="{{ $module->id }}">
                                            <i class="fas fa-plus"></i> Tambah Pelajaran
                                        </button>

                                        <!-- Modal Tambah Pelajaran -->
                                        <div class="modal fade" id="addLessonModal{{ $module->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <form action="{{ route('admin.lessons.store', $module->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Tambah Pelajaran Baru</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label for="lessonTitle" class="form-label">Judul Pelajaran <span class="text-danger">*</span></label>
                                                                <input type="text" class="form-control" id="lessonTitle" name="title" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="summernote" class="form-label">Deskripsi</label>
                                                                <textarea class="form-control" id="summernote" name="description" rows="3"></textarea>
                                                                {{-- <small class="text-muted">Jelaskan apa yang akan dipelajari dalam pelajaran ini.</small> --}}
                                                                {{-- <div id="summernote"></div> --}}
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="lessonVideo" class="form-label">Video URL</label>
                                                                <input type="text" 
                                                                    class="form-control" 
                                                                    placeholder="Contoh: https://www.youtube.com/watch?v=dQw4w9WgXcQ" 
                                                                    id="lessonVideo" 
                                                                    name="video_url"
                                                                    onchange="previewYouTubeVideo(this, 'videoPreview')">
                                                                <small class="text-muted">Masukkan URL video YouTube</small>
                                                                <div id="videoPreview" class="youtube-preview hidden"></div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="lessonDuration" class="form-label">Durasi</label>
                                                                <input type="text" 
                                                                    placeholder="Contoh: 1 jam 12 menit" 
                                                                    class="form-control" 
                                                                    id="lessonDuration" 
                                                                    name="duration">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-primary">Simpan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="list-group lesson-list" data-module-id="{{ $module->id }}">
                                        @foreach($module->lessons->sortBy('order') as $lesson)
                                            <div class="list-group-item d-flex justify-content-between align-items-center" 
                                                 data-lesson-id="{{ $lesson->id }}">
                                                <div>
                                                    <i class="fas fa-grip-vertical me-2 text-muted"></i>
                                                    {{ $lesson->title }}
                                                </div>
                                                <div class="btn-group">
                                                    <button type="button" 
                                                            class="btn btn-sm btn-info"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editLessonModal{{ $lesson->id }}"
                                                            data-lesson-id="{{ $lesson->id }}"
                                                            data-lesson-title="{{ $lesson->title }}"
                                                            data-lesson-description="{{ $lesson->description }}"
                                                            data-lesson-video-url="{{ $lesson->video_url }}"
                                                            data-lesson-duration="{{ $lesson->duration }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    
                                                    <!-- Modal Edit Pelajaran -->
                                                    <div class="modal fade" id="editLessonModal{{ $lesson->id }}" tabindex="-1">
                                                        <div class="modal-dialog modal-lg">
                                                            <div class="modal-content">
                                                                <form method="POST" action="{{ route('admin.lessons.update', $lesson->id) }}">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">Edit Pelajaran</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <div class="mb-3">
                                                                            <label for="editLessonTitle" class="form-label">Judul Pelajaran <span class="text-danger">*</span></label>
                                                                            <input type="text" value="{{ old('title', $lesson->title) }}" class="form-control" id="editLessonTitle" name="title" required>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="summernote" class="form-label">Deskripsi</label>
                                                                            <textarea class="form-control" id="summernote" name="description" rows="3">{{ old('description', $lesson->description) }}</textarea>
                                                                            {{-- <div id="summernote"></div> --}}
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="editLessonVideo" class="form-label">Video URL</label>
                                                                            <input type="text" 
                                                                                class="form-control" 
                                                                                placeholder="Contoh: https://www.youtube.com/watch?v=dQw4w9WgXcQ" 
                                                                                id="editLessonVideo" 
                                                                                name="video_url"
                                                                                value="{{ old('video_url', $lesson->video_url) }}"
                                                                                onchange="previewYouTubeVideo(this, 'editVideoPreview')">
                                                                            <small class="text-muted">Masukkan URL video YouTube</small>
                                                                            <div id="editVideoPreview" class="youtube-preview hidden"></div>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="editLessonDuration" class="form-label">Durasi</label>
                                                                            <input type="text" 
                                                                                placeholder="Contoh: 1 jam 12 menit" 
                                                                                class="form-control" 
                                                                                id="editLessonDuration" 
                                                                                value="{{ old('duration', $lesson->duration) }}"
                                                                                name="duration">
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <form action="{{ route('admin.lessons.destroy', $lesson) }}" 
                                                          method="POST" 
                                                          class="d-inline"
                                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelajaran ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted">
                                Belum ada modul. Klik tombol "Tambah Modul" untuk membuat modul baru.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Modul -->
<div class="modal fade" id="addModuleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.modules.store', $course) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Modul Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="moduleTitle" class="form-label">Judul Modul</label>
                        <input type="text" class="form-control" id="moduleTitle" name="title" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>



@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>

<script>
      $('#summernote').summernote({
        // placeholder: 'Hello Bootstrap 5',
        tabsize: 2,
        height: 100
      });

// Fungsi untuk mengekstrak video ID dari URL YouTube
function getYouTubeVideoId(url) {
    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
    const match = url.match(regExp);
    return (match && match[2].length === 11) ? match[2] : null;
}

// Fungsi untuk preview video YouTube
function previewYouTubeVideo(input, previewId) {
    const preview = document.getElementById(previewId);
    const url = input.value.trim();
    
    if (url) {
        const videoId = getYouTubeVideoId(url);
        if (videoId) {
            const embedUrl = `https://www.youtube.com/embed/${videoId}`;
            preview.innerHTML = `<iframe src="${embedUrl}" allowfullscreen></iframe>`;
            preview.classList.remove('hidden');
        } else {
            preview.classList.add('hidden');
            preview.innerHTML = '';
        }
    } else {
        preview.classList.add('hidden');
        preview.innerHTML = '';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi Sortable untuk modul
    new Sortable(document.getElementById('moduleList'), {
        animation: 150,
        handle: '.card-header',
        onEnd: function(evt) {
            const modules = Array.from(evt.to.children).map((card, index) => ({
                id: card.dataset.moduleId,
                order: index
            }));

            fetch('{{ route("admin.modules.reorder", $course) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ modules })
            });
        }
    });

    // Inisialisasi Sortable untuk pelajaran di setiap modul
    document.querySelectorAll('.lesson-list').forEach(list => {
        new Sortable(list, {
            animation: 150,
            handle: '.fa-grip-vertical',
            onEnd: function(evt) {
                const moduleId = evt.to.dataset.moduleId;
                const lessons = Array.from(evt.to.children).map((item, index) => ({
                    id: item.dataset.lessonId,
                    order: index
                }));

                fetch(`{{ url('admin/modules') }}/${moduleId}/lessons/reorder`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ lessons })
                });
            }
        });
    });

    // Handler untuk modal edit modul
    const editModuleModal = document.getElementById('editModuleModal');
    editModuleModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const moduleId = button.dataset.moduleId;
        const moduleTitle = button.dataset.moduleTitle;
        
        const form = editModuleModal.querySelector('#editModuleForm');
        form.action = `{{ url('admin/modules') }}/${moduleId}`;
        form.querySelector('#editModuleTitle').value = moduleTitle;
    });

    // Handler untuk modal tambah pelajaran
    const addLessonModal = document.getElementById('addLessonModal');
    addLessonModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const moduleId = button.dataset.moduleId;
        
        const form = addLessonModal.querySelector('#addLessonForm');
        form.action = `{{ url('admin/modules') }}/${moduleId}/lessons`;
    });

    // Handler untuk modal edit pelajaran
    const editLessonModal = document.getElementById('editLessonModal');
    editLessonModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const lessonId = button.dataset.lessonId;
        const lessonTitle = button.dataset.lessonTitle;
        const lessonDescription = button.dataset.lessonDescription;
        const lessonVideoUrl = button.dataset.lessonVideoUrl;
        const lessonDuration = button.dataset.lessonDuration;
        
        const form = editLessonModal.querySelector('#editLessonForm');
        form.action = `{{ url('admin/lessons') }}/${lessonId}`;
        form.querySelector('#editLessonTitle').value = lessonTitle;
        form.querySelector('#editLessonDescription').value = lessonDescription;
        form.querySelector('#editLessonVideo').value = lessonVideoUrl;
        form.querySelector('#editLessonDuration').value = lessonDuration;
        
        // Preview video jika ada
        if (lessonVideoUrl) {
            previewYouTubeVideo(form.querySelector('#editLessonVideo'), 'editVideoPreview');
        }
    });

    // Reset form saat modal ditutup
    ['addLessonModal', 'editLessonModal'].forEach(modalId => {
        const modal = document.getElementById(modalId);
        modal.addEventListener('hidden.bs.modal', function() {
            const form = modal.querySelector('form');
            form.reset();
            
            // Reset preview
            const preview = modal.querySelector('.youtube-preview');
            if (preview) {
                preview.classList.add('hidden');
                preview.innerHTML = '';
            }
        });
    });
});
</script>
@endpush
@endsection 