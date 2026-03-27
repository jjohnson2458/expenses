@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Expense Reports</h4>
    <a href="{{ url('/reports/create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> New Report
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Date Range</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Expenses</th>
                        <th>Created</th>
                        <th class="text-center" style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports ?? [] as $report)
                    <tr>
                        <td>
                            <a href="{{ url('/reports/' . ($report->id ?? $report['id'])) }}" class="fw-semibold text-decoration-none">
                                {{ $report->title ?? $report['title'] }}
                            </a>
                        </td>
                        <td>
                            @php
                                $status = $report->status ?? $report['status'] ?? 'draft';
                                $statusColors = [
                                    'draft' => 'secondary',
                                    'submitted' => 'primary',
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$status] ?? 'secondary' }}">
                                {{ ucfirst($status) }}
                            </span>
                        </td>
                        <td class="text-muted small">
                            {{ $report->start_date ?? $report['start_date'] ?? '' }}
                            @if(!empty($report->end_date ?? $report['end_date'] ?? ''))
                                &mdash; {{ $report->end_date ?? $report['end_date'] }}
                            @endif
                        </td>
                        <td class="text-end fw-semibold">${{ number_format($report->total ?? $report['total'] ?? 0, 2) }}</td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark">{{ $report->expense_count ?? $report['expense_count'] ?? 0 }}</span>
                        </td>
                        <td class="text-muted small">{{ $report->created_at ?? $report['created_at'] ?? '' }}</td>
                        <td class="text-center">
                            <a href="{{ url('/reports/' . ($report->id ?? $report['id'])) }}" class="btn btn-sm btn-outline-info" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ url('/reports/' . ($report->id ?? $report['id']) . '/edit') }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ url('/reports/' . ($report->id ?? $report['id'])) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this report?');">
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
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-file-earmark-bar-graph fs-1 d-block mb-2"></i>
                            No reports yet. <a href="{{ url('/reports/create') }}">Create your first report</a>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
