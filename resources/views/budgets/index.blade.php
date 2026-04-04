@extends('layouts.app')

@section('title', 'Budgets')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Monthly Budgets</h4>
        <p class="text-muted mb-0">Set spending limits per category and track your progress.</p>
    </div>
</div>

{{-- Month Navigation --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="d-flex justify-content-between align-items-center">
            @php
                $prevMonth = date('Y-m', strtotime($month . '-01 -1 month'));
                $nextMonth = date('Y-m', strtotime($month . '-01 +1 month'));
                $displayMonth = date('F Y', strtotime($month . '-01'));
            @endphp
            <a href="{{ url('/budgets?month=' . $prevMonth) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-chevron-left"></i> {{ date('M', strtotime($prevMonth . '-01')) }}
            </a>
            <h5 class="mb-0 fw-semibold">{{ $displayMonth }}</h5>
            <a href="{{ url('/budgets?month=' . $nextMonth) }}" class="btn btn-sm btn-outline-secondary">
                {{ date('M', strtotime($nextMonth . '-01')) }} <i class="bi bi-chevron-right"></i>
            </a>
        </div>
    </div>
</div>

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-muted small text-uppercase fw-semibold">Total Budgeted</div>
                <div class="fs-4 fw-bold text-primary">${{ number_format($summary['total_budgeted'], 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-muted small text-uppercase fw-semibold">Total Spent</div>
                <div class="fs-4 fw-bold {{ $summary['total_spent'] > $summary['total_budgeted'] ? 'text-danger' : 'text-success' }}">
                    ${{ number_format($summary['total_spent'], 2) }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-muted small text-uppercase fw-semibold">Over Budget</div>
                <div class="fs-4 fw-bold {{ $summary['over_budget_count'] > 0 ? 'text-danger' : 'text-success' }}">
                    {{ $summary['over_budget_count'] }} {{ $summary['over_budget_count'] === 1 ? 'category' : 'categories' }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Active Budgets --}}
@if($summary['budgets']->count() > 0)
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0 fw-semibold">Budget Status</h5>
    </div>
    <div class="card-body">
        @foreach($summary['budgets'] as $budget)
        @php
            $pct = $budget->percent_used;
            $barColor = $pct >= 100 ? 'bg-danger' : ($pct >= 90 ? 'bg-warning' : ($pct >= 75 ? 'bg-info' : 'bg-success'));
            $barWidth = min($pct, 100);
        @endphp
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div>
                    <span class="d-inline-block rounded-circle me-1" style="width:10px;height:10px;background:{{ $budget->category->color ?? '#6c757d' }}"></span>
                    <span class="fw-semibold">{{ $budget->category->name ?? 'Unknown' }}</span>
                </div>
                <div class="text-end">
                    <span class="{{ $pct >= 100 ? 'text-danger fw-bold' : 'text-muted' }}">
                        ${{ number_format($budget->spent, 2) }}
                    </span>
                    <span class="text-muted">/ ${{ number_format($budget->amount, 2) }}</span>
                    <span class="badge {{ $pct >= 100 ? 'bg-danger' : ($pct >= 90 ? 'bg-warning text-dark' : 'bg-light text-dark') }} ms-2">
                        {{ number_format($pct, 0) }}%
                    </span>
                </div>
            </div>
            <div class="progress" style="height: 8px;">
                <div class="progress-bar {{ $barColor }}" role="progressbar" style="width: {{ $barWidth }}%"></div>
            </div>
            @if($pct >= 100)
            <small class="text-danger"><i class="bi bi-exclamation-triangle"></i> Over budget by ${{ number_format($budget->spent - $budget->amount, 2) }}</small>
            @elseif($pct >= 90)
            <small class="text-warning"><i class="bi bi-exclamation-circle"></i> ${{ number_format($budget->remaining, 2) }} remaining</small>
            @elseif($pct >= 75)
            <small class="text-info"><i class="bi bi-info-circle"></i> ${{ number_format($budget->remaining, 2) }} remaining</small>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Copy Budgets --}}
@if($summary['budgets']->count() > 0)
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ url('/budgets/copy') }}" method="POST" class="row g-2 align-items-end">
            @csrf
            <input type="hidden" name="from_month" value="{{ $month }}">
            <div class="col-auto">
                <label class="form-label small">Copy these budgets to:</label>
                <input type="month" name="to_month" class="form-control form-control-sm" value="{{ $nextMonth }}" required>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-clipboard me-1"></i> Copy Budgets
                </button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- Set/Edit Budgets Form --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0 fw-semibold">Set Budgets for {{ $displayMonth }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ url('/budgets') }}" method="POST">
            @csrf
            <input type="hidden" name="month" value="{{ $month }}">

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Category</th>
                            <th style="width: 180px;">Budget Amount</th>
                            <th style="width: 120px;">Current Spend</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $cat)
                        @php
                            $existing = $budgetMap[$cat->id] ?? null;
                            $currentSpend = $existing ? $existing->spent : 0;
                        @endphp
                        <tr>
                            <td>
                                <span class="d-inline-block rounded-circle me-2" style="width:12px;height:12px;background:{{ $cat->color ?? '#6c757d' }}"></span>
                                <i class="bi bi-{{ $cat->icon ?? 'tag' }} me-1"></i>
                                {{ $cat->name }}
                                <input type="hidden" name="budgets[{{ $loop->index }}][category_id]" value="{{ $cat->id }}">
                            </td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="budgets[{{ $loop->index }}][amount]"
                                           class="form-control" step="0.01" min="0"
                                           value="{{ $existing ? number_format($existing->amount, 2, '.', '') : '0.00' }}"
                                           placeholder="0.00">
                                </div>
                            </td>
                            <td class="text-muted small">
                                ${{ number_format($currentSpend, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle me-1"></i> Save Budgets
            </button>
        </form>
    </div>
</div>
@endsection
