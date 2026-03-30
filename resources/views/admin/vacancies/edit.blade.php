@extends('layouts.admin')

@section('title', 'Редактировать вакансию')
@section('header', 'Редактировать: ' . $vacancy->title)

@push('styles')
<style>
/* Page Header */
.page-intro {
    background: linear-gradient(135deg, var(--panel) 0%, var(--grid) 100%);
    border: 1px solid var(--br);
    border-radius: 16px;
    padding: 24px 28px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 20px;
}

.page-intro__icon {
    width: 64px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(59, 130, 246, 0.1);
    color: #2563eb;
    border-radius: 16px;
    font-size: 28px;
    flex-shrink: 0;
}

.page-intro__content h2 {
    font-size: 20px;
    font-weight: 700;
    color: var(--fg-1);
    margin: 0 0 6px 0;
}

.page-intro__content p {
    font-size: 14px;
    color: var(--fg-3);
    margin: 0;
    line-height: 1.5;
}

/* Form Sections */
.form-section {
    background: var(--panel);
    border: 1px solid var(--br);
    border-radius: 16px;
    margin-bottom: 24px;
    overflow: hidden;
}

.form-section__header {
    background: var(--grid);
    border-bottom: 1px solid var(--br);
    padding: 16px 24px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.form-section__icon {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    font-size: 16px;
}

.form-section__icon.red { background: rgba(214, 0, 28, 0.1); color: var(--accent); }
.form-section__icon.blue { background: rgba(59, 130, 246, 0.1); color: #2563eb; }
.form-section__icon.green { background: rgba(34, 197, 94, 0.1); color: #16a34a; }
.form-section__icon.purple { background: rgba(139, 92, 246, 0.1); color: #7c3aed; }
.form-section__icon.yellow { background: rgba(245, 158, 11, 0.1); color: #d97706; }

.form-section__title {
    font-size: 15px;
    font-weight: 700;
    color: var(--fg-1);
    margin: 0;
}

.form-section__body {
    padding: 24px;
}

/* Form Controls */
.form-label {
    font-weight: 600;
    font-size: 13px;
    color: var(--fg-2);
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.form-label .required {
    color: var(--accent);
    font-weight: 700;
}

.form-control, .form-select {
    border: 2px solid var(--br);
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
    background: var(--panel);
    color: var(--fg-1);
}

.form-control:focus, .form-select:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 4px rgba(214, 0, 28, 0.1);
    outline: none;
}

.form-control::placeholder {
    color: var(--fg-3);
    font-weight: 400;
}

textarea.form-control {
    resize: vertical;
    min-height: 150px;
}

.form-text {
    font-size: 12px;
    color: var(--fg-3);
    margin-top: 6px;
}

/* Input Groups */
.input-with-icon {
    position: relative;
}

.input-with-icon .form-control {
    padding-left: 44px;
}

.input-with-icon__icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--fg-3);
    font-size: 16px;
}

/* Skills Input */
.skills-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 12px;
    min-height: 20px;
}

.skills-tags:empty::before {
    content: 'Навыки не выбраны';
    color: var(--fg-3);
    font-size: 13px;
    font-style: italic;
}

.skill-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: default;
    transition: all 0.2s ease;
}

.skill-tag.must-have {
    background: rgba(239, 68, 68, 0.1);
    color: #dc2626;
    border: 1px solid rgba(239, 68, 68, 0.25);
}

.skill-tag.nice-to-have {
    background: rgba(245, 158, 11, 0.1);
    color: #d97706;
    border: 1px solid rgba(245, 158, 11, 0.25);
}

.skill-tag__remove {
    cursor: pointer;
    opacity: 0.6;
    transition: all 0.15s;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: rgba(0,0,0,0.08);
    font-size: 11px;
}

.skill-tag__remove:hover {
    opacity: 1;
    background: rgba(0,0,0,0.18);
}

/* Skills Dropdown */
.skills-dropdown {
    position: relative;
}

.skills-dropdown__input {
    position: relative;
}

.skills-dropdown__input .form-control {
    padding-right: 44px;
}

.skills-dropdown__toggle {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--fg-3);
    cursor: pointer;
    padding: 4px;
    transition: transform 0.2s ease;
}

.skills-dropdown__toggle.open {
    transform: translateY(-50%) rotate(180deg);
}

.skills-dropdown__menu {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: var(--panel, #ffffff);
    border: 2px solid var(--br, #e9edf5);
    border-top: none;
    border-radius: 0 0 12px 12px;
    padding: 14px;
    z-index: 100;
    display: none;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
    max-height: 360px;
    overflow-y: auto;
}

.skills-dropdown__menu.show {
    display: block;
}

.skills-dropdown__group-title {
    font-size: 11px;
    font-weight: 700;
    color: var(--fg-3, #7c87a5);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 12px 0 8px 0;
    padding-bottom: 4px;
    border-bottom: 1px solid var(--br, #e9edf5);
    width: 100%;
    flex-basis: 100%;
}

.skills-dropdown__group-title:first-child {
    margin-top: 0;
}

.skills-dropdown__label {
    font-size: 12px;
    font-weight: 600;
    color: var(--fg-3);
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.skills-dropdown__count {
    background: var(--grid);
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 11px;
}

.skills-dropdown__options {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.skill-option {
    display: inline-flex;
    align-items: center;
    padding: 6px 14px;
    background: var(--grid, #eef1f7);
    border: 1px solid var(--br, #e9edf5);
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    color: var(--fg-2, #2d3754);
    cursor: pointer;
    transition: all 0.15s ease;
}

.skill-option:hover {
    background: rgba(59, 130, 246, 0.1);
    border-color: rgba(59, 130, 246, 0.3);
    color: #2563eb;
    transform: translateY(-1px);
}

.skills-dropdown__empty {
    color: var(--fg-3);
    font-size: 13px;
    text-align: center;
    padding: 20px;
}

.skills-dropdown__hint {
    font-size: 11px;
    color: var(--fg-3);
    text-align: center;
    padding: 8px 0 0 0;
    border-top: 1px solid var(--br);
    margin-top: 10px;
}

/* Checkbox Switch */
.form-switch-custom {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 20px;
    background: var(--grid);
    border: 1px solid var(--br);
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.form-switch-custom:hover {
    border-color: var(--accent);
}

.form-switch-custom input {
    display: none;
}

.form-switch-custom__toggle {
    width: 48px;
    height: 28px;
    background: var(--br);
    border-radius: 14px;
    position: relative;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.form-switch-custom__toggle::after {
    content: '';
    position: absolute;
    left: 4px;
    top: 4px;
    width: 20px;
    height: 20px;
    background: white;
    border-radius: 50%;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.form-switch-custom input:checked + .form-switch-custom__toggle {
    background: var(--good);
}

.form-switch-custom input:checked + .form-switch-custom__toggle::after {
    left: 24px;
}

.form-switch-custom__label {
    flex: 1;
}

.form-switch-custom__title {
    font-weight: 600;
    font-size: 14px;
    color: var(--fg-1);
    margin-bottom: 2px;
}

.form-switch-custom__desc {
    font-size: 12px;
    color: var(--fg-3);
}

/* Stats Card */
.stats-card {
    background: var(--panel);
    border: 1px solid var(--br);
    border-radius: 16px;
    overflow: hidden;
    margin-bottom: 24px;
}

.stats-card__header {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
    border-bottom: 1px solid var(--br);
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.stats-card__header i {
    color: #2563eb;
    font-size: 18px;
}

.stats-card__header span {
    font-weight: 700;
    font-size: 14px;
    color: var(--fg-1);
}

.stats-card__body {
    padding: 20px;
}

.stat-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid var(--br);
}

.stat-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.stat-row:first-child {
    padding-top: 0;
}

.stat-row__label {
    font-size: 13px;
    color: var(--fg-3);
    font-weight: 500;
}

.stat-row__value {
    font-size: 14px;
    font-weight: 700;
    color: var(--fg-1);
}

/* Danger Card */
.danger-card {
    background: var(--panel);
    border: 1px solid rgba(239, 68, 68, 0.3);
    border-radius: 16px;
    overflow: hidden;
}

.danger-card__header {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(239, 68, 68, 0.05) 100%);
    border-bottom: 1px solid rgba(239, 68, 68, 0.2);
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.danger-card__header i {
    color: #dc2626;
    font-size: 18px;
}

.danger-card__header span {
    font-weight: 700;
    font-size: 14px;
    color: #dc2626;
}

.danger-card__body {
    padding: 20px;
}

.danger-card__body p {
    font-size: 13px;
    color: var(--fg-3);
    margin-bottom: 16px;
}

.btn-danger-custom {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 12px 20px;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.btn-danger-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(220, 38, 38, 0.4);
}

/* Action Buttons */
.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    background: var(--grid);
    border: 1px solid var(--br);
    border-radius: 16px;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    background: var(--panel);
    color: var(--fg-2);
    border: 2px solid var(--br);
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
}

.btn-back:hover {
    border-color: var(--fg-3);
    color: var(--fg-1);
}

.btn-submit {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 28px;
    background: linear-gradient(135deg, var(--accent) 0%, #b8001a 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 4px 12px rgba(214, 0, 28, 0.3);
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(214, 0, 28, 0.4);
}

/* Status Badge */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status-badge.active {
    background: rgba(34, 197, 94, 0.1);
    color: #16a34a;
}

.status-badge.inactive {
    background: rgba(107, 114, 128, 0.1);
    color: #6b7280;
}
</style>
@endpush

@section('content')
<!-- Page Intro -->
<div class="page-intro">
    <div class="page-intro__icon">
        <i class="bi bi-pencil-square"></i>
    </div>
    <div class="page-intro__content">
        <h2>Редактирование вакансии</h2>
        <p>Измените параметры вакансии. После сохранения AI продолжит анализировать кандидатов с учётом новых требований.</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <form action="{{ route('admin.vacancies.update', $vacancy) }}" method="POST" id="vacancyForm">
            @csrf
            @method('PUT')

            <!-- Basic Info Section -->
            <div class="form-section">
                <div class="form-section__header">
                    <div class="form-section__icon red">
                        <i class="bi bi-info-circle-fill"></i>
                    </div>
                    <h3 class="form-section__title">Основная информация</h3>
                </div>
                <div class="form-section__body">
                    <div class="mb-4">
                        <label for="title" class="form-label">
                            Название вакансии <span class="required">*</span>
                        </label>
                        <div class="input-with-icon">
                            <span class="input-with-icon__icon"><i class="bi bi-briefcase"></i></span>
                            <input type="text"
                                   class="form-control @error('title') is-invalid @enderror"
                                   id="title"
                                   name="title"
                                   value="{{ old('title', $vacancy->title) }}"
                                   placeholder="Например: Senior Java Developer"
                                   required>
                        </div>
                        @error('title')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-0">
                        <label for="description" class="form-label">
                            Описание вакансии <span class="required">*</span>
                        </label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description"
                                  name="description"
                                  rows="6"
                                  placeholder="Опишите обязанности, требования, условия работы..."
                                  required>{{ old('description', $vacancy->description) }}</textarea>
                        <div class="form-text">Подробное описание поможет AI лучше оценить соответствие кандидатов</div>
                        @error('description')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Employment Details Section -->
            <div class="form-section">
                <div class="form-section__header">
                    <div class="form-section__icon blue">
                        <i class="bi bi-building"></i>
                    </div>
                    <h3 class="form-section__title">Условия работы</h3>
                </div>
                <div class="form-section__body">
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label for="employment_type" class="form-label">
                                Тип занятости <span class="required">*</span>
                            </label>
                            <select class="form-select @error('employment_type') is-invalid @enderror"
                                    id="employment_type"
                                    name="employment_type"
                                    required>
                                @foreach($employmentTypes as $type)
                                    <option value="{{ $type->value }}"
                                            {{ old('employment_type', $vacancy->employment_type->value) == $type->value ? 'selected' : '' }}>
                                        {{ $type->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('employment_type')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="location" class="form-label">
                                <i class="bi bi-geo-alt me-1"></i> Локация
                            </label>
                            <input type="text"
                                   class="form-control @error('location') is-invalid @enderror"
                                   id="location"
                                   name="location"
                                   value="{{ old('location', $vacancy->location) }}"
                                   placeholder="Ташкент">
                            @error('location')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label for="min_experience_years" class="form-label">
                                <i class="bi bi-clock-history me-1"></i> Мин. опыт (лет)
                            </label>
                            <input type="number"
                                   class="form-control @error('min_experience_years') is-invalid @enderror"
                                   id="min_experience_years"
                                   name="min_experience_years"
                                   value="{{ old('min_experience_years', $vacancy->min_experience_years) }}"
                                   min="0"
                                   max="50"
                                   step="0.5"
                                   placeholder="0">
                            @error('min_experience_years')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3 mb-md-0">
                            <label for="salary_min" class="form-label">
                                <i class="bi bi-cash me-1"></i> Зарплата от
                            </label>
                            <input type="number"
                                   class="form-control @error('salary_min') is-invalid @enderror"
                                   id="salary_min"
                                   name="salary_min"
                                   value="{{ old('salary_min', $vacancy->salary_min) }}"
                                   min="0"
                                   placeholder="5 000 000">
                            @error('salary_min')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="salary_max" class="form-label">
                                <i class="bi bi-cash-stack me-1"></i> Зарплата до
                            </label>
                            <input type="number"
                                   class="form-control @error('salary_max') is-invalid @enderror"
                                   id="salary_max"
                                   name="salary_max"
                                   value="{{ old('salary_max', $vacancy->salary_max) }}"
                                   min="0"
                                   placeholder="15 000 000">
                            @error('salary_max')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="form-text" style="margin-top: -12px;">Зарплата указывается в сумах</div>
                </div>
            </div>

            <!-- Skills Section -->
            <div class="form-section">
                <div class="form-section__header">
                    <div class="form-section__icon green">
                        <i class="bi bi-tools"></i>
                    </div>
                    <h3 class="form-section__title">Требуемые навыки</h3>
                </div>
                <div class="form-section__body">
                    <!-- Must-have Skills -->
                    <div class="mb-4">
                        <label class="form-label">
                            <span style="color: #dc2626;"><i class="bi bi-check-circle-fill me-1"></i></span>
                            Обязательные навыки (must-have)
                        </label>
                        <div class="skills-tags" id="mustHaveTags"></div>
                        <div class="skills-dropdown" id="mustHaveDropdown">
                            <div class="skills-dropdown__input">
                                <input type="text"
                                       class="form-control @error('must_have_skills') is-invalid @enderror"
                                       id="must_have_skills_search"
                                       placeholder="Поиск навыков..."
                                       autocomplete="off">
                                <span class="skills-dropdown__toggle" id="mustHaveToggle">
                                    <i class="bi bi-chevron-down"></i>
                                </span>
                            </div>
                            <div class="skills-dropdown__menu" id="mustHaveMenu">
                                <span class="skills-dropdown__label">Доступные навыки</span>
                                <div class="skills-dropdown__options" id="mustHaveOptions"></div>
                            </div>
                        </div>
                        <input type="hidden" id="must_have_skills" name="must_have_skills_input"
                               value="{{ old('must_have_skills_input', implode(', ', $vacancy->must_have_skills ?? [])) }}">
                        <div class="form-text">Кандидат должен владеть этими навыками. Влияют на match score.</div>
                        @error('must_have_skills')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Nice-to-have Skills -->
                    <div class="mb-0">
                        <label class="form-label">
                            <span style="color: #d97706;"><i class="bi bi-star-fill me-1"></i></span>
                            Желательные навыки (nice-to-have)
                        </label>
                        <div class="skills-tags" id="niceToHaveTags"></div>
                        <div class="skills-dropdown" id="niceToHaveDropdown">
                            <div class="skills-dropdown__input">
                                <input type="text"
                                       class="form-control @error('nice_to_have_skills') is-invalid @enderror"
                                       id="nice_to_have_skills_search"
                                       placeholder="Поиск навыков..."
                                       autocomplete="off">
                                <span class="skills-dropdown__toggle" id="niceToHaveToggle">
                                    <i class="bi bi-chevron-down"></i>
                                </span>
                            </div>
                            <div class="skills-dropdown__menu" id="niceToHaveMenu">
                                <span class="skills-dropdown__label">Доступные навыки</span>
                                <div class="skills-dropdown__options" id="niceToHaveOptions"></div>
                            </div>
                        </div>
                        <input type="hidden" id="nice_to_have_skills" name="nice_to_have_skills_input"
                               value="{{ old('nice_to_have_skills_input', implode(', ', $vacancy->nice_to_have_skills ?? [])) }}">
                        <div class="form-text">Желательные, но не обязательные навыки</div>
                        @error('nice_to_have_skills')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Publication Section -->
            <div class="form-section">
                <div class="form-section__header">
                    <div class="form-section__icon purple">
                        <i class="bi bi-megaphone-fill"></i>
                    </div>
                    <h3 class="form-section__title">Публикация</h3>
                </div>
                <div class="form-section__body">
                    <label class="form-switch-custom">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox"
                               id="is_active"
                               name="is_active"
                               value="1"
                               {{ old('is_active', $vacancy->is_active) ? 'checked' : '' }}>
                        <span class="form-switch-custom__toggle"></span>
                        <span class="form-switch-custom__label">
                            <span class="form-switch-custom__title">Вакансия активна</span>
                            <span class="form-switch-custom__desc">Вакансия видна кандидатам на сайте</span>
                        </span>
                    </label>
                </div>
            </div>

            <!-- Actions -->
            <div class="form-actions">
                <a href="{{ route('admin.vacancies.index') }}" class="btn-back">
                    <i class="bi bi-arrow-left"></i>
                    <span>Назад к списку</span>
                </a>
                <button type="submit" class="btn-submit">
                    <i class="bi bi-check-lg"></i>
                    <span>Сохранить изменения</span>
                </button>
            </div>
        </form>
    </div>

    <div class="col-lg-4">
        <!-- Stats Card -->
        <div class="stats-card">
            <div class="stats-card__header">
                <i class="bi bi-bar-chart-fill"></i>
                <span>Статистика вакансии</span>
            </div>
            <div class="stats-card__body">
                <div class="stat-row">
                    <span class="stat-row__label">Статус</span>
                    <span class="status-badge {{ $vacancy->is_active ? 'active' : 'inactive' }}">
                        <i class="bi bi-{{ $vacancy->is_active ? 'check-circle-fill' : 'pause-circle-fill' }}"></i>
                        {{ $vacancy->is_active ? 'Активна' : 'Неактивна' }}
                    </span>
                </div>
                <div class="stat-row">
                    <span class="stat-row__label">Всего заявок</span>
                    <span class="stat-row__value">{{ $vacancy->applications_count }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-row__label">Создано</span>
                    <span class="stat-row__value">{{ $vacancy->created_at->format('d.m.Y') }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-row__label">Автор</span>
                    <span class="stat-row__value">{{ $vacancy->creator?->name ?? 'Система' }}</span>
                </div>
            </div>
        </div>

        <!-- Danger Zone -->
        <div class="danger-card">
            <div class="danger-card__header">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>Опасная зона</span>
            </div>
            <div class="danger-card__body">
                @if($vacancy->applications()->exists())
                    <p style="margin: 0;">
                        <i class="bi bi-lock-fill me-1"></i>
                        Нельзя удалить вакансию, на которую есть заявки ({{ $vacancy->applications_count }}).
                    </p>
                @else
                    <p>Удаление вакансии необратимо. Все данные будут потеряны.</p>
                    <form action="{{ route('admin.vacancies.destroy', $vacancy) }}" method="POST"
                          onsubmit="return confirm('Вы уверены, что хотите удалить эту вакансию? Это действие нельзя отменить.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger-custom">
                            <i class="bi bi-trash-fill"></i>
                            <span>Удалить вакансию</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Recommended skills database grouped by category
    const skillGroups = {
        'Языки программирования': ['JavaScript', 'TypeScript', 'Python', 'Java', 'PHP', 'C#', 'C++', 'Go', 'Rust', 'Ruby', 'Swift', 'Kotlin'],
        'Frontend': ['React', 'Vue.js', 'Angular', 'Next.js', 'Nuxt.js', 'HTML5', 'CSS3', 'SASS', 'LESS', 'Tailwind CSS', 'Bootstrap'],
        'Backend': ['Node.js', 'Express.js', 'Django', 'Flask', 'Laravel', 'Spring Boot', 'ASP.NET', 'FastAPI', 'NestJS', 'Ruby on Rails'],
        'Базы данных': ['MySQL', 'PostgreSQL', 'MongoDB', 'Redis', 'Elasticsearch', 'Oracle', 'SQL Server', 'SQLite', 'Cassandra', 'SQL'],
        'DevOps и облако': ['Docker', 'Kubernetes', 'AWS', 'Azure', 'GCP', 'CI/CD', 'Jenkins', 'GitLab CI', 'GitHub Actions', 'Terraform', 'Ansible'],
        'Инструменты': ['Git', 'Linux', 'Nginx', 'REST API', 'GraphQL', 'Microservices', 'RabbitMQ', 'Kafka', 'WebSocket'],
        'Мобильная разработка': ['React Native', 'Flutter', 'iOS', 'Android', 'SwiftUI', 'Jetpack Compose'],
        'Тестирование': ['Jest', 'Mocha', 'Cypress', 'Selenium', 'JUnit', 'PHPUnit', 'pytest'],
        'Дизайн и аналитика': ['Figma', 'Adobe XD', 'Photoshop', 'Google Analytics', 'Power BI', 'Tableau'],
        'Языки': ['Английский язык', 'Русский язык', 'Узбекский язык'],
        'Управление': ['Agile', 'Scrum', 'Kanban', 'Jira', 'Confluence', 'Team Lead', 'Project Management'],
        'Финансы': ['1C', 'SAP', 'Финансовый анализ', 'Бухгалтерский учёт', 'Excel', 'VBA'],
    };
    const recommendedSkills = Object.values(skillGroups).flat();

    const dropdownInstances = {};

    function initSkillsDropdown(config) {
        const { hiddenInputId, searchInputId, tagsContainerId, toggleId, menuId, optionsContainerId, tagClass, excludeFromId } = config;
        const hiddenInput = document.getElementById(hiddenInputId);
        const searchInput = document.getElementById(searchInputId);
        const tagsContainer = document.getElementById(tagsContainerId);
        const toggle = document.getElementById(toggleId);
        const menu = document.getElementById(menuId);
        const optionsContainer = document.getElementById(optionsContainerId);
        let skills = [];
        let isOpen = false;

        dropdownInstances[hiddenInputId] = { getSkills: () => skills };

        if (hiddenInput.value.trim()) {
            skills = hiddenInput.value.split(',').map(s => s.trim()).filter(s => s);
            renderTags();
        }

        function getExcludedSkills() {
            if (excludeFromId && dropdownInstances[excludeFromId]) {
                return dropdownInstances[excludeFromId].getSkills();
            }
            return [];
        }

        function renderOptions(filter = '') {
            const filterLower = filter.toLowerCase();
            const excluded = getExcludedSkills();
            let html = '';
            let totalCount = 0;

            if (filter.trim()) {
                const filtered = recommendedSkills.filter(skill =>
                    skill.toLowerCase().includes(filterLower) && !skills.includes(skill) && !excluded.includes(skill)
                );
                totalCount = filtered.length;
                if (filtered.length === 0) {
                    html = `<div class="skill-option" data-skill="${filter}"><i class="bi bi-plus-circle me-1"></i> Добавить "${filter}"</div>`;
                } else {
                    html = filtered.map(skill => `<div class="skill-option" data-skill="${skill}">${skill}</div>`).join('');
                }
            } else {
                for (const [group, groupSkills] of Object.entries(skillGroups)) {
                    const available = groupSkills.filter(skill => !skills.includes(skill) && !excluded.includes(skill));
                    if (available.length === 0) continue;
                    totalCount += available.length;
                    html += `<div class="skills-dropdown__group-title">${group}</div>`;
                    html += available.map(skill => `<div class="skill-option" data-skill="${skill}">${skill}</div>`).join('');
                }
                if (!html) {
                    html = '<div class="skills-dropdown__empty">Все навыки уже добавлены</div>';
                }
            }

            // Update label with count
            const label = menu.querySelector('.skills-dropdown__label');
            if (label) {
                label.innerHTML = `Доступные навыки <span class="skills-dropdown__count">${totalCount}</span>`;
            }

            optionsContainer.innerHTML = html;
            if (totalCount > 0 && !filter.trim()) {
                optionsContainer.insertAdjacentHTML('afterend',
                    optionsContainer.nextElementSibling?.classList.contains('skills-dropdown__hint') ? '' :
                    '<div class="skills-dropdown__hint">Введите текст для поиска или нажмите Enter для добавления нового навыка</div>'
                );
            }
            optionsContainer.querySelectorAll('.skill-option').forEach(opt => {
                opt.addEventListener('click', function() {
                    const skill = this.dataset.skill;
                    if (!skills.includes(skill)) { skills.push(skill); updateHiddenInput(); renderTags(); renderOptions(searchInput.value); }
                    searchInput.value = ''; searchInput.focus();
                });
            });
        }

        function renderTags() {
            tagsContainer.innerHTML = skills.map(skill => `<span class="skill-tag ${tagClass}">${skill}<span class="skill-tag__remove" data-skill="${skill}"><i class="bi bi-x"></i></span></span>`).join('');
            tagsContainer.querySelectorAll('.skill-tag__remove').forEach(btn => { btn.addEventListener('click', function() { removeSkill(this.dataset.skill); }); });
        }

        function removeSkill(skill) { skills = skills.filter(s => s !== skill); updateHiddenInput(); renderTags(); renderOptions(searchInput.value); }
        function updateHiddenInput() { hiddenInput.value = skills.join(', '); }
        function openMenu() { isOpen = true; menu.classList.add('show'); toggle.classList.add('open'); renderOptions(searchInput.value); }
        function closeMenu() { isOpen = false; menu.classList.remove('show'); toggle.classList.remove('open'); }

        searchInput.addEventListener('focus', openMenu);
        searchInput.addEventListener('input', () => renderOptions(searchInput.value));
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); const value = searchInput.value.trim(); if (value && !skills.includes(value)) { skills.push(value); updateHiddenInput(); renderTags(); searchInput.value = ''; renderOptions(''); } }
            if (e.key === 'Escape') { closeMenu(); searchInput.blur(); }
        });
        toggle.addEventListener('click', function(e) { e.stopPropagation(); if (isOpen) { closeMenu(); } else { openMenu(); searchInput.focus(); } });
        document.addEventListener('click', function(e) { if (!menu.contains(e.target) && !searchInput.contains(e.target) && !toggle.contains(e.target)) { closeMenu(); } });
        renderOptions();
    }

    initSkillsDropdown({ hiddenInputId: 'must_have_skills', searchInputId: 'must_have_skills_search', tagsContainerId: 'mustHaveTags', toggleId: 'mustHaveToggle', menuId: 'mustHaveMenu', optionsContainerId: 'mustHaveOptions', tagClass: 'must-have', excludeFromId: 'nice_to_have_skills' });
    initSkillsDropdown({ hiddenInputId: 'nice_to_have_skills', searchInputId: 'nice_to_have_skills_search', tagsContainerId: 'niceToHaveTags', toggleId: 'niceToHaveToggle', menuId: 'niceToHaveMenu', optionsContainerId: 'niceToHaveOptions', tagClass: 'nice-to-have', excludeFromId: 'must_have_skills' });

    document.getElementById('vacancyForm').addEventListener('submit', function(e) {
        const mustHave = document.getElementById('must_have_skills').value;
        const niceToHave = document.getElementById('nice_to_have_skills').value;
        const createArrayInputs = (name, value) => { value.split(',').map(s => s.trim()).filter(s => s).forEach(item => { const input = document.createElement('input'); input.type = 'hidden'; input.name = name + '[]'; input.value = item; this.appendChild(input); }); };
        if (mustHave) createArrayInputs('must_have_skills', mustHave);
        if (niceToHave) createArrayInputs('nice_to_have_skills', niceToHave);
    });
});
</script>
@endpush
