@extends('employee.layouts.app')

@section('title', 'Дисциплинарные меры')
@section('page-title', 'Дисциплинарные меры')

@section('content')
<style>
    .disc-stats { display:flex; gap:16px; margin-bottom:24px; flex-wrap:wrap; }
    .disc-stat { flex:1; min-width:140px; padding:18px 20px; background:var(--panel); border:1px solid var(--br); border-radius:14px; display:flex; align-items:center; gap:14px; }
    .disc-stat .icon { width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0; }
    .disc-stat .info .value { font-size:22px; font-weight:800; color:var(--fg-1); }
    .disc-stat .info .label { font-size:12px; color:var(--fg-3); }

    .disc-card { display:flex; align-items:flex-start; gap:16px; padding:18px 20px; background:var(--panel); border:1px solid var(--br); border-radius:12px; margin-bottom:10px; transition:all 0.2s; }
    .disc-card:hover { border-color:var(--accent); box-shadow:0 2px 12px rgba(0,0,0,0.04); }
    .disc-card .type-icon { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; }
    .disc-card .info { flex:1; min-width:0; }
    .disc-card .title { font-weight:600; font-size:14px; color:var(--fg-1); margin-bottom:4px; }
    .disc-card .desc { font-size:13px; color:var(--fg-3); margin-bottom:6px; }
    .disc-card .meta { font-size:12px; color:var(--fg-3); display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
    .disc-card .actions { flex-shrink:0; display:flex; gap:6px; }
    .disc-card .actions a, .disc-card .actions button { padding:6px 12px; border-radius:8px; border:1px solid var(--br); background:transparent; color:var(--fg-3); font-size:12px; cursor:pointer; text-decoration:none; transition:all 0.2s; }
    .disc-card .actions a:hover, .disc-card .actions button:hover { border-color:var(--accent); color:var(--accent); }
    .tag { padding:3px 10px; border-radius:6px; font-size:11px; font-weight:600; }
</style>

{{-- Statistics --}}
<div class="disc-stats">
    <div class="disc-stat">
        <div class="icon" style="background:rgba(59,130,246,0.1);color:#3B82F6;"><i class="fa-solid fa-file-lines"></i></div>
        <div class="info"><div class="value">{{ $statistics['total'] }}</div><div class="label">Всего</div></div>
    </div>
    <div class="disc-stat">
        <div class="icon" style="background:rgba(239,68,68,0.1);color:#ef4444;"><i class="fa-solid fa-circle-exclamation"></i></div>
        <div class="info"><div class="value">{{ $statistics['active'] }}</div><div class="label">Активные</div></div>
    </div>
    <div class="disc-stat">
        <div class="icon" style="background:rgba(245,158,11,0.1);color:#f59e0b;"><i class="fa-solid fa-gavel"></i></div>
        <div class="info"><div class="value">{{ $statistics['appealed'] }}</div><div class="label">Обжалованные</div></div>
    </div>
    <div class="disc-stat">
        <div class="icon" style="background:rgba(34,197,94,0.1);color:#22c55e;"><i class="fa-solid fa-circle-check"></i></div>
        <div class="info"><div class="value">{{ $statistics['revoked'] }}</div><div class="label">Отменённые</div></div>
    </div>
</div>

{{-- Filter --}}
<div style="display:flex; gap:10px; margin-bottom:20px;">
    <select class="form-select" id="statusFilter" style="width:auto; padding:9px 14px; border:1px solid var(--br); border-radius:10px; font-size:13px;">
        <option value="">Все статусы</option>
        <option value="active" @selected(request('status') === 'active')>Активные</option>
        <option value="appealed" @selected(request('status') === 'appealed')>Обжалованные</option>
        <option value="revoked" @selected(request('status') === 'revoked')>Отменённые</option>
    </select>
</div>

{{-- List --}}
@forelse($actions as $action)
    @php
        $typeColors = ['warning'=>'#f59e0b','reprimand'=>'#ef4444','fine'=>'#E52716','suspension'=>'#8B5CF6','termination'=>'#1e1e1e'];
        $typeIcons = ['warning'=>'fa-triangle-exclamation','reprimand'=>'fa-ban','fine'=>'fa-money-bill','suspension'=>'fa-pause-circle','termination'=>'fa-user-slash'];
        $tc = $typeColors[$action->type] ?? '#6B7280';
        $ti = $typeIcons[$action->type] ?? 'fa-file';
    @endphp
    <div class="disc-card">
        <div class="type-icon" style="background:{{ $tc }}15;color:{{ $tc }};">
            <i class="fa-solid {{ $ti }}"></i>
        </div>
        <div class="info">
            <div style="display:flex; gap:6px; align-items:center; margin-bottom:6px; flex-wrap:wrap;">
                <span class="tag" style="background:{{ $tc }}15;color:{{ $tc }};">{{ $action->type_label }}</span>
                <span class="tag" style="background:rgba(107,114,128,0.1);color:var(--fg-3);">{{ $action->status_label }}</span>
                @if(!$action->employee_acknowledged)
                    <span class="tag" style="background:rgba(245,158,11,0.15);color:#f59e0b;">Не ознакомлен</span>
                @endif
            </div>
            <div class="title">{{ $action->title }}</div>
            <div class="desc">{{ Str::limit($action->description, 150) }}</div>
            <div class="meta">
                <span><i class="fa-solid fa-calendar me-1"></i>{{ $action->incident_date->format('d.m.Y') }}</span>
                <span>·</span>
                <span><i class="fa-solid fa-user me-1"></i>{{ $action->createdBy->name }}</span>
            </div>
        </div>
        <div class="actions">
            <a href="{{ route('employee.discipline.show', $action) }}"><i class="fa-solid fa-eye me-1"></i>Просмотр</a>
            @if($action->can_still_appeal)
                <button onclick="showAppealModal({{ $action->id }})"><i class="fa-solid fa-gavel me-1"></i>Жалоба</button>
            @endif
        </div>
    </div>
@empty
    <div style="text-align:center; padding:60px 20px;">
        <div style="width:80px;height:80px;border-radius:50%;background:rgba(34,197,94,0.08);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
            <i class="fa-solid fa-circle-check" style="font-size:32px;color:#22c55e;"></i>
        </div>
        <h5 style="color:var(--fg-1);">Всё чисто</h5>
        <p style="color:var(--fg-3);font-size:14px;">У вас нет дисциплинарных мер</p>
    </div>
@endforelse

@if($actions->hasPages())
    <div style="margin-top:16px;">{{ $actions->links() }}</div>
@endif

<!-- Appeal Modal -->
<div class="modal fade" id="appealModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header">
                <h5 class="modal-title">Подать жалобу</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="appealForm">
                <div class="modal-body">
                    <input type="hidden" id="appealActionId">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-1"></i>
                        Опишите вашу жалобу подробно. Минимум 50 символов.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Текст жалобы</label>
                        <textarea class="form-control" id="appealText" rows="5"
                                  minlength="50" maxlength="5000" required
                                  placeholder="Опишите вашу жалобу подробно..."></textarea>
                        <div class="form-text">
                            <span id="charCount">0</span>/5000
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-send me-1"></i>Отправить
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fix modal scroll lock
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('hidden.bs.modal', function() {
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        });
    });

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
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ appeal_text: appealText })
            });

            if (response.status === 419) { alert('Сессия истекла. Обновите страницу.'); return; }
            const data = await response.json();

            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert(data.error || 'Произошла ошибка');
            }
        } catch (error) {
            console.error('Appeal error:', error);
            alert(navigator.onLine ? 'Ошибка сервера. Попробуйте позже.' : 'Нет подключения к интернету.');
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
