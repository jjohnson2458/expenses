<?php
/**
 * Expense Ledger — Index View
 *
 * Variables: $expenses, $categories, $pagination, $totals, $filters
 */

ob_start();

$q        = $filters['q'] ?? '';
$from     = $filters['from'] ?? '';
$to       = $filters['to'] ?? '';
$catFilter = $filters['category'] ?? '';

$totalDebits  = (float) ($totals['total_debits'] ?? 0);
$totalCredits = (float) ($totals['total_credits'] ?? 0);
$net          = $totalCredits - $totalDebits;
?>

<!-- Header row -->
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4 gap-2">
    <h2 class="mb-0"><i class="bi bi-receipt me-2"></i>Expense Ledger</h2>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#voiceModal">
            <i class="bi bi-mic me-1"></i>Voice Input
        </button>
        <a href="/expenses/create" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Add Expense
        </a>
    </div>
</div>

<!-- Filter bar -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" action="/expenses" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label for="filterFrom" class="form-label small mb-1">From</label>
                <input type="date" class="form-control form-control-sm" id="filterFrom" name="from"
                       value="<?= e($from) ?>">
            </div>
            <div class="col-md-2">
                <label for="filterTo" class="form-label small mb-1">To</label>
                <input type="date" class="form-control form-control-sm" id="filterTo" name="to"
                       value="<?= e($to) ?>">
            </div>
            <div class="col-md-2">
                <label for="filterCategory" class="form-label small mb-1">Category</label>
                <select class="form-select form-select-sm" id="filterCategory" name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int) $cat['id'] ?>"
                            <?= $catFilter == $cat['id'] ? 'selected' : '' ?>>
                            <?= e($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="filterSearch" class="form-label small mb-1">Search</label>
                <input type="search" class="form-control form-control-sm" id="filterSearch" name="q"
                       placeholder="Description, vendor, notes..."
                       value="<?= e($q) ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
                <a href="/expenses" class="btn btn-sm btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Totals summary cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-danger">
            <div class="card-body py-2 text-center">
                <div class="small text-muted">Total Debits</div>
                <div class="fw-bold text-danger fs-5"><?= format_currency($totalDebits) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-success">
            <div class="card-body py-2 text-center">
                <div class="small text-muted">Total Credits</div>
                <div class="fw-bold text-success fs-5"><?= format_currency($totalCredits) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-<?= $net >= 0 ? 'success' : 'danger' ?>">
            <div class="card-body py-2 text-center">
                <div class="small text-muted">Net</div>
                <div class="fw-bold text-<?= $net >= 0 ? 'success' : 'danger' ?> fs-5">
                    <?= $net >= 0 ? '+' : '-' ?><?= format_currency(abs($net)) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Expense table -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover table-striped mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 100px;">Date</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Vendor</th>
                    <th class="text-center" style="width: 90px;">Type</th>
                    <th class="text-end" style="width: 120px;">Amount</th>
                    <th>Report</th>
                    <th class="text-end" style="width: 100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($expenses)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                            No expenses found. <a href="/expenses/create">Add your first expense</a>.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($expenses as $exp): ?>
                        <?php
                        $isCredit = ($exp['type'] ?? 'debit') === 'credit';
                        $amountClass = $isCredit ? 'text-success' : 'text-danger';
                        $amountPrefix = $isCredit ? '+' : '-';
                        $typeBadge = $isCredit
                            ? '<span class="badge bg-success-subtle text-success">Credit</span>'
                            : '<span class="badge bg-danger-subtle text-danger">Debit</span>';
                        $catColor = $exp['category_color'] ?? '#6c757d';
                        ?>
                        <tr>
                            <td class="text-nowrap"><?= format_date($exp['expense_date'], 'M j, Y') ?></td>
                            <td>
                                <?= e($exp['description']) ?>
                                <?php if (!empty($exp['notes'])): ?>
                                    <i class="bi bi-chat-left-text ms-1 text-muted small"
                                       data-bs-toggle="tooltip" title="<?= e($exp['notes']) ?>"></i>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($exp['category_name'])): ?>
                                    <span class="badge" style="background-color: <?= e($catColor) ?>;">
                                        <?= e($exp['category_name']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">Uncategorized</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($exp['vendor'] ?? '') ?></td>
                            <td class="text-center"><?= $typeBadge ?></td>
                            <td class="text-end fw-semibold <?= $amountClass ?>">
                                <?= $amountPrefix ?><?= format_currency((float) $exp['amount']) ?>
                            </td>
                            <td>
                                <?php if (!empty($exp['report_title'])): ?>
                                    <a href="/reports/<?= (int) $exp['report_id'] ?>" class="text-decoration-none small">
                                        <?= e($exp['report_title']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="/expenses/<?= (int) $exp['id'] ?>/edit"
                                   class="btn btn-sm btn-outline-primary py-0 px-1"
                                   title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="/expenses/<?= (int) $exp['id'] ?>/delete"
                                      class="d-inline"
                                      onsubmit="return confirm('Delete this expense?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php require VIEW_PATH . '/partials/pagination.php'; ?>

<!-- Voice Input Modal -->
<?php require VIEW_PATH . '/partials/voice-modal.php'; ?>

<?php
$content = ob_get_clean();
$title   = 'Expenses';
require VIEW_PATH . '/layouts/app.php';
?>
