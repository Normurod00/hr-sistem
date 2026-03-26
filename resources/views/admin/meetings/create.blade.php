@extends('layouts.admin')

@section('title', 'Создать видеовстречу')
@section('header', 'Создать видеовстречу')

@section('content')
<style>
    .page-header {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
    }
    .back-btn {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-secondary);
        text-decoration: none;
        transition: all 0.2s;
    }
    .back-btn:hover {
        background: var(--brb-red);
        color: white;
        border-color: var(--brb-red);
    }
    .page-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
    }

    .form-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        overflow: hidden;
    }
    .form-card-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .form-card-header i {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: linear-gradient(135deg, rgba(229, 39, 22, 0.1) 0%, rgba(229, 39, 22, 0.05) 100%);
        color: var(--brb-red);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .form-card-header h3 {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
    }
    .form-card-body {
        padding: 24px;
    }

    .candidate-preview {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 20px;
        background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        margin-bottom: 20px;
    }
    .candidate-avatar {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--brb-red) 0%, #ff6b5b 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 1.3rem;
    }
    .candidate-info h4 {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0 0 4px 0;
    }
    .candidate-info p {
        font-size: 0.85rem;
        color: var(--text-secondary);
        margin: 0;
    }

    /* Participants Selector */
    .participants-search {
        position: relative;
        margin-bottom: 16px;
    }
    .participants-search input {
        width: 100%;
        padding: 14px 16px 14px 44px;
        border: 1px solid var(--border-color);
        border-radius: 10px;
        font-size: 0.95rem;
        background: var(--card-bg);
        color: var(--text-primary);
        transition: all 0.2s;
    }
    .participants-search input:focus {
        outline: none;
        border-color: var(--brb-red);
        box-shadow: 0 0 0 3px rgba(229, 39, 22, 0.1);
    }
    .participants-search i {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-secondary);
    }

    .selected-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 16px;
        min-height: 40px;
    }
    .selected-tag {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        background: linear-gradient(135deg, var(--brb-red) 0%, #ff6b5b 100%);
        color: white;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
        animation: tagIn 0.2s ease;
    }
    @keyframes tagIn {
        from { transform: scale(0.8); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    .selected-tag .remove-btn {
        background: rgba(255,255,255,0.2);
        border: none;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        cursor: pointer;
        font-size: 0.7rem;
        transition: background 0.2s;
    }
    .selected-tag .remove-btn:hover {
        background: rgba(255,255,255,0.4);
    }

    .participants-list {
        border: 1px solid var(--border-color);
        border-radius: 12px;
        max-height: 320px;
        overflow-y: auto;
    }
    .participant-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
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
        background: rgba(229, 39, 22, 0.05);
    }
    .participant-item input[type="checkbox"] {
        width: 20px;
        height: 20px;
        accent-color: var(--brb-red);
        cursor: pointer;
    }
    .participant-avatar-sm {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.85rem;
    }
    .participant-details {
        flex: 1;
    }
    .participant-name {
        font-weight: 500;
        color: var(--text-primary);
        margin-bottom: 2px;
    }
    .participant-meta {
        font-size: 0.8rem;
        color: var(--text-secondary);
    }
    .participant-role-badge {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    .role-admin {
        background: rgba(229, 39, 22, 0.1);
        color: var(--brb-red);
    }
    .role-hr {
        background: rgba(33, 150, 243, 0.1);
        color: #2196f3;
    }

    /* Sidebar Cards */
    .sidebar-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 20px;
    }
    .sidebar-card-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .sidebar-card-title i {
        color: var(--brb-red);
    }

    .tips-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .tips-list li {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.9rem;
        color: var(--text-secondary);
    }
    .tips-list li:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    .tips-list li i {
        color: #4caf50;
        margin-top: 2px;
    }

    .submit-btn {
        width: 100%;
        padding: 14px 24px;
        font-size: 1rem;
        font-weight: 600;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    .cancel-link {
        display: block;
        text-align: center;
        padding: 14px;
        color: var(--text-secondary);
        text-decoration: none;
        font-size: 0.95rem;
        transition: color 0.2s;
    }
    .cancel-link:hover {
        color: var(--text-primary);
    }

    /* Duration Select */
    .duration-options {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }
    .duration-option {
        position: relative;
    }
    .duration-option input {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }
    .duration-option label {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 16px 12px;
        background: var(--bg-secondary);
        border: 2px solid transparent;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .duration-option label:hover {
        border-color: var(--brb-red);
    }
    .duration-option input:checked + label {
        background: rgba(229, 39, 22, 0.1);
        border-color: var(--brb-red);
    }
    .duration-option .duration-value {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--text-primary);
    }
    .duration-option .duration-label {
        font-size: 0.75rem;
        color: var(--text-secondary);
        margin-top: 4px;
    }
</style>

<div class="page-header">
    <a href="{{ route('admin.meetings.index') }}" class="back-btn">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <h1 class="page-title">Создать видеовстречу</h1>
</div>

<form action="{{ route('admin.meetings.store') }}" method="POST">
    @csrf

    @if($application ?? false)
        <input type="hidden" name="application_id" value="{{ $application->id }}">
    @endif

    <div class="row">
        <div class="col-lg-8">
            {{-- Basic Info --}}
            <div class="form-card mb-4">
                <div class="form-card-header">
                    <i class="fa-solid fa-info-circle"></i>
                    <h3>Основная информация</h3>
                </div>
                <div class="form-card-body">
                    @if($application ?? false)
                        <div class="candidate-preview">
                            <div class="candidate-avatar">
                                {{ $application->user->initials ?? 'К' }}
                            </div>
                            <div class="candidate-info">
                                <h4>{{ $application->user->name ?? 'Кандидат' }}</h4>
                                <p>{{ $application->vacancy->title ?? 'Вакансия' }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="mb-4">
                        <label for="title" class="form-label">Название встречи <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                               id="title" name="title"
                               value="{{ old('title', isset($application) ? 'Собеседование: ' . ($application->vacancy->title ?? '') : '') }}"
                               placeholder="Например: Собеседование на позицию..."
                               required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label">Описание <span class="text-muted">(необязательно)</span></label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="3"
                                  placeholder="Повестка встречи, вопросы для обсуждения...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="scheduled_at" class="form-label">Дата и время <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control @error('scheduled_at') is-invalid @enderror"
                                   id="scheduled_at" name="scheduled_at"
                                   value="{{ old('scheduled_at', now()->addHour()->format('Y-m-d\TH:i')) }}" required>
                            @error('scheduled_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label">Продолжительность <span class="text-danger">*</span></label>
                            <div class="duration-options">
                                <div class="duration-option">
                                    <input type="radio" name="duration_minutes" value="15" id="dur15" {{ old('duration_minutes') == 15 ? 'checked' : '' }}>
                                    <label for="dur15">
                                        <span class="duration-value">15</span>
                                        <span class="duration-label">минут</span>
                                    </label>
                                </div>
                                <div class="duration-option">
                                    <input type="radio" name="duration_minutes" value="30" id="dur30" {{ old('duration_minutes', 30) == 30 ? 'checked' : '' }}>
                                    <label for="dur30">
                                        <span class="duration-value">30</span>
                                        <span class="duration-label">минут</span>
                                    </label>
                                </div>
                                <div class="duration-option">
                                    <input type="radio" name="duration_minutes" value="45" id="dur45" {{ old('duration_minutes') == 45 ? 'checked' : '' }}>
                                    <label for="dur45">
                                        <span class="duration-value">45</span>
                                        <span class="duration-label">минут</span>
                                    </label>
                                </div>
                                <div class="duration-option">
                                    <input type="radio" name="duration_minutes" value="60" id="dur60" {{ old('duration_minutes') == 60 ? 'checked' : '' }}>
                                    <label for="dur60">
                                        <span class="duration-value">60</span>
                                        <span class="duration-label">минут</span>
                                    </label>
                                </div>
                                <div class="duration-option">
                                    <input type="radio" name="duration_minutes" value="90" id="dur90" {{ old('duration_minutes') == 90 ? 'checked' : '' }}>
                                    <label for="dur90">
                                        <span class="duration-value">90</span>
                                        <span class="duration-label">минут</span>
                                    </label>
                                </div>
                                <div class="duration-option">
                                    <input type="radio" name="duration_minutes" value="120" id="dur120" {{ old('duration_minutes') == 120 ? 'checked' : '' }}>
                                    <label for="dur120">
                                        <span class="duration-value">2</span>
                                        <span class="duration-label">часа</span>
                                    </label>
                                </div>
                            </div>
                            @error('duration_minutes')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Participants --}}
            <div class="form-card">
                <div class="form-card-header">
                    <i class="fa-solid fa-users"></i>
                    <h3>Участники встречи</h3>
                </div>
                <div class="form-card-body">
                    <p class="text-muted mb-3">Выберите сотрудников, которых хотите пригласить на встречу</p>

                    <div class="selected-tags" id="selectedParticipants">
                        {{-- Selected participants appear here --}}
                    </div>

                    <div class="participants-search">
                        <i class="fa-solid fa-search"></i>
                        <input type="text" id="participantSearch" placeholder="Поиск по имени...">
                    </div>

                    <div class="participants-list" id="participantsList">
                        @foreach($staffUsers as $user)
                            @if($user->id !== auth()->id())
                                <label class="participant-item" data-user-id="{{ $user->id }}" data-name="{{ $user->name }}">
                                    <input type="checkbox" name="participants[]" value="{{ $user->id }}"
                                           class="participant-checkbox" {{ in_array($user->id, old('participants', [])) ? 'checked' : '' }}>
                                    <div class="participant-avatar-sm">
                                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1)) }}
                                    </div>
                                    <div class="participant-details">
                                        <div class="participant-name">{{ $user->name }}</div>
                                        <div class="participant-meta">{{ $user->email }}</div>
                                    </div>
                                    <span class="participant-role-badge role-{{ $user->role }}">
                                        {{ $user->role === 'admin' ? 'Админ' : 'HR' }}
                                    </span>
                                </label>
                            @endif
                        @endforeach
                    </div>

                    @error('participants')
                        <div class="text-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Actions --}}
            <div class="sidebar-card">
                <button type="submit" class="btn btn-brb submit-btn">
                    <i class="fa-solid fa-video"></i>
                    Создать встречу
                </button>
                <a href="{{ route('admin.meetings.index') }}" class="cancel-link">Отменить</a>
            </div>

            {{-- Tips --}}
            <div class="sidebar-card">
                <div class="sidebar-card-title">
                    <i class="fa-solid fa-lightbulb"></i>
                    Подсказки
                </div>
                <ul class="tips-list">
                    <li>
                        <i class="fa-solid fa-check"></i>
                        <span>Участники получат уведомление о встрече</span>
                    </li>
                    <li>
                        <i class="fa-solid fa-check"></i>
                        <span>Ссылка на встречу будет создана автоматически</span>
                    </li>
                    <li>
                        <i class="fa-solid fa-check"></i>
                        <span>Вы можете начать встречу раньше запланированного времени</span>
                    </li>
                    <li>
                        <i class="fa-solid fa-check"></i>
                        <span>Редактирование доступно до начала встречи</span>
                    </li>
                </ul>
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
