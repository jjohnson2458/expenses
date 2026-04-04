@extends('layouts.app')

@section('title', 'Tax Package')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Tax Package Generator</h4>
        <p class="text-muted mb-0">Download your complete tax documentation for the year.</p>
    </div>
    <div>
        <form class="d-inline" method="GET">
            <select name="year" class="form-select form-select-sm d-inline-block" style="width:auto;" onchange="this.form.submit()">
                @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </form>
    </div>
</div>

{{-- Tax Readiness Score --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="fw-semibold mb-0">Tax Readiness Score</h5>
            <span class="fs-4 fw-bold {{ $data['readiness_score'] >= 80 ? 'text-success' : ($data['readiness_score'] >= 50 ? 'text-warning' : 'text-danger') }}">
                {{ $data['readiness_score'] }}%
            </span>
        </div>
        <div class="progress" style="height: 10px;">
            @php
                $scoreColor = $data['readiness_score'] >= 80 ? 'bg-success' : ($data['readiness_score'] >= 50 ? 'bg-warning' : 'bg-danger');
            @endphp
            <div class="progress-bar {{ $scoreColor }}" style="width: {{ $data['readiness_score'] }}%"></div>
        </div>
        <small class="text-muted mt-2 d-block">
            {{ $data['receipt_count'] }} of {{ $data['expense_count'] }} expenses have receipts attached.
            @if($data['readiness_score'] < 80)
                <a href="{{ url('/expenses') }}">Attach more receipts to improve your score.</a>
            @endif
        </small>
    </div>
</div>

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-muted small text-uppercase fw-semibold">Gross Income</div>
                <div class="fs-4 fw-bold text-success">${{ number_format($data['total_income'], 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-muted small text-uppercase fw-semibold">Total Expenses</div>
                <div class="fs-4 fw-bold text-danger">${{ number_format($data['total_expenses'], 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-muted small text-uppercase fw-semibold">Net Profit</div>
                <div class="fs-4 fw-bold {{ $data['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                    ${{ number_format($data['net_profit'], 2) }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-muted small text-uppercase fw-semibold">Est. SE Tax</div>
                <div class="fs-4 fw-bold text-warning">${{ number_format($data['self_employment_tax'], 2) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Deductions Summary --}}
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="fw-semibold"><i class="bi bi-car-front me-2"></i>Mileage Deduction</h6>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">{{ number_format($data['mileage_summary']['total_miles'], 1) }} miles @ $0.70/mi</span>
                    <span class="fw-bold">${{ number_format($data['mileage_deduction'], 2) }}</span>
                </div>
                <small class="text-muted">{{ $data['mileage_summary']['trip_count'] }} trips logged</small>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="fw-semibold"><i class="bi bi-house me-2"></i>Home Office Deduction</h6>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Simplified method</span>
                    <span class="fw-bold">${{ number_format($data['home_office_deduction'], 2) }}</span>
                </div>
                @if($profile && $profile->home_office)
                <small class="text-muted">{{ number_format($profile->home_office_sqft, 0) }} sqft @ $5/sqft</small>
                @else
                <small class="text-muted"><a href="{{ url('/tax/profile') }}">Set up home office</a></small>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Download Package --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0 fw-semibold"><i class="bi bi-download me-2"></i>Download Tax Package - {{ $year }}</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border">
                    <div class="card-body">
                        <h6 class="fw-semibold"><i class="bi bi-file-earmark-spreadsheet text-success me-2"></i>Profit & Loss</h6>
                        <p class="text-muted small mb-3">Income, expenses, deductions, and net profit summary.</p>
                        <a href="{{ url('/tax/package/profit-loss?year=' . $year) }}" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-download me-1"></i> CSV
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border">
                    <div class="card-body">
                        <h6 class="fw-semibold"><i class="bi bi-file-earmark-text text-primary me-2"></i>Schedule C Summary</h6>
                        <p class="text-muted small mb-3">Expenses mapped to IRS Schedule C line items.</p>
                        <a href="{{ url('/tax/package/schedule-c?year=' . $year) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-download me-1"></i> CSV
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border">
                    <div class="card-body">
                        <h6 class="fw-semibold"><i class="bi bi-list-ul text-info me-2"></i>Category Detail</h6>
                        <p class="text-muted small mb-3">Every expense grouped by category with subtotals.</p>
                        <a href="{{ url('/tax/package/category-detail?year=' . $year) }}" class="btn btn-sm btn-outline-info">
                            <i class="bi bi-download me-1"></i> CSV
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border">
                    <div class="card-body">
                        <h6 class="fw-semibold"><i class="bi bi-car-front text-warning me-2"></i>Mileage Log</h6>
                        <p class="text-muted small mb-3">IRS-compliant mileage log with deduction amounts.</p>
                        <a href="{{ url('/tax/package/mileage?year=' . $year) }}" class="btn btn-sm btn-outline-warning">
                            <i class="bi bi-download me-1"></i> CSV
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border">
                    <div class="card-body">
                        <h6 class="fw-semibold"><i class="bi bi-house text-secondary me-2"></i>Home Office</h6>
                        <p class="text-muted small mb-3">Home office deduction calculation worksheet.</p>
                        <a href="{{ url('/tax/package/home-office?year=' . $year) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-download me-1"></i> CSV
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border">
                    <div class="card-body">
                        <h6 class="fw-semibold"><i class="bi bi-file-earmark-code text-danger me-2"></i>TurboTax Import</h6>
                        <p class="text-muted small mb-3">CSV formatted for TurboTax Self-Employed import.</p>
                        <a href="{{ url('/tax/package/turbotax?year=' . $year) }}" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-download me-1"></i> CSV
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border">
                    <div class="card-body">
                        <h6 class="fw-semibold"><i class="bi bi-file-earmark-text text-success me-2"></i>QuickBooks IIF</h6>
                        <p class="text-muted small mb-3">Full year export in QuickBooks IIF format.</p>
                        <a href="{{ url('/tax/package/quickbooks?year=' . $year) }}" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-download me-1"></i> IIF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Schedule C Breakdown --}}
@if($data['schedule_c']->count() > 0)
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0 fw-semibold">Schedule C Line Items</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Line</th>
                        <th>Description</th>
                        <th class="text-center">Expenses</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['schedule_c'] as $line)
                    <tr>
                        <td class="fw-semibold">{{ $line->schedule_c_line }}</td>
                        <td>{{ $line->schedule_c_description }}</td>
                        <td class="text-center">{{ $line->expense_count }}</td>
                        <td class="text-end">${{ number_format($line->total_amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr class="fw-bold">
                        <td colspan="3">Total</td>
                        <td class="text-end">${{ number_format($data['schedule_c']->sum('total_amount'), 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
