@extends('layouts.app')

@section('title', 'Error Log')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Error Log</h4>
    @if(!empty($errors_list ?? []))
    <form action="{{ url('/admin/errors/clear') }}" method="POST" onsubmit="return confirm('Clear all error logs? This cannot be undone.');">
        @csrf
        <button type="submit" class="btn btn-danger">
            <i class="bi bi-trash me-1"></i> Clear All
        </button>
    </form>
    @endif
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Message</th>
                        <th style="width: 250px;">Context</th>
                        <th style="width: 170px;">Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($errors_list ?? [] as $error)
                    <tr>
                        <td class="text-muted">{{ $error->id ?? $error['id'] ?? '' }}</td>
                        <td>
                            <div class="text-danger fw-semibold" style="max-width: 500px; word-wrap: break-word;">
                                {{ $error->message ?? $error['message'] ?? '' }}
                            </div>
                        </td>
                        <td>
                            @if(!empty($error->context ?? $error['context'] ?? ''))
                            <code class="small" style="max-width: 250px; display: block; word-wrap: break-word;">
                                {{ Str::limit($error->context ?? $error['context'] ?? '', 200) }}
                            </code>
                            @else
                            <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $error->created_at ?? $error['created_at'] ?? '' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-5">
                            <i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>
                            No errors logged. Everything is running smoothly.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
