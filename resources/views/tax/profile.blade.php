@extends('layouts.app')

@section('title', 'Tax Profile')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-semibold"><i class="bi bi-building me-2"></i>Tax Profile</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ url('/tax/profile') }}">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Filing Status</label>
                            <select name="filing_status" class="form-select">
                                <option value="single" {{ $profile->filing_status === 'single' ? 'selected' : '' }}>Single</option>
                                <option value="married_joint" {{ $profile->filing_status === 'married_joint' ? 'selected' : '' }}>Married Filing Jointly</option>
                                <option value="married_separate" {{ $profile->filing_status === 'married_separate' ? 'selected' : '' }}>Married Filing Separately</option>
                                <option value="head_of_household" {{ $profile->filing_status === 'head_of_household' ? 'selected' : '' }}>Head of Household</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">State</label>
                            <select name="state" class="form-select">
                                <option value="">Select State</option>
                                @foreach($states as $state)
                                    <option value="{{ $state->state_code }}" {{ $profile->state === $state->state_code ? 'selected' : '' }}>
                                        {{ $state->state_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Business Entity Type</label>
                            <select name="business_entity" class="form-select">
                                <option value="sole_prop" {{ $profile->business_entity === 'sole_prop' ? 'selected' : '' }}>Sole Proprietorship</option>
                                <option value="llc" {{ $profile->business_entity === 'llc' ? 'selected' : '' }}>LLC</option>
                                <option value="s_corp" {{ $profile->business_entity === 's_corp' ? 'selected' : '' }}>S-Corp</option>
                                <option value="c_corp" {{ $profile->business_entity === 'c_corp' ? 'selected' : '' }}>C-Corp</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Business Name</label>
                            <input type="text" name="business_name" class="form-control" value="{{ $profile->business_name }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">EIN (Employer Identification Number)</label>
                        <input type="text" name="ein" class="form-control" value="{{ $profile->ein }}" placeholder="XX-XXXXXXX" style="max-width:200px">
                    </div>

                    <hr class="my-4">
                    <h6 class="fw-bold mb-3"><i class="bi bi-speedometer2 me-2"></i>Mileage Tracking</h6>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="track_mileage" name="track_mileage" value="1" {{ $profile->track_mileage ? 'checked' : '' }}>
                        <label class="form-check-label" for="track_mileage">Enable mileage tracking (2025 IRS rate: $0.70/mile)</label>
                    </div>

                    <hr class="my-4">
                    <h6 class="fw-bold mb-3"><i class="bi bi-house me-2"></i>Home Office</h6>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="home_office" name="home_office" value="1" {{ $profile->home_office ? 'checked' : '' }}>
                        <label class="form-check-label" for="home_office">I use a home office for business</label>
                    </div>

                    <div class="row" id="homeOfficeFields" style="{{ $profile->home_office ? '' : 'display:none' }}">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Office Square Footage</label>
                            <input type="number" name="home_office_sqft" class="form-control" value="{{ $profile->home_office_sqft }}" step="0.01" style="max-width:150px">
                            <div class="form-text">Max 300 sq ft for simplified method ($5/sqft)</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Total Home Square Footage</label>
                            <input type="number" name="home_total_sqft" class="form-control" value="{{ $profile->home_total_sqft }}" step="0.01" style="max-width:150px">
                            <div class="form-text">Used for regular method percentage calculation</div>
                        </div>
                    </div>

                    @if($profile->home_office && $profile->home_office_sqft)
                        <div class="alert alert-info py-2 small">
                            <i class="bi bi-info-circle me-1"></i>
                            Simplified deduction: <strong>${{ number_format($profile->homeOfficeDeduction(), 2) }}</strong>/year
                            @if($profile->home_total_sqft)
                                | Regular method: <strong>{{ $profile->homeOfficePercentage() }}%</strong> of home expenses
                            @endif
                        </div>
                    @endif

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Tax Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('home_office').addEventListener('change', function() {
    document.getElementById('homeOfficeFields').style.display = this.checked ? '' : 'none';
});
</script>
@endpush
