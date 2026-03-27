@extends('layouts.app')

@section('title', 'Expenses')

@section('content')
{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Expense Ledger</h4>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#voiceModal">
            <i class="bi bi-mic me-1"></i> Voice Input
        </button>
        <a href="{{ url('/expenses/create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Add Expense
        </a>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ url('/expenses') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Start Date</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">End Date</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Category</label>
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    @foreach($categories ?? [] as $category)
                        <option value="{{ $category->id ?? $category['id'] }}" {{ request('category_id') == ($category->id ?? $category['id']) ? 'selected' : '' }}>
                            {{ $category->name ?? $category['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Search</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search description, vendor..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-primary me-1">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="{{ url('/expenses') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="text-muted small text-uppercase fw-semibold">Total Debits</div>
                <div class="fs-4 fw-bold text-danger">${{ number_format($totalDebits ?? 0, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="text-muted small text-uppercase fw-semibold">Total Credits</div>
                <div class="fs-4 fw-bold text-success">${{ number_format($totalCredits ?? 0, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="text-muted small text-uppercase fw-semibold">Net</div>
                @php $net = ($totalCredits ?? 0) - ($totalDebits ?? 0); @endphp
                <div class="fs-4 fw-bold {{ $net >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ $net >= 0 ? '+' : '' }}${{ number_format(abs($net), 2) }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Expense Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th class="text-end">Amount</th>
                        <th class="text-center" style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses ?? [] as $expense)
                    <tr>
                        <td class="text-muted">{{ $expense->expense_date ?? '' }}</td>
                        <td>
                            <div class="fw-semibold">
                                {{ $expense->description ?? '' }}
                                @if(!empty($expense->receipt_path))
                                    <a href="{{ asset('storage/' . $expense->receipt_path) }}" target="_blank" title="View receipt" class="text-muted ms-1"><i class="bi bi-paperclip"></i></a>
                                @endif
                            </div>
                            @if(!empty($expense->vendor))
                                <small class="text-muted">{{ $expense->vendor }}</small>
                            @endif
                        </td>
                        <td>
                            @if(!empty($expense->category_name))
                            <span class="badge rounded-pill" style="background: {{ $expense->category_color ?? '#6c757d' }};">
                                {{ $expense->category_name }}
                            </span>
                            @else
                            <span class="text-muted small">Uncategorized</span>
                            @endif
                        </td>
                        <td class="text-end fw-semibold {{ ($expense->type ?? 'debit') === 'credit' ? 'text-success' : 'text-danger' }}">
                            {{ ($expense->type ?? 'debit') === 'credit' ? '+' : '-' }}${{ number_format($expense->amount ?? 0, 2) }}
                        </td>
                        <td class="text-center">
                            <a href="{{ url('/expenses/' . $expense->id . '/edit') }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ url('/expenses/' . $expense->id . '/delete') }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this expense?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">
                            <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                            No expenses found.
                            <a href="{{ url('/expenses/create') }}">Add your first expense</a>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Pagination --}}
@if(isset($expenses) && method_exists($expenses, 'links'))
<div class="mt-3 d-flex justify-content-center">
    {{ $expenses->links() }}
</div>
@endif

@include('partials.voice-modal')
@endsection
