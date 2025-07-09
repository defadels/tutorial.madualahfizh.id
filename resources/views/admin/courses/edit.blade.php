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
                                                data-bs-target="#editModuleModal"
                                                data-module-id="{{ $module->id }}"
                                                data-module-title="{{ $module->title }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
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
                                                data-bs-target="#addLessonModal"
                                                data-module-id="{{ $module->id }}">
                                            <i class="fas fa-plus"></i> Tambah Pelajaran
                                        </button>
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
                                                            data-bs-target="#editLessonModal"
                                                            data-lesson-id="{{ $lesson->id }}"
                                                            data-lesson-title="{{ $lesson->title }}"
                                                            data-lesson-description="{{ $lesson->description }}"
                                                            data-lesson-duration="{{ $lesson->duration }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
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

<!-- Modal Edit Modul -->
<div class="modal fade" id="editModuleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editModuleForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Modul</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editModuleTitle" class="form-label">Judul Modul</label>
                        <input type="text" class="form-control" id="editModuleTitle" name="title" required>
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

<!-- Modal Tambah Pelajaran -->
<div class="modal fade" id="addLessonModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="addLessonForm" method="POST" enctype="multipart/form-data">
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
                        <label for="lessonDescription" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="lessonDescription" name="description" rows="3"></textarea>
                        <small class="text-muted">Jelaskan apa yang akan dipelajari dalam pelajaran ini.</small>
                    </div>
                    <div class="mb-3">
                        <label for="lessonVideo" class="form-label">Video</label>
                        <input type="file" class="form-control" id="lessonVideo" name="video" accept="video/*" onchange="previewVideo(this, 'videoPreview')">
                        <small class="text-muted">Format: MP4, MOV, AVI. Ukuran maksimal: 100MB</small>
                        <div id="videoPreview" class="mt-2 d-none">
                            <video controls style="max-width: 100%; max-height: 300px;">
                                <source src="" type="video/mp4">
                                Browser Anda tidak mendukung tag video.
                            </video>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="lessonDuration" class="form-label">Durasi (detik)</label>
                        <input type="number" class="form-control" id="lessonDuration" name="duration">
                        <small class="text-muted">Akan terisi otomatis saat memilih video</small>
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

<!-- Modal Edit Pelajaran -->
<div class="modal fade" id="editLessonModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editLessonForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Pelajaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editLessonTitle" class="form-label">Judul Pelajaran <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editLessonTitle" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="editLessonDescription" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="editLessonDescription" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editLessonVideo" class="form-label">Video Baru</label>
                        <input type="file" class="form-control" id="editLessonVideo" name="video" accept="video/*" onchange="previewVideo(this, 'editVideoPreview')">
                        <small class="text-muted">Biarkan kosong jika tidak ingin mengubah video</small>
                        <div id="editVideoPreview" class="mt-2 d-none">
                            <video controls style="max-width: 100%; max-height: 300px;">
                                <source src="" type="video/mp4">
                                Browser Anda tidak mendukung tag video.
                            </video>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editLessonDuration" class="form-label">Durasi (detik)</label>
                        <input type="number" class="form-control" id="editLessonDuration" name="duration">
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

    // Fungsi untuk preview video
    function previewVideo(input, previewId) {
        const preview = document.getElementById(previewId);
        const video = preview.querySelector('video');
        
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const url = URL.createObjectURL(file);
            
            video.src = url;
            preview.classList.remove('d-none');
            
            // Set durasi otomatis
            video.onloadedmetadata = function() {
                const durationInput = input.closest('.modal-content').querySelector('[name="duration"]');
                durationInput.value = Math.round(video.duration);
            };
        } else {
            preview.classList.add('d-none');
            video.src = '';
        }
    }

    // Handler untuk modal edit pelajaran
    const editLessonModal = document.getElementById('editLessonModal');
    editLessonModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const lessonId = button.dataset.lessonId;
        const lessonTitle = button.dataset.lessonTitle;
        const lessonDescription = button.dataset.lessonDescription;
        const lessonDuration = button.dataset.lessonDuration;
        
        const form = editLessonModal.querySelector('#editLessonForm');
        form.action = `{{ url('admin/lessons') }}/${lessonId}`;
        form.querySelector('#editLessonTitle').value = lessonTitle;
        form.querySelector('#editLessonDescription').value = lessonDescription;
        form.querySelector('#editLessonDuration').value = lessonDuration;
        
        // Reset video preview
        const preview = document.getElementById('editVideoPreview');
        preview.classList.add('d-none');
        preview.querySelector('video').src = '';
    });

    // Reset form dan preview saat modal ditutup
    ['addLessonModal', 'editLessonModal'].forEach(modalId => {
        const modal = document.getElementById(modalId);
        modal.addEventListener('hidden.bs.modal', function() {
            const form = modal.querySelector('form');
            form.reset();
            
            const preview = modal.querySelector('[id$="VideoPreview"]');
            if (preview) {
                preview.classList.add('d-none');
                preview.querySelector('video').src = '';
            }
        });
    });
});
</script>
@endpush
@endsection 