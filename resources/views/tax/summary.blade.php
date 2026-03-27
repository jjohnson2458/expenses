@extends('layouts.app')

@section('title', 'Tax Summary')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1">{{ $year }} Tax Summary</h4>
                <p class="text-muted mb-0">Schedule C overview based on your expenses</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ url('/tax/summary?year=' . ($year - 1)) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-chevron-left"></i> {{ $year - 1 }}</a>
                <a href="{{ url('/tax/summary?year=' . ($year + 1)) }}" class="btn btn-sm btn-outline-secondary">{{ $year + 1 }} <i class="bi bi-chevron-right"></i></a>
            </div>
        </div>

        {{-- Key Numbers --}}
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="text-muted small text-uppercase fw-semibold">Gross Income</div>
                        <div class="fs-4 fw-bold text-success">${{ number_format($totalIncome, 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="text-muted small text-uppercase fw-semibold">Total Expenses</div>
                        <div class="fs-4 fw-bold text-danger">${{ number_format($totalExpenses, 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="text-muted small text-uppercase fw-semibold">Net Profit</div>
                        <div class="fs-4 fw-bold {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">${{ number_format($netProfit, 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="text-muted small text-uppercase fw-semibold">SE Tax (est.)</div>
                        <div class="fs-4 fw-bold" style="color:#f6c23e">${{ number_format($selfEmploymentTax, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Schedule C Breakdown --}}
            <div class="col-lg-8 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-semibold"><i class="bi bi-file-earmark-text me-2"></i>Schedule C Line Items</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Line</th>
                                        <th>Description</th>
                                        <th class="text-end">Amount</th>
                                        <th class="text-center">Expenses</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($scheduleCData as $line)
                                    <tr>
                                        <td><span class="badge bg-primary">{{ $line->schedule_c_line }}</span></td>
                                        <td>{{ $line->schedule_c_description }}</td>
                                        <td class="text-end fw-semibold">${{ number_format($line->total_amount, 2) }}</td>
                                        <td class="text-center"><span class="badge bg-light text-dark">{{ $line->expense_count }}</span></td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">No mapped expenses for {{ $year }}</td></tr>
                                    @endforelse
                                </tbody>
                                @if($scheduleCData->count())
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="2" class="fw-bold">Total Deductible Expenses</td>
                                        <td class="text-end fw-bold">${{ number_format($totalExpenses, 2) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Additional Deductions --}}
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-semibold"><i class="bi bi-calculator me-2"></i>Additional Deductions</h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="table mb-0">
                            <tbody>
                                <tr>
                                    <td><i class="bi bi-car-front text-primary me-2"></i>Mileage Deduction</td>
                                    <td class="text-end">{{ number_format($mileageSummary['total_miles'], 1) }} miles</td>
                                    <td class="text-end fw-semibold text-success">${{ number_format($mileageSummary['total_deduction'], 2) }}</td>
                                </tr>
                                <tr>
                                    <td><i class="bi bi-house text-primary me-2"></i>Home Office Deduction</td>
                                    <td class="text-end">{{ $profile?->home_office_sqft ?? 0 }} sq ft</td>
                                    <td class="text-end fw-semibold text-success">${{ number_format($homeOfficeDeduction, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><i class="bi bi-receipt text-primary me-2"></i>1/2 SE Tax Deduction</td>
                                    <td class="text-end"></td>
                                    <td class="text-end fw-semibold text-success">${{ number_format($selfEmploymentTax / 2, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Quarterly Estimates --}}
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-semibold"><i class="bi bi-calendar-check me-2"></i>Quarterly Estimates</h5>
                    </div>
                    <div class="card-body">
                        @forelse($quarters as $q)
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div>
                                <div class="fw-semibold">Q{{ $q->quarter }} {{ $year }}</div>
                                <small class="text-muted">Due: {{ \Carbon\Carbon::parse($q->due_date)->format('M j, Y') }}</small>
                            </div>
                            <div class="text-end">
                                @if($q->paid_amount > 0)
                                    <span class="badge bg-success">Paid</span>
                                    <div class="small fw-semibold">${{ number_format($q->paid_amount, 2) }}</div>
                                @elseif(\Carbon\Carbon::parse($q->due_date)->isPast())
                                    <span class="badge bg-danger">Overdue</span>
                                @else
                                    <span class="badge bg-warning text-dark">Upcoming</span>
                                @endif
                            </div>
                        </div>
                        @empty
                        <p class="text-muted text-center mb-0">No quarterly data</p>
                        @endforelse
                    </div>
                </div>

                {{-- Quick Links --}}
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-body">
                        <a href="{{ url('/tax/profile') }}" class="btn btn-outline-primary w-100 mb-2"><i class="bi bi-building me-1"></i>Tax Profile</a>
                        <a href="{{ url('/tax/mileage') }}" class="btn btn-outline-primary w-100 mb-2"><i class="bi bi-speedometer2 me-1"></i>Mileage Log</a>
                        <a href="{{ url('/export/csv') }}" class="btn btn-outline-secondary w-100"><i class="bi bi-download me-1"></i>Export Data</a>
                    </div>
                </div>

                {{-- Disclaimer --}}
                <div class="alert alert-warning mt-3 small">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <strong>Disclaimer:</strong> VQ Money provides estimates and organization, not tax filing. Verify all figures with a qualified tax professional.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
