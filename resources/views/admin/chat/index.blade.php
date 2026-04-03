@extends('layouts.admin')

@section('title', 'Чаты с кандидатами')
@section('header', 'Чаты с кандидатами')

@section('content')
<style>
    .chat-toolbar { display:flex; gap:12px; align-items:center; flex-wrap:wrap; margin-bottom:20px; }
    .chat-toolbar .search-box { flex:1; min-width:220px; position:relative; }
    .chat-toolbar .search-box input { width:100%; padding:10px 14px 10px 40px; border:1px solid var(--br); border-radius:10px; background:var(--panel); color:var(--fg-1); font-size:14px; outline:none; transition:border 0.2s; }
    .chat-toolbar .search-box input:focus { border-color:var(--accent); }
    .chat-toolbar .search-box i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--fg-3); }
    .chat-toolbar select { padding:10px 14px; border:1px solid var(--br); border-radius:10px; background:var(--panel); color:var(--fg-1); font-size:14px; cursor:pointer; min-width:160px; }

    .chat-stats { display:flex; gap:16px; margin-bottom:20px; }
    .chat-stat { flex:1; padding:16px 20px; background:var(--panel); border:1px solid var(--br); border-radius:12px; text-align:center; }
    .chat-stat .value { font-size:24px; font-weight:800; color:var(--fg-1); }
    .chat-stat .label { font-size:12px; color:var(--fg-3); margin-top:2px; }

    .chat-list-item { display:flex; align-items:center; padding:14px 20px; background:var(--panel); border:1px solid var(--br); border-radius:12px; margin-bottom:10px; transition:all 0.2s; text-decoration:none; color:inherit; }
    .chat-list-item:hover { border-color:var(--accent); transform:translateX(4px); box-shadow:0 2px 12px rgba(0,0,0,0.06); }
    .chat-list-item.has-unread { border-left:3px solid var(--accent); }

    .chat-avatar { width:48px; height:48px; border-radius:50%; object-fit:cover; margin-right:14px; flex-shrink:0; border:2px solid var(--br); }
    .chat-avatar-letter { width:48px; height:48px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; color:#fff; font-size:1.1rem; margin-right:14px; flex-shrink:0; }
    .chat-info { flex:1; min-width:0; }
    .chat-name { font-weight:700; font-size:15px; color:var(--fg-1); margin-bottom:1px; }
    .chat-vacancy { font-size:12px; color:var(--fg-3); display:flex; align-items:center; gap:6px; margin-bottom:2px; }
    .chat-vacancy .status-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
    .chat-preview { font-size:13px; color:var(--fg-3); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:420px; }
    .chat-preview.unread { color:var(--fg-1); font-weight:600; }
    .chat-meta { text-align:right; flex-shrink:0; margin-left:14px; }
    .chat-time { font-size:12px; color:var(--fg-3); margin-bottom:4px; }
    .chat-unread { display:inline-flex; align-items:center; justify-content:center; min-width:22px; height:22px; background:var(--accent); color:#fff; border-radius:11px; font-size:11px; font-weight:700; padding:0 6px; }

    .empty-state { text-align:center; padding:60px 20px; color:var(--fg-3); }
    .empty-state i { font-size:56px; opacity:0.2; margin-bottom:12px; display:block; }
</style>

{{-- Stats --}}
<div class="chat-stats">
    <div class="chat-stat">
        <div class="value">{{ $totalChats }}</div>
        <div class="label">Активных чатов</div>
    </div>
    <div class="chat-stat">
        <div class="value" style="color:{{ $totalUnread > 0 ? 'var(--accent)' : 'var(--fg-1)' }}">{{ $totalUnread }}</div>
        <div class="label">Непрочитанных сообщений</div>
    </div>
    <div class="chat-stat">
        <div class="value">{{ $chatRooms->total() }}</div>
        <div class="label">Найдено</div>
    </div>
</div>

{{-- Toolbar --}}
<form method="GET" class="chat-toolbar">
    <div class="search-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" name="search" placeholder="Поиск по имени или email кандидата..." value="{{ request('search') }}">
    </div>
    <select name="vacancy_id">
        <option value="">Все вакансии</option>
        @foreach($vacancies as $v)
            <option value="{{ $v->id }}" @selected(request('vacancy_id') == $v->id)>{{ Str::limit($v->title, 30) }}</option>
        @endforeach
    </select>
    <select name="sort">
        <option value="recent" @selected(request('sort') === 'recent')>Последние сообщения</option>
        <option value="name" @selected(request('sort') === 'name')>По имени А-Я</option>
    </select>
    <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--fg-3);cursor:pointer;">
        <input type="checkbox" name="unread_only" value="1" @checked(request('unread_only')) onchange="this.form.submit()">
        Только непрочитанные
    </label>
    <button type="submit" style="padding:10px 20px;background:var(--accent);color:#fff;border:none;border-radius:10px;font-weight:600;cursor:pointer;">
        <i class="fa-solid fa-filter me-1"></i>Фильтр
    </button>
    @if(request()->hasAny(['search','vacancy_id','sort','unread_only']))
        <a href="{{ route('admin.chat.index') }}" style="padding:10px 16px;border:1px solid var(--br);border-radius:10px;font-size:13px;color:var(--fg-3);text-decoration:none;">
            <i class="fa-solid fa-xmark me-1"></i>Сбросить
        </a>
    @endif
</form>

{{-- Chat List --}}
@forelse($chatRooms as $chat)
    @php
        $unread = $chat->unreadCountFor(auth()->id());
        $lastMsg = $chat->messages->first();
        $statusColors = ['new'=>'#3B82F6','in_review'=>'#f59e0b','invited'=>'#22c55e','rejected'=>'#ef4444','hired'=>'#8B5CF6'];
        $appStatus = $chat->application?->status?->value ?? 'new';
        $colors = ['#E52716','#3B82F6','#8B5CF6','#22c55e','#f59e0b','#ec4899','#06b6d4'];
    @endphp
    <a href="{{ route('admin.chat.show', $chat->application) }}" class="chat-list-item {{ $unread > 0 ? 'has-unread' : '' }}">
        @if($chat->candidate?->avatar)
            <img src="{{ $chat->candidate->avatar_url }}" alt="" class="chat-avatar">
        @else
            <div class="chat-avatar-letter" style="background:{{ $colors[($chat->candidate_id ?? 0) % count($colors)] }}">
                {{ mb_strtoupper(mb_substr($chat->candidate?->name ?? '?', 0, 1)) }}
            </div>
        @endif

        <div class="chat-info">
            <div class="chat-name">{{ $chat->candidate?->name ?? 'Кандидат' }}</div>
            <div class="chat-vacancy">
                <span class="status-dot" style="background:{{ $statusColors[$appStatus] ?? '#999' }}"></span>
                {{ $chat->application?->vacancy?->title ?? 'Без вакансии' }}
                <span style="opacity:0.6">·</span>
                {{ $chat->application?->status?->label() ?? '' }}
            </div>
            <div class="chat-preview {{ $unread > 0 ? 'unread' : '' }}">
                @if($lastMsg)
                    @if($lastMsg->sender_type === 'hr')
                        <span style="opacity:0.6">Вы: </span>
                    @elseif($lastMsg->sender_type === 'system')
                        <i class="fa-solid fa-robot" style="opacity:0.4;font-size:11px;"></i>
                    @endif
                    {{ Str::limit($lastMsg->message, 70) }}
                @else
                    <span style="opacity:0.5">Нет сообщений</span>
                @endif
            </div>
        </div>

        <div class="chat-meta">
            @if($chat->last_message_at)
                <div class="chat-time">{{ $chat->last_message_at->diffForHumans() }}</div>
            @endif
            @if($unread > 0)
                <span class="chat-unread">{{ $unread }}</span>
            @endif
        </div>
    </a>
@empty
    <div class="empty-state">
        <i class="fa-solid fa-comments"></i>
        @if(request()->hasAny(['search','vacancy_id','unread_only']))
            <p>Ничего не найдено</p>
            <p class="small">Попробуйте изменить параметры фильтра</p>
        @else
            <p>Нет активных чатов</p>
            <p class="small">Чаты появятся, когда вы пригласите кандидатов на собеседование</p>
        @endif
    </div>
@endforelse

@if($chatRooms->hasPages())
    <div class="mt-3">
        {{ $chatRooms->links() }}
    </div>
@endif
@endsection
