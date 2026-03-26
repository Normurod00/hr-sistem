<div class="mb-3">
    <label for="title" class="form-label">Название вакансии <span class="text-danger">*</span></label>
    <input type="text"
           class="form-control @error('title') is-invalid @enderror"
           id="title"
           name="title"
           value="{{ old('title', $vacancy->title ?? '') }}"
           placeholder="Например: Senior Java Developer"
           required>
    @error('title')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="description" class="form-label">Описание <span class="text-danger">*</span></label>
    <textarea class="form-control @error('description') is-invalid @enderror"
              id="description"
              name="description"
              rows="8"
              placeholder="Опишите обязанности, требования, условия работы..."
              required>{{ old('description', $vacancy->description ?? '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label for="employment_type" class="form-label">Тип занятости <span class="text-danger">*</span></label>
        <select class="form-select @error('employment_type') is-invalid @enderror"
                id="employment_type"
                name="employment_type"
                required>
            @foreach($employmentTypes as $type)
                <option value="{{ $type->value }}"
                        {{ old('employment_type', $vacancy->employment_type->value ?? '') == $type->value ? 'selected' : '' }}>
                    {{ $type->label() }}
                </option>
            @endforeach
        </select>
        @error('employment_type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="location" class="form-label">Локация</label>
        <input type="text"
               class="form-control @error('location') is-invalid @enderror"
               id="location"
               name="location"
               value="{{ old('location', $vacancy->location ?? '') }}"
               placeholder="Ташкент">
        @error('location')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-4">
        <label for="min_experience_years" class="form-label">Минимальный опыт (лет)</label>
        <input type="number"
               class="form-control @error('min_experience_years') is-invalid @enderror"
               id="min_experience_years"
               name="min_experience_years"
               value="{{ old('min_experience_years', $vacancy->min_experience_years ?? '') }}"
               min="0"
               max="50"
               step="0.5">
        @error('min_experience_years')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="salary_min" class="form-label">Зарплата от (сум)</label>
        <input type="number"
               class="form-control @error('salary_min') is-invalid @enderror"
               id="salary_min"
               name="salary_min"
               value="{{ old('salary_min', $vacancy->salary_min ?? '') }}"
               min="0">
        @error('salary_min')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="salary_max" class="form-label">Зарплата до (сум)</label>
        <input type="number"
               class="form-control @error('salary_max') is-invalid @enderror"
               id="salary_max"
               name="salary_max"
               value="{{ old('salary_max', $vacancy->salary_max ?? '') }}"
               min="0">
        @error('salary_max')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mb-3">
    <label for="must_have_skills" class="form-label">
        <i class="bi bi-check-circle text-danger me-1"></i> Обязательные навыки (must-have)
    </label>
    <input type="text"
           class="form-control @error('must_have_skills') is-invalid @enderror"
           id="must_have_skills"
           name="must_have_skills_input"
           value="{{ old('must_have_skills_input', isset($vacancy) ? implode(', ', $vacancy->must_have_skills ?? []) : '') }}"
           placeholder="Java, Spring Boot, PostgreSQL (через запятую)">
    <div class="form-text">Введите навыки через запятую</div>
    @error('must_have_skills')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="nice_to_have_skills" class="form-label">
        <i class="bi bi-star text-warning me-1"></i> Желательные навыки (nice-to-have)
    </label>
    <input type="text"
           class="form-control @error('nice_to_have_skills') is-invalid @enderror"
           id="nice_to_have_skills"
           name="nice_to_have_skills_input"
           value="{{ old('nice_to_have_skills_input', isset($vacancy) ? implode(', ', $vacancy->nice_to_have_skills ?? []) : '') }}"
           placeholder="Docker, Kubernetes, AWS (через запятую)">
    @error('nice_to_have_skills')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-4">
    <div class="form-check">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox"
               class="form-check-input"
               id="is_active"
               name="is_active"
               value="1"
               {{ old('is_active', $vacancy->is_active ?? true) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_active">
            Опубликовать вакансию (сделать активной)
        </label>
    </div>
</div>

<div class="d-flex justify-content-between">
    <a href="{{ route('admin.vacancies.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Назад
    </a>
    <button type="submit" class="btn btn-brb">
        <i class="bi bi-check me-1"></i> Сохранить
    </button>
</div>

@push('scripts')
<script>
    // Convert comma-separated inputs to arrays
    document.querySelector('form').addEventListener('submit', function(e) {
        const mustHave = document.getElementById('must_have_skills').value;
        const niceToHave = document.getElementById('nice_to_have_skills').value;

        // Create hidden inputs for arrays
        const createArrayInputs = (name, value) => {
            const items = value.split(',').map(s => s.trim()).filter(s => s);
            items.forEach(item => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name + '[]';
                input.value = item;
                this.appendChild(input);
            });
        };

        if (mustHave) createArrayInputs('must_have_skills', mustHave);
        if (niceToHave) createArrayInputs('nice_to_have_skills', niceToHave);
    });
</script>
@endpush
