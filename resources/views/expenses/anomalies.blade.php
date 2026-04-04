@extends('layouts.app')

@section('title', 'Anomaly Detection')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Anomaly Detection</h4>
        <p class="text-muted mb-0">Potential duplicates, unusual amounts, and uncategorized expenses.</p>
    </div>
</div>

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <a href="{{ url('/anomalies?type=all') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 {{ $filter === 'all' ? 'border-start border-primary border-3' : '' }}">
                <div class="card-body text-center">
                    <div class="text-muted small text-uppercase fw-semibold">Total Flags</div>
                    <div class="fs-3 fw-bold {{ $counts['total'] > 0 ? 'text-danger' : 'text-success' }}">{{ $counts['total'] }}</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ url('/anomalies?type=duplicate') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 {{ $filter === 'duplicate' ? 'border-start border-warning border-3' : '' }}">
                <div class="card-body text-center">
                    <div class="text-muted small text-uppercase fw-semibold">Duplicates</div>
                    <div class="fs-3 fw-bold {{ $counts['duplicates'] > 0 ? 'text-warning' : 'text-muted' }}">{{ $counts['duplicates'] }}</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ url('/anomalies?type=unusual') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 {{ $filter === 'unusual' ? 'border-start border-danger border-3' : '' }}">
                <div class="card-body text-center">
                    <div class="text-muted small text-uppercase fw-semibold">Unusual Amounts</div>
                    <div class="fs-3 fw-bold {{ $counts['unusual'] > 0 ? 'text-danger' : 'text-muted' }}">{{ $counts['unusual'] }}</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ url('/anomalies?type=uncategorized') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 {{ $filter === 'uncategorized' ? 'border-start border-info border-3' : '' }}">
                <div class="card-body text-center">
                    <div class="text-muted small text-uppercase fw-semibold">Uncategorized</div>
                    <div class="fs-3 fw-bold {{ $counts['uncategorized'] > 0 ? 'text-info' : 'text-muted' }}">{{ $counts['uncategorized'] }}</div>
                </div>
            </div>
        </a>
    </div>
</div>

{{-- Results --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0 fw-semibold">
            @if($filter === 'duplicate') Potential Duplicates
            @elseif($filter === 'unusual') Unusual Amounts
            @elseif($filter === 'uncategorized') Uncategorized Expenses
            @else All Flagged Items
            @endif
            <span class="badge bg-secondary ms-2">{{ $allAnomalies->count() }}</span>
        </h5>
    </div>
    <div class="card-body p-0">
        @if($allAnomalies->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th class="text-end">Amount</th>
                        <th>Reason</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allAnomalies as $item)
                    <tr>
                        <td>
                            @if($item->anomaly_type === 'duplicate')
                                <span class="badge bg-warning text-dark"><i class="bi bi-files me-1"></i>Duplicate</span>
                            @elseif($item->anomaly_type === 'unusual')
                                <span class="badge bg-danger"><i class="bi bi-exclamation-triangle me-1"></i>Unusual</span>
                            @else
                                <span class="badge bg-info"><i class="bi bi-tag me-1"></i>No Category</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $item->expense_date }}</td>
                        <td>{{ $item->description ?? '' }}</td>
                        <td>
                            @if($item->category_name)
                            <span class="badge rounded-pill" style="background:{{ $item->category_color ?? '#6c757d' }}">{{ $item->category_name }}</span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-end fw-semibold {{ ($item->type ?? 'debit') === 'credit' ? 'text-success' : 'text-danger' }}">
                            {{ ($item->type ?? 'debit') === 'credit' ? '+' : '-' }}${{ number_format($item->amount, 2) }}
                        </td>
                        <td class="small text-muted" style="max-width:200px;">{{ $item->anomaly_reason }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ url('/expenses/' . $item->id . '/edit') }}" class="btn btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ url('/anomalies/' . $item->id . '/dismiss') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success" title="Mark Reviewed">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-shield-check fs-1 text-success d-block mb-3"></i>
            <h5 class="text-success">All Clear</h5>
            <p class="text-muted">No anomalies detected in the last 6 months.</p>
        </div>
        @endif
    </div>
</div>
@endsection
