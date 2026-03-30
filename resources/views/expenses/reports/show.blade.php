@extends('layouts.app')

@section('title', $report['title'] ?? 'Report Details')

@section('content')
{{-- Report Info --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="mb-1">{{ $report['title'] ?? '' }}</h4>
                @if(!empty($report['description'] ?? ''))
                    <p class="text-muted mb-2">{{ $report['description'] }}</p>
                @endif
                <div class="d-flex gap-3 align-items-center">
                    @php
                        $status = $report['status'] ?? 'draft';
                        $statusColors = [
                            'draft' => 'secondary',
                            'submitted' => 'primary',
                            'approved' => 'success',
                            'rejected' => 'danger',
                        ];
                    @endphp
                    <span class="badge bg-{{ $statusColors[$status] ?? 'secondary' }} fs-6">{{ ucfirst($status) }}</span>
                    @if(!empty($report['start_date'] ?? ''))
                        <span class="text-muted small">
                            <i class="bi bi-calendar me-1"></i>
                            {{ $report['start_date'] }}
                            @if(!empty($report['end_date'] ?? ''))
                                &mdash; {{ $report['end_date'] }}
                            @endif
                        </span>
                    @endif
                </div>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <div class="fs-3 fw-bold mb-2">${{ number_format($report['total'] ?? 0, 2) }}</div>
                <div class="d-flex gap-2 justify-content-md-end">
                    <a href="{{ url('/reports/' . $report['id'] . '/edit') }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                    <a href="{{ url('/reports/' . $report['id'] . '/print') }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                        <i class="bi bi-printer me-1"></i> Print
                    </a>
                    <a href="{{ url('/reports/' . $report['id'] . '/export') }}" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-download me-1"></i> Export
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Linked Expenses --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold">Linked Expenses</h5>
        <span class="badge bg-primary">{{ count($expenses ?? []) }} expenses</span>
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
                        <th class="text-center" style="width: 80px;">Remove</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses ?? [] as $expense)
                    <tr>
                        <td class="text-muted">{{ $expense->date ?? $expense['date'] ?? '' }}</td>
                        <td class="fw-semibold">{{ $expense->description ?? $expense['description'] ?? '' }}</td>
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
                        <td class="text-center">
                            <form action="{{ url('/reports/' . $report['id'] . '/remove-expense') }}" method="POST" onsubmit="return confirm('Remove from report?');">
                                @csrf
                                <input type="hidden" name="expense_id" value="{{ $expense->id ?? $expense['id'] }}">
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            No expenses linked to this report yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Expense to Report --}}
@if(!empty($availableExpenses ?? []))
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0 fw-semibold">Add Expense to Report</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ url('/reports/' . $report['id'] . '/expenses') }}" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-8">
                <label class="form-label fw-semibold">Select Expense</label>
                <select name="expense_id" class="form-select" required>
                    <option value="">Choose an expense...</option>
                    @foreach($availableExpenses as $avail)
                        <option value="{{ $avail->id ?? $avail['id'] }}">
                            {{ $avail->date ?? $avail['date'] }} - {{ $avail->description ?? $avail['description'] }} (${{ number_format($avail->amount ?? $avail['amount'] ?? 0, 2) }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-plus-circle me-1"></i> Add to Report
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
