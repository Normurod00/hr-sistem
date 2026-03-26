@extends('employee.layouts.app')

@section('title', 'Политики и регламенты')
@section('page-title', 'Политики и регламенты')

@section('content')
<div class="row g-4">
    <!-- Sidebar Filters -->
    <div class="col-lg-3">
        <!-- Search -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form action="{{ route('employee.policies.index') }}" method="GET">
                    <div class="input-group">
                        <input type="text" name="q" class="form-control"
                               placeholder="Поиск..." value="{{ $searchQuery }}">
                        <button type="submit" class="btn btn-brb">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Categories -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="mb-0">Категории</h6>
            </div>
            <div class="list-group list-group-flush">
                <a href="{{ route('employee.policies.index') }}"
                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ !$currentCategory ? 'active' : '' }}">
                    Все
                    <span class="badge bg-secondary rounded-pill">
                        {{ collect($categories)->sum('count') }}
                    </span>
                </a>
                @foreach($categories as $cat)
                    <a href="{{ route('employee.policies.index', ['category' => $cat['key']]) }}"
                       class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ $currentCategory === $cat['key'] ? 'active' : '' }}">
                        {{ $cat['label'] }}
                        <span class="badge bg-secondary rounded-pill">{{ $cat['count'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Popular -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="mb-0">Популярные</h6>
            </div>
            <div class="list-group list-group-flush">
                @foreach($popular as $policy)
                    <a href="{{ route('employee.policies.show', $policy) }}"
                       class="list-group-item list-group-item-action">
                        <div class="fw-medium text-truncate">{{ $policy->title }}</div>
                        <small class="text-muted">{{ $policy->view_count }} просмотров</small>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Policies List -->
    <div class="col-lg-9">
        @if($searchQuery)
            <div class="alert alert-info mb-4">
                Результаты поиска по запросу: <strong>{{ $searchQuery }}</strong>
                <a href="{{ route('employee.policies.index') }}" class="ms-2">Сбросить</a>
            </div>
        @endif

        <div class="row g-4">
            @forelse($policies as $policy)
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100 policy-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge bg-light text-dark">{{ $policy->category_label }}</span>
                                <span class="text-muted small">{{ $policy->code }}</span>
                            </div>

                            <h5 class="card-title">
                                <a href="{{ route('employee.policies.show', $policy) }}" class="text-decoration-none text-dark stretched-link">
                                    {{ $policy->title }}
                                </a>
                            </h5>

                            <p class="card-text text-muted small">
                                {{ $policy->excerpt }}
                            </p>

                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <small class="text-muted">
                                    <i class="bi bi-calendar me-1"></i>
                                    {{ $policy->effective_date->format('d.m.Y') }}
                                </small>
                                @if($policy->hasFile())
                                    <span class="badge bg-info-subtle text-info">
                                        <i class="bi bi-file-pdf"></i> PDF
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="bi bi-file-text fs-1 text-muted d-block mb-3"></i>
                        <h5>Политики не найдены</h5>
                        <p class="text-muted">Попробуйте изменить параметры поиска</p>
                    </div>
                </div>
            @endforelse
        </div>

        @if($policies->hasPages())
            <div class="mt-4">
                {{ $policies->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .policy-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .policy-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
    }
</style>
@endpush
