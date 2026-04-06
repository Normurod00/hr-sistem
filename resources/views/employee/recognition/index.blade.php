@extends('employee.layouts.app')

@section('title', 'Система признания')
@section('page-title', 'Система признания')

@section('content')
<style>
    .recog-stats { display:flex; gap:16px; margin-bottom:24px; flex-wrap:wrap; }
    .recog-stat { flex:1; min-width:180px; padding:20px; background:var(--panel); border:1px solid var(--br); border-radius:14px; display:flex; align-items:center; gap:14px; }
    .recog-stat .icon { width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0; }
    .recog-stat .info .value { font-size:22px; font-weight:800; color:var(--fg-1); }
    .recog-stat .info .label { font-size:12px; color:var(--fg-3); }
    .recog-stat.cta { background:linear-gradient(135deg, #667eea 0%, #764ba2 100%); border:none; text-decoration:none; color:#fff; transition:all 0.2s; }
    .recog-stat.cta:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(102,126,234,0.3); }
    .recog-stat.cta .icon { background:rgba(255,255,255,0.2); }
    .recog-stat.cta .info .value { color:#fff; }
    .recog-stat.cta .info .label { color:rgba(255,255,255,0.8); }

    .awards-row { display:grid; grid-template-columns:repeat(3, 1fr); gap:16px; margin-bottom:24px; }
    @media(max-width:768px) { .awards-row { grid-template-columns:1fr; } }
    .award-card { border-radius:16px; padding:24px; color:#fff; position:relative; overflow:hidden; min-height:140px; }
    .award-card::before { content:''; position:absolute; top:-50%; right:-50%; width:100%; height:100%; background:radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); }
    .award-card.gold { background:linear-gradient(135deg, #f5af19, #f12711); }
    .award-card.silver { background:linear-gradient(135deg, #a8b8c8, #4a6274); }
    .award-card.bronze { background:linear-gradient(135deg, #cd7f32, #8b4513); }
    .award-card .award-header { font-size:13px; opacity:0.9; margin-bottom:12px; display:flex; align-items:center; gap:6px; }
    .award-card .award-avatar { width:60px; height:60px; border-radius:50%; border:3px solid rgba(255,255,255,0.3); display:flex; align-items:center; justify-content:center; background:rgba(255,255,255,0.2); font-size:24px; font-weight:700; flex-shrink:0; }
    .award-card .award-name { font-size:18px; font-weight:700; }
    .award-card .award-sub { font-size:12px; opacity:0.8; }
    .award-card .empty-award { text-align:center; padding:10px 0; opacity:0.6; }
    .award-card .empty-award i { font-size:28px; opacity:0.5; margin-bottom:8px; display:block; }

    .section-card { background:var(--panel); border:1px solid var(--br); border-radius:14px; overflow:hidden; }
    .section-card .head { padding:16px 20px; border-bottom:1px solid var(--br); display:flex; justify-content:space-between; align-items:center; }
    .section-card .head h6 { margin:0; font-weight:700; color:var(--fg-1); font-size:15px; }
    .section-card .head a { font-size:13px; color:var(--accent); text-decoration:none; font-weight:500; }

    .lb-item { display:flex; align-items:center; gap:12px; padding:12px 20px; border-bottom:1px solid var(--br); transition:background 0.15s; }
    .lb-item:last-child { border-bottom:none; }
    .lb-item:hover { background:rgba(0,0,0,0.015); }
    .lb-rank { width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:13px; flex-shrink:0; }
    .lb-rank.r1 { background:linear-gradient(135deg, #FFD700, #FFA500); color:#fff; }
    .lb-rank.r2 { background:linear-gradient(135deg, #C0C0C0, #A0A0A0); color:#fff; }
    .lb-rank.r3 { background:linear-gradient(135deg, #CD7F32, #8B4513); color:#fff; }
    .lb-rank.rn { background:var(--br); color:var(--fg-3); }
    .lb-avatar { width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg, #667eea, #764ba2); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:600; font-size:14px; flex-shrink:0; }
    .lb-info { flex:1; }
    .lb-name { font-weight:600; font-size:14px; color:var(--fg-1); }
    .lb-meta { font-size:12px; color:var(--fg-3); }
    .lb-points { font-weight:700; font-size:16px; color:#667eea; }

    .nom-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(160px, 1fr)); gap:12px; padding:16px 20px; }
    .nom-card { text-align:center; padding:20px 12px; border:1px solid var(--br); border-radius:12px; transition:all 0.2s; text-decoration:none; color:inherit; display:block; }
    .nom-card:hover { border-color:#667eea; transform:translateY(-2px); box-shadow:0 4px 12px rgba(102,126,234,0.12); }
    .nom-card .nom-icon { width:52px; height:52px; border-radius:14px; display:flex; align-items:center; justify-content:center; margin:0 auto 10px; font-size:22px; color:#fff; }
    .nom-card .nom-name { font-weight:600; font-size:13px; color:var(--fg-1); margin-bottom:4px; }
    .nom-card .nom-pts { font-size:11px; color:var(--fg-3); }

    .empty-block { text-align:center; padding:40px 20px; color:var(--fg-3); }
    .empty-block i { font-size:40px; opacity:0.15; display:block; margin-bottom:8px; }

    .recent-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:12px; padding:16px 20px; }
    .recent-card { display:flex; align-items:center; gap:12px; padding:14px 16px; background:rgba(0,0,0,0.015); border-radius:12px; }
    .recent-card .name { font-weight:600; font-size:14px; color:var(--fg-1); }
    .recent-card .award-label { font-size:12px; color:var(--fg-3); }
</style>

{{-- Stats --}}
<div class="recog-stats">
    <div class="recog-stat">
        <div class="icon" style="background:rgba(102,126,234,0.1);color:#667eea;"><i class="fa-solid fa-trophy"></i></div>
        <div class="info"><div class="value">{{ $stats['total_nominations_this_month'] }}</div><div class="label">Номинации за месяц</div></div>
    </div>
    <div class="recog-stat">
        <div class="icon" style="background:rgba(245,158,11,0.1);color:#f59e0b;"><i class="fa-solid fa-hourglass-half"></i></div>
        <div class="info"><div class="value">{{ $stats['pending_nominations'] }}</div><div class="label">На рассмотрении</div></div>
    </div>
    <div class="recog-stat">
        <div class="icon" style="background:rgba(34,197,94,0.1);color:#22c55e;"><i class="fa-solid fa-users"></i></div>
        <div class="info"><div class="value">{{ $stats['active_employees'] }}</div><div class="label">С баллами</div></div>
    </div>
    <a href="{{ route('employee.recognition.nominate') }}" class="recog-stat cta">
        <div class="icon"><i class="fa-solid fa-plus"></i></div>
        <div class="info"><div class="value">Номинировать</div><div class="label">Новая номинация</div></div>
    </a>
</div>

{{-- Awards --}}
<div class="awards-row">
    @php
        $awards = [
            ['key' => 'employee_of_month', 'label' => 'Сотрудник месяца', 'class' => 'gold', 'icon' => 'fa-award'],
            ['key' => 'employee_of_quarter', 'label' => 'Сотрудник квартала', 'class' => 'silver', 'icon' => 'fa-trophy'],
            ['key' => 'employee_of_year', 'label' => 'Сотрудник года', 'class' => 'bronze', 'icon' => 'fa-gem'],
        ];
    @endphp
    @foreach($awards as $a)
        <div class="award-card {{ $a['class'] }}">
            <div class="award-header"><i class="fa-solid {{ $a['icon'] }}"></i> {{ $a['label'] }}</div>
            @if($stats[$a['key']])
                <div style="display:flex;align-items:center;gap:14px;">
                    <div class="award-avatar">{{ strtoupper(substr($stats[$a['key']]->user->name ?? 'U', 0, 1)) }}</div>
                    <div>
                        <div class="award-name">{{ $stats[$a['key']]->user->name ?? 'Unknown' }}</div>
                        <div class="award-sub">{{ $stats[$a['key']]->period_label ?? '' }}</div>
                    </div>
                </div>
            @else
                <div class="empty-award">
                    <i class="fa-solid fa-hourglass-half"></i>
                    Ещё не выбран
                </div>
            @endif
        </div>
    @endforeach
</div>

{{-- Leaderboard + Nomination Types --}}
<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:24px;">
    {{-- Leaderboard --}}
    <div class="section-card">
        <div class="head">
            <h6><i class="fa-solid fa-ranking-star me-2" style="color:#667eea;"></i>Рейтинг за месяц</h6>
            <a href="{{ route('employee.recognition.leaderboard') }}">Все</a>
        </div>
        @forelse($leaderboardMonth as $item)
            <div class="lb-item">
                <div class="lb-rank {{ $item['rank'] <= 3 ? 'r'.$item['rank'] : 'rn' }}">{{ $item['rank'] }}</div>
                <div class="lb-avatar">{{ strtoupper(substr($item['user']->name ?? 'U', 0, 1)) }}</div>
                <div class="lb-info">
                    <div class="lb-name">{{ $item['user']->name ?? 'Unknown' }}</div>
                    <div class="lb-meta"><i class="fa-solid fa-trophy me-1" style="color:#f59e0b;"></i>{{ $item['awards_won'] }} наград</div>
                </div>
                <div class="lb-points">{{ number_format($item['points']) }}</div>
            </div>
        @empty
            <div class="empty-block"><i class="fa-solid fa-ranking-star"></i><p>Нет данных за этот месяц</p></div>
        @endforelse
    </div>

    {{-- Nomination Types --}}
    <div class="section-card">
        <div class="head">
            <h6><i class="fa-solid fa-star me-2" style="color:#f59e0b;"></i>Типы номинаций</h6>
        </div>
        @if(count($stats['nomination_types'] ?? []) > 0)
            <div class="nom-grid">
                @foreach($stats['nomination_types'] as $type)
                    <a href="{{ route('employee.recognition.nominate', ['type' => $type->id]) }}" class="nom-card">
                        <div class="nom-icon" style="background:{{ $type->color }};">
                            <i class="bi {{ $type->icon }}"></i>
                        </div>
                        <div class="nom-name">{{ $type->name }}</div>
                        <div class="nom-pts"><i class="fa-solid fa-coins me-1"></i>{{ $type->points_reward }} балл</div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="empty-block"><i class="fa-solid fa-star"></i><p>Типы номинаций ещё не настроены</p></div>
        @endif
    </div>
</div>

{{-- Recent Awards --}}
@if($recentAwards->count() > 0)
    <div class="section-card">
        <div class="head">
            <h6><i class="fa-solid fa-clock-rotate-left me-2" style="color:#06b6d4;"></i>Последние награды</h6>
            <a href="{{ route('employee.recognition.hall-of-fame') }}">Зал славы</a>
        </div>
        <div class="recent-grid">
            @foreach($recentAwards as $award)
                <div class="recent-card">
                    <div class="lb-avatar" style="width:44px;height:44px;">{{ strtoupper(substr($award->user->name ?? 'U', 0, 1)) }}</div>
                    <div>
                        <div class="name">{{ $award->user->name ?? 'Unknown' }}</div>
                        <div class="award-label">
                            <i class="bi {{ $award->award_type->icon() }}" style="color:{{ $award->award_type->color() }};"></i>
                            {{ $award->award_type->label() }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
@endsection
