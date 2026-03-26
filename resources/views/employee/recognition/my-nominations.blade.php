@extends('employee.layouts.app')

@section('title', 'Менинг номинацияларим')
@section('page-title', 'Менинг номинацияларим')

@section('content')
<div class="row g-4 mb-4">
    <!-- Stats -->
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-card-icon success">
                    <i class="bi bi-coin"></i>
                </div>
                <div>
                    <div class="stat-label">Жами баллар</div>
                    <div class="stat-value">{{ number_format($myBalance->total_points) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-card-icon warning">
                    <i class="bi bi-star-fill"></i>
                </div>
                <div>
                    <div class="stat-label">Олинган номинациялар</div>
                    <div class="stat-value">{{ $myBalance->nominations_received }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-card-icon info">
                    <i class="bi bi-hand-thumbs-up-fill"></i>
                </div>
                <div>
                    <div class="stat-label">Берилган номинациялар</div>
                    <div class="stat-value">{{ $myBalance->nominations_given }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-card-icon primary">
                    <i class="bi bi-trophy-fill"></i>
                </div>
                <div>
                    <div class="stat-label">Мукофотлар</div>
                    <div class="stat-value">{{ $myBalance->awards_won }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Received Nominations -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-inbox-fill text-success me-2"></i>
                    Олинган номинациялар
                </h5>
            </div>
            <div class="card-body p-0">
                @forelse($nominationsReceived as $nomination)
                <div class="p-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi {{ $nomination->nominationType->icon }}"
                               style="color: {{ $nomination->nominationType->color }}; font-size: 20px;"></i>
                            <span class="fw-semibold">{{ $nomination->nominationType->name }}</span>
                        </div>
                        <span class="badge bg-{{ $nomination->status->color() }}">
                            {{ $nomination->status->label() }}
                        </span>
                    </div>
                    <p class="text-muted small mb-2">{{ Str::limit($nomination->reason, 100) }}</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="bi bi-person me-1"></i>{{ $nomination->nominator->name ?? 'Unknown' }}
                        </small>
                        <small class="text-muted">{{ $nomination->created_at->diffForHumans() }}</small>
                    </div>
                </div>
                @empty
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox" style="font-size: 48px; opacity: 0.3;"></i>
                    <div class="mt-2">Номинациялар йўқ</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Given Nominations -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-send-fill text-info me-2"></i>
                    Берилган номинациялар
                </h5>
                <a href="{{ route('employee.recognition.nominate') }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>Янги
                </a>
            </div>
            <div class="card-body p-0">
                @forelse($nominationsGiven as $nomination)
                <div class="p-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi {{ $nomination->nominationType->icon }}"
                               style="color: {{ $nomination->nominationType->color }}; font-size: 20px;"></i>
                            <span class="fw-semibold">{{ $nomination->nominee->name ?? 'Unknown' }}</span>
                        </div>
                        <span class="badge bg-{{ $nomination->status->color() }}">
                            {{ $nomination->status->label() }}
                        </span>
                    </div>
                    <p class="text-muted small mb-2">{{ Str::limit($nomination->reason, 100) }}</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">{{ $nomination->nominationType->name }}</small>
                        <small class="text-muted">{{ $nomination->created_at->diffForHumans() }}</small>
                    </div>
                </div>
                @empty
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox" style="font-size: 48px; opacity: 0.3;"></i>
                    <div class="mt-2">Номинациялар йўқ</div>
                    <a href="{{ route('employee.recognition.nominate') }}" class="btn btn-primary mt-3">
                        <i class="bi bi-plus-lg me-1"></i>Номинация қилиш
                    </a>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- My Awards -->
@if($myAwards->count() > 0)
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-trophy-fill text-warning me-2"></i>
            Менинг мукофотларим
        </h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @foreach($myAwards as $award)
            <div class="col-md-6 col-lg-4">
                <div class="p-3 rounded-3 text-center" style="background: linear-gradient(135deg, {{ $award->award_type->color() }}20, {{ $award->award_type->color() }}10);">
                    <i class="bi {{ $award->award_type->icon() }}" style="font-size: 32px; color: {{ $award->award_type->color() }};"></i>
                    <div class="fw-bold mt-2">{{ $award->award_type->label() }}</div>
                    <div class="text-muted small">{{ $award->period_label }}</div>
                    <div class="badge bg-success mt-2">+{{ $award->points_awarded }} балл</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
@endsection
