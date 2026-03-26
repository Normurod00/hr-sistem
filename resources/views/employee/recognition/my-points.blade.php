@extends('employee.layouts.app')

@section('title', 'Менинг балларим')
@section('page-title', 'Баллар тарихи')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-card-icon primary">
                    <i class="bi bi-coin"></i>
                </div>
                <div>
                    <div class="stat-label">Жами баллар</div>
                    <div class="stat-value">{{ number_format($balance->total_points) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-card-icon success">
                    <i class="bi bi-calendar-month"></i>
                </div>
                <div>
                    <div class="stat-label">Ойлик</div>
                    <div class="stat-value">{{ number_format($balance->monthly_points) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-card-icon info">
                    <i class="bi bi-calendar3"></i>
                </div>
                <div>
                    <div class="stat-label">Кварталлик</div>
                    <div class="stat-value">{{ number_format($balance->quarterly_points) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-card-icon warning">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div>
                    <div class="stat-label">Йиллик</div>
                    <div class="stat-value">{{ number_format($balance->yearly_points) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-clock-history text-info me-2"></i>
            Баллар тарихи
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Сана</th>
                        <th>Тавсиф</th>
                        <th>Манба</th>
                        <th class="text-end">Балл</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $point)
                    <tr>
                        <td>
                            <div class="small">{{ $point->created_at->format('d.m.Y') }}</div>
                            <div class="text-muted small">{{ $point->created_at->format('H:i') }}</div>
                        </td>
                        <td>{{ $point->description }}</td>
                        <td>
                            <span class="badge bg-secondary">
                                <i class="bi {{ $point->source_type->icon() }} me-1"></i>
                                {{ $point->source_type->label() }}
                            </span>
                        </td>
                        <td class="text-end">
                            @if($point->points >= 0)
                            <span class="text-success fw-bold">+{{ number_format($point->points) }}</span>
                            @else
                            <span class="text-danger fw-bold">{{ number_format($point->points) }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox" style="font-size: 48px; opacity: 0.3;"></i>
                            <div class="mt-2">Тарих йўқ</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($history->hasPages())
    <div class="card-footer">
        {{ $history->links() }}
    </div>
    @endif
</div>
@endsection
