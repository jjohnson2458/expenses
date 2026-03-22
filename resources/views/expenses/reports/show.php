<?php
/**
 * Expense Reports - Show (single report detail)
 *
 * @author J.J. Johnson <visionquest716@gmail.com>
 */
ob_start();

$statusBadge = function (string $status): string {
    $map = [
        'draft'     => 'secondary',
        'submitted' => 'primary',
        'approved'  => 'success',
        'rejected'  => 'danger',
    ];
    $class = $map[$status] ?? 'secondary';
    $label = ucfirst($status);
    return "<span class=\"badge bg-{$class} fs-6\">{$label}</span>";
};

$typeBadge = function (?string $type): string {
    if ($type === 'credit') {
        return '<span class="badge bg-success">Credit</span>';
    }
    return '<span class="badge bg-danger">Debit</span>';
};

$reportId = (int) $report['id'];
$totalAmount = (float) ($report['total_amount'] ?? 0);

// Calculate debit/credit totals from linked expenses
$totalDebits  = 0;
$totalCredits = 0;
foreach ($linkedExpenses as $exp) {
    $amt = (float) $exp['amount'];
    if ($amt < 0) {
        $totalDebits += abs($amt);
    } else {
        $totalCredits += $amt;
    }
}
?>

<!-- Report Header -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h2 class="mb-2"><?= e($report['title']) ?></h2>
                <div class="d-flex flex-wrap align-items-center gap-3 mb-2">
                    <?= $statusBadge($report['status'] ?? 'draft') ?>
                    <?php if (!empty($report['date_from']) || !empty($report['date_to'])): ?>
                        <span class="text-muted">
                            <i class="bi bi-calendar-range me-1"></i>
                            <?php if (!empty($report['date_from']) && !empty($report['date_to'])): ?>
                                <?= format_date($report['date_from']) ?> &mdash; <?= format_date($report['date_to']) ?>
                            <?php elseif (!empty($report['date_from'])): ?>
                                From <?= format_date($report['date_from']) ?>
                            <?php else: ?>
                                Through <?= format_date($report['date_to']) ?>
                            <?php endif; ?>
                        </span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($report['description'])): ?>
                    <p class="text-muted mb-0"><?= nl2br(e($report['description'])) ?></p>
                <?php endif; ?>
            </div>
            <div class="text-end">
                <div class="text-muted small">Total Amount</div>
                <div class="fs-2 fw-bold text-primary"><?= format_currency($totalAmount) ?></div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex flex-wrap gap-2 mt-3 pt-3 border-top">
            <a href="/reports/<?= $reportId ?>/edit" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
            <a href="/reports/<?= $reportId ?>/print" class="btn btn-outline-secondary btn-sm" target="_blank">
                <i class="bi bi-printer me-1"></i> Print
            </a>
            <a href="/export/csv?report_id=<?= $reportId ?>" class="btn btn-outline-success btn-sm">
                <i class="bi bi-filetype-csv me-1"></i> Export CSV
            </a>
            <form method="POST" action="/reports/<?= $reportId ?>/delete"
                  class="d-inline"
                  onsubmit="return confirm('Delete this report? Linked expenses will be unlinked but not deleted.')">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-trash me-1"></i> Delete
                </button>
            </form>
            <a href="/reports" class="btn btn-outline-secondary btn-sm ms-auto">
                <i class="bi bi-arrow-left me-1"></i> Back to Reports
            </a>
        </div>
    </div>
</div>

<!-- Add Expense Section -->
<?php if (!empty($availableExpenses)): ?>
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Add Expense to Report</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="/reports/<?= $reportId ?>/add-expense" class="d-flex gap-2 align-items-end">
            <?= csrf_field() ?>
            <div class="flex-grow-1">
                <label for="expense_id" class="form-label">Select an expense</label>
                <select class="form-select" id="expense_id" name="expense_id" required>
                    <option value="">-- Choose an expense --</option>
                    <?php foreach ($availableExpenses as $avail): ?>
                        <option value="<?= (int) $avail['id'] ?>">
                            <?= format_date($avail['expense_date'] ?? $avail['created_at']) ?>
                            &mdash; <?= e($avail['description'] ?? 'No description') ?>
                            (<?= format_currency((float) $avail['amount']) ?>)
                            <?php if (!empty($avail['category_name'])): ?>
                                [<?= e($avail['category_name']) ?>]
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add
            </button>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Linked Expenses Table -->
<div class="card">
    <div class="card-header bg-light">
        <h5 class="mb-0">
            <i class="bi bi-receipt me-2"></i>Linked Expenses
            <span class="badge bg-secondary ms-2"><?= count($linkedExpenses) ?></span>
        </h5>
    </div>
    <?php if (empty($linkedExpenses)): ?>
        <div class="card-body text-center text-muted py-4">
            <i class="bi bi-inbox display-4 d-block mb-2"></i>
            <p>No expenses linked to this report yet.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th class="text-center">Type</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($linkedExpenses as $expense): ?>
                    <tr>
                        <td><?= format_date($expense['expense_date'] ?? $expense['created_at']) ?></td>
                        <td><?= e($expense['description'] ?? '') ?></td>
                        <td>
                            <?php
                            $catId = $expense['category_id'] ?? null;
                            echo $catId && isset($categoryMap[$catId]) ? e($categoryMap[$catId]) : '<span class="text-muted">Uncategorized</span>';
                            ?>
                        </td>
                        <td class="text-center">
                            <?= $typeBadge($expense['type'] ?? null) ?>
                        </td>
                        <td class="text-end fw-semibold">
                            <?= format_currency((float) $expense['amount']) ?>
                        </td>
                        <td class="text-end">
                            <form method="POST" action="/reports/<?= $reportId ?>/remove-expense"
                                  class="d-inline"
                                  onsubmit="return confirm('Remove this expense from the report?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="expense_id" value="<?= (int) $expense['id'] ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Remove from report">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr class="fw-bold">
                        <td colspan="4" class="text-end">Total:</td>
                        <td class="text-end"><?= format_currency($totalAmount) ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$title = $report['title'];
require __DIR__ . '/../../layouts/app.php';
?>
