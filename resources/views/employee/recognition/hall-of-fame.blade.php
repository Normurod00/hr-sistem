@extends('employee.layouts.app')

@section('title', 'Шон-шараф зали')
@section('page-title', 'Шон-шараф зали')

@section('content')
<style>
    .hall-section {
        margin-bottom: 40px;
    }
    .hall-title {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }
    .hall-title-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
    }
    .hall-title h3 {
        margin: 0;
        font-weight: 700;
    }

    .winner-card {
        background: white;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        text-align: center;
        transition: all 0.2s;
    }
    .winner-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }
    .winner-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        margin: 0 auto 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        font-weight: bold;
        color: white;
    }
    .winner-name {
        font-weight: 700;
        font-size: 16px;
        margin-bottom: 4px;
    }
    .winner-period {
        font-size: 13px;
        color: #6c757d;
        margin-bottom: 8px;
    }
    .winner-stats {
        display: flex;
        justify-content: center;
        gap: 16px;
        font-size: 12px;
        color: #6c757d;
    }
</style>

<!-- Employees of the Year -->
<div class="hall-section">
    <div class="hall-title">
        <div class="hall-title-icon" style="background: linear-gradient(135deg, #FFD700, #FFA500);">
            <i class="bi bi-gem"></i>
        </div>
        <h3>Йил ходимлари</h3>
    </div>
    <div class="row g-4">
        @forelse($employeesOfYear as $award)
        <div class="col-md-4 col-lg-3">
            <div class="winner-card">
                <div class="winner-avatar" style="background: linear-gradient(135deg, #FFD700, #FFA500);">
                    {{ strtoupper(substr($award->user->name ?? 'U', 0, 1)) }}
                </div>
                <div class="winner-name">{{ $award->user->name ?? 'Unknown' }}</div>
                <div class="winner-period">{{ $award->period_label }}</div>
                <div class="winner-stats">
                    <span><i class="bi bi-star-fill text-warning me-1"></i>{{ $award->nominations_count }}</span>
                    <span><i class="bi bi-coin text-success me-1"></i>{{ $award->points_awarded }}</span>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="text-center py-4 text-muted">
                <i class="bi bi-hourglass-split" style="font-size: 32px; opacity: 0.3;"></i>
                <div class="mt-2">Ҳали эълон қилинмаган</div>
            </div>
        </div>
        @endforelse
    </div>
</div>

<!-- Employees of the Quarter -->
<div class="hall-section">
    <div class="hall-title">
        <div class="hall-title-icon" style="background: linear-gradient(135deg, #C0C0C0, #808080);">
            <i class="bi bi-trophy-fill"></i>
        </div>
        <h3>Квартал ходимлари</h3>
    </div>
    <div class="row g-4">
        @forelse($employeesOfQuarter as $award)
        <div class="col-md-4 col-lg-3">
            <div class="winner-card">
                <div class="winner-avatar" style="background: linear-gradient(135deg, #C0C0C0, #808080);">
                    {{ strtoupper(substr($award->user->name ?? 'U', 0, 1)) }}
                </div>
                <div class="winner-name">{{ $award->user->name ?? 'Unknown' }}</div>
                <div class="winner-period">{{ $award->period_label }}</div>
                <div class="winner-stats">
                    <span><i class="bi bi-star-fill text-warning me-1"></i>{{ $award->nominations_count }}</span>
                    <span><i class="bi bi-coin text-success me-1"></i>{{ $award->points_awarded }}</span>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="text-center py-4 text-muted">
                <i class="bi bi-hourglass-split" style="font-size: 32px; opacity: 0.3;"></i>
                <div class="mt-2">Ҳали эълон қилинмаган</div>
            </div>
        </div>
        @endforelse
    </div>
</div>

<!-- Employees of the Month -->
<div class="hall-section">
    <div class="hall-title">
        <div class="hall-title-icon" style="background: linear-gradient(135deg, #CD7F32, #8B4513);">
            <i class="bi bi-award-fill"></i>
        </div>
        <h3>Ой ходимлари</h3>
    </div>
    <div class="row g-4">
        @forelse($employeesOfMonth as $award)
        <div class="col-md-4 col-lg-3">
            <div class="winner-card">
                <div class="winner-avatar" style="background: linear-gradient(135deg, #CD7F32, #8B4513);">
                    {{ strtoupper(substr($award->user->name ?? 'U', 0, 1)) }}
                </div>
                <div class="winner-name">{{ $award->user->name ?? 'Unknown' }}</div>
                <div class="winner-period">{{ $award->period_label }}</div>
                <div class="winner-stats">
                    <span><i class="bi bi-star-fill text-warning me-1"></i>{{ $award->nominations_count }}</span>
                    <span><i class="bi bi-coin text-success me-1"></i>{{ $award->points_awarded }}</span>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="text-center py-4 text-muted">
                <i class="bi bi-hourglass-split" style="font-size: 32px; opacity: 0.3;"></i>
                <div class="mt-2">Ҳали эълон қилинмаган</div>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection
