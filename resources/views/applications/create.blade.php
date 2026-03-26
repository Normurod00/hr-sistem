@extends('layouts.app')

@section('title', 'Откликнуться — ' . $vacancy->title)

@section('content')
<style>
    .apply-page {
        background: #f9fafb;
        min-height: calc(100vh - 200px);
        padding: 40px 0 60px;
    }

    .apply-header {
        background: #fff;
        border-bottom: 1px solid #e5e5e5;
        padding: 20px 0;
    }

    .apply-header .breadcrumb {
        margin-bottom: 0;
        font-size: 14px;
    }

    .apply-header .breadcrumb a {
        color: var(--brb-red);
        text-decoration: none;
    }

    .apply-header .breadcrumb a:hover {
        text-decoration: underline;
    }

    .apply-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        overflow: hidden;
    }

    .apply-card-header {
        padding: 28px 32px;
        border-bottom: 1px solid #f0f0f0;
    }

    .apply-card-title {
        font-size: 24px;
        font-weight: 700;
        color: #222;
        margin-bottom: 8px;
    }

    .apply-card-subtitle {
        color: #666;
        font-size: 15px;
    }

    .vacancy-preview {
        background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
        border-radius: 12px;
        padding: 20px;
        margin: 24px 32px;
        border: 1px solid #e5e5e5;
    }

    .vacancy-preview-title {
        font-size: 18px;
        font-weight: 700;
        color: #222;
        margin-bottom: 8px;
    }

    .vacancy-preview-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        font-size: 14px;
        color: #666;
    }

    .vacancy-preview-meta i {
        color: #999;
        margin-right: 4px;
    }

    .vacancy-preview-salary {
        color: var(--brb-green);
        font-weight: 600;
    }

    /* Resume Type Tabs */
    .resume-tabs {
        display: flex;
        margin: 0 32px 24px;
        border: 1px solid #e5e5e5;
        border-radius: 12px;
        overflow: hidden;
        background: #f8f9fa;
    }

    .resume-tab {
        flex: 1;
        padding: 16px 20px;
        background: transparent;
        border: none;
        cursor: pointer;
        font-size: 15px;
        font-weight: 500;
        color: #666;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.2s;
    }

    .resume-tab:hover {
        background: rgba(255,255,255,0.5);
        color: #333;
    }

    .resume-tab.active {
        background: #fff;
        color: var(--brb-red);
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    }

    .resume-tab i {
        font-size: 20px;
    }

    .resume-tab-content {
        display: none;
    }

    .resume-tab-content.active {
        display: block;
    }

    .apply-form {
        padding: 0 32px 32px;
    }

    .form-section {
        margin-bottom: 28px;
    }

    .form-section-title {
        font-size: 16px;
        font-weight: 700;
        color: #222;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-section-title .required-star {
        color: var(--brb-red);
    }

    .upload-zone {
        border: 2px dashed #d0d0d0;
        border-radius: 12px;
        padding: 32px;
        text-align: center;
        background: #fafafa;
        transition: all 0.2s;
        cursor: pointer;
        position: relative;
    }

    .upload-zone:hover {
        border-color: var(--brb-red);
        background: rgba(214, 0, 28, 0.02);
    }

    .upload-zone.dragover {
        border-color: var(--brb-red);
        background: rgba(214, 0, 28, 0.05);
    }

    .upload-zone input[type="file"] {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }

    .upload-icon {
        font-size: 48px;
        color: #bbb;
        margin-bottom: 12px;
    }

    .upload-title {
        font-size: 16px;
        font-weight: 600;
        color: #333;
        margin-bottom: 4px;
    }

    .upload-hint {
        font-size: 13px;
        color: #888;
    }

    .upload-formats {
        margin-top: 12px;
        font-size: 12px;
        color: #999;
    }

    .selected-file {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        background: rgba(35, 169, 66, 0.08);
        border: 1px solid rgba(35, 169, 66, 0.2);
        border-radius: 8px;
        margin-top: 12px;
    }

    .selected-file i {
        font-size: 24px;
        color: var(--brb-green);
    }

    .selected-file-name {
        font-weight: 500;
        color: #333;
    }

    .selected-file-size {
        font-size: 12px;
        color: #888;
    }

    .selected-file-remove {
        margin-left: auto;
        color: #999;
        cursor: pointer;
        padding: 4px;
    }

    .selected-file-remove:hover {
        color: var(--brb-red);
    }

    /* Form Inputs */
    .form-input, .form-select {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #ddd;
        border-radius: 10px;
        font-size: 15px;
        font-family: inherit;
        transition: border-color 0.2s;
    }

    .form-input:focus, .form-select:focus {
        outline: none;
        border-color: var(--brb-red);
        box-shadow: 0 0 0 3px rgba(214, 0, 28, 0.1);
    }

    .form-input::placeholder {
        color: #aaa;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }

    .form-row-3 {
        grid-template-columns: repeat(3, 1fr);
    }

    .form-group {
        margin-bottom: 16px;
    }

    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 500;
        color: #444;
        margin-bottom: 6px;
    }

    .form-label .required-star {
        color: var(--brb-red);
    }

    .form-textarea {
        width: 100%;
        min-height: 100px;
        padding: 12px 16px;
        border: 1px solid #ddd;
        border-radius: 10px;
        font-size: 15px;
        font-family: inherit;
        resize: vertical;
        transition: border-color 0.2s;
    }

    .form-textarea:focus {
        outline: none;
        border-color: var(--brb-red);
        box-shadow: 0 0 0 3px rgba(214, 0, 28, 0.1);
    }

    .form-textarea::placeholder {
        color: #aaa;
    }

    .char-counter {
        text-align: right;
        font-size: 12px;
        color: #999;
        margin-top: 6px;
    }

    /* Dynamic Sections (Experience, Education) */
    .dynamic-section {
        border: 1px solid #e5e5e5;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 16px;
        background: #fafafa;
        position: relative;
    }

    .dynamic-section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .dynamic-section-title {
        font-weight: 600;
        color: #333;
    }

    .btn-remove-section {
        background: none;
        border: none;
        color: #999;
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 4px;
    }

    .btn-remove-section:hover {
        color: var(--brb-red);
        background: rgba(214, 0, 28, 0.08);
    }

    .btn-add-section {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        background: #f5f5f5;
        border: 1px dashed #ccc;
        border-radius: 8px;
        color: #666;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        width: 100%;
        justify-content: center;
    }

    .btn-add-section:hover {
        background: #fff;
        border-color: var(--brb-red);
        color: var(--brb-red);
    }

    /* Skills Tags */
    .skills-input-wrapper {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        padding: 10px 14px;
        border: 1px solid #ddd;
        border-radius: 10px;
        min-height: 50px;
        background: #fff;
        cursor: text;
    }

    .skills-input-wrapper:focus-within {
        border-color: var(--brb-red);
        box-shadow: 0 0 0 3px rgba(214, 0, 28, 0.1);
    }

    .skill-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: rgba(214, 0, 28, 0.08);
        border-radius: 20px;
        font-size: 13px;
        color: #333;
    }

    .skill-tag-remove {
        cursor: pointer;
        color: #999;
        font-size: 16px;
        line-height: 1;
    }

    .skill-tag-remove:hover {
        color: var(--brb-red);
    }

    .skills-input {
        border: none;
        outline: none;
        flex: 1;
        min-width: 120px;
        font-size: 14px;
        padding: 4px;
    }

    .skills-hint {
        font-size: 12px;
        color: #999;
        margin-top: 6px;
    }

    /* Language Section */
    .language-row {
        display: grid;
        grid-template-columns: 2fr 1fr auto;
        gap: 12px;
        align-items: end;
        margin-bottom: 12px;
    }

    .btn-remove-lang {
        padding: 12px;
        background: none;
        border: 1px solid #ddd;
        border-radius: 8px;
        color: #999;
        cursor: pointer;
    }

    .btn-remove-lang:hover {
        border-color: var(--brb-red);
        color: var(--brb-red);
    }

    /* AI Notice */
    .ai-notice {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 16px 20px;
        background: linear-gradient(135deg, #e8f4fd 0%, #f0f8ff 100%);
        border-radius: 10px;
        border: 1px solid #c5e3f6;
        margin-bottom: 28px;
    }

    .ai-notice i {
        font-size: 24px;
        color: #0095ff;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .ai-notice-content h6 {
        font-weight: 700;
        color: #0077cc;
        margin-bottom: 4px;
        font-size: 14px;
    }

    .ai-notice-content p {
        color: #555;
        font-size: 13px;
        margin: 0;
        line-height: 1.5;
    }

    .apply-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 20px;
        border-top: 1px solid #f0f0f0;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 12px 20px;
        color: #666;
        text-decoration: none;
        font-weight: 500;
        border-radius: 8px;
        transition: all 0.2s;
    }

    .btn-back:hover {
        background: #f5f5f5;
        color: #333;
    }

    .btn-submit {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 14px 32px;
        background: var(--brb-red);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-submit:hover {
        background: #b8001a;
        transform: translateY(-1px);
    }

    .btn-submit:disabled {
        background: #ccc;
        cursor: not-allowed;
        transform: none;
    }

    .privacy-note {
        font-size: 12px;
        color: #999;
        text-align: center;
        margin-top: 20px;
    }

    .privacy-note a {
        color: var(--brb-red);
        text-decoration: none;
    }

    .privacy-note a:hover {
        text-decoration: underline;
    }

    .error-message {
        color: var(--brb-red);
        font-size: 13px;
        margin-top: 6px;
    }

    /* Section Divider */
    .section-divider {
        display: flex;
        align-items: center;
        gap: 16px;
        margin: 32px 0;
        color: #999;
        font-size: 13px;
    }

    .section-divider::before,
    .section-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e5e5e5;
    }

    @media (max-width: 768px) {
        .apply-card-header,
        .apply-form {
            padding-left: 20px;
            padding-right: 20px;
        }

        .vacancy-preview,
        .resume-tabs {
            margin-left: 20px;
            margin-right: 20px;
        }

        .form-row,
        .form-row-3 {
            grid-template-columns: 1fr;
        }

        .language-row {
            grid-template-columns: 1fr 1fr;
        }

        .language-row .btn-remove-lang {
            grid-column: span 2;
            justify-self: end;
        }

        .apply-actions {
            flex-direction: column-reverse;
            gap: 12px;
        }

        .btn-submit {
            width: 100%;
            justify-content: center;
        }

        .resume-tab span {
            display: none;
        }

        .resume-tab i {
            font-size: 24px;
        }
    }
</style>

<!-- Header with breadcrumb -->
<div class="apply-header">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('vacant.index') }}">Вакансии</a></li>
                <li class="breadcrumb-item"><a href="{{ route('vacant.show', $vacancy) }}">{{ Str::limit($vacancy->title, 30) }}</a></li>
                <li class="breadcrumb-item text-muted">Отклик</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Main Content -->
<div class="apply-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="apply-card">
                    <div class="apply-card-header">
                        <h1 class="apply-card-title">Отклик на вакансию</h1>
                        <p class="apply-card-subtitle">Загрузите готовое резюме или заполните форму</p>
                    </div>

                    <!-- Vacancy Preview -->
                    <div class="vacancy-preview">
                        <div class="vacancy-preview-title">{{ $vacancy->title }}</div>
                        <div class="vacancy-preview-meta">
                            <span><i class="bi bi-building"></i> BRB Bank</span>
                            @if($vacancy->location)
                                <span><i class="bi bi-geo-alt"></i> {{ $vacancy->location }}</span>
                            @endif
                            <span><i class="bi bi-briefcase"></i> {{ $vacancy->employment_type_label }}</span>
                            @if($vacancy->salary_formatted)
                                <span class="vacancy-preview-salary">
                                    <i class="bi bi-cash"></i> {{ $vacancy->salary_formatted }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Resume Type Tabs -->
                    <div class="resume-tabs">
                        <button type="button" class="resume-tab active" data-tab="upload">
                            <i class="bi bi-cloud-arrow-up"></i>
                            <span>Загрузить резюме</span>
                        </button>
                        <button type="button" class="resume-tab" data-tab="create">
                            <i class="bi bi-pencil-square"></i>
                            <span>Создать резюме</span>
                        </button>
                    </div>

                    <!-- TAB 1: Upload Resume -->
                    <div class="resume-tab-content active" id="tab-upload">
                        <form action="{{ route('applications.store', $vacancy) }}" method="POST" enctype="multipart/form-data" class="apply-form" id="uploadForm">
                            @csrf
                            <input type="hidden" name="resume_type" value="upload">

                            <!-- Resume Upload -->
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="bi bi-file-earmark-text"></i>
                                    Резюме
                                    <span class="required-star">*</span>
                                </div>

                                <div class="upload-zone" id="uploadZone">
                                    <input type="file"
                                           name="resume"
                                           id="resumeInput"
                                           accept=".pdf,.doc,.docx,.txt,.rtf"
                                           required>
                                    <div class="upload-icon">
                                        <i class="bi bi-cloud-arrow-up"></i>
                                    </div>
                                    <div class="upload-title">Перетащите файл или нажмите для выбора</div>
                                    <div class="upload-hint">Загрузите своё резюме</div>
                                    <div class="upload-formats">PDF, DOC, DOCX, TXT, RTF — до 10 МБ</div>
                                </div>

                                <div class="selected-file" id="selectedFile" style="display: none;">
                                    <i class="bi bi-file-earmark-check"></i>
                                    <div>
                                        <div class="selected-file-name" id="fileName"></div>
                                        <div class="selected-file-size" id="fileSize"></div>
                                    </div>
                                    <span class="selected-file-remove" id="removeFile" title="Удалить">
                                        <i class="bi bi-x-lg"></i>
                                    </span>
                                </div>

                                @error('resume')
                                    <div class="error-message"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Cover Letter -->
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="bi bi-chat-text"></i>
                                    Сопроводительное письмо
                                    <span class="text-muted" style="font-weight: 400; font-size: 13px;">(необязательно)</span>
                                </div>

                                <textarea name="cover_letter"
                                          id="coverLetterUpload"
                                          class="form-textarea @error('cover_letter') is-invalid @enderror"
                                          placeholder="Расскажите, почему вы заинтересованы в этой позиции..."
                                          maxlength="5000">{{ old('cover_letter') }}</textarea>
                                <div class="char-counter"><span id="charCountUpload">0</span> / 5000</div>

                                @error('cover_letter')
                                    <div class="error-message"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- AI Notice -->
                            <div class="ai-notice">
                                <i class="bi bi-robot"></i>
                                <div class="ai-notice-content">
                                    <h6>Интеллектуальный анализ</h6>
                                    <p>
                                        Ваше резюме будет проанализировано AI-системой для оценки соответствия
                                        требованиям вакансии.
                                    </p>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="apply-actions">
                                <a href="{{ route('vacant.show', $vacancy) }}" class="btn-back">
                                    <i class="bi bi-arrow-left"></i> К вакансии
                                </a>
                                <button type="submit" class="btn-submit">
                                    <i class="bi bi-send-fill"></i> Отправить отклик
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- TAB 2: Create Resume Form -->
                    <div class="resume-tab-content" id="tab-create">
                        <form action="{{ route('applications.store', $vacancy) }}" method="POST" class="apply-form" id="createForm">
                            @csrf
                            <input type="hidden" name="resume_type" value="form">

                            <!-- Personal Info Section -->
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="bi bi-person"></i>
                                    Личные данные
                                    <span class="required-star">*</span>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">ФИО <span class="required-star">*</span></label>
                                        <input type="text" name="full_name" class="form-input" placeholder="Иванов Иван Иванович" required value="{{ old('full_name', auth()->user()->name ?? '') }}">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Дата рождения <span class="required-star">*</span></label>
                                        <input type="date" name="birth_date" class="form-input" required value="{{ old('birth_date') }}">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Телефон <span class="required-star">*</span></label>
                                        <input type="tel" name="phone" class="form-input" placeholder="+998 90 123 45 67" required value="{{ old('phone', auth()->user()->phone ?? '') }}">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Email <span class="required-star">*</span></label>
                                        <input type="email" name="email" class="form-input" placeholder="example@email.com" required value="{{ old('email', auth()->user()->email ?? '') }}">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Город проживания <span class="required-star">*</span></label>
                                        <input type="text" name="city" class="form-input" placeholder="Ташкент" required value="{{ old('city') }}">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Гражданство</label>
                                        <input type="text" name="citizenship" class="form-input" placeholder="Узбекистан" value="{{ old('citizenship', 'Узбекистан') }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Desired Position -->
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="bi bi-briefcase"></i>
                                    Желаемая должность
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Должность</label>
                                        <input type="text" name="desired_position" class="form-input" placeholder="Например: Менеджер по продажам" value="{{ old('desired_position', $vacancy->title) }}">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Ожидаемая зарплата (сум)</label>
                                        <input type="text" name="desired_salary" class="form-input" placeholder="от 5 000 000" value="{{ old('desired_salary') }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Education Section -->
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="bi bi-mortarboard"></i>
                                    Образование
                                </div>

                                <div id="educationContainer">
                                    <div class="dynamic-section education-item">
                                        <div class="dynamic-section-header">
                                            <span class="dynamic-section-title">Образование #1</span>
                                            <button type="button" class="btn-remove-section" onclick="removeSection(this)" style="display: none;">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>

                                        <div class="form-row">
                                            <div class="form-group">
                                                <label class="form-label">Уровень образования</label>
                                                <select name="education[0][level]" class="form-select">
                                                    <option value="">Выберите</option>
                                                    <option value="secondary">Среднее</option>
                                                    <option value="vocational">Среднее специальное</option>
                                                    <option value="incomplete_higher">Неоконченное высшее</option>
                                                    <option value="bachelor">Бакалавр</option>
                                                    <option value="master">Магистр</option>
                                                    <option value="phd">Доктор наук</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Год окончания</label>
                                                <input type="number" name="education[0][year]" class="form-input" placeholder="2020" min="1970" max="2030">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label">Учебное заведение</label>
                                            <input type="text" name="education[0][institution]" class="form-input" placeholder="Название университета или колледжа">
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label">Специальность</label>
                                            <input type="text" name="education[0][speciality]" class="form-input" placeholder="Например: Экономика и финансы">
                                        </div>
                                    </div>
                                </div>

                                <button type="button" class="btn-add-section" onclick="addEducation()">
                                    <i class="bi bi-plus-circle"></i> Добавить образование
                                </button>
                            </div>

                            <!-- Experience Section -->
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="bi bi-building"></i>
                                    Опыт работы
                                </div>

                                <div id="experienceContainer">
                                    <div class="dynamic-section experience-item">
                                        <div class="dynamic-section-header">
                                            <span class="dynamic-section-title">Место работы #1</span>
                                            <button type="button" class="btn-remove-section" onclick="removeSection(this)" style="display: none;">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>

                                        <div class="form-row">
                                            <div class="form-group">
                                                <label class="form-label">Компания</label>
                                                <input type="text" name="experience[0][company]" class="form-input" placeholder="Название компании">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Должность</label>
                                                <input type="text" name="experience[0][position]" class="form-input" placeholder="Ваша должность">
                                            </div>
                                        </div>

                                        <div class="form-row form-row-3">
                                            <div class="form-group">
                                                <label class="form-label">Начало работы</label>
                                                <input type="month" name="experience[0][start_date]" class="form-input">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Окончание</label>
                                                <input type="month" name="experience[0][end_date]" class="form-input">
                                            </div>
                                            <div class="form-group" style="display: flex; align-items: end;">
                                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                                    <input type="checkbox" name="experience[0][current]" onchange="toggleCurrentJob(this)">
                                                    <span style="font-size: 14px;">По настоящее время</span>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label">Обязанности и достижения</label>
                                            <textarea name="experience[0][description]" class="form-textarea" rows="3" placeholder="Опишите ваши основные обязанности и достижения..."></textarea>
                                        </div>
                                    </div>
                                </div>

                                <button type="button" class="btn-add-section" onclick="addExperience()">
                                    <i class="bi bi-plus-circle"></i> Добавить место работы
                                </button>
                            </div>

                            <!-- Skills Section -->
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="bi bi-star"></i>
                                    Навыки
                                </div>

                                <div class="skills-input-wrapper" id="skillsWrapper" onclick="document.getElementById('skillsInput').focus()">
                                    <input type="text"
                                           id="skillsInput"
                                           class="skills-input"
                                           placeholder="Введите навык и нажмите Enter">
                                </div>
                                <input type="hidden" name="skills" id="skillsHidden" value="{{ old('skills', '') }}">
                                <div class="skills-hint">Например: Excel, 1C, Английский язык, Продажи</div>
                            </div>

                            <!-- Languages Section -->
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="bi bi-translate"></i>
                                    Знание языков
                                </div>

                                <div id="languagesContainer">
                                    <div class="language-row">
                                        <div class="form-group" style="margin-bottom: 0;">
                                            <label class="form-label">Язык</label>
                                            <select name="languages[0][name]" class="form-select">
                                                <option value="">Выберите язык</option>
                                                <option value="Узбекский" selected>Узбекский</option>
                                                <option value="Русский">Русский</option>
                                                <option value="Английский">Английский</option>
                                                <option value="Немецкий">Немецкий</option>
                                                <option value="Французский">Французский</option>
                                                <option value="Китайский">Китайский</option>
                                                <option value="Корейский">Корейский</option>
                                                <option value="Турецкий">Турецкий</option>
                                                <option value="Арабский">Арабский</option>
                                            </select>
                                        </div>
                                        <div class="form-group" style="margin-bottom: 0;">
                                            <label class="form-label">Уровень</label>
                                            <select name="languages[0][level]" class="form-select">
                                                <option value="native">Родной</option>
                                                <option value="fluent">Свободно</option>
                                                <option value="advanced">Продвинутый</option>
                                                <option value="intermediate">Средний</option>
                                                <option value="basic">Базовый</option>
                                            </select>
                                        </div>
                                        <button type="button" class="btn-remove-lang" onclick="removeLanguage(this)" style="visibility: hidden;">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                </div>

                                <button type="button" class="btn-add-section" onclick="addLanguage()" style="margin-top: 12px;">
                                    <i class="bi bi-plus-circle"></i> Добавить язык
                                </button>
                            </div>

                            <!-- About Section -->
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="bi bi-person-lines-fill"></i>
                                    О себе
                                </div>

                                <textarea name="about"
                                          class="form-textarea"
                                          rows="4"
                                          placeholder="Расскажите о себе: ваши сильные стороны, профессиональные цели, интересы...">{{ old('about') }}</textarea>
                            </div>

                            <!-- Cover Letter -->
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="bi bi-chat-text"></i>
                                    Сопроводительное письмо
                                    <span class="text-muted" style="font-weight: 400; font-size: 13px;">(необязательно)</span>
                                </div>

                                <textarea name="cover_letter"
                                          id="coverLetterCreate"
                                          class="form-textarea"
                                          placeholder="Почему вы заинтересованы в этой позиции?"
                                          maxlength="5000">{{ old('cover_letter') }}</textarea>
                                <div class="char-counter"><span id="charCountCreate">0</span> / 5000</div>
                            </div>

                            <!-- AI Notice -->
                            <div class="ai-notice">
                                <i class="bi bi-robot"></i>
                                <div class="ai-notice-content">
                                    <h6>Интеллектуальный анализ</h6>
                                    <p>
                                        Ваше резюме будет проанализировано AI-системой для оценки соответствия
                                        требованиям вакансии.
                                    </p>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="apply-actions">
                                <a href="{{ route('vacant.show', $vacancy) }}" class="btn-back">
                                    <i class="bi bi-arrow-left"></i> К вакансии
                                </a>
                                <button type="submit" class="btn-submit">
                                    <i class="bi bi-send-fill"></i> Отправить отклик
                                </button>
                            </div>
                        </form>
                    </div>

                    <p class="privacy-note">
                        Отправляя отклик, вы соглашаетесь с <a href="#">условиями использования</a>
                        и <a href="#">политикой конфиденциальности</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    const tabs = document.querySelectorAll('.resume-tab');
    const tabContents = document.querySelectorAll('.resume-tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const tabId = this.dataset.tab;

            tabs.forEach(t => t.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));

            this.classList.add('active');
            document.getElementById('tab-' + tabId).classList.add('active');
        });
    });

    // File upload handling
    const uploadZone = document.getElementById('uploadZone');
    const resumeInput = document.getElementById('resumeInput');
    const selectedFile = document.getElementById('selectedFile');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const removeFile = document.getElementById('removeFile');

    resumeInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const file = this.files[0];
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            selectedFile.style.display = 'flex';
            uploadZone.style.display = 'none';
        }
    });

    removeFile.addEventListener('click', function() {
        resumeInput.value = '';
        selectedFile.style.display = 'none';
        uploadZone.style.display = 'block';
    });

    // Drag and drop
    uploadZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });

    uploadZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
    });

    uploadZone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            resumeInput.files = e.dataTransfer.files;
            resumeInput.dispatchEvent(new Event('change'));
        }
    });

    // Character counters
    const coverLetterUpload = document.getElementById('coverLetterUpload');
    const charCountUpload = document.getElementById('charCountUpload');
    const coverLetterCreate = document.getElementById('coverLetterCreate');
    const charCountCreate = document.getElementById('charCountCreate');

    if (coverLetterUpload) {
        coverLetterUpload.addEventListener('input', function() {
            charCountUpload.textContent = this.value.length;
        });
        charCountUpload.textContent = coverLetterUpload.value.length;
    }

    if (coverLetterCreate) {
        coverLetterCreate.addEventListener('input', function() {
            charCountCreate.textContent = this.value.length;
        });
        charCountCreate.textContent = coverLetterCreate.value.length;
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Skills input
    const skillsInput = document.getElementById('skillsInput');
    const skillsWrapper = document.getElementById('skillsWrapper');
    const skillsHidden = document.getElementById('skillsHidden');
    let skills = skillsHidden.value ? skillsHidden.value.split(',').filter(s => s.trim()) : [];

    // Render existing skills
    renderSkills();

    skillsInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            const skill = this.value.trim();
            if (skill && !skills.includes(skill)) {
                skills.push(skill);
                renderSkills();
                this.value = '';
            }
        } else if (e.key === 'Backspace' && !this.value && skills.length > 0) {
            skills.pop();
            renderSkills();
        }
    });

    function renderSkills() {
        // Remove existing tags
        skillsWrapper.querySelectorAll('.skill-tag').forEach(tag => tag.remove());

        // Add tags before input
        skills.forEach((skill, index) => {
            const tag = document.createElement('span');
            tag.className = 'skill-tag';
            tag.innerHTML = `${skill} <span class="skill-tag-remove" onclick="removeSkill(${index})">&times;</span>`;
            skillsWrapper.insertBefore(tag, skillsInput);
        });

        skillsHidden.value = skills.join(',');
    }

    window.removeSkill = function(index) {
        skills.splice(index, 1);
        renderSkills();
    };

    // Update CSRF token before submit
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const csrfInput = form.querySelector('input[name="_token"]');
            if (csrfToken && csrfInput) {
                csrfInput.value = csrfToken;
            }
        });
    });
});

// Education counter
let educationCount = 1;

function addEducation() {
    const container = document.getElementById('educationContainer');
    const index = educationCount++;

    const html = `
        <div class="dynamic-section education-item">
            <div class="dynamic-section-header">
                <span class="dynamic-section-title">Образование #${index + 1}</span>
                <button type="button" class="btn-remove-section" onclick="removeSection(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Уровень образования</label>
                    <select name="education[${index}][level]" class="form-select">
                        <option value="">Выберите</option>
                        <option value="secondary">Среднее</option>
                        <option value="vocational">Среднее специальное</option>
                        <option value="incomplete_higher">Неоконченное высшее</option>
                        <option value="bachelor">Бакалавр</option>
                        <option value="master">Магистр</option>
                        <option value="phd">Доктор наук</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Год окончания</label>
                    <input type="number" name="education[${index}][year]" class="form-input" placeholder="2020" min="1970" max="2030">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Учебное заведение</label>
                <input type="text" name="education[${index}][institution]" class="form-input" placeholder="Название университета или колледжа">
            </div>

            <div class="form-group">
                <label class="form-label">Специальность</label>
                <input type="text" name="education[${index}][speciality]" class="form-input" placeholder="Например: Экономика и финансы">
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', html);
    updateRemoveButtons();
}

// Experience counter
let experienceCount = 1;

function addExperience() {
    const container = document.getElementById('experienceContainer');
    const index = experienceCount++;

    const html = `
        <div class="dynamic-section experience-item">
            <div class="dynamic-section-header">
                <span class="dynamic-section-title">Место работы #${index + 1}</span>
                <button type="button" class="btn-remove-section" onclick="removeSection(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Компания</label>
                    <input type="text" name="experience[${index}][company]" class="form-input" placeholder="Название компании">
                </div>
                <div class="form-group">
                    <label class="form-label">Должность</label>
                    <input type="text" name="experience[${index}][position]" class="form-input" placeholder="Ваша должность">
                </div>
            </div>

            <div class="form-row form-row-3">
                <div class="form-group">
                    <label class="form-label">Начало работы</label>
                    <input type="month" name="experience[${index}][start_date]" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Окончание</label>
                    <input type="month" name="experience[${index}][end_date]" class="form-input">
                </div>
                <div class="form-group" style="display: flex; align-items: end;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="experience[${index}][current]" onchange="toggleCurrentJob(this)">
                        <span style="font-size: 14px;">По настоящее время</span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Обязанности и достижения</label>
                <textarea name="experience[${index}][description]" class="form-textarea" rows="3" placeholder="Опишите ваши основные обязанности и достижения..."></textarea>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', html);
    updateRemoveButtons();
}

// Language counter
let languageCount = 1;

function addLanguage() {
    const container = document.getElementById('languagesContainer');
    const index = languageCount++;

    const html = `
        <div class="language-row">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Язык</label>
                <select name="languages[${index}][name]" class="form-select">
                    <option value="">Выберите язык</option>
                    <option value="Узбекский">Узбекский</option>
                    <option value="Русский">Русский</option>
                    <option value="Английский">Английский</option>
                    <option value="Немецкий">Немецкий</option>
                    <option value="Французский">Французский</option>
                    <option value="Китайский">Китайский</option>
                    <option value="Корейский">Корейский</option>
                    <option value="Турецкий">Турецкий</option>
                    <option value="Арабский">Арабский</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Уровень</label>
                <select name="languages[${index}][level]" class="form-select">
                    <option value="native">Родной</option>
                    <option value="fluent">Свободно</option>
                    <option value="advanced">Продвинутый</option>
                    <option value="intermediate">Средний</option>
                    <option value="basic">Базовый</option>
                </select>
            </div>
            <button type="button" class="btn-remove-lang" onclick="removeLanguage(this)">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', html);
    updateLanguageRemoveButtons();
}

function removeSection(btn) {
    btn.closest('.dynamic-section').remove();
    updateRemoveButtons();
}

function removeLanguage(btn) {
    btn.closest('.language-row').remove();
    updateLanguageRemoveButtons();
}

function updateRemoveButtons() {
    // Education
    const eduItems = document.querySelectorAll('.education-item');
    eduItems.forEach((item, index) => {
        const btn = item.querySelector('.btn-remove-section');
        btn.style.display = eduItems.length > 1 ? 'block' : 'none';
    });

    // Experience
    const expItems = document.querySelectorAll('.experience-item');
    expItems.forEach((item, index) => {
        const btn = item.querySelector('.btn-remove-section');
        btn.style.display = expItems.length > 1 ? 'block' : 'none';
    });
}

function updateLanguageRemoveButtons() {
    const langRows = document.querySelectorAll('.language-row');
    langRows.forEach((row, index) => {
        const btn = row.querySelector('.btn-remove-lang');
        btn.style.visibility = langRows.length > 1 ? 'visible' : 'hidden';
    });
}

function toggleCurrentJob(checkbox) {
    const row = checkbox.closest('.form-row-3');
    const endDateInput = row.querySelector('input[type="month"][name*="end_date"]');
    if (checkbox.checked) {
        endDateInput.disabled = true;
        endDateInput.value = '';
    } else {
        endDateInput.disabled = false;
    }
}
</script>
@endsection
