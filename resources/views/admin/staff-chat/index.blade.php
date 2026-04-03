@extends('layouts.admin')

@section('title', 'Чаты с сотрудниками')
@section('header', 'Чаты с сотрудниками')

@section('content')
<style>
    .chat-toolbar { display:flex; gap:12px; align-items:center; flex-wrap:wrap; margin-bottom:20px; }
    .chat-toolbar .search-box { flex:1; min-width:220px; position:relative; }
    .chat-toolbar .search-box input { width:100%; padding:10px 14px 10px 40px; border:1px solid var(--br); border-radius:10px; background:var(--panel); color:var(--fg-1); font-size:14px; outline:none; transition:border 0.2s; }
    .chat-toolbar .search-box input:focus { border-color:var(--accent); }
    .chat-toolbar .search-box i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--fg-3); }
    .chat-toolbar select { padding:10px 14px; border:1px solid var(--br); border-radius:10px; background:var(--panel); color:var(--fg-1); font-size:14px; cursor:pointer; }
    .chat-tabs { display:flex; gap:4px; background:var(--panel); border:1px solid var(--br); border-radius:10px; padding:3px; }
    .chat-tabs button { padding:8px 16px; border:none; border-radius:8px; background:transparent; color:var(--fg-3); font-size:13px; font-weight:600; cursor:pointer; transition:all 0.2s; }
    .chat-tabs button.active { background:var(--accent); color:#fff; }
    .chat-tabs button:hover:not(.active) { color:var(--fg-1); }

    .chat-stats { display:flex; gap:16px; margin-bottom:20px; }
    .chat-stat { flex:1; padding:16px 20px; background:var(--panel); border:1px solid var(--br); border-radius:12px; text-align:center; }
    .chat-stat .value { font-size:24px; font-weight:800; color:var(--fg-1); }
    .chat-stat .label { font-size:12px; color:var(--fg-3); margin-top:2px; }

    .chat-list-item { display:flex; align-items:center; padding:14px 20px; background:var(--panel); border:1px solid var(--br); border-radius:12px; margin-bottom:10px; transition:all 0.2s; text-decoration:none; color:inherit; cursor:pointer; }
    .chat-list-item:hover { border-color:var(--accent); transform:translateX(4px); box-shadow:0 2px 12px rgba(0,0,0,0.06); }
    .chat-list-item.has-unread { border-left:3px solid var(--accent); }

    .chat-avatar { width:48px; height:48px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; color:#fff; font-size:1.1rem; margin-right:14px; flex-shrink:0; position:relative; }
    .chat-avatar.online::after { content:''; position:absolute; bottom:2px; right:2px; width:12px; height:12px; background:#22c55e; border-radius:50%; border:2px solid var(--panel); }
    .chat-info { flex:1; min-width:0; }
    .chat-name { font-weight:700; font-size:15px; color:var(--fg-1); margin-bottom:1px; display:flex; align-items:center; gap:8px; }
    .chat-name .role-tag { font-size:10px; padding:2px 8px; border-radius:6px; font-weight:600; text-transform:uppercase; }
    .chat-preview { font-size:13px; color:var(--fg-3); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:400px; }
    .chat-preview.unread { color:var(--fg-1); font-weight:600; }
    .chat-meta { text-align:right; flex-shrink:0; margin-left:12px; }
    .chat-time { font-size:12px; color:var(--fg-3); margin-bottom:4px; }
    .chat-unread { display:inline-flex; align-items:center; justify-content:center; min-width:22px; height:22px; background:var(--accent); color:#fff; border-radius:11px; font-size:11px; font-weight:700; padding:0 6px; }
    .chat-dept { font-size:11px; color:var(--fg-3); margin-top:2px; }

    .empty-state { text-align:center; padding:60px 20px; color:var(--fg-3); }
    .empty-state i { font-size:56px; opacity:0.2; margin-bottom:12px; display:block; }
</style>

@php
    $totalEmployees = $employees->count();
    $activeChats = $chats->count();
    $totalUnread = $chats->sum('unread_count');
@endphp

{{-- Stats --}}
<div class="chat-stats">
    <div class="chat-stat">
        <div class="value">{{ $totalEmployees }}</div>
        <div class="label">Всего сотрудников</div>
    </div>
    <div class="chat-stat">
        <div class="value">{{ $activeChats }}</div>
        <div class="label">Активных чатов</div>
    </div>
    <div class="chat-stat">
        <div class="value" style="color:{{ $totalUnread > 0 ? 'var(--accent)' : 'var(--fg-1)' }}">{{ $totalUnread }}</div>
        <div class="label">Непрочитанных</div>
    </div>
</div>

{{-- Toolbar --}}
<div class="chat-toolbar">
    <div class="search-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchInput" placeholder="Поиск по имени или email..." autofocus>
    </div>
    <select id="filterRole">
        <option value="">Все роли</option>
        <option value="employee">Сотрудники</option>
        <option value="hr">HR</option>
        <option value="admin">Администраторы</option>
    </select>
    <select id="sortBy">
        <option value="unread">Непрочитанные первые</option>
        <option value="recent">Последнее сообщение</option>
        <option value="name">По имени А-Я</option>
    </select>
    <div class="chat-tabs">
        <button class="active" data-filter="all">Все</button>
        <button data-filter="active">С чатом</button>
        <button data-filter="new">Новые</button>
    </div>
</div>

{{-- Chat List --}}
<div id="chatList">
    @foreach($employees as $emp)
        @php
            $chat = $chats->get($emp->id);
            $unread = $chat ? $chat->unread_count : 0;
            $lastMsg = $chat?->lastMessage;
            $colors = ['#E52716','#3B82F6','#8B5CF6','#22c55e','#f59e0b','#ec4899','#06b6d4','#84cc16'];
            $color = $colors[$emp->id % count($colors)];
        @endphp
        <a href="{{ $chat ? route('admin.staff-chat.show', $chat) : route('admin.staff-chat.start', $emp) }}"
           class="chat-list-item {{ $unread > 0 ? 'has-unread' : '' }}"
           data-name="{{ mb_strtolower($emp->name) }}"
           data-email="{{ mb_strtolower($emp->email) }}"
           data-role="{{ $emp->role }}"
           data-has-chat="{{ $chat ? '1' : '0' }}"
           data-unread="{{ $unread }}"
           data-last-msg="{{ $lastMsg?->created_at?->timestamp ?? 0 }}"
           data-sort-name="{{ mb_strtolower($emp->name) }}">

            <div class="chat-avatar" style="background:{{ $color }};">
                {{ mb_strtoupper(mb_substr($emp->name, 0, 1)) }}
            </div>

            <div class="chat-info">
                <div class="chat-name">
                    {{ $emp->name }}
                    @if($emp->role === 'hr')
                        <span class="role-tag" style="background:rgba(59,130,246,0.15);color:#3B82F6;">HR</span>
                    @elseif($emp->role === 'admin')
                        <span class="role-tag" style="background:rgba(229,39,22,0.12);color:#E52716;">Admin</span>
                    @endif
                </div>
                <div class="chat-preview {{ $unread > 0 ? 'unread' : '' }}">
                    @if($lastMsg)
                        {{ $lastMsg->sender_id === auth()->id() ? 'Вы: ' : '' }}{{ Str::limit($lastMsg->message, 60) }}
                    @else
                        {{ $emp->email }}
                    @endif
                </div>
            </div>

            <div class="chat-meta">
                @if($lastMsg)
                    <div class="chat-time">{{ $lastMsg->formatted_time }}</div>
                @endif
                @if($unread > 0)
                    <span class="chat-unread">{{ $unread }}</span>
                @elseif(!$chat)
                    <span style="font-size:11px;color:var(--fg-3);">Начать чат</span>
                @endif
            </div>
        </a>
    @endforeach

    <div class="empty-state" id="emptyState" style="display:none;">
        <i class="fa-solid fa-search"></i>
        <p>Ничего не найдено</p>
    </div>
</div>

<script>
const items = Array.from(document.querySelectorAll('.chat-list-item'));
const searchInput = document.getElementById('searchInput');
const filterRole = document.getElementById('filterRole');
const sortBy = document.getElementById('sortBy');
const emptyState = document.getElementById('emptyState');
const chatList = document.getElementById('chatList');
const tabs = document.querySelectorAll('.chat-tabs button');

let activeTab = 'all';

tabs.forEach(btn => {
    btn.addEventListener('click', () => {
        tabs.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        activeTab = btn.dataset.filter;
        applyFilters();
    });
});

searchInput.addEventListener('input', applyFilters);
filterRole.addEventListener('change', applyFilters);
sortBy.addEventListener('change', applyFilters);

function applyFilters() {
    const q = searchInput.value.toLowerCase();
    const role = filterRole.value;
    const sort = sortBy.value;
    let visible = 0;

    // Sort
    const sorted = [...items].sort((a, b) => {
        if (sort === 'unread') {
            const diff = parseInt(b.dataset.unread) - parseInt(a.dataset.unread);
            if (diff !== 0) return diff;
            return parseInt(b.dataset.lastMsg) - parseInt(a.dataset.lastMsg);
        }
        if (sort === 'recent') return parseInt(b.dataset.lastMsg) - parseInt(a.dataset.lastMsg);
        if (sort === 'name') return a.dataset.sortName.localeCompare(b.dataset.sortName);
        return 0;
    });

    sorted.forEach(item => chatList.insertBefore(item, emptyState));

    items.forEach(item => {
        const matchSearch = !q || item.dataset.name.includes(q) || item.dataset.email.includes(q);
        const matchRole = !role || item.dataset.role === role;
        const matchTab = activeTab === 'all'
            || (activeTab === 'active' && item.dataset.hasChat === '1')
            || (activeTab === 'new' && item.dataset.hasChat === '0');

        const show = matchSearch && matchRole && matchTab;
        item.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    emptyState.style.display = visible === 0 ? '' : 'none';
}

applyFilters();
</script>
@endsection
