@extends('employee.layouts.app')

@section('title', 'Политики и регламенты')
@section('page-title', 'Политики и регламенты')

@section('content')
<style>
    .pol-search { position:relative; margin-bottom:24px; }
    .pol-search input { width:100%; padding:12px 18px 12px 46px; border:1px solid var(--br); border-radius:12px; background:var(--panel); color:var(--fg-1); font-size:14px; outline:none; transition:border 0.2s; }
    .pol-search input:focus { border-color:var(--accent); }
    .pol-search i { position:absolute; left:16px; top:50%; transform:translateY(-50%); color:var(--fg-3); }

    .pol-cats { display:flex; gap:8px; margin-bottom:24px; flex-wrap:wrap; }
    .pol-cat { padding:8px 16px; border:1px solid var(--br); border-radius:10px; text-decoration:none; font-size:13px; font-weight:500; color:var(--fg-3); background:var(--panel); transition:all 0.2s; display:flex; align-items:center; gap:6px; }
    .pol-cat:hover { border-color:var(--accent); color:var(--accent); }
    .pol-cat.active { background:var(--accent); color:#fff; border-color:var(--accent); }
    .pol-cat .count { font-size:11px; padding:2px 6px; border-radius:6px; background:rgba(255,255,255,0.2); }
    .pol-cat:not(.active) .count { background:rgba(0,0,0,0.05); }

    .pol-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(340px, 1fr)); gap:16px; margin-bottom:24px; }
    .pol-card { background:var(--panel); border:1px solid var(--br); border-radius:14px; padding:20px; transition:all 0.2s; text-decoration:none; color:inherit; display:flex; flex-direction:column; }
    .pol-card:hover { border-color:var(--accent); transform:translateY(-3px); box-shadow:0 4px 20px rgba(0,0,0,0.06); }
    .pol-card .head { display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; }
    .pol-card .cat-tag { padding:4px 10px; border-radius:8px; font-size:11px; font-weight:600; }
    .pol-card .code { font-size:11px; color:var(--fg-3); font-family:monospace; }
    .pol-card h6 { font-weight:600; font-size:15px; color:var(--fg-1); margin-bottom:8px; line-height:1.4; }
    .pol-card .excerpt { font-size:13px; color:var(--fg-3); line-height:1.5; flex:1; margin-bottom:12px; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden; }
    .pol-card .foot { display:flex; justify-content:space-between; align-items:center; font-size:12px; color:var(--fg-3); }
    .pol-card .pdf-badge { padding:3px 8px; border-radius:6px; background:rgba(229,39,22,0.1); color:#E52716; font-size:11px; font-weight:600; }

    .popular-section { background:var(--panel); border:1px solid var(--br); border-radius:14px; padding:20px; margin-bottom:24px; }
    .popular-section h6 { font-weight:700; margin-bottom:14px; color:var(--fg-1); }
    .popular-item { display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid var(--br); }
    .popular-item:last-child { border-bottom:none; }
    .popular-item a { font-size:13px; color:var(--fg-1); text-decoration:none; font-weight:500; }
    .popular-item a:hover { color:var(--accent); }
    .popular-item .views { font-size:11px; color:var(--fg-3); }

    .empty-state { text-align:center; padding:60px 20px; }
    .empty-state i { font-size:48px; opacity:0.15; display:block; margin-bottom:12px; color:var(--fg-3); }
</style>

{{-- Search --}}
<form action="{{ route('employee.policies.index') }}" method="GET" class="pol-search">
    <i class="fa-solid fa-magnifying-glass"></i>
    <input type="text" name="q" placeholder="Поиск по политикам..." value="{{ $searchQuery }}"
        @if(request('category')) data-cat="{{ request('category') }}" @endif>
    @if(request('category'))
        <input type="hidden" name="category" value="{{ request('category') }}">
    @endif
</form>

@if($searchQuery)
    <div style="padding:10px 16px;background:rgba(59,130,246,0.08);border-radius:10px;margin-bottom:16px;font-size:13px;color:var(--fg-1);">
        Результаты по запросу: <strong>{{ $searchQuery }}</strong>
        <a href="{{ route('employee.policies.index') }}" style="margin-left:8px;color:var(--accent);">Сбросить</a>
    </div>
@endif

{{-- Categories --}}
<div class="pol-cats">
    <a href="{{ route('employee.policies.index') }}" class="pol-cat {{ !$currentCategory ? 'active' : '' }}">
        Все <span class="count">{{ collect($categories)->sum('count') }}</span>
    </a>
    @foreach($categories as $cat)
        <a href="{{ route('employee.policies.index', ['category' => $cat['key']]) }}"
           class="pol-cat {{ $currentCategory === $cat['key'] ? 'active' : '' }}">
            {{ $cat['label'] }} <span class="count">{{ $cat['count'] }}</span>
        </a>
    @endforeach
</div>

{{-- Popular --}}
@if($popular->count() > 0 && !$searchQuery)
    <div class="popular-section">
        <h6><i class="fa-solid fa-fire me-2" style="color:#f59e0b;"></i>Популярные</h6>
        @foreach($popular as $policy)
            <div class="popular-item">
                <a href="{{ route('employee.policies.show', $policy) }}">{{ $policy->title }}</a>
                <span class="views"><i class="fa-solid fa-eye me-1"></i>{{ $policy->view_count }}</span>
            </div>
        @endforeach
    </div>
@endif

{{-- Grid --}}
<div class="pol-grid">
    @forelse($policies as $policy)
        @php
            $catColors = ['hr'=>'#3B82F6','finance'=>'#22c55e','security'=>'#ef4444','it'=>'#8B5CF6','compliance'=>'#f59e0b','general'=>'#6B7280'];
            $cc = $catColors[$policy->category] ?? '#6B7280';
        @endphp
        <a href="{{ route('employee.policies.show', $policy) }}" class="pol-card">
            <div class="head">
                <span class="cat-tag" style="background:{{ $cc }}15;color:{{ $cc }};">{{ $policy->category_label }}</span>
                <span class="code">{{ $policy->code }}</span>
            </div>
            <h6>{{ $policy->title }}</h6>
            <div class="excerpt">{{ $policy->excerpt }}</div>
            <div class="foot">
                <span><i class="fa-solid fa-calendar me-1"></i>{{ $policy->effective_date->format('d.m.Y') }}</span>
                @if($policy->hasFile())
                    <span class="pdf-badge"><i class="fa-solid fa-file-pdf me-1"></i>PDF</span>
                @endif
            </div>
        </a>
    @empty
        <div style="grid-column:1/-1;">
            <div class="empty-state">
                <i class="fa-solid fa-book-open"></i>
                <h5 style="color:var(--fg-1);">Политики не найдены</h5>
                <p style="color:var(--fg-3);font-size:14px;">Попробуйте изменить параметры поиска</p>
            </div>
        </div>
    @endforelse
</div>

@if($policies->hasPages())
    <div>{{ $policies->withQueryString()->links() }}</div>
@endif
@endsection
