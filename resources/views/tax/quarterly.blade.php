@extends('layouts.app')

@section('title', 'Quarterly Tax Estimates')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Quarterly Tax Estimates</h4>
        <p class="text-muted mb-0">Track estimated tax payments for {{ $year }}.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ url('/tax/quarterly?year=' . ($year - 1)) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-chevron-left"></i> {{ $year - 1 }}</a>
        <a href="{{ url('/tax/quarterly?year=' . ($year + 1)) }}" class="btn btn-sm btn-outline-secondary">{{ $year + 1 }} <i class="bi bi-chevron-right"></i></a>
    </div>
</div>

{{-- Estimate Summary --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-muted small text-uppercase fw-semibold">Net Profit (YTD)</div>
                <div class="fs-4 fw-bold {{ $estimate['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                    ${{ number_format($estimate['net_profit'], 2) }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-muted small text-uppercase fw-semibold">Est. SE Tax</div>
                <div class="fs-4 fw-bold text-warning">${{ number_format($estimate['self_employment_tax'], 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-muted small text-uppercase fw-semibold">Est. Income Tax</div>
                <div class="fs-4 fw-bold text-info">${{ number_format($estimate['estimated_income_tax'], 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-muted small text-uppercase fw-semibold">Per Quarter</div>
                <div class="fs-4 fw-bold text-primary">${{ number_format($estimate['quarterly_amount'], 2) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Generate/Refresh --}}
<div class="mb-4">
    <form action="{{ url('/tax/quarterly/generate') }}" method="POST" class="d-inline">
        @csrf
        <input type="hidden" name="year" value="{{ $year }}">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-calculator me-1"></i> {{ $quarters->count() > 0 ? 'Recalculate Estimates' : 'Generate Estimates' }}
        </button>
    </form>
    <a href="{{ $irsPayUrl }}" target="_blank" class="btn btn-outline-success ms-2">
        <i class="bi bi-box-arrow-up-right me-1"></i> IRS Direct Pay
    </a>
</div>

{{-- Quarter Cards --}}
<div class="row g-3">
    @for($q = 1; $q <= 4; $q++)
    @php
        $data = $quarters[$q] ?? null;
        $isPaid = $data && $data->paid_amount > 0;
        $isOverdue = $data && !$isPaid && \Carbon\Carbon::parse($data->due_date)->isPast();
        $daysUntil = $data ? now()->diffInDays(\Carbon\Carbon::parse($data->due_date), false) : null;
        $isUpcoming = $data && !$isPaid && !$isOverdue && $daysUntil !== null && $daysUntil <= 30;
    @endphp
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100 {{ $isPaid ? 'border-start border-success border-3' : ($isOverdue ? 'border-start border-danger border-3' : ($isUpcoming ? 'border-start border-warning border-3' : '')) }}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h5 class="fw-bold mb-0">Q{{ $q }}</h5>
                    @if($isPaid)
                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Paid</span>
                    @elseif($isOverdue)
                        <span class="badge bg-danger"><i class="bi bi-exclamation-circle me-1"></i>Overdue</span>
                    @elseif($isUpcoming)
                        <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Due Soon</span>
                    @elseif($data)
                        <span class="badge bg-secondary">Upcoming</span>
                    @else
                        <span class="badge bg-light text-muted">Not Set</span>
                    @endif
                </div>

                @if($data)
                <div class="mb-2">
                    <small class="text-muted d-block">Due Date</small>
                    <span class="fw-semibold">{{ \Carbon\Carbon::parse($data->due_date)->format('M j, Y') }}</span>
                    @if(!$isPaid && $daysUntil !== null)
                        <small class="{{ $daysUntil < 0 ? 'text-danger' : ($daysUntil <= 14 ? 'text-warning' : 'text-muted') }}">
                            ({{ $daysUntil < 0 ? abs($daysUntil) . ' days overdue' : $daysUntil . ' days' }})
                        </small>
                    @endif
                </div>
                <div class="mb-2">
                    <small class="text-muted d-block">Estimated Amount</small>
                    <span class="fs-5 fw-bold">${{ number_format($data->estimated_amount, 2) }}</span>
                </div>

                @if($isPaid)
                <div class="mb-2">
                    <small class="text-muted d-block">Paid</small>
                    <span class="text-success fw-semibold">${{ number_format($data->paid_amount, 2) }}</span>
                    <small class="text-muted">on {{ \Carbon\Carbon::parse($data->paid_date)->format('M j, Y') }}</small>
                </div>
                @if($data->confirmation_number)
                <div class="mb-2">
                    <small class="text-muted d-block">Confirmation</small>
                    <code>{{ $data->confirmation_number }}</code>
                </div>
                @endif
                @else
                {{-- Mark Paid Form --}}
                <hr>
                <form action="{{ url('/tax/quarterly/' . $data->id . '/pay') }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <input type="number" name="paid_amount" class="form-control form-control-sm"
                               step="0.01" min="0" value="{{ number_format($data->estimated_amount, 2, '.', '') }}" placeholder="Amount paid">
                    </div>
                    <div class="mb-2">
                        <input type="date" name="paid_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-2">
                        <input type="text" name="confirmation_number" class="form-control form-control-sm" placeholder="Confirmation # (optional)">
                    </div>
                    <button type="submit" class="btn btn-sm btn-success w-100">
                        <i class="bi bi-check-circle me-1"></i> Mark Paid
                    </button>
                </form>
                @endif
                @else
                <p class="text-muted small">Generate estimates to populate this quarter.</p>
                @endif
            </div>
        </div>
    </div>
    @endfor
</div>

{{-- Disclaimer --}}
<div class="alert alert-warning mt-4 small">
    <i class="bi bi-exclamation-triangle me-1"></i>
    <strong>Disclaimer:</strong> These are estimates based on your current income and expenses. The income tax estimate uses a flat 22% rate as a rough approximation.
    Consult a tax professional for accurate calculations based on your full tax situation.
</div>
@endsection
