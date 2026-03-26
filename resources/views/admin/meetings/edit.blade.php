@extends('layouts.admin')

@section('title', 'Редактировать встречу')
@section('header', 'Редактировать встречу')

@section('content')
<style>
    .form-section {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
    }
    .form-section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--border-color);
    }

    .participants-selector {
        border: 1px solid var(--border-color);
        border-radius: 8px;
        max-height: 300px;
        overflow-y: auto;
    }
    .participant-item {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        border-bottom: 1px solid var(--border-color);
        cursor: pointer;
        transition: background 0.2s;
    }
    .participant-item:last-child {
        border-bottom: none;
    }
    .participant-item:hover {
        background: var(--bg-secondary);
    }
    .participant-item.selected {
        background: rgba(229, 39, 22, 0.1);
    }
    .participant-checkbox {
        margin-right: 12px;
    }
    .participant-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--brb-red);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        margin-right: 12px;
    }
    .participant-info {
        flex: 1;
    }
    .participant-name {
        font-weight: 500;
        color: var(--text-primary);
    }
    .participant-role {
        font-size: 0.85rem;
        color: var(--text-secondary);
    }

    .selected-participants {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
    }
    .selected-tag {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        background: var(--brb-red);
        color: white;
        border-radius: 20px;
        font-size: 0.9rem;
    }
    .selected-tag .remove-btn {
        background: none;
        border: none;
        color: white;
        cursor: pointer;
        padding: 0;
        font-size: 1rem;
    }

    .search-input {
        padding: 12px 16px;
        border-bottom: 1px solid var(--border-color);
    }
    .search-input input {
        width: 100%;
        border: none;
        outline: none;
        background: transparent;
        font-size: 0.95rem;
    }
</style>

<a href="{{ route('admin.meetings.show', $meeting) }}" class="btn btn-outline-secondary mb-4">
    <i class="fa-solid fa-arrow-left me-2"></i> Назад
</a>

<form action="{{ route('admin.meetings.update', $meeting) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-lg-8">
            {{-- Основная информация --}}
            <div class="form-section">
                <div class="form-section-title">
                    <i class="fa-solid fa-info-circle me-2"></i> Основная информация
                </div>

                <div class="mb-3">
                    <label for="title" class="form-label">Название встречи <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror"
                           id="title" name="title" value="{{ old('title', $meeting->title) }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Описание</label>
                    <textarea class="form-control @error('description') is-invalid @enderror"
                              id="description" name="description" rows="3">{{ old('description', $meeting->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="scheduled_at" class="form-label">Дата и время <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control @error('scheduled_at') is-invalid @enderror"
                               id="scheduled_at" name="scheduled_at"
                               value="{{ old('scheduled_at', $meeting->scheduled_at->format('Y-m-d\TH:i')) }}" required>
                        @error('scheduled_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="duration_minutes" class="form-label">Продолжительность <span class="text-danger">*</span></label>
                        <select class="form-select @error('duration_minutes') is-invalid @enderror"
                                id="duration_minutes" name="duration_minutes" required>
                            <option value="15" {{ old('duration_minutes', $meeting->duration_minutes) == 15 ? 'selected' : '' }}>15 минут</option>
                            <option value="30" {{ old('duration_minutes', $meeting->duration_minutes) == 30 ? 'selected' : '' }}>30 минут</option>
                            <option value="45" {{ old('duration_minutes', $meeting->duration_minutes) == 45 ? 'selected' : '' }}>45 минут</option>
                            <option value="60" {{ old('duration_minutes', $meeting->duration_minutes) == 60 ? 'selected' : '' }}>1 час</option>
                            <option value="90" {{ old('duration_minutes', $meeting->duration_minutes) == 90 ? 'selected' : '' }}>1.5 часа</option>
                            <option value="120" {{ old('duration_minutes', $meeting->duration_minutes) == 120 ? 'selected' : '' }}>2 часа</option>
                        </select>
                        @error('duration_minutes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Участники --}}
            <div class="form-section">
                <div class="form-section-title">
                    <i class="fa-solid fa-users me-2"></i> Участники встречи
                </div>

                <p class="text-muted mb-3">
                    Выберите сотрудников, которых хотите пригласить на встречу
                </p>

                @php
                    $currentParticipants = $meeting->participants->pluck('user_id')->toArray();
                @endphp

                <div class="selected-participants" id="selectedParticipants">
                    {{-- Selected participants will appear here --}}
                </div>

                <div class="participants-selector mt-3">
                    <div class="search-input">
                        <input type="text" id="participantSearch" placeholder="Поиск сотрудника...">
                    </div>

                    <div id="participantsList">
                        @foreach($staffUsers as $user)
                            @if($user->id !== auth()->id())
                                <label class="participant-item" data-user-id="{{ $user->id }}" data-name="{{ $user->name }}">
                                    <input type="checkbox" name="participants[]" value="{{ $user->id }}"
                                           class="participant-checkbox"
                                           {{ in_array($user->id, old('participants', $currentParticipants)) ? 'checked' : '' }}>
                                    <div class="participant-avatar">
                                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1)) }}
                                    </div>
                                    <div class="participant-info">
                                        <div class="participant-name">{{ $user->name }}</div>
                                        <div class="participant-role">
                                            {{ $user->role === 'admin' ? 'Администратор' : 'HR-менеджер' }}
                                            &bull; {{ $user->email }}
                                        </div>
                                    </div>
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>

                @error('participants')
                    <div class="text-danger mt-2">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Действия --}}
            <div class="form-section">
                <div class="form-section-title">
                    <i class="fa-solid fa-paper-plane me-2"></i> Действия
                </div>

                <button type="submit" class="btn btn-brb w-100 mb-3">
                    <i class="fa-solid fa-save me-2"></i> Сохранить изменения
                </button>

                <a href="{{ route('admin.meetings.show', $meeting) }}" class="btn btn-outline-secondary w-100">
                    Отмена
                </a>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('participantSearch');
    const participantsList = document.getElementById('participantsList');
    const selectedContainer = document.getElementById('selectedParticipants');
    const checkboxes = document.querySelectorAll('.participant-checkbox');

    // Search functionality
    searchInput.addEventListener('input', function() {
        const search = this.value.toLowerCase();
        const items = participantsList.querySelectorAll('.participant-item');

        items.forEach(item => {
            const name = item.dataset.name.toLowerCase();
            item.style.display = name.includes(search) ? 'flex' : 'none';
        });
    });

    // Update selected participants display
    function updateSelectedDisplay() {
        selectedContainer.innerHTML = '';
        checkboxes.forEach(cb => {
            if (cb.checked) {
                const item = cb.closest('.participant-item');
                const name = item.dataset.name;
                const userId = item.dataset.userId;

                const tag = document.createElement('div');
                tag.className = 'selected-tag';
                tag.innerHTML = `
                    <span>${name}</span>
                    <button type="button" class="remove-btn" data-user-id="${userId}">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                `;
                selectedContainer.appendChild(tag);
            }
        });

        // Add remove functionality
        selectedContainer.querySelectorAll('.remove-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const userId = this.dataset.userId;
                const checkbox = document.querySelector(`.participant-item[data-user-id="${userId}"] .participant-checkbox`);
                if (checkbox) {
                    checkbox.checked = false;
                    checkbox.closest('.participant-item').classList.remove('selected');
                    updateSelectedDisplay();
                }
            });
        });
    }

    // Checkbox change handlers
    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            this.closest('.participant-item').classList.toggle('selected', this.checked);
            updateSelectedDisplay();
        });

        // Initialize selected state
        if (cb.checked) {
            cb.closest('.participant-item').classList.add('selected');
        }
    });

    // Initial display update
    updateSelectedDisplay();
});
</script>
@endsection
