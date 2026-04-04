@extends('layouts.app')

@section('title', 'Monthly Summary')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Monthly Summary</h4>
        <p class="text-muted mb-0">Your spending overview for {{ $displayMonth }}.</p>
    </div>
    <div class="d-flex gap-2">
        @php
            $prev = date('Y-m', strtotime($month . '-01 -1 month'));
            $next = date('Y-m', strtotime($month . '-01 +1 month'));
        @endphp
        <a href="{{ url('/summary?month=' . $prev) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-chevron-left"></i></a>
        <a href="{{ url('/summary?month=' . $next) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-chevron-right"></i></a>
        <form action="{{ url('/summary/send') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-envelope me-1"></i>Email Summary</button>
        </form>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-muted small text-uppercase fw-semibold">Total Spent</div>
                <div class="fs-3 fw-bold text-danger">${{ number_format($totalSpend, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-muted small text-uppercase fw-semibold">Credits</div>
                <div class="fs-3 fw-bold text-success">${{ number_format($totalCredits, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-muted small text-uppercase fw-semibold">Transactions</div>
                <div class="fs-3 fw-bold text-primary">{{ $transactionCount }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Category Breakdown --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-semibold">Spending by Category</h5>
            </div>
            <div class="card-body">
                @forelse($categories as $cat)
                @php $pct = $totalSpend > 0 ? round(($cat->total / $totalSpend) * 100, 0) : 0; @endphp
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="fw-semibold">
                            <span class="d-inline-block rounded-circle me-1" style="width:10px;height:10px;background:{{ $cat->color ?? '#6c757d' }}"></span>
                            {{ $cat->category_name ?? 'Uncategorized' }}
                        </span>
                        <span class="text-muted">${{ number_format($cat->total, 2) }} ({{ $pct }}%)</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar" style="width:{{ $pct }}%;background:{{ $cat->color ?? '#6c757d' }}"></div>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center">No expenses for this month.</p>
                @endforelse
            </div>
        </div>

        {{-- Trend --}}
        @if($trend->count() > 1)
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-semibold">6-Month Cash Flow Trend</h5>
            </div>
            <div class="card-body">
                <canvas id="trendChart" height="120"></canvas>
            </div>
        </div>
        @endif
    </div>

    {{-- Budget Status --}}
    <div class="col-lg-5">
        @if(($budgetSummary['budgets'] ?? collect())->count() > 0)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-semibold">Budget Status</h5>
            </div>
            <div class="card-body">
                @foreach($budgetSummary['budgets'] as $budget)
                @php
                    $pct = $budget->percent_used;
                    $barColor = $pct >= 100 ? 'bg-danger' : ($pct >= 90 ? 'bg-warning' : 'bg-success');
                @endphp
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small fw-semibold">{{ $budget->category->name ?? 'Unknown' }}</span>
                        <span class="small {{ $pct >= 100 ? 'text-danger fw-bold' : 'text-muted' }}">
                            {{ number_format($pct, 0) }}%
                        </span>
                    </div>
                    <div class="progress" style="height: 5px;">
                        <div class="progress-bar {{ $barColor }}" style="width:{{ min($pct, 100) }}%"></div>
                    </div>
                </div>
                @endforeach

                @if($budgetSummary['over_budget_count'] > 0)
                <div class="alert alert-danger mt-2 mb-0 py-2 small">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    {{ $budgetSummary['over_budget_count'] }} over budget
                </div>
                @endif
            </div>
        </div>
        @endif

        <div class="card border-0 shadow-sm {{ ($budgetSummary['budgets'] ?? collect())->count() > 0 ? 'mt-4' : '' }}">
            <div class="card-body text-center">
                <i class="bi bi-envelope-check fs-1 text-primary d-block mb-2"></i>
                <p class="text-muted small mb-0">Monthly summaries are emailed automatically on the 5th of each month.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if($trend->count() > 1)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const trendData = @json($trend);
new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: trendData.map(t => {
            const [y, m] = t.month.split('-');
            return new Date(y, m-1).toLocaleDateString('en-US', {month:'short', year:'2-digit'});
        }),
        datasets: [{
            label: 'Spending',
            data: trendData.map(t => parseFloat(t.total)),
            borderColor: '#e74a3b',
            backgroundColor: 'rgba(231,74,59,0.1)',
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { callback: v => '$' + v.toLocaleString() }
            }
        }
    }
});
</script>
@endif
@endpush
