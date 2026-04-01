@extends('layouts.admin')

@section('title', 'Типы номинаций')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <i class="bi bi-tags-fill text-info me-2"></i>
        Типы номинаций
    </h4>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTypeModal">
        <i class="bi bi-plus-lg me-1"></i>Добавить тип
    </button>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Иконка</th>
                        <th>Название</th>
                        <th>Описание</th>
                        <th class="text-center">Баллы</th>
                        <th>Статус</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($types as $type)
                    <tr>
                        <td>
                            <span style="color: {{ $type->color ?? '#666' }};">
                                <i class="bi {{ $type->icon ?? 'bi-star' }} fs-5"></i>
                            </span>
                        </td>
                        <td class="fw-semibold">{{ $type->name }}</td>
                        <td class="text-muted small">{{ Str::limit($type->description, 60) }}</td>
                        <td class="text-center">
                            <span class="badge bg-success">+{{ $type->points_reward }}</span>
                        </td>
                        <td>
                            @if($type->is_active)
                                <span class="badge bg-success">Активен</span>
                            @else
                                <span class="badge bg-secondary">Отключён</span>
                            @endif
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal" data-bs-target="#editTypeModal{{ $type->id }}">
                                <i class="bi bi-pencil"></i>
                            </button>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editTypeModal{{ $type->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('admin.recognition.update-nomination-type', $type) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title">Редактировать тип</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Название</label>
                                            <input type="text" name="name" class="form-control" value="{{ $type->name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Описание</label>
                                            <textarea name="description" class="form-control" rows="2">{{ $type->description }}</textarea>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Иконка</label>
                                                <input type="text" name="icon" class="form-control" value="{{ $type->icon }}" placeholder="bi-star">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Цвет</label>
                                                <input type="text" name="color" class="form-control" value="{{ $type->color }}" placeholder="#ff6600">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Баллы</label>
                                                <input type="number" name="points_reward" class="form-control" value="{{ $type->points_reward }}" min="0" required>
                                            </div>
                                        </div>
                                        <div class="form-check">
                                            <input type="hidden" name="is_active" value="0">
                                            <input type="checkbox" name="is_active" class="form-check-input" value="1" {{ $type->is_active ? 'checked' : '' }}>
                                            <label class="form-check-label">Активен</label>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                        <button type="submit" class="btn btn-primary">Сохранить</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-tags" style="font-size: 48px; opacity: 0.3;"></i>
                            <div class="mt-2">Типы номинаций не найдены</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.recognition.store-nomination-type') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Новый тип номинации</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Название</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" class="form-control" required placeholder="example-slug">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Описание</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Иконка</label>
                            <input type="text" name="icon" class="form-control" placeholder="bi-star">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Цвет</label>
                            <input type="text" name="color" class="form-control" placeholder="#ff6600">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Баллы</label>
                            <input type="number" name="points_reward" class="form-control" value="10" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Создать</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
