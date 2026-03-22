<?php
/**
 * Dashboard - Main overview with stats, chart, recent expenses, and category breakdown
 */

ob_start();

$csrfToken      = $_SESSION['csrf_token'] ?? '';
$thisMonth      = number_format((float)($stats['total_this_month'] ?? 0), 2);
$lastMonth      = number_format((float)($stats['total_last_month'] ?? 0), 2);
$totalCredits   = number_format((float)($stats['total_credits'] ?? 0), 2);
$totalDebits    = number_format((float)($stats['total_debits'] ?? 0), 2);
$totalCount     = (int)($stats['count'] ?? 0);

// Calculate grand total for category percentage bars
$categoryGrandTotal = 0;
foreach ($byCategory as $cat) {
    $categoryGrandTotal += abs((float)$cat['total_amount']);
}
?>

<!-- ============================================================
     STAT CARDS
     ============================================================ -->
<div class="row g-3 mb-4">
    <!-- This Month -->
    <div class="col-sm-6 col-xl">
        <div class="card border-start border-primary border-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <i class="bi bi-calendar3 text-primary" style="font-size: 1.75rem;"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold">This Month</div>
                        <div class="fs-4 fw-bold">$<?= $thisMonth ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Last Month -->
    <div class="col-sm-6 col-xl">
        <div class="card border-start border-secondary border-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <i class="bi bi-calendar-minus text-secondary" style="font-size: 1.75rem;"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold">Last Month</div>
                        <div class="fs-4 fw-bold">$<?= $lastMonth ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Credits -->
    <div class="col-sm-6 col-xl">
        <div class="card border-start border-success border-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <i class="bi bi-arrow-up-circle text-success" style="font-size: 1.75rem;"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold">Total Credits</div>
                        <div class="fs-4 fw-bold text-success">$<?= $totalCredits ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Debits -->
    <div class="col-sm-6 col-xl">
        <div class="card border-start border-danger border-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <i class="bi bi-arrow-down-circle text-danger" style="font-size: 1.75rem;"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold">Total Debits</div>
                        <div class="fs-4 fw-bold text-danger">$<?= $totalDebits ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Transactions -->
    <div class="col-sm-6 col-xl">
        <div class="card border-start border-info border-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <i class="bi bi-hash text-info" style="font-size: 1.75rem;"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold">Transactions</div>
                        <div class="fs-4 fw-bold"><?= $totalCount ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     QUICK ACTIONS
     ============================================================ -->
<div class="mb-4">
    <a href="/expenses/create" class="btn btn-primary me-2">
        <i class="bi bi-plus-circle me-1"></i> Add Expense
    </a>
    <a href="/reports/create" class="btn btn-outline-secondary me-2">
        <i class="bi bi-file-earmark-plus me-1"></i> New Report
    </a>
    <a href="/import" class="btn btn-outline-secondary">
        <i class="bi bi-upload me-1"></i> Import
    </a>
</div>

<!-- ============================================================
     MONTHLY CHART
     ============================================================ -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-bar-chart me-1"></i> Monthly Expenses (Last 12 Months)
    </div>
    <div class="card-body">
        <canvas id="monthlyChart" height="100"></canvas>
    </div>
</div>

<!-- ============================================================
     RECENT EXPENSES  &  CATEGORY BREAKDOWN
     ============================================================ -->
<div class="row g-4">

    <!-- Recent Expenses -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history me-1"></i> Recent Expenses</span>
                <a href="/expenses" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentExpenses)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                        <p class="mt-2 mb-0">No expenses recorded yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Category</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentExpenses as $exp): ?>
                                    <?php
                                    $amt      = (float)$exp['amount'];
                                    $amtClass = $amt >= 0 ? 'text-success' : 'text-danger';
                                    $amtSign  = $amt >= 0 ? '+' : '';
                                    ?>
                                    <tr>
                                        <td class="text-nowrap"><?= htmlspecialchars($exp['expense_date'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($exp['description'] ?? '') ?></td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                <?= htmlspecialchars($exp['category_name'] ?? 'Uncategorized') ?>
                                            </span>
                                        </td>
                                        <td class="text-end fw-semibold <?= $amtClass ?>">
                                            <?= $amtSign ?>$<?= number_format(abs($amt), 2) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Expenses by Category -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-pie-chart me-1"></i> Expenses by Category
            </div>
            <div class="card-body">
                <?php if (empty($byCategory)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-tags" style="font-size: 2rem;"></i>
                        <p class="mt-2 mb-0">No category data available.</p>
                    </div>
                <?php else: ?>
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($byCategory as $cat): ?>
                            <?php
                            $catTotal = abs((float)$cat['total_amount']);
                            $pct      = $categoryGrandTotal > 0 ? round(($catTotal / $categoryGrandTotal) * 100) : 0;
                            $catName  = $cat['category_name'] ?? 'Uncategorized';
                            ?>
                            <li class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="fw-semibold"><?= htmlspecialchars($catName) ?></span>
                                    <span class="text-muted">$<?= number_format($catTotal, 2) ?></span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar" role="progressbar"
                                         style="width: <?= $pct ?>%"
                                         aria-valuenow="<?= $pct ?>"
                                         aria-valuemin="0"
                                         aria-valuemax="100"></div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
$title   = 'Dashboard';
require __DIR__ . '/../layouts/app.php';
?>
