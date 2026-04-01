@extends('layouts.admin')

@section('title', 'Номинации')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <i class="bi bi-star-fill text-warning me-2"></i>
        Список номинаций
    </h4>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Все статусы</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Ожидание</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Утверждена</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Отклонена</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="type" class="form-select">
                    <option value="">Все типы</option>
                    @foreach($nominationTypes as $type)
                    <option value="{{ $type->id }}" {{ request('type') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search me-1"></i>Фильтровать
                </button>
                <a href="{{ route('admin.recognition.nominations') }}" class="btn btn-outline-secondary">Сбросить</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Номинант</th>
                        <th>Тип</th>
                        <th>Номинирующий</th>
                        <th>Причина</th>
                        <th>Статус</th>
                        <th>Дата</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($nominations as $nomination)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $nomination->nominee->name ?? 'Unknown' }}</div>
                        </td>
                        <td>
                            <span class="badge" style="background: {{ $nomination->nominationType->color }}20; color: {{ $nomination->nominationType->color }};">
                                <i class="bi {{ $nomination->nominationType->icon }} me-1"></i>
                                {{ $nomination->nominationType->name }}
                            </span>
                        </td>
                        <td class="text-muted">{{ $nomination->nominator->name ?? 'Unknown' }}</td>
                        <td class="text-muted small">{{ Str::limit($nomination->reason, 50) }}</td>
                        <td>
                            <span class="badge bg-{{ $nomination->status->color() }}">
                                {{ $nomination->status->label() }}
                            </span>
                        </td>
                        <td class="text-muted small">{{ $nomination->created_at->format('d.m.Y') }}</td>
                        <td>
                            @if($nomination->isPending())
                            <div class="d-flex gap-1">
                                <form action="{{ route('admin.recognition.approve-nomination', $nomination) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                        data-bs-toggle="modal" data-bs-target="#rejectModal{{ $nomination->id }}">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>

                            <!-- Reject Modal -->
                            <div class="modal fade" id="rejectModal{{ $nomination->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.recognition.reject-nomination', $nomination) }}" method="POST">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Отклонить номинацию</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <textarea name="comment" class="form-control" rows="3" placeholder="Причина..." required></textarea>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                                <button type="submit" class="btn btn-danger">Отклонить</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox" style="font-size: 48px; opacity: 0.3;"></i>
                            <div class="mt-2">Номинации не найдены</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($nominations->hasPages())
    <div class="card-footer bg-white">
        {{ $nominations->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection