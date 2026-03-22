<?php
/**
 * Expense Reports - Index (list all reports)
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
    return "<span class=\"badge bg-{$class}\">{$label}</span>";
};
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Expense Reports</h2>
    <a href="/reports/create" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Create Report
    </a>
</div>

<?php if (empty($reports)): ?>
    <div class="card">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-file-earmark-text display-4 d-block mb-3"></i>
            <p class="mb-3">No expense reports yet.</p>
            <a href="/reports/create" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Create Your First Report
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Date Range</th>
                        <th class="text-end">Total Amount</th>
                        <th class="text-center"># Expenses</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $report): ?>
                    <tr>
                        <td>
                            <a href="/reports/<?= (int) $report['id'] ?>" class="fw-semibold text-decoration-none">
                                <?= e($report['title']) ?>
                            </a>
                        </td>
                        <td><?= $statusBadge($report['status'] ?? 'draft') ?></td>
                        <td>
                            <?php if (!empty($report['date_from']) && !empty($report['date_to'])): ?>
                                <?= format_date($report['date_from']) ?> &mdash; <?= format_date($report['date_to']) ?>
                            <?php elseif (!empty($report['date_from'])): ?>
                                From <?= format_date($report['date_from']) ?>
                            <?php elseif (!empty($report['date_to'])): ?>
                                Through <?= format_date($report['date_to']) ?>
                            <?php else: ?>
                                <span class="text-muted">&mdash;</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end fw-semibold">
                            <?= format_currency((float) ($report['calculated_total'] ?? $report['total_amount'] ?? 0)) ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark"><?= (int) ($report['expense_count'] ?? 0) ?></span>
                        </td>
                        <td>
                            <?php if (!empty($report['created_at'])): ?>
                                <?= format_date($report['created_at']) ?>
                            <?php else: ?>
                                <span class="text-muted">&mdash;</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="/reports/<?= (int) $report['id'] ?>"
                                   class="btn btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="/reports/<?= (int) $report['id'] ?>/edit"
                                   class="btn btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="/reports/<?= (int) $report['id'] ?>/delete"
                                      class="d-inline"
                                      onsubmit="return confirm('Delete this report? Linked expenses will be unlinked but not deleted.')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (isset($pagination)): ?>
        <?php require VIEW_PATH . '/partials/pagination.php'; ?>
    <?php endif; ?>
<?php endif; ?>

<?php
$content = ob_get_clean();
$title = 'Expense Reports';
require __DIR__ . '/../../layouts/app.php';
?>
