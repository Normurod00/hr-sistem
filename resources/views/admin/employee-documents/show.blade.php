@extends('layouts.admin')

@section('title', 'Документ: ' . $document->original_name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <i class="bi {{ $document->document_type_icon }} text-primary me-2"></i>
        {{ $document->document_type_label }}
    </h4>
    <div class="d-flex gap-2">
        <form action="{{ route('admin.employee-documents.reprocess', $document) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-warning btn-sm"><i class="bi bi-arrow-repeat me-1"></i>Переобработать</button>
        </form>
        <a href="{{ route('admin.employee-documents.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Назад
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- File Info -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white"><h6 class="mb-0">Информация о файле</h6></div>
            <div class="card-body">
                <dl class="mb-0">
                    <dt class="small text-muted">Сотрудник</dt>
                    <dd class="fw-semibold">{{ $document->employeeProfile?->user?->name ?? '—' }}</dd>

                    <dt class="small text-muted">Тип документа</dt>
                    <dd><span class="badge bg-light text-dark border"><i class="bi {{ $document->document_type_icon }} me-1"></i>{{ $document->document_type_label }}</span></dd>

                    <dt class="small text-muted">Файл</dt>
                    <dd>{{ $document->original_name }}</dd>

                    <dt class="small text-muted">Размер</dt>
                    <dd>{{ $document->size_formatted }}</dd>

                    <dt class="small text-muted">Загружен</dt>
                    <dd>{{ $document->created_at->format('d.m.Y H:i') }}</dd>

                    <dt class="small text-muted">Загрузил</dt>
                    <dd>{{ $document->uploader?->name ?? '—' }}</dd>

                    <dt class="small text-muted">Статус</dt>
                    <dd><span class="badge bg-{{ $document->status_color }} fs-6">{{ $document->status_label }}</span></dd>

                    @if($document->processed_at)
                    <dt class="small text-muted">Обработан</dt>
                    <dd>{{ $document->processed_at->format('d.m.Y H:i') }}</dd>
                    @endif

                    @if($document->error_message)
                    <dt class="small text-muted">Ошибка</dt>
                    <dd class="text-danger">{{ $document->error_message }}</dd>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    <!-- AI Analysis Result -->
    <div class="col-lg-8">
        @if($document->has_analysis)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-robot me-2"></i>Результат AI анализа</h6>
            </div>
            <div class="card-body">
                @php $analysis = $document->analysis_result; @endphp

                @if(!empty($analysis['summary']))
                <div class="mb-3">
                    <strong class="small text-muted">Summary</strong>
                    <p>{{ $analysis['summary'] }}</p>
                </div>
                @endif

                @if(!empty($analysis['position_title']))
                <div class="mb-3">
                    <strong class="small text-muted">Должность</strong>
                    <p class="fw-semibold">{{ $analysis['position_title'] }}</p>
                </div>
                @endif

                @if(!empty($analysis['skills']))
                <div class="mb-3">
                    <strong class="small text-muted">Навыки</strong>
                    <div class="d-flex flex-wrap gap-1 mt-1">
                        @foreach($analysis['skills'] as $skill)
                        <span class="badge bg-primary bg-opacity-10 text-primary border">{{ is_array($skill) ? ($skill['name'] ?? $skill) : $skill }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(!empty($analysis['education']))
                <div class="mb-3">
                    <strong class="small text-muted">Образование</strong>
                    @foreach($analysis['education'] as $edu)
                    <div class="p-2 bg-light rounded mb-1 small">
                        {{ is_array($edu) ? (($edu['degree'] ?? '') . ' — ' . ($edu['institution'] ?? '') . ' (' . ($edu['year'] ?? '') . ')') : $edu }}
                    </div>
                    @endforeach
                </div>
                @endif

                @if(!empty($analysis['domains']))
                <div class="mb-3">
                    <strong class="small text-muted">Области</strong>
                    <div class="d-flex flex-wrap gap-1 mt-1">
                        @foreach($analysis['domains'] as $domain)
                        <span class="badge bg-success bg-opacity-10 text-success border">{{ $domain }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(!empty($analysis['contact_info']))
                <div class="mb-3">
                    <strong class="small text-muted">Контакты</strong>
                    <div class="small">
                        @foreach($analysis['contact_info'] as $key => $val)
                            @if($val)<div>{{ $key }}: {{ $val }}</div>@endif
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="mt-2 small text-muted">
                    <i class="bi bi-check-circle me-1"></i>Текст извлечён: {{ $analysis['extracted_text_length'] ?? 0 }} символов
                </div>
            </div>
        </div>
        @else
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-robot" style="font-size: 48px; opacity: 0.3;"></i>
                <div class="mt-2">AI анализ ещё не выполнен</div>
                @if($document->status === 'failed')
                <div class="mt-1 text-danger">{{ $document->error_message }}</div>
                @endif
            </div>
        </div>
        @endif

        <!-- Extracted Text -->
        @if($document->parsed_text)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0">Извлечённый текст</h6></div>
            <div class="card-body">
                <pre class="mb-0 small" style="white-space: pre-wrap; max-height: 400px; overflow-y: auto;">{{ Str::limit($document->parsed_text, 5000) }}</pre>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
