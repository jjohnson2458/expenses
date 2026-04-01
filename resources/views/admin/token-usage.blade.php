@extends('layouts.app')

@section('title', 'API Token Usage')

@section('content')
<h4 class="fw-bold mb-4"><i class="bi bi-graph-up me-2"></i>API Token Usage</h4>

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-semibold mb-1">All Time</div>
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="fs-4 fw-bold">{{ number_format($allTime->calls) }}</div>
                        <div class="text-muted small">API calls</div>
                    </div>
                    <div class="text-end">
                        <div class="fs-5 fw-bold">{{ number_format($allTime->tokens) }}</div>
                        <div class="text-muted small">tokens</div>
                    </div>
                    <div class="text-end">
                        <div class="fs-5 fw-bold text-success">${{ number_format($allTime->cost, 4) }}</div>
                        <div class="text-muted small">cost</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-semibold mb-1">Today</div>
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="fs-4 fw-bold">{{ number_format($today->calls) }}</div>
                        <div class="text-muted small">calls</div>
                    </div>
                    <div class="text-end">
                        <div class="fs-5 fw-bold">{{ number_format($today->tokens) }}</div>
                        <div class="text-muted small">tokens</div>
                    </div>
                    <div class="text-end">
                        <div class="fs-5 fw-bold text-success">${{ number_format($today->cost, 4) }}</div>
                        <div class="text-muted small">cost</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-semibold mb-1">This Month</div>
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="fs-4 fw-bold">{{ number_format($thisMonth->calls) }}</div>
                        <div class="text-muted small">calls</div>
                    </div>
                    <div class="text-end">
                        <div class="fs-5 fw-bold">{{ number_format($thisMonth->tokens) }}</div>
                        <div class="text-muted small">tokens</div>
                    </div>
                    <div class="text-end">
                        <div class="fs-5 fw-bold text-success">${{ number_format($thisMonth->cost, 4) }}</div>
                        <div class="text-muted small">cost</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- Usage by Feature --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-semibold">Usage by Feature</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Feature</th><th class="text-end">Calls</th><th class="text-end">Tokens</th><th class="text-end">Cost</th><th class="text-end">Avg ms</th></tr>
                    </thead>
                    <tbody>
                        @forelse($byFeature as $row)
                        <tr>
                            <td><code>{{ $row->feature }}</code></td>
                            <td class="text-end">{{ number_format($row->calls) }}</td>
                            <td class="text-end">{{ number_format($row->tokens) }}</td>
                            <td class="text-end">${{ number_format($row->cost, 4) }}</td>
                            <td class="text-end text-muted">{{ number_format($row->avg_ms, 0) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">No API calls yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Usage by Model --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-semibold">Usage by Model</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Model</th><th class="text-end">Calls</th><th class="text-end">Input</th><th class="text-end">Output</th><th class="text-end">Cost</th></tr>
                    </thead>
                    <tbody>
                        @forelse($byModel as $row)
                        <tr>
                            <td><code>{{ $row->model }}</code></td>
                            <td class="text-end">{{ number_format($row->calls) }}</td>
                            <td class="text-end">{{ number_format($row->input_tokens) }}</td>
                            <td class="text-end">{{ number_format($row->output_tokens) }}</td>
                            <td class="text-end">${{ number_format($row->cost, 4) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">No API calls yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- Daily Usage (30 days) --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-semibold">Daily Usage (Last 30 Days)</h5>
            </div>
            <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Date</th><th class="text-end">Calls</th><th class="text-end">Tokens</th><th class="text-end">Cost</th></tr>
                    </thead>
                    <tbody>
                        @forelse($dailyUsage as $row)
                        <tr>
                            <td>{{ $row->date }}</td>
                            <td class="text-end">{{ number_format($row->calls) }}</td>
                            <td class="text-end">{{ number_format($row->tokens) }}</td>
                            <td class="text-end">${{ number_format($row->cost, 4) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">No data yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Usage by User (Top 20) --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-semibold">Usage by User (Top 20)</h5>
            </div>
            <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>User</th><th class="text-end">Calls</th><th class="text-end">Tokens</th><th class="text-end">Cost</th></tr>
                    </thead>
                    <tbody>
                        @forelse($byUser as $row)
                        <tr>
                            <td>{{ $row->name ?? 'Unknown' }} <span class="text-muted small">{{ $row->email }}</span></td>
                            <td class="text-end">{{ number_format($row->calls) }}</td>
                            <td class="text-end">{{ number_format($row->tokens) }}</td>
                            <td class="text-end">${{ number_format($row->cost, 4) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">No data yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Recent Calls --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0 fw-semibold">Recent API Calls (Last 100)</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Time</th>
                        <th>User</th>
                        <th>Feature</th>
                        <th>Model</th>
                        <th class="text-end">Input</th>
                        <th class="text-end">Output</th>
                        <th class="text-end">Cost</th>
                        <th class="text-end">ms</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentCalls as $call)
                    <tr>
                        <td class="text-muted small text-nowrap">{{ \Carbon\Carbon::parse($call->created_at)->format('M j H:i') }}</td>
                        <td class="small">{{ $call->user_name ?? '—' }}</td>
                        <td><code class="small">{{ $call->feature }}</code></td>
                        <td class="small">{{ $call->model }}</td>
                        <td class="text-end small">{{ number_format($call->input_tokens) }}</td>
                        <td class="text-end small">{{ number_format($call->output_tokens) }}</td>
                        <td class="text-end small">${{ number_format($call->estimated_cost_usd, 4) }}</td>
                        <td class="text-end small text-muted">{{ $call->response_time_ms ? number_format($call->response_time_ms) : '—' }}</td>
                        <td>
                            @if($call->success)
                                <span class="badge bg-success">OK</span>
                            @else
                                <span class="badge bg-danger" title="{{ $call->error_message }}">FAIL</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">No API calls recorded yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
