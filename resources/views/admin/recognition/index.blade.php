@extends('admin.layouts.app')

@section('title', 'Система признания')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <i class="bi bi-trophy-fill text-warning me-2"></i>
        Система признания
    </h4>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.recognition.create-award') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Выдать награду
        </a>
    </div>
</div>

<!-- Stats -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-warning bg-opacity-10">
                        <i class="bi bi-star-fill text-warning fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Номинации за месяц</div>
                        <div class="fs-4 fw-bold">{{ $stats['total_nominations_this_month'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-success bg-opacity-10">
                        <i class="bi bi-check-circle-fill text-success fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Утверждено</div>
                        <div class="fs-4 fw-bold">{{ $stats['approved_nominations_this_month'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-danger bg-opacity-10">
                        <i class="bi bi-hourglass-split text-danger fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Ожидание</div>
                        <div class="fs-4 fw-bold">{{ $stats['pending_nominations'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-primary bg-opacity-10">
                        <i class="bi bi-people-fill text-primary fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Активные сотрудники</div>
                        <div class="fs-4 fw-bold">{{ $stats['active_employees'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Pending Nominations -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Ожидающие номинации</h5>
                <a href="{{ route('admin.recognition.nominations') }}" class="btn btn-sm btn-outline-primary">Все</a>
            </div>
            <div class="card-body p-0">
                @forelse($pendingNominations as $nomination)
                <div class="p-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="d-flex gap-3">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-2" style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi {{ $nomination->nominationType->icon }}" style="color: {{ $nomination->nominationType->color }};"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $nomination->nominee->name ?? 'Unknown' }}</div>
                                <div class="small text-muted">
                                    {{ $nomination->nominationType->name }} |
                                    <i class="bi bi-person me-1"></i>{{ $nomination->nominator->name ?? 'Unknown' }}
                                </div>
                                <div class="small text-muted mt-1">{{ Str::limit($nomination->reason, 80) }}</div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
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
                    </div>
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
                                    <div class="mb-3">
                                        <label class="form-label">Причина</label>
                                        <textarea name="comment" class="form-control" rows="3" required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                    <button type="submit" class="btn btn-danger">Отклонить</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-check-circle" style="font-size: 48px; opacity: 0.3;"></i>
                    <div class="mt-2">Нет ожидающих номинаций</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Quick Actions & Recent Awards -->
    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Быстрые действия</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.recognition.nominations') }}" class="btn btn-outline-primary">
                        <i class="bi bi-list-check me-2"></i>Номинации
                    </a>
                    <a href="{{ route('admin.recognition.awards') }}" class="btn btn-outline-warning">
                        <i class="bi bi-trophy me-2"></i>Награды
                    </a>
                    <a href="{{ route('admin.recognition.nomination-types') }}" class="btn btn-outline-info">
                        <i class="bi bi-tags me-2"></i>Типы номинаций
                    </a>
                    <a href="{{ route('admin.recognition.leaderboard') }}" class="btn btn-outline-success">
                        <i class="bi bi-bar-chart me-2"></i>Рейтинг
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Awards -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Последние награды</h5>
            </div>
            <div class="card-body p-0">
                @forelse($recentAwards as $award)
                <div class="p-3 border-bottom d-flex align-items-center gap-3">
                    <div class="rounded-circle p-2" style="background: {{ $award->award_type->color() }}20;">
                        <i class="bi {{ $award->award_type->icon() }}" style="color: {{ $award->award_type->color() }};"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold small">{{ $award->user->name ?? 'Unknown' }}</div>
                        <div class="text-muted small">{{ $award->award_type->label() }}</div>
                    </div>
                    @if(!$award->is_published)
                    <span class="badge bg-warning text-dark">Не объявлена</span>
                    @endif
                </div>
                @empty
                <div class="text-center py-4 text-muted">
                    <div class="small">Нет наград</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection