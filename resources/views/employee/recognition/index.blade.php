@extends('employee.layouts.app')

@section('title', 'Эътироф тахтаси')
@section('page-title', 'Эътироф тахтаси')

@section('content')
<style>
    .award-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 16px;
        padding: 24px;
        color: white;
        position: relative;
        overflow: hidden;
    }
    .award-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    }
    .award-card.gold { background: linear-gradient(135deg, #f5af19 0%, #f12711 100%); }
    .award-card.silver { background: linear-gradient(135deg, #bdc3c7 0%, #2c3e50 100%); }
    .award-card.bronze { background: linear-gradient(135deg, #cd7f32 0%, #8b4513 100%); }

    .award-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        border: 4px solid rgba(255,255,255,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,0.2);
        font-size: 32px;
        font-weight: bold;
    }
    .award-title { font-size: 14px; opacity: 0.9; margin-bottom: 4px; }
    .award-name { font-size: 20px; font-weight: 700; margin-bottom: 4px; }
    .award-position { font-size: 13px; opacity: 0.8; }

    .leaderboard-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        border-radius: 10px;
        transition: all 0.2s;
    }
    .leaderboard-item:hover { background: #f8f9fa; }
    .leaderboard-rank {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
    }
    .rank-1 { background: linear-gradient(135deg, #FFD700, #FFA500); color: white; }
    .rank-2 { background: linear-gradient(135deg, #C0C0C0, #A0A0A0); color: white; }
    .rank-3 { background: linear-gradient(135deg, #CD7F32, #8B4513); color: white; }
    .rank-default { background: #e9ecef; color: #495057; }

    .leaderboard-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea, #764ba2);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
    }
    .leaderboard-info { flex: 1; }
    .leaderboard-name { font-weight: 600; font-size: 14px; }
    .leaderboard-dept { font-size: 12px; color: #6c757d; }
    .leaderboard-points {
        font-weight: 700;
        font-size: 16px;
        color: #667eea;
    }

    .nomination-type-card {
        text-align: center;
        padding: 20px;
        border-radius: 12px;
        border: 2px solid #e9ecef;
        transition: all 0.2s;
        cursor: pointer;
    }
    .nomination-type-card:hover {
        border-color: #667eea;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
    }
    .nomination-type-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 12px;
        font-size: 24px;
        color: white;
    }
    .nomination-type-name { font-weight: 600; margin-bottom: 4px; }
    .nomination-type-points { font-size: 12px; color: #6c757d; }
</style>

<!-- Stats Row -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="stat-card-icon primary">
                    <i class="bi bi-trophy-fill"></i>
                </div>
                <div>
                    <div class="stat-label">Ой номинациялари</div>
                    <div class="stat-value">{{ $stats['total_nominations_this_month'] }}</div>
                </div>
            </div>
            <div class="text-muted small">
                {{ $stats['approved_nominations_this_month'] }} та тасдиқланган
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="stat-card-icon warning">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div>
                    <div class="stat-label">Кутилмоқда</div>
                    <div class="stat-value">{{ $stats['pending_nominations'] }}</div>
                </div>
            </div>
            <div class="text-muted small">
                Кўриб чиқилаётган номинациялар
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="stat-card-icon success">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <div class="stat-label">Фаол ходимлар</div>
                    <div class="stat-value">{{ $stats['active_employees'] }}</div>
                </div>
            </div>
            <div class="text-muted small">
                Баллга эга ходимлар
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <a href="{{ route('employee.recognition.nominate') }}" class="text-decoration-none">
            <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-card-icon" style="background: rgba(255,255,255,0.2);">
                        <i class="bi bi-plus-lg"></i>
                    </div>
                    <div>
                        <div class="stat-label" style="color: rgba(255,255,255,0.8);">Янги номинация</div>
                        <div class="stat-value">Номинация қилиш</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Awards Section -->
<div class="row g-4 mb-4">
    @if($stats['employee_of_month'])
    <div class="col-md-4">
        <div class="award-card gold">
            <div class="award-title">
                <i class="bi bi-award-fill me-1"></i> Ой ходими
            </div>
            <div class="d-flex align-items-center gap-3 mt-3">
                <div class="award-avatar">
                    {{ strtoupper(substr($stats['employee_of_month']->user->name ?? 'U', 0, 1)) }}
                </div>
                <div>
                    <div class="award-name">{{ $stats['employee_of_month']->user->name ?? 'Unknown' }}</div>
                    <div class="award-position">{{ $stats['employee_of_month']->period_label }}</div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="col-md-4">
        <div class="award-card" style="opacity: 0.6;">
            <div class="award-title">
                <i class="bi bi-award-fill me-1"></i> Ой ходими
            </div>
            <div class="text-center mt-4">
                <i class="bi bi-hourglass-split" style="font-size: 32px; opacity: 0.5;"></i>
                <div class="mt-2" style="opacity: 0.7;">Ҳали танланмаган</div>
            </div>
        </div>
    </div>
    @endif

    @if($stats['employee_of_quarter'])
    <div class="col-md-4">
        <div class="award-card silver">
            <div class="award-title">
                <i class="bi bi-trophy-fill me-1"></i> Квартал ходими
            </div>
            <div class="d-flex align-items-center gap-3 mt-3">
                <div class="award-avatar">
                    {{ strtoupper(substr($stats['employee_of_quarter']->user->name ?? 'U', 0, 1)) }}
                </div>
                <div>
                    <div class="award-name">{{ $stats['employee_of_quarter']->user->name ?? 'Unknown' }}</div>
                    <div class="award-position">{{ $stats['employee_of_quarter']->period_label }}</div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="col-md-4">
        <div class="award-card silver" style="opacity: 0.6;">
            <div class="award-title">
                <i class="bi bi-trophy-fill me-1"></i> Квартал ходими
            </div>
            <div class="text-center mt-4">
                <i class="bi bi-hourglass-split" style="font-size: 32px; opacity: 0.5;"></i>
                <div class="mt-2" style="opacity: 0.7;">Ҳали танланмаган</div>
            </div>
        </div>
    </div>
    @endif

    @if($stats['employee_of_year'])
    <div class="col-md-4">
        <div class="award-card bronze">
            <div class="award-title">
                <i class="bi bi-gem me-1"></i> Йил ходими
            </div>
            <div class="d-flex align-items-center gap-3 mt-3">
                <div class="award-avatar">
                    {{ strtoupper(substr($stats['employee_of_year']->user->name ?? 'U', 0, 1)) }}
                </div>
                <div>
                    <div class="award-name">{{ $stats['employee_of_year']->user->name ?? 'Unknown' }}</div>
                    <div class="award-position">{{ $stats['employee_of_year']->period_label }}</div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="col-md-4">
        <div class="award-card bronze" style="opacity: 0.6;">
            <div class="award-title">
                <i class="bi bi-gem me-1"></i> Йил ходими
            </div>
            <div class="text-center mt-4">
                <i class="bi bi-hourglass-split" style="font-size: 32px; opacity: 0.5;"></i>
                <div class="mt-2" style="opacity: 0.7;">Ҳали танланмаган</div>
            </div>
        </div>
    </div>
    @endif
</div>

<div class="row g-4">
    <!-- Leaderboard -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-bar-chart-fill text-primary me-2"></i>
                    Ой рейтинги
                </h5>
                <a href="{{ route('employee.recognition.leaderboard') }}" class="btn btn-sm btn-outline-primary">
                    Ҳаммаси
                </a>
            </div>
            <div class="card-body p-0">
                @forelse($leaderboardMonth as $item)
                <div class="leaderboard-item">
                    <div class="leaderboard-rank {{ $item['rank'] <= 3 ? 'rank-' . $item['rank'] : 'rank-default' }}">
                        {{ $item['rank'] }}
                    </div>
                    <div class="leaderboard-avatar">
                        {{ strtoupper(substr($item['user']->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="leaderboard-info">
                        <div class="leaderboard-name">{{ $item['user']->name ?? 'Unknown' }}</div>
                        <div class="leaderboard-dept">
                            <i class="bi bi-trophy-fill text-warning me-1"></i>
                            {{ $item['awards_won'] }} мукофот
                        </div>
                    </div>
                    <div class="leaderboard-points">
                        {{ number_format($item['points']) }}
                        <small class="text-muted">балл</small>
                    </div>
                </div>
                @empty
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox" style="font-size: 48px; opacity: 0.3;"></i>
                    <div class="mt-2">Маълумот йўқ</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Nomination Types -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-stars text-warning me-2"></i>
                    Номинация турлари
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @forelse($stats['nomination_types'] as $type)
                    <div class="col-6">
                        <a href="{{ route('employee.recognition.nominate', ['type' => $type->id]) }}" class="text-decoration-none">
                            <div class="nomination-type-card">
                                <div class="nomination-type-icon" style="background: {{ $type->color }};">
                                    <i class="bi {{ $type->icon }}"></i>
                                </div>
                                <div class="nomination-type-name text-dark">{{ $type->name }}</div>
                                <div class="nomination-type-points">
                                    <i class="bi bi-coin me-1"></i>{{ $type->points_reward }} балл
                                </div>
                            </div>
                        </a>
                    </div>
                    @empty
                    <div class="col-12 text-center text-muted py-4">
                        Номинация турлари мавжуд эмас
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Awards -->
@if($recentAwards->count() > 0)
<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-clock-history text-info me-2"></i>
            Охирги мукофотлар
        </h5>
        <a href="{{ route('employee.recognition.hall-of-fame') }}" class="btn btn-sm btn-outline-info">
            Шон-шараф зали
        </a>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @foreach($recentAwards as $award)
            <div class="col-md-6 col-lg-4">
                <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background: #f8f9fa;">
                    <div class="leaderboard-avatar" style="width: 48px; height: 48px;">
                        {{ strtoupper(substr($award->user->name ?? 'U', 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-semibold">{{ $award->user->name ?? 'Unknown' }}</div>
                        <div class="small text-muted">
                            <i class="bi {{ $award->award_type->icon() }} me-1" style="color: {{ $award->award_type->color() }};"></i>
                            {{ $award->award_type->label() }}
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
@endsection
