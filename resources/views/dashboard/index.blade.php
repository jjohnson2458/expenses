@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-xl col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-circle p-3" style="background: rgba(78,115,223,0.1);">
                            <i class="bi bi-calendar-month text-primary fs-4"></i>
                        </div>
                    </div>
                    <div class="ms-3">
                        <div class="text-muted small text-uppercase fw-semibold">This Month</div>
                        <div class="fs-4 fw-bold">${{ number_format($thisMonth ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-circle p-3" style="background: rgba(54,185,204,0.1);">
                            <i class="bi bi-calendar-check text-info fs-4"></i>
                        </div>
                    </div>
                    <div class="ms-3">
                        <div class="text-muted small text-uppercase fw-semibold">Last Month</div>
                        <div class="fs-4 fw-bold">${{ number_format($lastMonth ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-circle p-3" style="background: rgba(28,200,138,0.1);">
                            <i class="bi bi-arrow-down-circle text-success fs-4"></i>
                        </div>
                    </div>
                    <div class="ms-3">
                        <div class="text-muted small text-uppercase fw-semibold">Total Credits</div>
                        <div class="fs-4 fw-bold text-success">${{ number_format($totalCredits ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-circle p-3" style="background: rgba(231,74,59,0.1);">
                            <i class="bi bi-arrow-up-circle text-danger fs-4"></i>
                        </div>
                    </div>
                    <div class="ms-3">
                        <div class="text-muted small text-uppercase fw-semibold">Total Debits</div>
                        <div class="fs-4 fw-bold text-danger">${{ number_format($totalDebits ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-circle p-3" style="background: rgba(246,194,62,0.1);">
                            <i class="bi bi-hash text-warning fs-4"></i>
                        </div>
                    </div>
                    <div class="ms-3">
                        <div class="text-muted small text-uppercase fw-semibold">Transactions</div>
                        <div class="fs-4 fw-bold">{{ number_format($transactionCount ?? 0) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Tax Deadline Alert --}}
@if(!empty($nextDeadline))
@php $daysUntil = now()->diffInDays(\Carbon\Carbon::parse($nextDeadline->due_date), false); @endphp
@if($daysUntil <= 30 && $daysUntil >= 0)
<div class="alert {{ $daysUntil <= 7 ? 'alert-danger' : 'alert-warning' }} d-flex align-items-center mb-4" role="alert">
    <i class="bi bi-exclamation-triangle me-2 fs-5"></i>
    <div>
        <strong>Q{{ $nextDeadline->quarter }} Estimated Tax Due:</strong>
        {{ \Carbon\Carbon::parse($nextDeadline->due_date)->format('M j, Y') }}
        ({{ $daysUntil }} {{ $daysUntil === 1 ? 'day' : 'days' }} away)
        <a href="{{ url('/tax/summary') }}" class="ms-2">View Tax Summary <i class="bi bi-arrow-right"></i></a>
    </div>
</div>
@endif
@endif

{{-- Quick Actions --}}
<div class="mb-4">
    <a href="{{ url('/expenses/create') }}" class="btn btn-primary me-2">
        <i class="bi bi-plus-circle me-1"></i> Add Expense
    </a>
    <a href="{{ url('/reports/create') }}" class="btn btn-outline-primary me-2">
        <i class="bi bi-file-earmark-plus me-1"></i> New Report
    </a>
    <a href="{{ url('/tax/mileage') }}" class="btn btn-outline-primary me-2">
        <i class="bi bi-speedometer2 me-1"></i> Log Trip
    </a>
    <a href="{{ url('/import') }}" class="btn btn-outline-secondary">
        <i class="bi bi-upload me-1"></i> Import
    </a>
</div>

{{-- Budget Status Widget --}}
@if(($budgetSummary['budgets'] ?? collect())->count() > 0)
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold"><i class="bi bi-piggy-bank me-2"></i>Budget Status - {{ date('F Y') }}</h5>
        <a href="{{ url('/budgets') }}" class="btn btn-sm btn-outline-primary">Manage</a>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @foreach($budgetSummary['budgets']->take(6) as $budget)
            @php
                $pct = $budget->percent_used;
                $barColor = $pct >= 100 ? 'bg-danger' : ($pct >= 90 ? 'bg-warning' : ($pct >= 75 ? 'bg-info' : 'bg-success'));
            @endphp
            <div class="col-md-6">
                <div class="d-flex justify-content-between mb-1">
                    <span class="small fw-semibold">
                        <span class="d-inline-block rounded-circle me-1" style="width:8px;height:8px;background:{{ $budget->category->color ?? '#6c757d' }}"></span>
                        {{ $budget->category->name ?? 'Unknown' }}
                    </span>
                    <span class="small {{ $pct >= 100 ? 'text-danger fw-bold' : 'text-muted' }}">
                        ${{ number_format($budget->spent, 2) }} / ${{ number_format($budget->amount, 2) }}
                    </span>
                </div>
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar {{ $barColor }}" style="width: {{ min($pct, 100) }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
        @if($budgetSummary['over_budget_count'] > 0)
        <div class="alert alert-danger mt-3 mb-0 py-2 small">
            <i class="bi bi-exclamation-triangle me-1"></i>
            {{ $budgetSummary['over_budget_count'] }} {{ $budgetSummary['over_budget_count'] === 1 ? 'category is' : 'categories are' }} over budget this month.
        </div>
        @endif
    </div>
</div>
@endif

{{-- Chart --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0 fw-semibold">Monthly Overview</h5>
    </div>
    <div class="card-body">
        <canvas id="monthlyChart" height="100"></canvas>
    </div>
</div>

<div class="row g-4">
    {{-- Recent Expenses --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold">Recent Expenses</h5>
                <a href="{{ url('/expenses') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Category</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentExpenses ?? [] as $expense)
                            <tr>
                                <td class="text-muted small">{{ $expense->expense_date ?? '' }}</td>
                                <td>{{ $expense->description ?? '' }}</td>
                                <td>
                                    @if(!empty($expense->category_name))
                                    <span class="badge rounded-pill" style="background: {{ $expense->category_color ?? '#6c757d' }};">
                                        {{ $expense->category_name }}
                                    </span>
                                    @endif
                                </td>
                                <td class="text-end fw-semibold {{ ($expense->type ?? 'debit') === 'credit' ? 'text-success' : 'text-danger' }}">
                                    {{ ($expense->type ?? 'debit') === 'credit' ? '+' : '-' }}${{ number_format($expense->amount ?? 0, 2) }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                                    No expenses yet. <a href="{{ url('/expenses/create') }}">Add your first expense</a>.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Category Breakdown --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-semibold">Spending by Category</h5>
            </div>
            <div class="card-body">
                @if(($categoryBreakdown ?? collect())->count() > 0)
                <canvas id="categoryDonut" height="200"></canvas>
                <hr class="my-3">
                @endif
                @forelse($categoryBreakdown ?? [] as $cat)
                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small fw-semibold">
                            <span class="d-inline-block rounded-circle me-1" style="width:10px;height:10px;background:{{ $cat->color }}"></span>
                            {{ $cat->name }}
                        </span>
                        <span class="small text-muted">${{ number_format($cat->total, 2) }}</span>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center mb-0">No data available</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Product Roadmap --}}
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0 fw-semibold">
            <a class="text-decoration-none text-dark" data-bs-toggle="collapse" href="#roadmapCollapse" role="button">
                <i class="bi bi-map me-2"></i>VQMoney Product Roadmap
                <i class="bi bi-chevron-down small ms-1"></i>
            </a>
        </h5>
    </div>
    <div class="collapse" id="roadmapCollapse">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <h6 class="text-success"><i class="bi bi-check-circle me-1"></i> Completed</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-1"><i class="bi bi-check text-success me-1"></i> Laravel 12 migration</li>
                        <li class="mb-1"><i class="bi bi-check text-success me-1"></i> Expense ledger with credits/debits</li>
                        <li class="mb-1"><i class="bi bi-check text-success me-1"></i> Voice input for expenses</li>
                        <li class="mb-1"><i class="bi bi-check text-success me-1"></i> Receipt upload & OCR infrastructure</li>
                        <li class="mb-1"><i class="bi bi-check text-success me-1"></i> Tax profile & IRS Schedule C mapping</li>
                        <li class="mb-1"><i class="bi bi-check text-success me-1"></i> Mileage tracker (IRS $0.70/mi)</li>
                        <li class="mb-1"><i class="bi bi-check text-success me-1"></i> Stripe subscription billing</li>
                        <li class="mb-1"><i class="bi bi-check text-success me-1"></i> User registration & password reset</li>
                        <li class="mb-1"><i class="bi bi-check text-success me-1"></i> SSL & landing page</li>
                        <li class="mb-1"><i class="bi bi-check text-success me-1"></i> CSV/IIF/iCal export</li>
                        <li class="mb-1"><i class="bi bi-check text-success me-1"></i> Bilingual (EN/ES)</li>
                        <li class="mb-1"><i class="bi bi-check text-success me-1"></i> Budget tracking with alerts</li>
                        <li class="mb-1"><i class="bi bi-check text-success me-1"></i> Tax package generator (7 exports)</li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6 class="text-primary"><i class="bi bi-arrow-right-circle me-1"></i> Coming Soon</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-1"><i class="bi bi-dash text-primary me-1"></i> Email-to-expense (receipts@vqmoney.com)</li>
                        <li class="mb-1"><i class="bi bi-dash text-primary me-1"></i> PDF report generation</li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6 class="text-muted"><i class="bi bi-circle me-1"></i> Planned</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-1"><i class="bi bi-dot text-muted me-1"></i> Plaid bank feed integration</li>
                        <li class="mb-1"><i class="bi bi-dot text-muted me-1"></i> QuickBooks Online sync</li>
                        <li class="mb-1"><i class="bi bi-dot text-muted me-1"></i> Multi-user & team accounts</li>
                        <li class="mb-1"><i class="bi bi-dot text-muted me-1"></i> PWA mobile experience</li>
                        <li class="mb-1"><i class="bi bi-dot text-muted me-1"></i> REST API</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const monthlyData = @json($monthlyTotals ?? []);
    const labels = monthlyData.map(item => item.month || item.label || '');
    const debits = monthlyData.map(item => parseFloat(item.debits || item.debit || 0));
    const credits = monthlyData.map(item => parseFloat(item.credits || item.credit || 0));

    const ctx = document.getElementById('monthlyChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Debits',
                        data: debits,
                        backgroundColor: 'rgba(231, 74, 59, 0.7)',
                        borderColor: '#e74a3b',
                        borderWidth: 1,
                        borderRadius: 4
                    },
                    {
                        label: 'Credits',
                        data: credits,
                        backgroundColor: 'rgba(28, 200, 138, 0.7)',
                        borderColor: '#1cc88a',
                        borderWidth: 1,
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) { return '$' + value.toLocaleString(); }
                        }
                    }
                }
            }
        });
    }
    // Category Donut Chart
    const catData = @json(($categoryBreakdown ?? collect())->values());
    const donutCtx = document.getElementById('categoryDonut');
    if (donutCtx && catData.length > 0) {
        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: catData.map(c => c.name || 'Uncategorized'),
                datasets: [{
                    data: catData.map(c => parseFloat(c.total || 0)),
                    backgroundColor: catData.map(c => c.color || '#6c757d'),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                cutout: '65%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return ctx.label + ': $' + ctx.parsed.toLocaleString(undefined, {minimumFractionDigits: 2});
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
