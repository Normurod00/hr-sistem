@extends('admin.layouts.app')

@section('title', 'Награды')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <i class="bi bi-trophy-fill text-warning me-2"></i>
        Награды
    </h4>
    <a href="{{ route('admin.recognition.create-award') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Выдать награду
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Сотрудник</th>
                        <th>Награда</th>
                        <th>Период</th>
                        <th class="text-center">Номинации</th>
                        <th class="text-center">Балл</th>
                        <th>Статус</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($awards as $award)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $award->user->name ?? 'Unknown' }}</div>
                        </td>
                        <td>
                            <span style="color: {{ $award->award_type->color() }};">
                                <i class="bi {{ $award->award_type->icon() }} me-1"></i>
                                {{ $award->award_type->label() }}
                            </span>
                        </td>
                        <td class="text-muted">{{ $award->period_label }}</td>
                        <td class="text-center">
                            <span class="badge bg-warning text-dark">{{ $award->nominations_count }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success">+{{ $award->points_awarded }}</span>
                        </td>
                        <td>
                            @if($award->is_published)
                            <span class="badge bg-success">Объявлена</span>
                            @else
                            <span class="badge bg-secondary">Скрыта</span>
                            @endif
                        </td>
                        <td>
                            @if($award->is_published)
                            <form action="{{ route('admin.recognition.unpublish-award', $award) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                            </form>
                            @else
                            <form action="{{ route('admin.recognition.publish-award', $award) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-trophy" style="font-size: 48px; opacity: 0.3;"></i>
                            <div class="mt-2">Нет наград</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($awards->hasPages())
    <div class="card-footer bg-white">
        {{ $awards->links() }}
    </div>
    @endif
</div>
@endsection