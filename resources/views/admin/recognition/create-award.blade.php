@extends('layouts.admin')

@section('title', 'Выдать награду')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">
                <i class="bi bi-trophy-fill text-warning me-2"></i>
                {{ $awardType->label() }} выдать
            </h4>
            <a href="{{ route('admin.recognition.awards') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Назад
            </a>
        </div>

        <!-- Award Type Tabs -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.recognition.create-award', ['type' => 'employee_of_month']) }}"
                       class="btn {{ $awardType->value === 'employee_of_month' ? 'btn-primary' : 'btn-outline-primary' }}">
                        <i class="bi bi-award-fill me-1"></i>Сотрудник месяца
                    </a>
                    <a href="{{ route('admin.recognition.create-award', ['type' => 'employee_of_quarter']) }}"
                       class="btn {{ $awardType->value === 'employee_of_quarter' ? 'btn-primary' : 'btn-outline-primary' }}">
                        <i class="bi bi-trophy-fill me-1"></i>Сотрудник квартала
                    </a>
                    <a href="{{ route('admin.recognition.create-award', ['type' => 'employee_of_year']) }}"
                       class="btn {{ $awardType->value === 'employee_of_year' ? 'btn-primary' : 'btn-outline-primary' }}">
                        <i class="bi bi-gem me-1"></i>Сотрудник года
                    </a>
                </div>
            </div>
        </div>

        <!-- Candidates -->
        @if($candidates->isNotEmpty())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Кандидаты (по количеству номинаций)</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Сотрудник</th>
                            <th class="text-center">Номинации</th>
                            <th class="text-center">Баллы</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($candidates as $index => $candidate)
                        <tr class="{{ $index === 0 ? 'table-success' : '' }}">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($index === 0)
                                    <span class="badge bg-success">Рекомендация</span>
                                    @endif
                                    <span class="fw-semibold">{{ $candidate['user']->name ?? 'Unknown' }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning text-dark">{{ $candidate['nominations_count'] }}</span>
                            </td>
                            <td class="text-center">{{ number_format($candidate['total_points']) }}</td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-primary select-candidate"
                                        data-user-id="{{ $candidate['user']->id }}"
                                        data-user-name="{{ $candidate['user']->name }}">
                                    Выбрать
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Award Form -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Данные награды</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.recognition.store-award') }}" method="POST">
                    @csrf
                    <input type="hidden" name="award_type" value="{{ $awardType->value }}">

                    <div class="mb-3">
                        <label class="form-label">Сотрудник</label>
                        <select name="user_id" id="user_id" class="form-select" required>
                            <option value="">-- Выберите --</option>
                            @foreach($candidates as $candidate)
                            <option value="{{ $candidate['user']->id }}">
                                {{ $candidate['user']->name }} ({{ $candidate['nominations_count'] }} номинация)
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Тип номинации (необязательно)</label>
                        <select name="nomination_type_id" class="form-select">
                            <option value="">Общий</option>
                            @foreach($nominationTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Комментарий (необязательно)</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="mb-4">
                        <div class="form-check">
                            <input type="checkbox" name="publish_now" id="publish_now" class="form-check-input" value="1" checked>
                            <label for="publish_now" class="form-check-label">Объявить сразу</label>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-trophy-fill me-1"></i>Выдать награду
                        </button>
                        <a href="{{ route('admin.recognition.index') }}" class="btn btn-outline-secondary">Отмена</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.select-candidate').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('user_id').value = this.dataset.userId;
    });
});
</script>
@endpush
@endsection