<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VQ Money - Smart Expense Tracking & Tax-Ready Reporting</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4e73df;
            --primary-dark: #224abe;
            --dark: #1a1c2e;
            --success: #1cc88a;
            --danger: #e74a3b;
            --gold: #f6c23e;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #333;
            overflow-x: hidden;
        }

        /* ── Navbar ── */
        .navbar-splash {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            padding: 1rem 0;
            transition: all 0.3s;
        }
        .navbar-splash.scrolled {
            background: rgba(26, 28, 46, 0.97);
            box-shadow: 0 2px 20px rgba(0,0,0,0.3);
            padding: 0.6rem 0;
        }
        .navbar-splash .navbar-brand {
            color: #fff;
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .navbar-splash .navbar-brand .bi { color: var(--primary); font-size: 1.6rem; }
        .navbar-splash .nav-link { color: rgba(255,255,255,0.8); font-weight: 500; transition: color 0.2s; }
        .navbar-splash .nav-link:hover { color: #fff; }
        .navbar-splash .btn-sign-in {
            border: 2px solid rgba(255,255,255,0.5);
            color: #fff;
            border-radius: 2rem;
            padding: 0.4rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        .navbar-splash .btn-sign-in:hover {
            background: #fff;
            color: var(--dark);
            border-color: #fff;
        }

        /* ── Hero ── */
        .hero {
            min-height: 100vh;
            background: url('/images/hero-banner.png') center center / cover no-repeat;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(26,28,46,0.75) 0%, rgba(45,49,84,0.55) 50%, rgba(78,115,223,0.35) 100%);
        }
        .hero-content { position: relative; z-index: 2; }
        .hero-label {
            text-transform: uppercase;
            letter-spacing: 0.2em;
            font-size: 0.9rem;
            font-weight: 700;
            color: rgba(255,255,255,0.85);
            margin-bottom: 0.75rem;
        }
        .hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            color: #fff;
            line-height: 1.15;
            margin-bottom: 1.25rem;
        }
        .hero h1 .highlight {
            background: linear-gradient(135deg, var(--success), #36d9a0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hero .lead {
            font-size: 1.25rem;
            color: rgba(255,255,255,0.75);
            max-width: 520px;
            margin-bottom: 2rem;
            line-height: 1.7;
        }
        .hero .btn-cta {
            background: linear-gradient(135deg, var(--success), #17a673);
            border: none;
            color: #fff;
            font-size: 1.1rem;
            font-weight: 700;
            padding: 0.85rem 2.5rem;
            border-radius: 3rem;
            transition: all 0.3s;
            box-shadow: 0 6px 25px rgba(28,200,138,0.4);
        }
        .hero .btn-cta:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 35px rgba(28,200,138,0.5);
            color: #fff;
        }
        .hero .btn-learn {
            color: rgba(255,255,255,0.8);
            font-weight: 600;
            font-size: 1.05rem;
            text-decoration: none;
            margin-left: 1.5rem;
            transition: color 0.2s;
        }
        .hero .btn-learn:hover { color: #fff; }
        .hero-visual {
            position: relative;
            z-index: 2;
        }
        .hero-card {
            background: rgba(255,255,255,0.07);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 1.25rem;
            padding: 2rem;
            color: #fff;
        }
        .hero-card .stat-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }
        .hero-card .stat { text-align: center; }
        .hero-card .stat-value { font-size: 1.75rem; font-weight: 700; }
        .hero-card .stat-value.green { color: var(--success); }
        .hero-card .stat-value.red { color: var(--danger); }
        .hero-card .stat-value.blue { color: var(--primary); }
        .hero-card .stat-label { font-size: 0.75rem; color: rgba(255,255,255,0.5); text-transform: uppercase; letter-spacing: 0.05em; }
        .hero-card .expense-row {
            display: flex;
            align-items: center;
            padding: 0.65rem 0;
            border-top: 1px solid rgba(255,255,255,0.08);
            font-size: 0.9rem;
        }
        .hero-card .expense-row .icon-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.75rem;
            font-size: 0.9rem;
        }
        .hero-card .expense-row .vendor { flex: 1; }
        .hero-card .expense-row .vendor small { display: block; color: rgba(255,255,255,0.4); font-size: 0.75rem; }
        .hero-card .expense-row .amount { font-weight: 600; }
        .hero-card .expense-row .amount.debit { color: var(--danger); }

        /* ── Features ── */
        .features {
            padding: 6rem 0;
            background: #f8f9fc;
        }
        .section-label {
            text-transform: uppercase;
            letter-spacing: 0.12em;
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.75rem;
        }
        .section-title {
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 1rem;
        }
        .section-subtitle {
            font-size: 1.1rem;
            color: #6c757d;
            max-width: 600px;
            margin: 0 auto 3.5rem;
        }
        .feature-card {
            background: #fff;
            border-radius: 1rem;
            padding: 2rem 1.75rem;
            height: 100%;
            border: 1px solid #e8ecf4;
            transition: all 0.3s;
        }
        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.08);
            border-color: var(--primary);
        }
        .feature-icon {
            width: 56px;
            height: 56px;
            border-radius: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1.25rem;
        }
        .feature-icon.blue { background: rgba(78,115,223,0.1); color: var(--primary); }
        .feature-icon.green { background: rgba(28,200,138,0.1); color: var(--success); }
        .feature-icon.red { background: rgba(231,74,59,0.1); color: var(--danger); }
        .feature-icon.gold { background: rgba(246,194,62,0.1); color: #d4a017; }
        .feature-card h5 { font-weight: 700; color: var(--dark); margin-bottom: 0.5rem; }
        .feature-card p { color: #6c757d; font-size: 0.95rem; margin: 0; line-height: 1.65; }

        /* ── How It Works ── */
        .how-it-works {
            padding: 6rem 0;
            background: #fff;
        }
        .step-number {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff;
            font-size: 1.25rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }
        .step h5 { font-weight: 700; color: var(--dark); margin-bottom: 0.5rem; }
        .step p { color: #6c757d; font-size: 0.95rem; }
        .step-connector {
            position: relative;
        }
        .step-connector::after {
            content: '';
            position: absolute;
            top: 24px;
            left: calc(50% + 30px);
            width: calc(100% - 60px);
            height: 2px;
            background: linear-gradient(90deg, var(--primary), rgba(78,115,223,0.2));
        }

        /* ── Pricing ── */
        .pricing {
            padding: 6rem 0;
            background: #f8f9fc;
        }
        .price-card {
            background: #fff;
            border-radius: 1rem;
            padding: 2.5rem 2rem;
            text-align: center;
            border: 2px solid #e8ecf4;
            height: 100%;
            transition: all 0.3s;
            position: relative;
        }
        .price-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.08);
        }
        .price-card.featured {
            border-color: var(--primary);
            box-shadow: 0 8px 30px rgba(78,115,223,0.15);
        }
        .price-card .badge-popular {
            position: absolute;
            top: -14px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff;
            padding: 0.3rem 1.25rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .price-card .plan-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        .price-card .price {
            font-size: 3rem;
            font-weight: 800;
            color: var(--dark);
            line-height: 1;
            margin-bottom: 0.25rem;
        }
        .price-card .price span { font-size: 1rem; font-weight: 500; color: #858796; }
        .price-card .price-annual {
            font-size: 0.85rem;
            color: var(--success);
            margin-bottom: 1.5rem;
            font-weight: 600;
        }
        .price-card .feature-list {
            list-style: none;
            padding: 0;
            margin: 0 0 2rem;
            text-align: left;
        }
        .price-card .feature-list li {
            padding: 0.45rem 0;
            font-size: 0.9rem;
            color: #5a5c69;
            display: flex;
            align-items: start;
            gap: 0.6rem;
        }
        .price-card .feature-list .bi-check-circle-fill { color: var(--success); flex-shrink: 0; margin-top: 2px; }
        .price-card .feature-list .bi-x-circle { color: #ccc; flex-shrink: 0; margin-top: 2px; }
        .price-card .btn-plan {
            width: 100%;
            padding: 0.7rem;
            border-radius: 2rem;
            font-weight: 700;
            font-size: 0.95rem;
        }
        .btn-plan-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            color: #fff;
        }
        .btn-plan-primary:hover { color: #fff; box-shadow: 0 6px 20px rgba(78,115,223,0.4); }
        .btn-plan-outline {
            border: 2px solid var(--primary);
            color: var(--primary);
            background: transparent;
        }
        .btn-plan-outline:hover { background: var(--primary); color: #fff; }

        /* ── Comparison Banner ── */
        .comparison {
            padding: 4rem 0;
            background: var(--dark);
            color: #fff;
        }
        .comparison h3 { font-weight: 800; margin-bottom: 1.5rem; }
        .comp-item {
            text-align: center;
            padding: 1.5rem 1rem;
        }
        .comp-item .comp-price {
            font-size: 2rem;
            font-weight: 800;
        }
        .comp-item .comp-price.strikethrough {
            text-decoration: line-through;
            color: rgba(255,255,255,0.3);
        }
        .comp-item .comp-price.ours { color: var(--success); }
        .comp-item .comp-name { font-size: 0.85rem; color: rgba(255,255,255,0.5); margin-top: 0.25rem; }

        /* ── CTA ── */
        .cta-section {
            padding: 6rem 0;
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            text-align: center;
            color: #fff;
        }
        .cta-section h2 { font-size: 2.5rem; font-weight: 800; margin-bottom: 1rem; }
        .cta-section p { font-size: 1.15rem; opacity: 0.85; max-width: 550px; margin: 0 auto 2rem; }
        .cta-section .btn-cta-white {
            background: #fff;
            color: var(--primary);
            font-weight: 700;
            padding: 0.85rem 2.5rem;
            border-radius: 3rem;
            font-size: 1.1rem;
            border: none;
            transition: all 0.3s;
            box-shadow: 0 6px 25px rgba(0,0,0,0.15);
        }
        .cta-section .btn-cta-white:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 35px rgba(0,0,0,0.2);
        }

        /* ── Footer ── */
        .footer-splash {
            background: var(--dark);
            color: rgba(255,255,255,0.5);
            padding: 3rem 0 2rem;
        }
        .footer-splash .footer-brand {
            color: #fff;
            font-size: 1.25rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }
        .footer-splash .footer-brand .bi { color: var(--primary); }
        .footer-splash a { color: rgba(255,255,255,0.5); text-decoration: none; transition: color 0.2s; }
        .footer-splash a:hover { color: #fff; }
        .footer-splash .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.08);
            margin-top: 2rem;
            padding-top: 1.5rem;
            font-size: 0.85rem;
        }

        /* ── Responsive ── */
        @media (max-width: 991.98px) {
            .hero h1 { font-size: 2.5rem; }
            .hero-visual { margin-top: 3rem; }
            .step-connector::after { display: none; }
        }
        @media (max-width: 575.98px) {
            .hero h1 { font-size: 2rem; }
            .hero .lead { font-size: 1.05rem; }
            .section-title { font-size: 1.75rem; }
            .hero .btn-learn { display: block; margin: 1rem 0 0 0; }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar-splash">
        <div class="container d-flex align-items-center justify-content-between">
            <a href="#" class="navbar-brand">
                <i class="bi bi-wallet2"></i> VQ Money
            </a>
            <div class="d-flex align-items-center gap-4">
                <a href="#features" class="nav-link d-none d-md-inline">Features</a>
                <a href="#pricing" class="nav-link d-none d-md-inline">Pricing</a>
                <a href="{{ url('/demo') }}" class="nav-link d-none d-md-inline">Live Demo</a>
                <a href="{{ url('/login') }}" class="btn btn-sign-in">Sign In</a>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content">
                    <p class="hero-label">VQ MONEY</p>
                    <h1>Your trusted<br><span class="highlight">accounting partner</span></h1>
                    <p class="lead">Snap a receipt, speak an expense, or forward an email. VQ Money categorizes everything, maps it to IRS tax lines, and generates your tax package automatically.</p>
                    <div class="d-flex align-items-center flex-wrap">
                        <a href="{{ url('/register') }}" class="btn btn-cta">Get Started Free</a>
                        <a href="#features" class="btn-learn"><i class="bi bi-play-circle me-1"></i> See how it works</a>
                    </div>
                </div>
                <div class="col-lg-5 offset-lg-1 hero-visual">
                    <div class="hero-card">
                        <div class="stat-row">
                            <div class="stat">
                                <div class="stat-value green">$12,480</div>
                                <div class="stat-label">Income</div>
                            </div>
                            <div class="stat">
                                <div class="stat-value red">$4,236</div>
                                <div class="stat-label">Expenses</div>
                            </div>
                            <div class="stat">
                                <div class="stat-value blue">$1,847</div>
                                <div class="stat-label">Tax Saved</div>
                            </div>
                        </div>
                        <div class="expense-row">
                            <div class="icon-circle" style="background:rgba(28,200,138,0.15)">
                                <i class="bi bi-cart" style="color:var(--success)"></i>
                            </div>
                            <div class="vendor">Amazon Web Services<small>Mar 26 &middot; Office Supplies</small></div>
                            <div class="amount debit">-$47.82</div>
                        </div>
                        <div class="expense-row">
                            <div class="icon-circle" style="background:rgba(78,115,223,0.15)">
                                <i class="bi bi-car-front" style="color:var(--primary)"></i>
                            </div>
                            <div class="vendor">Uber Business<small>Mar 25 &middot; Travel</small></div>
                            <div class="amount debit">-$32.50</div>
                        </div>
                        <div class="expense-row">
                            <div class="icon-circle" style="background:rgba(246,194,62,0.15)">
                                <i class="bi bi-cup-hot" style="color:#d4a017"></i>
                            </div>
                            <div class="vendor">Client Lunch - Olive Garden<small>Mar 24 &middot; Meals &amp; Entertainment</small></div>
                            <div class="amount debit">-$68.40</div>
                        </div>
                        <div class="expense-row">
                            <div class="icon-circle" style="background:rgba(231,74,59,0.15)">
                                <i class="bi bi-receipt" style="color:var(--danger)"></i>
                            </div>
                            <div class="vendor">Staples<small>Mar 23 &middot; Office Supplies</small></div>
                            <div class="amount debit">-$124.99</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="features" id="features">
        <div class="container text-center">
            <div class="section-label">Features</div>
            <h2 class="section-title">Everything you need. Nothing you don't.</h2>
            <p class="section-subtitle">Built for self-employed professionals, freelancers, and small business owners who want tax season to be painless.</p>

            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card">
                        <div class="feature-icon blue"><i class="bi bi-camera"></i></div>
                        <h5>Receipt OCR</h5>
                        <p>Snap a photo. AI extracts vendor, amount, date, and tax. One tap to confirm.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card">
                        <div class="feature-icon green"><i class="bi bi-mic"></i></div>
                        <h5>Voice Input</h5>
                        <p>"Lunch at Applebee's, thirty-two fifty, business meal." Hands-free expense entry.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card">
                        <div class="feature-icon gold"><i class="bi bi-envelope-arrow-down"></i></div>
                        <h5>Email Receipts</h5>
                        <p>Forward any receipt email. It's parsed, categorized, and filed automatically.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card">
                        <div class="feature-icon red"><i class="bi bi-bank"></i></div>
                        <h5>Tax-Ready</h5>
                        <p>Every expense maps to IRS Schedule C lines. One-click year-end tax package.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card">
                        <div class="feature-icon green"><i class="bi bi-graph-up-arrow"></i></div>
                        <h5>Smart Reports</h5>
                        <p>Interactive dashboards, budget tracking, and spending trends at a glance.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card">
                        <div class="feature-icon blue"><i class="bi bi-arrow-repeat"></i></div>
                        <h5>Recurring Expenses</h5>
                        <p>Set it and forget it. Monthly bills auto-post so nothing slips through.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card">
                        <div class="feature-icon gold"><i class="bi bi-tags"></i></div>
                        <h5>Smart Categories</h5>
                        <p>AI learns your spending patterns and categorizes new expenses automatically.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card">
                        <div class="feature-icon red"><i class="bi bi-file-earmark-arrow-down"></i></div>
                        <h5>Export Anywhere</h5>
                        <p>CSV, QuickBooks IIF, iCal, PDF reports. Your data goes where you need it.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="how-it-works" id="how-it-works">
        <div class="container text-center">
            <div class="section-label">How It Works</div>
            <h2 class="section-title">Three steps to tax freedom</h2>
            <p class="section-subtitle">Stop dreading tax season. VQ Money does the organizing all year so April is just a click.</p>

            <div class="row mt-5">
                <div class="col-md-4 step step-connector">
                    <div class="step-number">1</div>
                    <h5>Capture</h5>
                    <p>Snap a receipt, say it out loud, forward an email, or let your bank feed handle it. Five ways in, zero friction.</p>
                </div>
                <div class="col-md-4 step step-connector">
                    <div class="step-number">2</div>
                    <h5>Categorize</h5>
                    <p>AI reads the receipt, picks the right category, maps it to the correct IRS tax line, and files it. You just confirm.</p>
                </div>
                <div class="col-md-4 step">
                    <div class="step-number">3</div>
                    <h5>Export</h5>
                    <p>One click generates your full tax package: Schedule C summary, P&L, receipt archive, mileage log, and more.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing -->
    <section class="pricing" id="pricing">
        <div class="container text-center">
            <div class="section-label">Pricing</div>
            <h2 class="section-title">Your entire year of tax prep under $100</h2>
            <p class="section-subtitle">No contracts. No hidden fees. Cancel anytime.</p>

            <div class="row g-4 justify-content-center">
                <div class="col-md-6 col-lg-3">
                    <div class="price-card">
                        <div class="plan-name">Free</div>
                        <div class="price">$0</div>
                        <div class="price-annual">&nbsp;</div>
                        <ul class="feature-list">
                            <li><i class="bi bi-check-circle-fill"></i> Manual expense entry</li>
                            <li><i class="bi bi-check-circle-fill"></i> 25 expenses / month</li>
                            <li><i class="bi bi-check-circle-fill"></i> Basic categories</li>
                            <li><i class="bi bi-check-circle-fill"></i> CSV export</li>
                            <li><i class="bi bi-x-circle"></i> Receipt OCR</li>
                            <li><i class="bi bi-x-circle"></i> Tax mapping</li>
                            <li><i class="bi bi-x-circle"></i> Bank feeds</li>
                        </ul>
                        <a href="{{ url('/register') }}" class="btn btn-plan btn-plan-outline">Start Free</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="price-card featured">
                        <div class="badge-popular">Most Popular</div>
                        <div class="plan-name">Solo</div>
                        <div class="price">$9.99<span>/mo</span></div>
                        <div class="price-annual">$99/yr &mdash; save 17%</div>
                        <ul class="feature-list">
                            <li><i class="bi bi-check-circle-fill"></i> Unlimited expenses</li>
                            <li><i class="bi bi-check-circle-fill"></i> Receipt OCR (50/mo)</li>
                            <li><i class="bi bi-check-circle-fill"></i> Email-to-expense</li>
                            <li><i class="bi bi-check-circle-fill"></i> Voice input</li>
                            <li><i class="bi bi-check-circle-fill"></i> IRS tax line mapping</li>
                            <li><i class="bi bi-check-circle-fill"></i> Schedule C export</li>
                            <li><i class="bi bi-check-circle-fill"></i> Mileage tracker</li>
                        </ul>
                        <a href="{{ url('/register') }}" class="btn btn-plan btn-plan-primary">Get Solo</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="price-card">
                        <div class="plan-name">Pro</div>
                        <div class="price">$19.99<span>/mo</span></div>
                        <div class="price-annual">$199/yr &mdash; save 17%</div>
                        <ul class="feature-list">
                            <li><i class="bi bi-check-circle-fill"></i> Everything in Solo</li>
                            <li><i class="bi bi-check-circle-fill"></i> Unlimited OCR</li>
                            <li><i class="bi bi-check-circle-fill"></i> Bank feed integration</li>
                            <li><i class="bi bi-check-circle-fill"></i> Full tax package</li>
                            <li><i class="bi bi-check-circle-fill"></i> PDF reports</li>
                            <li><i class="bi bi-check-circle-fill"></i> Multi-tag dimensions</li>
                            <li><i class="bi bi-check-circle-fill"></i> Budgets & forecasting</li>
                        </ul>
                        <a href="{{ url('/register') }}" class="btn btn-plan btn-plan-outline">Get Pro</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="price-card">
                        <div class="plan-name">Team</div>
                        <div class="price">$7.99<span>/user</span></div>
                        <div class="price-annual">Per user, per month</div>
                        <ul class="feature-list">
                            <li><i class="bi bi-check-circle-fill"></i> Everything in Pro</li>
                            <li><i class="bi bi-check-circle-fill"></i> Multi-user accounts</li>
                            <li><i class="bi bi-check-circle-fill"></i> Approval workflows</li>
                            <li><i class="bi bi-check-circle-fill"></i> Team dashboards</li>
                            <li><i class="bi bi-check-circle-fill"></i> Role-based access</li>
                            <li><i class="bi bi-check-circle-fill"></i> Priority support</li>
                            <li><i class="bi bi-check-circle-fill"></i> API access</li>
                        </ul>
                        <a href="{{ url('/register') }}" class="btn btn-plan btn-plan-outline">Contact Sales</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Competitor Comparison -->
    <section class="comparison">
        <div class="container text-center">
            <h3>Why pay more for less?</h3>
            <div class="row justify-content-center">
                <div class="col-6 col-md comp-item">
                    <div class="comp-price strikethrough">$30</div>
                    <div class="comp-name">QuickBooks Simple Start</div>
                </div>
                <div class="col-6 col-md comp-item">
                    <div class="comp-price strikethrough">$90</div>
                    <div class="comp-name">QuickBooks Plus</div>
                </div>
                <div class="col-6 col-md comp-item">
                    <div class="comp-price strikethrough">$9</div>
                    <div class="comp-name">Expensify / user</div>
                </div>
                <div class="col-6 col-md comp-item">
                    <div class="comp-price strikethrough">$400</div>
                    <div class="comp-name">TurboTax Self-Employed</div>
                </div>
                <div class="col-12 col-md comp-item">
                    <div class="comp-price ours">$9.99</div>
                    <div class="comp-name">VQ Money Solo</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-section">
        <div class="container">
            <h2>Never dread tax season again.</h2>
            <p>Start tracking expenses for free. Upgrade when you're ready for the full tax package.</p>
            <a href="{{ url('/register') }}" class="btn btn-cta-white">Get Started Free</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer-splash">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="footer-brand"><i class="bi bi-wallet2"></i> VQ Money</div>
                    <p class="mb-0" style="font-size:0.9rem">Smart expense tracking and tax-ready reporting for self-employed professionals.</p>
                </div>
                <div class="col-md-2 mb-3">
                    <h6 class="text-white mb-2" style="font-size:0.85rem; font-weight:700;">Product</h6>
                    <div><a href="#features" style="font-size:0.85rem">Features</a></div>
                    <div><a href="#pricing" style="font-size:0.85rem">Pricing</a></div>
                    <div><a href="#how-it-works" style="font-size:0.85rem">How It Works</a></div>
                </div>
                <div class="col-md-2 mb-3">
                    <h6 class="text-white mb-2" style="font-size:0.85rem; font-weight:700;">Legal</h6>
                    <div><a href="{{ url('/terms') }}" style="font-size:0.85rem">Terms of Service</a></div>
                    <div><a href="{{ url('/privacy') }}" style="font-size:0.85rem">Privacy Policy</a></div>
                </div>
                <div class="col-md-4 mb-3">
                    <h6 class="text-white mb-2" style="font-size:0.85rem; font-weight:700;">Contact</h6>
                    <div style="font-size:0.85rem">VisionQuest Services LLC</div>
                    <div><a href="mailto:support@vqmoney.com" style="font-size:0.85rem">support@vqmoney.com</a></div>
                </div>
            </div>
            <div class="footer-bottom text-center">
                &copy; 2026 VisionQuest Services LLC. All rights reserved.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar-splash');
            navbar.classList.toggle('scrolled', window.scrollY > 50);
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>
</body>
</html>
