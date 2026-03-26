@extends('employee.layouts.app')

@section('title', 'Интизомий чоралар')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-file-earmark-text me-2 text-primary"></i>Интизомий чоралар
            </h4>
            <p class="text-muted mb-0">Сизга тегишли интизомий чоралар рўйхати</p>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-6 fw-bold text-primary">{{ $statistics['total'] }}</div>
                    <small class="text-muted">Жами</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-6 fw-bold text-danger">{{ $statistics['active'] }}</div>
                    <small class="text-muted">Фаол</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-6 fw-bold text-warning">{{ $statistics['appealed'] }}</div>
                    <small class="text-muted">Шикоят қилинган</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-6 fw-bold text-success">{{ $statistics['revoked'] }}</div>
                    <small class="text-muted">Бекор қилинган</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions List -->
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Рўйхат</h5>
            <div>
                <select class="form-select form-select-sm" id="statusFilter" style="width: auto;">
                    <option value="">Барчаси</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Фаол</option>
                    <option value="appealed" {{ request('status') === 'appealed' ? 'selected' : '' }}>Шикоят</option>
                    <option value="revoked" {{ request('status') === 'revoked' ? 'selected' : '' }}>Бекор</option>
                </select>
            </div>
        </div>

        @if($actions->count() > 0)
            <div class="list-group list-group-flush">
                @foreach($actions as $action)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge bg-{{ $action->type_color }} me-2">
                                        {{ $action->type_label }}
                                    </span>
                                    <span class="badge bg-{{ $action->status_color }}">
                                        {{ $action->status_label }}
                                    </span>
                                    @if(!$action->employee_acknowledged)
                                        <span class="badge bg-warning ms-2">
                                            <i class="bi bi-exclamation-triangle"></i> Танишилмаган
                                        </span>
                                    @endif
                                </div>
                                <h6 class="mb-1">{{ $action->title }}</h6>
                                <p class="text-muted small mb-2">{{ Str::limit($action->description, 150) }}</p>
                                <div class="small text-muted">
                                    <i class="bi bi-calendar me-1"></i>{{ $action->incident_date->format('d.m.Y') }}
                                    <span class="mx-2">|</span>
                                    <i class="bi bi-person me-1"></i>{{ $action->createdBy->name }}
                                </div>
                            </div>
                            <div class="text-end">
                                <a href="{{ route('employee.discipline.show', $action) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Кўриш
                                </a>
                                @if($action->can_still_appeal)
                                    <button class="btn btn-sm btn-outline-warning ms-1"
                                            onclick="showAppealModal({{ $action->id }})">
                                        <i class="bi bi-chat-left-text"></i> Шикоят
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="card-footer">
                {{ $actions->links() }}
            </div>
        @else
            <div class="card-body text-center py-5">
                <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
                <h5 class="mt-3">Интизомий чоралар йўқ</h5>
                <p class="text-muted">Сизга нисбатан ҳеч қандай интизомий чора қўлланилмаган</p>
            </div>
        @endif
    </div>
</div>

<!-- Appeal Modal -->
<div class="modal fade" id="appealModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Шикоят қилиш</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="appealForm">
                <div class="modal-body">
                    <input type="hidden" id="appealActionId">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-1"></i>
                        Шикоятингизни батафсил ёзинг. Камида 50 та белги бўлиши керак.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Шикоят матни</label>
                        <textarea class="form-control" id="appealText" rows="5"
                                  minlength="50" maxlength="5000" required
                                  placeholder="Шикоятингизни батафсил ёзинг..."></textarea>
                        <div class="form-text">
                            <span id="charCount">0</span>/5000
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Бекор</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-send me-1"></i>Юбориш
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status filter
    document.getElementById('statusFilter').addEventListener('change', function() {
        const url = new URL(window.location.href);
        if (this.value) {
            url.searchParams.set('status', this.value);
        } else {
            url.searchParams.delete('status');
        }
        window.location.href = url.toString();
    });

    // Char count
    document.getElementById('appealText').addEventListener('input', function() {
        document.getElementById('charCount').textContent = this.value.length;
    });

    // Appeal form
    document.getElementById('appealForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const actionId = document.getElementById('appealActionId').value;
        const appealText = document.getElementById('appealText').value;

        try {
            const response = await fetch(`/discipline/${actionId}/appeal`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ appeal_text: appealText })
            });

            const data = await response.json();

            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert(data.error || 'Хатолик юз берди');
            }
        } catch (error) {
            alert('Сервер билан боғланишда хатолик');
        }
    });
});

function showAppealModal(actionId) {
    document.getElementById('appealActionId').value = actionId;
    document.getElementById('appealText').value = '';
    document.getElementById('charCount').textContent = '0';
    new bootstrap.Modal(document.getElementById('appealModal')).show();
}
</script>
@endpush
@endsection
