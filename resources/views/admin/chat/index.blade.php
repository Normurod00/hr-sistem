@extends('layouts.admin')

@section('title', 'Чаты с кандидатами')
@section('header', 'Чаты с кандидатами')

@section('content')
<style>
    .chat-list-item {
        display: flex;
        align-items: center;
        padding: 16px 20px;
        background: var(--panel);
        border: 1px solid var(--br);
        border-radius: 12px;
        margin-bottom: 12px;
        transition: all 0.2s;
        text-decoration: none;
        color: inherit;
    }

    .chat-list-item:hover {
        border-color: var(--accent);
        transform: translateX(4px);
    }

    .chat-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 16px;
        border: 2px solid var(--br);
    }

    .chat-info {
        flex: 1;
        min-width: 0;
    }

    .chat-name {
        font-weight: 700;
        font-size: 15px;
        color: var(--fg-1);
        margin-bottom: 2px;
    }

    .chat-vacancy {
        font-size: 13px;
        color: var(--fg-3);
    }

    .chat-meta {
        text-align: right;
    }

    .chat-time {
        font-size: 12px;
        color: var(--fg-3);
        margin-bottom: 4px;
    }

    .chat-unread {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 22px;
        height: 22px;
        background: var(--accent);
        color: #fff;
        border-radius: 11px;
        font-size: 12px;
        font-weight: 700;
        padding: 0 6px;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--fg-3);
    }

    .empty-state i {
        font-size: 64px;
        opacity: 0.3;
        margin-bottom: 16px;
    }
</style>

<div class="card">
    <div class="card-header">
        <span style="font-weight: 700; color: var(--fg-1);">
            <i class="bi bi-chat-dots me-2"></i>Активные чаты
        </span>
    </div>
    <div class="card-body">
        @forelse($chatRooms as $chat)
            <a href="{{ route('admin.chat.show', $chat->application) }}" class="chat-list-item">
                <img src="{{ $chat->candidate->avatar_url }}" alt="" class="chat-avatar">
                <div class="chat-info">
                    <div class="chat-name">{{ $chat->candidate->name }}</div>
                    <div class="chat-vacancy">{{ $chat->application->vacancy->title }}</div>
                </div>
                <div class="chat-meta">
                    @if($chat->last_message_at)
                        <div class="chat-time">{{ $chat->last_message_at->diffForHumans() }}</div>
                    @endif
                    @php $unread = $chat->unreadCountFor(auth()->id()); @endphp
                    @if($unread > 0)
                        <span class="chat-unread">{{ $unread }}</span>
                    @endif
                </div>
            </a>
        @empty
            <div class="empty-state">
                <i class="bi bi-chat-dots"></i>
                <p>Нет активных чатов</p>
                <p class="small">Чаты появятся, когда вы пригласите кандидатов на собеседование</p>
            </div>
        @endforelse
    </div>
</div>

@if($chatRooms->hasPages())
    <div class="mt-4">
        {{ $chatRooms->links() }}
    </div>
@endif
@endsection
