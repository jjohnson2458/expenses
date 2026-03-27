@extends('layouts.app')

@section('title', 'Recurring Expenses')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Recurring Expenses</h4>
    <div class="d-flex gap-2">
        <form action="{{ url('/recurring/process') }}" method="POST" class="d-inline" onsubmit="return confirm('Process all active recurring expenses for this month?');">
            @csrf
            <button type="submit" class="btn btn-success">
                <i class="bi bi-play-circle me-1"></i> Process Monthly
            </button>
        </form>
        <a href="{{ url('/recurring/create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Add Recurring
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th class="text-end">Amount</th>
                        <th class="text-center">Day</th>
                        <th>Status</th>
                        <th>Last Processed</th>
                        <th class="text-center" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recurringExpenses ?? [] as $recurring)
                    <tr>
                        <td class="fw-semibold">{{ $recurring->description ?? $recurring['description'] ?? '' }}</td>
                        <td>
                            @if(!empty($recurring->category_name ?? $recurring['category_name'] ?? ''))
                            <span class="badge rounded-pill" style="background: {{ $recurring->category_color ?? $recurring['category_color'] ?? '#6c757d' }};">
                                {{ $recurring->category_name ?? $recurring['category_name'] }}
                            </span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ ($recurring->type ?? $recurring['type'] ?? 'debit') === 'credit' ? 'bg-success' : 'bg-danger' }}">
                                {{ ucfirst($recurring->type ?? $recurring['type'] ?? 'debit') }}
                            </span>
                        </td>
                        <td class="text-end fw-semibold">${{ number_format($recurring->amount ?? $recurring['amount'] ?? 0, 2) }}</td>
                        <td class="text-center">{{ $recurring->day_of_month ?? $recurring['day_of_month'] ?? '' }}</td>
                        <td>
                            @if($recurring->active ?? $recurring['active'] ?? 1)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $recurring->last_processed ?? $recurring['last_processed'] ?? 'Never' }}</td>
                        <td class="text-center">
                            <a href="{{ url('/recurring/' . ($recurring->id ?? $recurring['id']) . '/edit') }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ url('/recurring/' . ($recurring->id ?? $recurring['id'])) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this recurring expense?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-arrow-repeat fs-1 d-block mb-2"></i>
                            No recurring expenses yet. <a href="{{ url('/recurring/create') }}">Set up your first recurring expense</a>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
