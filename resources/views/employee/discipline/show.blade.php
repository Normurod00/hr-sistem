@extends('employee.layouts.app')

@section('title', $action->title)

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('employee.discipline.index') }}" class="text-muted text-decoration-none mb-2 d-inline-block">
                <i class="bi bi-arrow-left me-1"></i>Орқага
            </a>
            <h4 class="mb-1">
                <i class="bi bi-file-earmark-text me-2 text-primary"></i>{{ $action->title }}
            </h4>
        </div>
        <div>
            <span class="badge bg-{{ $action->type_color }} me-2">{{ $action->type_label }}</span>
            <span class="badge bg-{{ $action->status_color }}">{{ $action->status_label }}</span>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Description Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Тафсилотлар</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Тавсиф</h6>
                        <p class="mb-0">{{ $action->description }}</p>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Сабаб</h6>
                        <p class="mb-0">{{ $action->reason }}</p>
                    </div>

                    @if($action->fine_amount)
                        <div class="alert alert-danger">
                            <i class="bi bi-currency-exchange me-2"></i>
                            <strong>Жарима миқдори:</strong>
                            {{ number_format($action->fine_amount, 0, '.', ' ') }} {{ $action->fine_currency }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Appeal Section -->
            @if($action->appeal_text)
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-chat-left-text me-2"></i>Шикоят</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted">
                                Юборилган: {{ $action->appealed_at->format('d.m.Y H:i') }}
                            </small>
                        </div>
                        <p class="mb-3">{{ $action->appeal_text }}</p>

                        @if($action->appeal_status)
                            <div class="alert alert-{{ $action->appeal_status === 'approved' ? 'success' : ($action->appeal_status === 'rejected' ? 'danger' : 'info') }}">
                                <strong>Ҳолат:</strong>
                                @switch($action->appeal_status)
                                    @case('pending')
                                        Кўриб чиқилмоқда
                                        @break
                                    @case('approved')
                                        Қабул қилинди
                                        @break
                                    @case('rejected')
                                        Рад этилди
                                        @break
                                @endswitch
                            </div>
                        @endif

                        @if($action->appeal_resolution)
                            <div class="mt-3">
                                <h6 class="text-muted mb-2">Қарор</h6>
                                <p class="mb-0">{{ $action->appeal_resolution }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Acknowledgement Section -->
            @if(!$action->employee_acknowledged)
                <div class="card shadow-sm border-warning">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">Танишганлигингизни тасдиқланг</h5>
                        <p class="text-muted mb-4">
                            Ушбу интизомий чора билан танишганлигингизни тасдиқлашингиз керак.
                        </p>
                        <button class="btn btn-warning btn-lg" onclick="acknowledgeAction()">
                            <i class="bi bi-check-lg me-2"></i>Танишдим
                        </button>
                    </div>
                </div>
            @else
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    Сиз ушбу чора билан танишганлигингизни тасдиқладингиз
                    <small class="d-block mt-1">{{ $action->acknowledged_at->format('d.m.Y H:i') }}</small>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Info Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-calendar me-2"></i>Саналар</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Ҳодиса санаси:</td>
                            <td class="text-end fw-medium">{{ $action->incident_date->format('d.m.Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Чора санаси:</td>
                            <td class="text-end fw-medium">{{ $action->action_date->format('d.m.Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Кучга киради:</td>
                            <td class="text-end fw-medium">{{ $action->effective_from->format('d.m.Y') }}</td>
                        </tr>
                        @if($action->effective_until)
                            <tr>
                                <td class="text-muted">Муддати:</td>
                                <td class="text-end fw-medium">{{ $action->effective_until->format('d.m.Y') }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Details Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-list-ul me-2"></i>Қўшимча маълумотлар</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Категория:</td>
                            <td class="text-end">
                                @switch($action->category)
                                    @case('attendance')
                                        Давомат
                                        @break
                                    @case('performance')
                                        Иш сифати
                                        @break
                                    @case('conduct')
                                        Хулқ-атвор
                                        @break
                                    @case('policy_violation')
                                        Сиёсат бузиш
                                        @break
                                    @default
                                        {{ $action->category }}
                                @endswitch
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Оғирлик даражаси:</td>
                            <td class="text-end">{{ $action->severity_label }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Яратган:</td>
                            <td class="text-end">{{ $action->createdBy->name }}</td>
                        </tr>
                        @if($action->approvedBy)
                            <tr>
                                <td class="text-muted">Тасдиқлаган:</td>
                                <td class="text-end">{{ $action->approvedBy->name }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Appeal Card -->
            @if($action->can_still_appeal)
                <div class="card shadow-sm border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0"><i class="bi bi-chat-left-text me-2"></i>Шикоят қилиш</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            Ушбу чорага қарши шикоят қилишингиз мумкин.
                            @if($action->appeal_deadline)
                                <br>
                                <strong>Муддат:</strong> {{ $action->appeal_deadline->format('d.m.Y') }} гача
                            @endif
                        </p>
                        <button class="btn btn-warning w-100" onclick="showAppealModal()">
                            <i class="bi bi-send me-1"></i>Шикоят юбориш
                        </button>
                    </div>
                </div>
            @endif
        </div>
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
    // Char count
    const appealText = document.getElementById('appealText');
    if (appealText) {
        appealText.addEventListener('input', function() {
            document.getElementById('charCount').textContent = this.value.length;
        });
    }

    // Appeal form
    const appealForm = document.getElementById('appealForm');
    if (appealForm) {
        appealForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const text = document.getElementById('appealText').value;

            try {
                const response = await fetch('{{ route("employee.discipline.appeal", $action) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ appeal_text: text })
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
    }
});

function showAppealModal() {
    document.getElementById('appealText').value = '';
    document.getElementById('charCount').textContent = '0';
    new bootstrap.Modal(document.getElementById('appealModal')).show();
}

async function acknowledgeAction() {
    if (!confirm('Танишганлигингизни тасдиқлайсизми?')) {
        return;
    }

    try {
        const response = await fetch('{{ route("employee.discipline.acknowledge", $action) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
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
}
</script>
@endpush
@endsection
