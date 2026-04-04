<!DOCTYPE html>
<html lang="{{ session('lang', 'en') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - VQ Money</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/css/app.css" rel="stylesheet">
    @stack('styles')
    <style>
        :root {
            --sidebar-bg: #1a1c2e;
            --sidebar-width: 260px;
            --primary: #4e73df;
            --success: #1cc88a;
            --danger: #e74a3b;
        }

        body {
            min-height: 100vh;
            background: #f4f6f9;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            color: #fff;
            z-index: 1040;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
        }

        .sidebar-brand {
            padding: 1.25rem 1.5rem;
            font-size: 1.35rem;
            font-weight: 700;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            display: flex;
            align-items: center;
            gap: 0.65rem;
            text-decoration: none;
            color: #fff;
        }

        .sidebar-brand:hover { color: #fff; }

        .sidebar-brand .bi {
            font-size: 1.5rem;
            color: var(--primary);
        }

        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 0.75rem 0;
        }

        .sidebar-nav .nav-link {
            color: rgba(255,255,255,0.6);
            padding: 0.6rem 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .sidebar-nav .nav-link:hover,
        .sidebar-nav .nav-link.active {
            color: #fff;
            background: rgba(255,255,255,0.05);
            border-left-color: var(--primary);
        }

        .sidebar-nav .nav-link .bi { font-size: 1.1rem; width: 1.25rem; text-align: center; }

        .sidebar-nav .nav-section {
            padding: 1rem 1.5rem 0.4rem;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(255,255,255,0.35);
            font-weight: 600;
        }

        .sidebar-nav .nav-submenu .nav-link {
            padding-left: 3rem;
            font-size: 0.85rem;
        }

        .sidebar-footer {
            border-top: 1px solid rgba(255,255,255,0.08);
            padding: 1rem 1.5rem;
        }

        .sidebar-footer .user-info {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.5);
        }

        .sidebar-footer .user-name {
            font-size: 0.9rem;
            color: #fff;
            font-weight: 500;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .top-bar {
            background: #fff;
            border-bottom: 1px solid #e3e6f0;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        .top-bar .page-title {
            font-size: 1.15rem;
            font-weight: 600;
            color: #2d3436;
            margin: 0;
        }

        .top-bar .search-form {
            max-width: 320px;
        }

        .content-wrapper {
            flex: 1;
            padding: 1.5rem;
        }

        .main-footer {
            padding: 1rem 1.5rem;
            text-align: center;
            font-size: 0.8rem;
            color: #858796;
            border-top: 1px solid #e3e6f0;
        }

        /* Language switcher */
        .lang-switcher .btn {
            font-size: 0.75rem;
            padding: 0.2rem 0.5rem;
        }

        /* Mobile */
        .sidebar-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #2d3436;
            cursor: pointer;
        }

        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.5);
                z-index: 1035;
            }
            .sidebar-overlay.show { display: block; }
            .main-content { margin-left: 0; }
            .sidebar-toggle { display: inline-block; }
        }
    </style>
</head>
<body>
    {{-- Sidebar --}}
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <nav class="sidebar" id="sidebar">
        <a href="{{ url('/dashboard') }}" class="sidebar-brand">
            <i class="bi bi-wallet2"></i>
            <span>VQ Money</span>
        </a>

        <div class="sidebar-nav">
            <a href="{{ url('/dashboard') }}" class="nav-link {{ request()->is('dashboard*') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="{{ url('/expenses') }}" class="nav-link {{ request()->is('expenses*') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i> Expenses
            </a>
            <a href="{{ url('/categories') }}" class="nav-link {{ request()->is('categories*') ? 'active' : '' }}">
                <i class="bi bi-tags"></i> Categories
            </a>
            <a href="{{ url('/reports') }}" class="nav-link {{ request()->is('reports*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-bar-graph"></i> Reports
            </a>
            <a href="{{ url('/recurring') }}" class="nav-link {{ request()->is('recurring*') ? 'active' : '' }}">
                <i class="bi bi-arrow-repeat"></i> Recurring
            </a>
            <a href="{{ url('/summary') }}" class="nav-link {{ request()->is('summary*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-line"></i> Monthly Summary
            </a>
            <a href="{{ url('/import') }}" class="nav-link {{ request()->is('import*') ? 'active' : '' }}">
                <i class="bi bi-upload"></i> Import
            </a>
            <a href="{{ url('/budgets') }}" class="nav-link {{ request()->is('budgets*') ? 'active' : '' }}">
                <i class="bi bi-piggy-bank"></i> Budgets
            </a>
            <a href="{{ url('/anomalies') }}" class="nav-link {{ request()->is('anomalies*') ? 'active' : '' }}">
                <i class="bi bi-shield-exclamation"></i> Anomalies
            </a>

            <div class="nav-section">Tax Center</div>
            <div class="nav-submenu">
                <a href="{{ url('/tax/summary') }}" class="nav-link {{ request()->is('tax/summary*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text"></i> Tax Summary
                </a>
                <a href="{{ url('/tax/mileage') }}" class="nav-link {{ request()->is('tax/mileage*') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Mileage Tracker
                </a>
                <a href="{{ url('/tax/profile') }}" class="nav-link {{ request()->is('tax/profile*') ? 'active' : '' }}">
                    <i class="bi bi-building"></i> Tax Profile
                </a>
                <a href="{{ url('/tax/quarterly') }}" class="nav-link {{ request()->is('tax/quarterly*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-check"></i> Quarterly Estimates
                </a>
                <a href="{{ url('/tax/package') }}" class="nav-link {{ request()->is('tax/package*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i> Tax Package
                </a>
            </div>

            <div class="nav-section">Export</div>
            <div class="nav-submenu">
                <a href="{{ url('/export/csv') }}" class="nav-link">
                    <i class="bi bi-filetype-csv"></i> CSV Export
                </a>
                <a href="{{ url('/export/ofx') }}" class="nav-link">
                    <i class="bi bi-file-earmark-code"></i> OFX (Open Financial)
                </a>
                <a href="{{ url('/export/qfx') }}" class="nav-link">
                    <i class="bi bi-file-earmark-code"></i> QFX (Quicken)
                </a>
                <a href="{{ url('/export/qbo') }}" class="nav-link">
                    <i class="bi bi-file-earmark-code"></i> QBO (QuickBooks)
                </a>
                <a href="{{ url('/export/quickbooks') }}" class="nav-link">
                    <i class="bi bi-file-earmark-text"></i> QuickBooks IIF
                </a>
                <a href="{{ url('/export/calendar') }}" class="nav-link">
                    <i class="bi bi-calendar-event"></i> iCal Export
                </a>
            </div>

            <div class="nav-section">Account</div>
            <a href="{{ url('/billing') }}" class="nav-link {{ request()->is('billing*') ? 'active' : '' }}">
                <i class="bi bi-credit-card"></i> Billing & Plans
            </a>
            <a href="{{ url('/settings') }}" class="nav-link {{ request()->is('settings*') ? 'active' : '' }}">
                <i class="bi bi-gear"></i> Settings
            </a>

            @if(Auth::check() && Auth::user()->is_admin)
            <a href="{{ url('/admin/errors') }}" class="nav-link {{ request()->is('admin/errors*') ? 'active' : '' }}">
                <i class="bi bi-bug"></i> Error Log
            </a>
            <a href="{{ url('/admin/token-usage') }}" class="nav-link {{ request()->is('admin/token-usage*') ? 'active' : '' }}">
                <i class="bi bi-graph-up"></i> Token Usage
            </a>
            @endif
        </div>

        <div class="sidebar-footer">
            @auth
            <div class="user-name">{{ Auth::user()->name }}</div>
            <div class="user-info">{{ Auth::user()->email }}</div>
            @endauth
            <div class="lang-switcher mt-2">
                <a href="{{ url('/language/en') }}" class="btn btn-sm {{ session('lang', 'en') === 'en' ? 'btn-light' : 'btn-outline-light' }}">EN</a>
                <a href="{{ url('/language/es') }}" class="btn btn-sm {{ session('lang', 'en') === 'es' ? 'btn-light' : 'btn-outline-light' }}">ES</a>
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <div class="main-content">
        <div class="top-bar">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            <h1 class="page-title">@yield('title', 'Dashboard')</h1>
            <div class="ms-auto d-flex align-items-center gap-3">
                <form class="search-form d-none d-md-block" action="{{ url('/expenses') }}" method="GET">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" placeholder="Search expenses..." value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i>
                        <span class="d-none d-md-inline">{{ Auth::user()->name ?? 'User' }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ url('/settings') }}"><i class="bi bi-gear me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ url('/logout') }}" method="POST">
                                @csrf
                                <button class="dropdown-item" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('flash'))
            <div class="content-wrapper pb-0" style="padding-bottom: 0 !important;">
                <div class="alert alert-{{ session('flash')['type'] ?? 'info' }} alert-dismissible fade show" role="alert">
                    {{ session('flash')['message'] ?? '' }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        <div class="content-wrapper">
            @yield('content')
        </div>

        <footer class="main-footer">
            &copy; 2026 VisionQuest Services LLC. All rights reserved.
        </footer>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/app.js"></script>
    <script>
        // Sidebar toggle for mobile
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        });
        document.getElementById('sidebarOverlay')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.remove('show');
            this.classList.remove('show');
        });

        // CSRF token for AJAX
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });
    </script>
    @stack('scripts')
</body>
</html>
