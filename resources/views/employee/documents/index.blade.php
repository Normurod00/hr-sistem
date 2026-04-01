@extends('employee.layouts.app')

@section('title', 'Мои документы')
@section('page-title', 'Мои документы')

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <!-- Upload -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-cloud-upload me-2"></i>Загрузить документ</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('employee.documents.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <select name="document_type" class="form-select" required>
                                <option value="diploma">Диплом</option>
                                <option value="certificate">Сертификат</option>
                                <option value="contract">Трудовой договор</option>
                                <option value="id_document">Удостоверение</option>
                                <option value="medical">Медицинская справка</option>
                                <option value="other">Другое</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <input type="file" name="file" class="form-control" required accept=".pdf,.doc,.docx,.txt,.rtf,.jpg,.jpeg,.png">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-brb w-100"><i class="bi bi-upload me-1"></i>Загрузить</button>
                        </div>
                    </div>
                    <div class="form-text mt-2">PDF, DOC, DOCX, TXT, RTF, JPG, PNG. Максимум 10 МБ. AI обработка начнётся автоматически.</div>
                </form>
            </div>
        </div>

        <!-- Documents List -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Загруженные документы</h5>
            </div>
            <div class="card-body p-0">
                @forelse($documents as $doc)
                <div class="p-3 border-bottom d-flex align-items-center gap-3">
                    <div class="rounded-3 p-2 bg-light">
                        <i class="bi {{ $doc->document_type_icon }} fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold">{{ $doc->original_name }}</div>
                        <div class="small text-muted">
                            {{ $doc->document_type_label }} &middot; {{ $doc->size_formatted }} &middot; {{ $doc->created_at->format('d.m.Y') }}
                        </div>
                    </div>
                    <span class="badge bg-{{ $doc->status_color }}">{{ $doc->status_label }}</span>
                </div>
                @empty
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-file-earmark-x" style="font-size: 48px; opacity: 0.3;"></i>
                    <div class="mt-2">У вас нет загруженных документов</div>
                </div>
                @endforelse
            </div>
            @if($documents->hasPages())
            <div class="card-footer bg-white">{{ $documents->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
