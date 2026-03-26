@extends('employee.layouts.app')

@section('title', $policy->title)
@section('page-title', 'Политика')

@section('content')
<div class="row g-4">
    <!-- Sidebar -->
    <div class="col-lg-3">
        <!-- Policy Info Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <span class="badge bg-light text-dark mb-3">{{ $policy->category_label }}</span>

                <h5 class="mb-3">{{ $policy->title }}</h5>

                <dl class="mb-0">
                    <dt class="text-muted small">Код</dt>
                    <dd class="mb-3">{{ $policy->code }}</dd>

                    <dt class="text-muted small">Версия</dt>
                    <dd class="mb-3">{{ $policy->version ?? '1.0' }}</dd>

                    <dt class="text-muted small">Дата вступления в силу</dt>
                    <dd class="mb-3">{{ $policy->effective_date->format('d.m.Y') }}</dd>

                    @if($policy->expiry_date)
                        <dt class="text-muted small">Срок действия до</dt>
                        <dd class="mb-3">{{ $policy->expiry_date->format('d.m.Y') }}</dd>
                    @endif

                    <dt class="text-muted small">Просмотров</dt>
                    <dd class="mb-0">{{ $policy->view_count }}</dd>
                </dl>
            </div>
        </div>

        <!-- Actions -->
        <div class="d-grid gap-2">
            @if($policy->hasFile())
                <a href="{{ route('employee.policies.download', $policy) }}" class="btn btn-brb">
                    <i class="bi bi-download me-2"></i>
                    Скачать PDF
                </a>
            @endif

            <a href="{{ route('employee.policies.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>
                Все политики
            </a>
        </div>
    </div>

    <!-- Content -->
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $policy->title }}</h5>
                    <span class="text-muted small">{{ $policy->code }}</span>
                </div>
            </div>
            <div class="card-body">
                @if($policy->excerpt)
                    <div class="alert alert-info mb-4">
                        <i class="bi bi-info-circle me-2"></i>
                        {{ $policy->excerpt }}
                    </div>
                @endif

                <div class="policy-content">
                    {!! nl2br(e($policy->content)) !!}
                </div>
            </div>
        </div>

        <!-- Related Policies -->
        @if(isset($related) && $related->count() > 0)
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0">Связанные документы</h6>
                </div>
                <div class="list-group list-group-flush">
                    @foreach($related as $relatedPolicy)
                        <a href="{{ route('employee.policies.show', $relatedPolicy) }}"
                           class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="fw-medium">{{ $relatedPolicy->title }}</div>
                                    <small class="text-muted">{{ $relatedPolicy->code }}</small>
                                </div>
                                <i class="bi bi-chevron-right text-muted"></i>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .policy-content {
        line-height: 1.8;
    }

    .policy-content h1,
    .policy-content h2,
    .policy-content h3 {
        margin-top: 1.5rem;
        margin-bottom: 1rem;
    }
</style>
@endpush
