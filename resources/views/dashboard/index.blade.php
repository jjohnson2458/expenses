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

{{-- Quick Actions --}}
<div class="mb-4">
    <a href="{{ url('/expenses/create') }}" class="btn btn-primary me-2">
        <i class="bi bi-plus-circle me-1"></i> Add Expense
    </a>
    <a href="{{ url('/reports/create') }}" class="btn btn-outline-primary me-2">
        <i class="bi bi-file-earmark-plus me-1"></i> New Report
    </a>
    <a href="{{ url('/import') }}" class="btn btn-outline-secondary">
        <i class="bi bi-upload me-1"></i> Import
    </a>
</div>

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
                                <td class="text-muted small">{{ $expense->date ?? $expense['date'] ?? '' }}</td>
                                <td>{{ $expense->description ?? $expense['description'] ?? '' }}</td>
                                <td>
                                    @if(!empty($expense->category_name ?? $expense['category_name'] ?? ''))
                                    <span class="badge rounded-pill" style="background: {{ $expense->category_color ?? $expense['category_color'] ?? '#6c757d' }};">
                                        {{ $expense->category_name ?? $expense['category_name'] }}
                                    </span>
                                    @endif
                                </td>
                                <td class="text-end fw-semibold {{ ($expense->type ?? $expense['type'] ?? 'debit') === 'credit' ? 'text-success' : 'text-danger' }}">
                                    {{ ($expense->type ?? $expense['type'] ?? 'debit') === 'credit' ? '+' : '-' }}${{ number_format($expense->amount ?? $expense['amount'] ?? 0, 2) }}
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
                <h5 class="mb-0 fw-semibold">By Category</h5>
            </div>
            <div class="card-body">
                @forelse($categoryBreakdown ?? [] as $cat)
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small fw-semibold">{{ $cat->name ?? $cat['name'] ?? 'Uncategorized' }}</span>
                        <span class="small text-muted">${{ number_format($cat->total ?? $cat['total'] ?? 0, 2) }}</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar" role="progressbar"
                             style="width: {{ $cat->percentage ?? $cat['percentage'] ?? 0 }}%; background: {{ $cat->color ?? $cat['color'] ?? '#4e73df' }};"
                             aria-valuenow="{{ $cat->percentage ?? $cat['percentage'] ?? 0 }}"
                             aria-valuemin="0" aria-valuemax="100">
                        </div>
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
                        <li class="mb-1"><i class="bi bi-check text-success me-1"></i> Expense ledger with credits/debits</li>
                        <li class="mb-1"><i class="bi bi-check text-success me-1"></i> Category management with drag-and-drop</li>
                        <li class="mb-1"><i class="bi bi-check text-success me-1"></i> Multiple expense reports</li>
                        <li class="mb-1"><i class="bi bi-check text-success me-1"></i> Voice input for expenses</li>
                        <li class="mb-1"><i class="bi bi-check text-success me-1"></i> CSV import/export</li>
                        <li class="mb-1"><i class="bi bi-check text-success me-1"></i> QuickBooks IIF export</li>
                        <li class="mb-1"><i class="bi bi-check text-success me-1"></i> Recurring expenses</li>
                        <li class="mb-1"><i class="bi bi-check text-success me-1"></i> Bilingual support (EN/ES)</li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6 class="text-primary"><i class="bi bi-arrow-right-circle me-1"></i> In Progress</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-1"><i class="bi bi-dash text-primary me-1"></i> Receipt OCR with AI</li>
                        <li class="mb-1"><i class="bi bi-dash text-primary me-1"></i> Enhanced dashboard analytics</li>
                        <li class="mb-1"><i class="bi bi-dash text-primary me-1"></i> Laravel migration</li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6 class="text-muted"><i class="bi bi-circle me-1"></i> Planned</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-1"><i class="bi bi-dot text-muted me-1"></i> Multi-user support</li>
                        <li class="mb-1"><i class="bi bi-dot text-muted me-1"></i> Budget tracking</li>
                        <li class="mb-1"><i class="bi bi-dot text-muted me-1"></i> Mobile app</li>
                        <li class="mb-1"><i class="bi bi-dot text-muted me-1"></i> API integrations</li>
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
});
</script>
@endpush
