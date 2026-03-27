@extends('layouts.app')

@section('title', 'Mileage Tracker')

@section('content')
<div class="row">
    {{-- Summary Cards --}}
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="text-muted small text-uppercase fw-semibold">Total Miles</div>
                <div class="fs-3 fw-bold text-primary">{{ number_format($summary['total_miles'], 1) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="text-muted small text-uppercase fw-semibold">Tax Deduction</div>
                <div class="fs-3 fw-bold text-success">${{ number_format($summary['total_deduction'], 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="text-muted small text-uppercase fw-semibold">Trips</div>
                <div class="fs-3 fw-bold" style="color:#f6c23e">{{ $summary['trip_count'] }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Log Trip Form --}}
    <div class="col-lg-5 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-semibold"><i class="bi bi-plus-circle me-2"></i>Log Trip</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ url('/tax/mileage') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Date</label>
                        <input type="date" name="trip_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">From</label>
                        <input type="text" name="start_location" class="form-control" placeholder="Starting address" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">To</label>
                        <input type="text" name="end_location" class="form-control" placeholder="Destination address" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Business Purpose</label>
                        <input type="text" name="business_purpose" class="form-control" placeholder="e.g. Client meeting, delivery" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold">Miles</label>
                            <input type="number" name="miles" class="form-control" step="0.1" min="0.1" required>
                        </div>
                        <div class="col-6 mb-3 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="round_trip" name="round_trip" value="1">
                                <label class="form-check-label" for="round_trip">Round trip</label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-lg me-1"></i>Log Trip</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Trip Log --}}
    <div class="col-lg-7 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold"><i class="bi bi-list-ul me-2"></i>{{ $year }} Trip Log</h5>
                <span class="badge bg-light text-dark">IRS Rate: $0.70/mile</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Route</th>
                                <th>Purpose</th>
                                <th class="text-end">Miles</th>
                                <th class="text-end">Deduction</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($summary['logs'] as $log)
                            <tr>
                                <td class="text-muted small">{{ $log->trip_date->format('m/d') }}</td>
                                <td>
                                    <small>{{ $log->start_location }} <i class="bi bi-arrow-right text-muted"></i> {{ $log->end_location }}</small>
                                    @if($log->round_trip) <span class="badge bg-light text-muted" style="font-size:0.65rem">RT</span> @endif
                                </td>
                                <td><small>{{ $log->business_purpose }}</small></td>
                                <td class="text-end">{{ number_format($log->miles, 1) }}</td>
                                <td class="text-end text-success fw-semibold">${{ number_format($log->deduction, 2) }}</td>
                                <td>
                                    <form method="POST" action="{{ url('/tax/mileage/' . $log->id . '/delete') }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Delete this trip?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No trips logged yet</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
