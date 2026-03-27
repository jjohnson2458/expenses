@extends('layouts.app')

@section('title', 'Billing & Plans')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1">Billing & Plans</h4>
                <p class="text-muted mb-0">Manage your subscription and billing details</p>
            </div>
            @if($subscription && !$subscription->canceled())
                <form method="POST" action="{{ url('/billing/portal') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-credit-card me-1"></i>Manage Payment Method
                    </button>
                </form>
            @endif
        </div>

        @if(request('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i>Your subscription is now active! Welcome aboard.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(request('canceled'))
            <div class="alert alert-warning alert-dismissible fade show">
                <i class="bi bi-info-circle me-2"></i>Checkout was canceled. No charges were made.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Current Plan Banner --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body d-flex align-items-center justify-content-between p-4">
                <div>
                    <div class="text-muted small text-uppercase fw-bold" style="letter-spacing:0.08em">Current Plan</div>
                    <h3 class="fw-bold mb-1" style="color: #1a1c2e">
                        @if($currentPlan === 'free')
                            Free
                        @elseif($currentPlan === 'solo')
                            Solo
                        @elseif($currentPlan === 'pro')
                            Pro
                        @elseif($currentPlan === 'team')
                            Team
                        @endif
                    </h3>
                    @if($onGracePeriod)
                        <span class="badge bg-warning text-dark">Cancels {{ $subscription->ends_at->format('M j, Y') }}</span>
                    @elseif($currentPlan !== 'free')
                        <span class="badge bg-success">Active</span>
                    @endif
                </div>
                <div>
                    @if($onGracePeriod)
                        <form method="POST" action="{{ url('/billing/resume') }}">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Resume Subscription
                            </button>
                        </form>
                    @elseif($currentPlan !== 'free')
                        <form method="POST" action="{{ url('/billing/cancel') }}" onsubmit="return confirm('Are you sure you want to cancel? You will retain access until the end of your billing period.')">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm">Cancel Subscription</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Billing Toggle --}}
        <div class="text-center mb-4">
            <div class="btn-group" role="group" id="billingToggle">
                <button type="button" class="btn btn-outline-primary active" data-billing="monthly">Monthly</button>
                <button type="button" class="btn btn-outline-primary" data-billing="annual">Annual <span class="badge bg-success ms-1">Save 17%</span></button>
            </div>
        </div>

        {{-- Plan Cards --}}
        <div class="row g-4 mb-4">
            {{-- Free --}}
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 {{ $currentPlan === 'free' ? 'border-primary' : '' }}" style="border-radius: 1rem;">
                    <div class="card-body text-center p-4">
                        @if($currentPlan === 'free')
                            <span class="badge bg-primary mb-2">Current Plan</span>
                        @endif
                        <h5 class="fw-bold" style="color: #1a1c2e">Free</h5>
                        <div class="my-3">
                            <span style="font-size: 2.5rem; font-weight: 800; color: #1a1c2e">$0</span>
                        </div>
                        <ul class="list-unstyled text-start mb-4" style="font-size: 0.9rem; color: #5a5c69">
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Manual entry</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>25 expenses/mo</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Basic categories</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>CSV export</li>
                            <li class="mb-2 text-muted"><i class="bi bi-x-circle me-2" style="color:#ccc"></i>Receipt OCR</li>
                            <li class="mb-2 text-muted"><i class="bi bi-x-circle me-2" style="color:#ccc"></i>Tax mapping</li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Solo --}}
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 {{ $currentPlan === 'solo' ? 'border-primary' : '' }}" style="border-radius: 1rem; {{ $currentPlan !== 'solo' ? 'border: 2px solid #4e73df;' : '' }}">
                    @if($currentPlan !== 'solo')
                        <div class="text-center" style="margin-top: -12px">
                            <span class="badge rounded-pill" style="background: linear-gradient(135deg, #4e73df, #224abe); padding: 6px 16px; font-size: 0.75rem;">Most Popular</span>
                        </div>
                    @endif
                    <div class="card-body text-center p-4">
                        @if($currentPlan === 'solo')
                            <span class="badge bg-primary mb-2">Current Plan</span>
                        @endif
                        <h5 class="fw-bold" style="color: #1a1c2e">Solo</h5>
                        <div class="my-3">
                            <span class="price-monthly" style="font-size: 2.5rem; font-weight: 800; color: #1a1c2e">$9.99</span>
                            <span class="price-annual" style="font-size: 2.5rem; font-weight: 800; color: #1a1c2e; display:none">$99</span>
                            <span class="text-muted price-monthly">/mo</span>
                            <span class="text-muted price-annual" style="display:none">/yr</span>
                        </div>
                        <ul class="list-unstyled text-start mb-4" style="font-size: 0.9rem; color: #5a5c69">
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Unlimited expenses</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Receipt OCR (50/mo)</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Email-to-expense</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Voice input</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>IRS tax mapping</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Mileage tracker</li>
                        </ul>
                        @if($currentPlan !== 'solo')
                            <form method="POST" action="{{ url($currentPlan === 'free' ? '/billing/subscribe' : '/billing/change') }}">
                                @csrf
                                <input type="hidden" name="plan" class="plan-input" data-monthly="solo_monthly" data-annual="solo_annual" value="solo_monthly">
                                <button type="submit" class="btn w-100 fw-bold" style="background: linear-gradient(135deg, #4e73df, #224abe); color: #fff; border-radius: 2rem;">
                                    {{ $currentPlan === 'free' ? 'Upgrade to Solo' : 'Switch to Solo' }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Pro --}}
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 {{ $currentPlan === 'pro' ? 'border-primary' : '' }}" style="border-radius: 1rem;">
                    <div class="card-body text-center p-4">
                        @if($currentPlan === 'pro')
                            <span class="badge bg-primary mb-2">Current Plan</span>
                        @endif
                        <h5 class="fw-bold" style="color: #1a1c2e">Pro</h5>
                        <div class="my-3">
                            <span class="price-monthly" style="font-size: 2.5rem; font-weight: 800; color: #1a1c2e">$19.99</span>
                            <span class="price-annual" style="font-size: 2.5rem; font-weight: 800; color: #1a1c2e; display:none">$199</span>
                            <span class="text-muted price-monthly">/mo</span>
                            <span class="text-muted price-annual" style="display:none">/yr</span>
                        </div>
                        <ul class="list-unstyled text-start mb-4" style="font-size: 0.9rem; color: #5a5c69">
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Everything in Solo</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Unlimited OCR</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Bank feed integration</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Full tax package</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>PDF reports</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Budgets & forecasting</li>
                        </ul>
                        @if($currentPlan !== 'pro')
                            <form method="POST" action="{{ url($currentPlan === 'free' ? '/billing/subscribe' : '/billing/change') }}">
                                @csrf
                                <input type="hidden" name="plan" class="plan-input" data-monthly="pro_monthly" data-annual="pro_annual" value="pro_monthly">
                                <button type="submit" class="btn btn-outline-primary w-100 fw-bold" style="border-radius: 2rem;">
                                    {{ $currentPlan === 'free' ? 'Upgrade to Pro' : 'Switch to Pro' }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Team --}}
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 {{ $currentPlan === 'team' ? 'border-primary' : '' }}" style="border-radius: 1rem;">
                    <div class="card-body text-center p-4">
                        @if($currentPlan === 'team')
                            <span class="badge bg-primary mb-2">Current Plan</span>
                        @endif
                        <h5 class="fw-bold" style="color: #1a1c2e">Team</h5>
                        <div class="my-3">
                            <span style="font-size: 2.5rem; font-weight: 800; color: #1a1c2e">$7.99</span>
                            <span class="text-muted">/user/mo</span>
                        </div>
                        <ul class="list-unstyled text-start mb-4" style="font-size: 0.9rem; color: #5a5c69">
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Everything in Pro</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Multi-user accounts</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Approval workflows</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Team dashboards</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Role-based access</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>API access</li>
                        </ul>
                        @if($currentPlan !== 'team')
                            <form method="POST" action="{{ url($currentPlan === 'free' ? '/billing/subscribe' : '/billing/change') }}">
                                @csrf
                                <input type="hidden" name="plan" value="team_monthly">
                                <button type="submit" class="btn btn-outline-primary w-100 fw-bold" style="border-radius: 2rem;">
                                    {{ $currentPlan === 'free' ? 'Upgrade to Team' : 'Switch to Team' }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('#billingToggle .btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('#billingToggle .btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        const billing = this.dataset.billing;
        const isAnnual = billing === 'annual';

        document.querySelectorAll('.price-monthly').forEach(el => el.style.display = isAnnual ? 'none' : '');
        document.querySelectorAll('.price-annual').forEach(el => el.style.display = isAnnual ? '' : 'none');

        document.querySelectorAll('.plan-input').forEach(input => {
            input.value = isAnnual ? input.dataset.annual : input.dataset.monthly;
        });
    });
});
</script>
@endpush
