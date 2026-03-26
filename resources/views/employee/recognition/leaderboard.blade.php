@extends('employee.layouts.app')

@section('title', 'Рейтинг')
@section('page-title', 'Ходимлар рейтинги')

@section('content')
<style>
    .period-tabs .nav-link {
        border-radius: 20px;
        padding: 8px 20px;
        color: #6c757d;
        font-weight: 500;
    }
    .period-tabs .nav-link.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .my-rank-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 16px;
        padding: 24px;
    }
    .leaderboard-table th {
        font-weight: 600;
        color: #6c757d;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .rank-badge {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
    }
    .rank-1 { background: linear-gradient(135deg, #FFD700, #FFA500); color: white; }
    .rank-2 { background: linear-gradient(135deg, #C0C0C0, #A0A0A0); color: white; }
    .rank-3 { background: linear-gradient(135deg, #CD7F32, #8B4513); color: white; }
    .rank-default { background: #e9ecef; color: #495057; }

    .user-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea, #764ba2);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 16px;
    }
    .current-user-row {
        background: rgba(102, 126, 234, 0.1);
    }
</style>

<!-- My Rank Card -->
@if($userBalance)
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="my-rank-card">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div style="width: 80px; height: 80px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center;">
                        <span style="font-size: 32px; font-weight: 700;">#{{ $userBalance->rank }}</span>
                    </div>
                </div>
                <div class="col">
                    <div style="opacity: 0.8; font-size: 14px;">Сизнинг рейтингингиз</div>
                    <div style="font-size: 28px; font-weight: 700;">{{ number_format($userBalance->total_points) }} балл</div>
                    <div style="opacity: 0.8; font-size: 14px;">
                        <i class="bi bi-trophy-fill me-1"></i> {{ $userBalance->awards_won }} мукофот
                        <span class="mx-2">|</span>
                        <i class="bi bi-star-fill me-1"></i> {{ $userBalance->nominations_received }} номинация
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="text-muted small">Ойлик</div>
                        <div class="fs-4 fw-bold text-primary">{{ number_format($userBalance->monthly_points) }}</div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small">Кварталлик</div>
                        <div class="fs-4 fw-bold text-info">{{ number_format($userBalance->quarterly_points) }}</div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small">Йиллик</div>
                        <div class="fs-4 fw-bold text-success">{{ number_format($userBalance->yearly_points) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Period Tabs -->
<div class="card">
    <div class="card-header">
        <ul class="nav period-tabs">
            <li class="nav-item">
                <a class="nav-link {{ $currentPeriod === 'month' ? 'active' : '' }}"
                   href="{{ route('employee.recognition.leaderboard', ['period' => 'month']) }}">
                    Ой
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $currentPeriod === 'quarter' ? 'active' : '' }}"
                   href="{{ route('employee.recognition.leaderboard', ['period' => 'quarter']) }}">
                    Квартал
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $currentPeriod === 'year' ? 'active' : '' }}"
                   href="{{ route('employee.recognition.leaderboard', ['period' => 'year']) }}">
                    Йил
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $currentPeriod === 'total' ? 'active' : '' }}"
                   href="{{ route('employee.recognition.leaderboard', ['period' => 'total']) }}">
                    Умумий
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 leaderboard-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">Ўрин</th>
                        <th>Ходим</th>
                        <th class="text-center" style="width: 100px;">Номинациялар</th>
                        <th class="text-center" style="width: 100px;">Мукофотлар</th>
                        <th class="text-end" style="width: 120px;">Балл</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaderboard as $item)
                    <tr class="{{ $item['user']->id === auth()->id() ? 'current-user-row' : '' }}">
                        <td>
                            <div class="rank-badge {{ $item['rank'] <= 3 ? 'rank-' . $item['rank'] : 'rank-default' }}">
                                {{ $item['rank'] }}
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="user-avatar">
                                    {{ strtoupper(substr($item['user']->name ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold">
                                        {{ $item['user']->name ?? 'Unknown' }}
                                        @if($item['user']->id === auth()->id())
                                        <span class="badge bg-primary ms-1">Сиз</span>
                                        @endif
                                    </div>
                                    @if($item['user']->employeeProfile)
                                    <small class="text-muted">{{ $item['user']->employeeProfile->department }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-warning text-dark">
                                <i class="bi bi-star-fill me-1"></i>{{ $item['nominations_received'] }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info">
                                <i class="bi bi-trophy-fill me-1"></i>{{ $item['awards_won'] }}
                            </span>
                        </td>
                        <td class="text-end">
                            <span class="fs-5 fw-bold text-primary">{{ number_format($item['points']) }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox" style="font-size: 48px; opacity: 0.3;"></i>
                            <div class="mt-2">Маълумот йўқ</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
