@extends('employee.layouts.app')

@section('title', 'Номинация қилиш')
@section('page-title', 'Ходимни номинация қилиш')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-star-fill text-warning me-2"></i>
                    Янги номинация
                </h5>
            </div>
            <div class="card-body">
                @if($errors->any())
                <div class="alert alert-danger mb-4">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    {{ $errors->first() }}
                </div>
                @endif

                <form action="{{ route('employee.recognition.store-nomination') }}" method="POST">
                    @csrf

                    <!-- Nomination Type -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Номинация тури</label>
                        <div class="row g-3">
                            @foreach($nominationTypes as $type)
                            <div class="col-md-6">
                                <input type="radio" class="btn-check" name="nomination_type_id"
                                       id="type_{{ $type->id }}" value="{{ $type->id }}"
                                       {{ old('nomination_type_id', request('type')) == $type->id ? 'checked' : '' }}
                                       required>
                                <label class="btn btn-outline-primary w-100 p-3 text-start" for="type_{{ $type->id }}">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-3 p-2" style="background: {{ $type->color }}20;">
                                            <i class="bi {{ $type->icon }}" style="color: {{ $type->color }}; font-size: 20px;"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $type->name }}</div>
                                            <small class="text-muted">
                                                <i class="bi bi-coin me-1"></i>{{ $type->points_reward }} балл
                                            </small>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            @endforeach
                        </div>
                        @error('nomination_type_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Employee Selection -->
                    <div class="mb-4">
                        <label for="nominee_id" class="form-label fw-semibold">Ходимни танланг</label>
                        <select name="nominee_id" id="nominee_id" class="form-select form-select-lg @error('nominee_id') is-invalid @enderror" required>
                            <option value="">-- Ходимни танланг --</option>
                            @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ old('nominee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }}
                                @if($employee->employeeProfile)
                                    - {{ $employee->employeeProfile->department }}
                                @endif
                            </option>
                            @endforeach
                        </select>
                        @error('nominee_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Reason -->
                    <div class="mb-4">
                        <label for="reason" class="form-label fw-semibold">Номинация сабаби</label>
                        <textarea name="reason" id="reason" rows="5"
                                  class="form-control @error('reason') is-invalid @enderror"
                                  placeholder="Ушбу ходимни нима учун номинация қилаётганингизни тушунтиринг..."
                                  required>{{ old('reason') }}</textarea>
                        <div class="form-text">Камида 10 белги. Аниқ ва ишонарли сабаблар номинациянинг тасдиқланиш имкониятини оширади.</div>
                        @error('reason')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-send me-2"></i>
                            Номинация юбориш
                        </button>
                        <a href="{{ route('employee.recognition.index') }}" class="btn btn-outline-secondary btn-lg">
                            Бекор қилиш
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Info Box -->
        <div class="alert alert-info mt-4">
            <div class="d-flex gap-3">
                <i class="bi bi-info-circle-fill fs-4"></i>
                <div>
                    <h6 class="mb-2">Номинация қоидалари</h6>
                    <ul class="mb-0 small">
                        <li>Ҳар бир ходимни бир ойда бир турдаги номинацияда фақат бир марта номинация қилиш мумкин</li>
                        <li>Номинация HR томонидан кўриб чиқилади ва тасдиқланади</li>
                        <li>Номинация қилган ходимга ҳам балл берилади</li>
                        <li>Энг кўп номинация олган ходим "Ой ходими" бўлиши мумкин</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Simple search for employee select
    document.getElementById('nominee_id').addEventListener('focus', function() {
        this.size = 10;
    });
    document.getElementById('nominee_id').addEventListener('blur', function() {
        this.size = 1;
    });
</script>
@endpush
