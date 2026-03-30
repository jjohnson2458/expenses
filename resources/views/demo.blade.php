<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Demo - VQ Money</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" rel="preload" as="script">
    <style>
        :root {
            --primary: #4e73df;
            --primary-dark: #224abe;
            --dark: #1a1c2e;
            --success: #1cc88a;
            --danger: #e74a3b;
            --gold: #f6c23e;
        }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f0f2f5; }

        .demo-banner {
            background: linear-gradient(135deg, var(--dark) 0%, #2d3154 100%);
            color: #fff;
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .demo-banner .badge { background: var(--gold); color: var(--dark); font-weight: 700; }

        .demo-sidebar {
            background: var(--dark);
            color: rgba(255,255,255,0.7);
            border-radius: 12px;
            padding: 1.5rem 1rem;
            height: fit-content;
            position: sticky;
            top: 80px;
        }
        .demo-sidebar .nav-item {
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .demo-sidebar .nav-item.active { background: rgba(78,115,223,0.2); color: #fff; }
        .demo-sidebar .nav-section { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; color: rgba(255,255,255,0.3); padding: 0.75rem 0.75rem 0.25rem; font-weight: 700; }

        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 1.25rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .card-demo {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            overflow: hidden;
        }
        .card-demo .card-header-demo {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #eee;
            font-weight: 600;
        }
        .card-demo .card-body-demo { padding: 1.25rem; }

        .badge-debit { background: rgba(231,74,59,0.1); color: var(--danger); }
        .badge-credit { background: rgba(28,200,138,0.1); color: var(--success); }

        .feature-highlight {
            background: linear-gradient(135deg, rgba(78,115,223,0.05) 0%, rgba(28,200,138,0.05) 100%);
            border: 1px solid rgba(78,115,223,0.15);
            border-radius: 12px;
            padding: 2rem;
        }
        .feature-highlight .feature-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #fff;
        }

        .cta-bottom {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: #fff;
            border-radius: 16px;
            padding: 3rem;
            text-align: center;
        }
        .cta-bottom .btn-cta {
            background: #fff;
            color: var(--primary);
            font-weight: 700;
            padding: 0.75rem 2.5rem;
            border-radius: 50px;
            font-size: 1.1rem;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.2s;
        }
        .cta-bottom .btn-cta:hover { transform: translateY(-2px); }

        .table-demo th { font-size: 0.8rem; text-transform: uppercase; color: #888; font-weight: 600; }
        .table-demo td { vertical-align: middle; }

        @media (max-width: 991.98px) {
            .demo-sidebar { display: none; }
        }
    </style>
</head>
<body>

    <!-- Demo Banner -->
    <div class="demo-banner">
        <div class="container d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-wallet2 fs-4" style="color: var(--primary)"></i>
                <strong>VQ Money</strong>
                <span class="badge">LIVE DEMO</span>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ url('/register') }}" class="btn btn-primary btn-sm">Sign Up Free</a>
                <a href="{{ url('/login') }}" class="btn btn-outline-light btn-sm">Sign In</a>
            </div>
        </div>
    </div>

    <div class="container py-4">
        <div class="row g-4">
            <!-- Sidebar Preview -->
            <div class="col-lg-2">
                <div class="demo-sidebar">
                    <div class="nav-item active"><i class="bi bi-speedometer2"></i> Dashboard</div>
                    <div class="nav-item"><i class="bi bi-receipt"></i> Expenses</div>
                    <div class="nav-item"><i class="bi bi-tags"></i> Categories</div>
                    <div class="nav-item"><i class="bi bi-file-earmark-bar-graph"></i> Reports</div>
                    <div class="nav-item"><i class="bi bi-arrow-repeat"></i> Recurring</div>
                    <div class="nav-item"><i class="bi bi-upload"></i> Import</div>
                    <div class="nav-section">Tax Center</div>
                    <div class="nav-item"><i class="bi bi-file-earmark-text"></i> Tax Summary</div>
                    <div class="nav-item"><i class="bi bi-speedometer2"></i> Mileage</div>
                    <div class="nav-section">Export</div>
                    <div class="nav-item"><i class="bi bi-filetype-csv"></i> CSV</div>
                    <div class="nav-item"><i class="bi bi-file-earmark-code"></i> OFX / QFX / QBO</div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-10">

                <!-- Page Title -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="fw-bold mb-1">Dashboard</h4>
                        <p class="text-muted mb-0">Here's what your VQ Money dashboard looks like with real expense data.</p>
                    </div>
                    <form method="POST" action="{{ url('/demo') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-play-circle me-1"></i> Try It Live
                        </button>
                    </form>
                </div>

                <!-- Stat Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md">
                        <div class="stat-card">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon" style="background: rgba(78,115,223,0.1); color: var(--primary);">
                                    <i class="bi bi-calendar-month"></i>
                                </div>
                                <div>
                                    <div class="text-muted small text-uppercase fw-semibold">This Month</div>
                                    <div class="fs-4 fw-bold">$2,847.32</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="stat-card">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon" style="background: rgba(54,185,204,0.1); color: #36b9cc;">
                                    <i class="bi bi-calendar-check"></i>
                                </div>
                                <div>
                                    <div class="text-muted small text-uppercase fw-semibold">Last Month</div>
                                    <div class="fs-4 fw-bold">$3,124.87</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="stat-card">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon" style="background: rgba(28,200,138,0.1); color: var(--success);">
                                    <i class="bi bi-arrow-down-circle"></i>
                                </div>
                                <div>
                                    <div class="text-muted small text-uppercase fw-semibold">Total Credits</div>
                                    <div class="fs-4 fw-bold text-success">$18,450.00</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="stat-card">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon" style="background: rgba(231,74,59,0.1); color: var(--danger);">
                                    <i class="bi bi-arrow-up-circle"></i>
                                </div>
                                <div>
                                    <div class="text-muted small text-uppercase fw-semibold">Total Debits</div>
                                    <div class="fs-4 fw-bold text-danger">$12,340.56</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="stat-card">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon" style="background: rgba(246,194,62,0.1); color: var(--gold);">
                                    <i class="bi bi-hash"></i>
                                </div>
                                <div>
                                    <div class="text-muted small text-uppercase fw-semibold">Transactions</div>
                                    <div class="fs-4 fw-bold">247</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chart -->
                <div class="card-demo mb-4">
                    <div class="card-header-demo">Monthly Overview</div>
                    <div class="card-body-demo">
                        <canvas id="demoChart" height="80"></canvas>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <!-- Recent Expenses -->
                    <div class="col-lg-8">
                        <div class="card-demo">
                            <div class="card-header-demo d-flex justify-content-between align-items-center">
                                Recent Expenses
                                <span class="badge bg-primary rounded-pill">Live Data</span>
                            </div>
                            <div class="card-body-demo p-0">
                                <table class="table table-hover mb-0 table-demo">
                                    <thead>
                                        <tr><th>Date</th><th>Description</th><th>Category</th><th class="text-end">Amount</th></tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-muted small">Mar 28</td>
                                            <td>Wegmans #092</td>
                                            <td><span class="badge rounded-pill" style="background:#1cc88a">Groceries</span></td>
                                            <td class="text-end fw-semibold text-danger">-$68.42</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted small">Mar 27</td>
                                            <td>ZOOM.COM</td>
                                            <td><span class="badge rounded-pill" style="background:#4e73df">Software</span></td>
                                            <td class="text-end fw-semibold text-danger">-$16.99</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted small">Mar 27</td>
                                            <td>GoDaddy Domain Renewal</td>
                                            <td><span class="badge rounded-pill" style="background:#4e73df">Software</span></td>
                                            <td class="text-end fw-semibold text-danger">-$65.55</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted small">Mar 26</td>
                                            <td>Client Invoice #1047</td>
                                            <td><span class="badge rounded-pill" style="background:#f6c23e;color:#333">Income</span></td>
                                            <td class="text-end fw-semibold text-success">+$2,500.00</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted small">Mar 25</td>
                                            <td>Advance Auto Parts</td>
                                            <td><span class="badge rounded-pill" style="background:#e74a3b">Auto</span></td>
                                            <td class="text-end fw-semibold text-danger">-$18.35</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted small">Mar 24</td>
                                            <td>National Grid - Electric</td>
                                            <td><span class="badge rounded-pill" style="background:#858796">Utilities</span></td>
                                            <td class="text-end fw-semibold text-danger">-$294.44</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted small">Mar 24</td>
                                            <td>GEICO Auto Insurance</td>
                                            <td><span class="badge rounded-pill" style="background:#e74a3b">Insurance</span></td>
                                            <td class="text-end fw-semibold text-danger">-$145.09</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Category Breakdown -->
                    <div class="col-lg-4">
                        <div class="card-demo">
                            <div class="card-header-demo">Spending by Category</div>
                            <div class="card-body-demo">
                                <canvas id="demoCategoryChart" height="200"></canvas>
                                <hr class="my-3">
                                @php
                                $cats = [
                                    ['name' => 'Groceries', 'color' => '#1cc88a', 'total' => 847.32],
                                    ['name' => 'Software', 'color' => '#4e73df', 'total' => 612.44],
                                    ['name' => 'Utilities', 'color' => '#858796', 'total' => 548.90],
                                    ['name' => 'Auto', 'color' => '#e74a3b', 'total' => 412.18],
                                    ['name' => 'Insurance', 'color' => '#e74a3b', 'total' => 290.18],
                                    ['name' => 'Office', 'color' => '#f6c23e', 'total' => 136.30],
                                ];
                                @endphp
                                @foreach($cats as $cat)
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="small fw-semibold">
                                            <span class="d-inline-block rounded-circle me-1" style="width:10px;height:10px;background:{{ $cat['color'] }}"></span>
                                            {{ $cat['name'] }}
                                        </span>
                                        <span class="small text-muted">${{ number_format($cat['total'], 2) }}</span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Feature Highlights -->
                <h5 class="fw-bold mb-3"><i class="bi bi-stars me-2"></i>Key Features</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="feature-highlight h-100">
                            <div class="feature-icon mb-3" style="background: var(--primary);"><i class="bi bi-bank"></i></div>
                            <h6 class="fw-bold">Bank Import</h6>
                            <p class="text-muted small mb-0">Import OFX, QFX, or QBO files directly from your bank. Duplicate transactions are automatically detected and skipped.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-highlight h-100">
                            <div class="feature-icon mb-3" style="background: var(--success);"><i class="bi bi-receipt"></i></div>
                            <h6 class="fw-bold">IRS Tax Mapping</h6>
                            <p class="text-muted small mb-0">Every expense maps to Schedule C line items. Generate your full tax summary with quarterly estimates at tax time.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-highlight h-100">
                            <div class="feature-icon mb-3" style="background: var(--gold);"><i class="bi bi-car-front"></i></div>
                            <h6 class="fw-bold">Mileage Tracker</h6>
                            <p class="text-muted small mb-0">Log business trips with the IRS standard rate ($0.70/mi for 2025). Automatic round-trip calculation and deduction totals.</p>
                        </div>
                    </div>
                </div>

                <!-- Tax Summary Preview -->
                <div class="card-demo mb-4">
                    <div class="card-header-demo d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-file-earmark-text me-2"></i>Tax Summary Preview</span>
                        <span class="badge bg-warning text-dark">Schedule C</span>
                    </div>
                    <div class="card-body-demo">
                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <div class="text-muted small text-uppercase fw-semibold">Gross Income</div>
                                <div class="fs-4 fw-bold text-success">$18,450.00</div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-muted small text-uppercase fw-semibold">Total Expenses</div>
                                <div class="fs-4 fw-bold text-danger">$12,340.56</div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-muted small text-uppercase fw-semibold">Net Profit</div>
                                <div class="fs-4 fw-bold text-success">$6,109.44</div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-muted small text-uppercase fw-semibold">SE Tax (est.)</div>
                                <div class="fs-4 fw-bold" style="color: var(--gold);">$863.47</div>
                            </div>
                        </div>
                        <table class="table table-sm table-demo mb-0">
                            <thead><tr><th>Line</th><th>Description</th><th class="text-end">Amount</th></tr></thead>
                            <tbody>
                                <tr><td><span class="badge bg-secondary">8</span></td><td>Advertising</td><td class="text-end">$320.00</td></tr>
                                <tr><td><span class="badge bg-secondary">17</span></td><td>Legal & Professional Services</td><td class="text-end">$853.79</td></tr>
                                <tr><td><span class="badge bg-secondary">18</span></td><td>Office Expense</td><td class="text-end">$1,247.30</td></tr>
                                <tr><td><span class="badge bg-secondary">22</span></td><td>Supplies</td><td class="text-end">$436.82</td></tr>
                                <tr><td><span class="badge bg-secondary">25</span></td><td>Utilities</td><td class="text-end">$4,609.69</td></tr>
                                <tr><td><span class="badge bg-secondary">9</span></td><td>Car & Truck Expenses</td><td class="text-end">$2,540.03</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- CTA -->
                <div class="cta-bottom mb-4">
                    <h3 class="fw-bold mb-2">Ready to take control of your finances?</h3>
                    <p class="mb-4 opacity-75">Start free. No credit card required. Import your bank data in seconds.</p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ url('/register') }}" class="btn-cta">Get Started Free</a>
                        <form method="POST" action="{{ url('/demo') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-light btn-lg rounded-pill px-4">
                                <i class="bi bi-play-circle me-1"></i> Try Demo
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Monthly chart
        const months = ['Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar'];
        new Chart(document.getElementById('demoChart'), {
            type: 'bar',
            data: {
                labels: months,
                datasets: [
                    { label: 'Debits', data: [2840, 3124, 2956, 2712, 3045, 2847], backgroundColor: 'rgba(231,74,59,0.7)', borderColor: '#e74a3b', borderWidth: 1, borderRadius: 4 },
                    { label: 'Credits', data: [3200, 2800, 3500, 3100, 2900, 3350], backgroundColor: 'rgba(28,200,138,0.7)', borderColor: '#1cc88a', borderWidth: 1, borderRadius: 4 }
                ]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true, ticks: { callback: v => '$' + v.toLocaleString() } } }
            }
        });

        // Category donut
        new Chart(document.getElementById('demoCategoryChart'), {
            type: 'doughnut',
            data: {
                labels: ['Groceries', 'Software', 'Utilities', 'Auto', 'Insurance', 'Office'],
                datasets: [{
                    data: [847.32, 612.44, 548.90, 412.18, 290.18, 136.30],
                    backgroundColor: ['#1cc88a', '#4e73df', '#858796', '#e74a3b', '#e74a3b', '#f6c23e'],
                    borderWidth: 2, borderColor: '#fff'
                }]
            },
            options: { responsive: true, cutout: '65%', plugins: { legend: { display: false } } }
        });
    });
    </script>
</body>
</html>
