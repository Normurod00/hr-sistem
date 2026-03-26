@extends('layouts.app')

@section('title', 'Редактировать профиль — BRB Bank')

@section('content')
<style>
    .edit-page {
        background: #f9fafb;
        min-height: calc(100vh - 200px);
        padding: 40px 0 60px;
    }

    .edit-header {
        background: #fff;
        border-bottom: 1px solid #e5e5e5;
        padding: 20px 0;
    }

    .edit-header .breadcrumb {
        margin-bottom: 0;
        font-size: 14px;
    }

    .edit-header .breadcrumb a {
        color: var(--brb-red);
        text-decoration: none;
    }

    .edit-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        overflow: hidden;
    }

    .edit-card-header {
        padding: 24px 32px;
        border-bottom: 1px solid #f0f0f0;
    }

    .edit-card-title {
        font-size: 22px;
        font-weight: 700;
        color: #222;
        margin: 0;
    }

    .edit-card-subtitle {
        color: #666;
        font-size: 14px;
        margin-top: 4px;
    }

    .edit-card-body {
        padding: 32px;
    }

    .avatar-section {
        display: flex;
        align-items: center;
        gap: 24px;
        padding-bottom: 32px;
        margin-bottom: 32px;
        border-bottom: 1px solid #f0f0f0;
    }

    .avatar-preview {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--brb-red) 0%, #8B0012 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        font-weight: 700;
        color: #fff;
        overflow: hidden;
    }

    .avatar-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .avatar-actions {
        flex: 1;
    }

    .avatar-hint {
        font-size: 13px;
        color: #888;
        margin-top: 8px;
    }

    .btn-upload {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 20px;
        background: #f5f5f5;
        color: #333;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-upload:hover {
        background: #e8e8e8;
    }

    .form-group {
        margin-bottom: 24px;
    }

    .form-label {
        display: block;
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .form-input {
        width: 100%;
        padding: 14px 16px;
        border: 1px solid #ddd;
        border-radius: 10px;
        font-size: 15px;
        transition: all 0.2s;
    }

    .form-input:focus {
        outline: none;
        border-color: var(--brb-red);
        box-shadow: 0 0 0 3px rgba(214, 0, 28, 0.1);
    }

    .form-input:disabled {
        background: #f8f8f8;
        color: #888;
        cursor: not-allowed;
    }

    .form-hint {
        font-size: 12px;
        color: #888;
        margin-top: 6px;
    }

    .error-text {
        color: var(--brb-red);
        font-size: 13px;
        margin-top: 6px;
    }

    .form-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 24px;
        border-top: 1px solid #f0f0f0;
        margin-top: 8px;
    }

    .btn-cancel {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 12px 24px;
        color: #666;
        text-decoration: none;
        font-weight: 500;
        border-radius: 8px;
        transition: all 0.2s;
    }

    .btn-cancel:hover {
        background: #f5f5f5;
        color: #333;
    }

    .btn-save {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 14px 32px;
        background: var(--brb-red);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-save:hover {
        background: #b8001a;
        transform: translateY(-1px);
    }

    @media (max-width: 768px) {
        .edit-card-header,
        .edit-card-body {
            padding-left: 20px;
            padding-right: 20px;
        }

        .avatar-section {
            flex-direction: column;
            text-align: center;
        }

        .form-actions {
            flex-direction: column-reverse;
            gap: 12px;
        }

        .btn-save {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<!-- Header -->
<div class="edit-header">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('profile.show') }}">Профиль</a></li>
                <li class="breadcrumb-item text-muted">Редактирование</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Main Content -->
<div class="edit-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="edit-card">
                    <div class="edit-card-header">
                        <h1 class="edit-card-title">Редактировать профиль</h1>
                        <p class="edit-card-subtitle">Обновите ваши личные данные</p>
                    </div>

                    <div class="edit-card-body">
                        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <!-- Avatar Section -->
                            <div class="avatar-section">
                                <div class="avatar-preview" id="avatarPreview">
                                    @if($user->avatar_url && !str_contains($user->avatar_url, 'ui-avatars'))
                                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
                                    @else
                                        {{ mb_substr($user->name, 0, 1) }}
                                    @endif
                                </div>
                                <div class="avatar-actions">
                                    <label class="btn-upload">
                                        <i class="bi bi-camera"></i> Изменить фото
                                        <input type="file" id="avatar" name="avatar" accept="image/*" style="display: none;">
                                    </label>
                                    <div class="avatar-hint">JPG, PNG или GIF. Максимум 2 МБ.</div>
                                </div>
                            </div>

                            <!-- Name -->
                            <div class="form-group">
                                <label for="name" class="form-label">Полное имя</label>
                                <input type="text"
                                       class="form-input @error('name') is-invalid @enderror"
                                       id="name"
                                       name="name"
                                       value="{{ old('name', $user->name) }}"
                                       placeholder="Иванов Иван Петрович"
                                       required>
                                @error('name')
                                    <div class="error-text">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email (readonly) -->
                            <div class="form-group">
                                <label for="email" class="form-label">Email</label>
                                <input type="email"
                                       class="form-input"
                                       value="{{ $user->email }}"
                                       disabled>
                                <div class="form-hint">Email нельзя изменить</div>
                            </div>

                            <!-- Phone -->
                            <div class="form-group">
                                <label for="phone" class="form-label">Телефон</label>
                                <input type="tel"
                                       class="form-input @error('phone') is-invalid @enderror"
                                       id="phone"
                                       name="phone"
                                       value="{{ old('phone', $user->phone) }}"
                                       placeholder="+998 90 123 45 67">
                                @error('phone')
                                    <div class="error-text">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Actions -->
                            <div class="form-actions">
                                <a href="{{ route('profile.show') }}" class="btn-cancel">
                                    <i class="bi bi-arrow-left"></i> Отмена
                                </a>
                                <button type="submit" class="btn-save">
                                    <i class="bi bi-check-lg"></i> Сохранить изменения
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('avatar').addEventListener('change', function(e) {
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatarPreview');
            preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
        };
        reader.readAsDataURL(this.files[0]);
    }
});
</script>
@endsection
