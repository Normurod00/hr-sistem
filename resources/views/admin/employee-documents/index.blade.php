@extends('layouts.admin')

@section('title', 'Документы сотрудников — AI анализ')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <i class="bi bi-file-earmark-medical text-primary me-2"></i>
        Документы сотрудников — AI анализ
    </h4>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
        <i class="bi bi-cloud-upload me-1"></i>Загрузить документ
    </button>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-2">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-muted small">Всего</div>
                <div class="fs-3 fw-bold">{{ $kpi['total'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-muted small">Обработано</div>
                <div class="fs-3 fw-bold text-success">{{ $kpi['parsed'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-muted small">Ожидают</div>
                <div class="fs-3 fw-bold text-warning">{{ $kpi['pending'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-muted small">В обработке</div>
                <div class="fs-3 fw-bold text-info">{{ $kpi['processing'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-muted small">Ошибки</div>
                <div class="fs-3 fw-bold text-danger">{{ $kpi['failed'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-muted small">Успешность</div>
                <div class="fs-3 fw-bold {{ $kpi['success_rate'] >= 80 ? 'text-success' : ($kpi['success_rate'] >= 50 ? 'text-warning' : 'text-danger') }}">{{ $kpi['success_rate'] }}%</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Type Distribution -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0">По типу документа</h6></div>
            <div class="card-body">
                <canvas id="typeChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <!-- Status Distribution -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0">По статусу обработки</h6></div>
            <div class="card-body">
                <canvas id="statusChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Статус</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Все</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Ожидает</option>
                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>В обработке</option>
                    <option value="parsed" {{ request('status') === 'parsed' ? 'selected' : '' }}>Обработан</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Ошибка</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Тип документа</label>
                <select name="document_type" class="form-select form-select-sm">
                    <option value="">Все</option>
                    <option value="contract" {{ request('document_type') === 'contract' ? 'selected' : '' }}>Трудовой договор</option>
                    <option value="diploma" {{ request('document_type') === 'diploma' ? 'selected' : '' }}>Диплом</option>
                    <option value="certificate" {{ request('document_type') === 'certificate' ? 'selected' : '' }}>Сертификат</option>
                    <option value="id_document" {{ request('document_type') === 'id_document' ? 'selected' : '' }}>Удостоверение</option>
                    <option value="medical" {{ request('document_type') === 'medical' ? 'selected' : '' }}>Медицинская</option>
                    <option value="other" {{ request('document_type') === 'other' ? 'selected' : '' }}>Другое</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Сотрудник</label>
                <select name="employee_id" class="form-select form-select-sm">
                    <option value="">Все</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->user->name ?? $emp->employee_number }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-primary me-2"><i class="bi bi-search me-1"></i>Фильтр</button>
                <a href="{{ route('admin.employee-documents.index') }}" class="btn btn-sm btn-outline-secondary">Сбросить</a>
            </div>
        </form>
    </div>
</div>

<!-- Documents Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Сотрудник</th>
                        <th>Тип</th>
                        <th>Файл</th>
                        <th>Размер</th>
                        <th>Статус</th>
                        <th>Обработан</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $doc)
                    <tr>
                        <td class="fw-semibold">{{ $doc->employeeProfile?->user?->name ?? '—' }}</td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                <i class="bi {{ $doc->document_type_icon }} me-1"></i>{{ $doc->document_type_label }}
                            </span>
                        </td>
                        <td class="small">{{ Str::limit($doc->original_name, 30) }}</td>
                        <td class="small text-muted">{{ $doc->size_formatted }}</td>
                        <td><span class="badge bg-{{ $doc->status_color }}">{{ $doc->status_label }}</span></td>
                        <td class="small text-muted">{{ $doc->processed_at?->format('d.m.Y H:i') ?? '—' }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.employee-documents.show', $doc) }}" class="btn btn-sm btn-outline-primary" title="Просмотр">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <form action="{{ route('admin.employee-documents.reprocess', $doc) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-warning" title="Переобработать">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.employee-documents.destroy', $doc) }}" method="POST" class="d-inline" onsubmit="return confirm('Удалить документ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Удалить">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-file-earmark-x" style="font-size: 48px; opacity: 0.3;"></i>
                            <div class="mt-2">Документы не найдены</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($documents->hasPages())
    <div class="card-footer bg-white">{{ $documents->links() }}</div>
    @endif
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.employee-documents.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-cloud-upload me-2"></i>Загрузить документ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Сотрудник</label>
                        <select name="employee_profile_id" class="form-select" required>
                            <option value="">— Выберите —</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->user->name ?? $emp->employee_number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Тип документа</label>
                        <select name="document_type" class="form-select" required>
                            <option value="contract">Трудовой договор</option>
                            <option value="diploma">Диплом</option>
                            <option value="certificate">Сертификат</option>
                            <option value="id_document">Удостоверение личности</option>
                            <option value="medical">Медицинская справка</option>
                            <option value="other">Другое</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Файл</label>
                        <input type="file" name="file" class="form-control" required accept=".pdf,.doc,.docx,.txt,.rtf,.jpg,.jpeg,.png">
                        <div class="form-text">PDF, DOC, DOCX, TXT, RTF, JPG, PNG. Максимум 10 МБ.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-1"></i>Загрузить</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const typeData = @json($typeDistribution);
const typeLabels = {contract:'Договор',diploma:'Диплом',certificate:'Сертификат',id_document:'Удостоверение',medical:'Медицинская',other:'Другое'};
const typeColors = ['#3b82f6','#8b5cf6','#22c55e','#f59e0b','#ec4899','#6b7280'];

new Chart(document.getElementById('typeChart'), {
    type: 'doughnut',
    data: {
        labels: typeData.map(d => typeLabels[d.document_type] || d.document_type),
        datasets: [{ data: typeData.map(d => d.count), backgroundColor: typeColors.slice(0, typeData.length) }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } } }
});

const statusData = @json($statusDistribution);
const statusColors = {pending:'#f59e0b',processing:'#06b6d4',parsed:'#22c55e',failed:'#ef4444'};
const statusLabels = {pending:'Ожидает',processing:'Обработка',parsed:'Обработан',failed:'Ошибка'};

new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: statusData.map(d => statusLabels[d.status] || d.status),
        datasets: [{ data: statusData.map(d => d.count), backgroundColor: statusData.map(d => statusColors[d.status] || '#6b7280') }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } } }
});
</script>
@endsection
