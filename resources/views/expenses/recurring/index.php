<?php
/**
 * Recurring Expenses — Index View
 *
 * Variables: $recurring
 */

ob_start();
?>

<!-- Header row -->
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4 gap-2">
    <h2 class="mb-0"><i class="bi bi-arrow-repeat me-2"></i>Recurring Expenses</h2>
    <div class="d-flex gap-2">
        <form method="POST" action="/recurring/process" class="d-inline"
              onsubmit="return confirm('Process all due recurring expenses for this month?');">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-success btn-sm">
                <i class="bi bi-play-circle me-1"></i>Process Monthly
            </button>
        </form>
        <a href="/recurring/create" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Add Recurring
        </a>
    </div>
</div>

<!-- Recurring Expenses Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Description</th>
                    <th>Category</th>
                    <th class="text-center">Type</th>
                    <th class="text-end">Amount</th>
                    <th class="text-center">Day</th>
                    <th class="text-center">Status</th>
                    <th>Last Processed</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recurring)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-arrow-repeat d-block fs-1 mb-2 opacity-25"></i>
                            No recurring expenses yet.
                            <a href="/recurring/create" class="d-block mt-2">Create your first one</a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recurring as $item): ?>
                        <?php
                        $isDebit    = ($item['type'] ?? 'debit') === 'debit';
                        $typeBadge  = $isDebit
                            ? '<span class="badge bg-danger-subtle text-danger">Debit</span>'
                            : '<span class="badge bg-success-subtle text-success">Credit</span>';
                        $amountClass = $isDebit ? 'text-danger' : 'text-success';
                        $amountPrefix = $isDebit ? '-' : '+';
                        $statusBadge = $item['is_active']
                            ? '<span class="badge bg-success-subtle text-success">Active</span>'
                            : '<span class="badge bg-secondary-subtle text-secondary">Inactive</span>';
                        ?>
                        <tr>
                            <td>
                                <?= e($item['description']) ?>
                                <?php if (!empty($item['vendor'])): ?>
                                    <span class="text-muted small d-block"><?= e($item['vendor']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($item['category_name'])): ?>
                                    <span class="badge" style="background-color: <?= e($item['category_color'] ?? '#6c757d') ?>;">
                                        <?= e($item['category_name']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">Uncategorized</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= $typeBadge ?></td>
                            <td class="text-end fw-semibold <?= $amountClass ?>">
                                <?= $amountPrefix ?><?= format_currency((float) $item['amount']) ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark"><?= (int) $item['day_of_month'] ?></span>
                            </td>
                            <td class="text-center"><?= $statusBadge ?></td>
                            <td>
                                <?php if (!empty($item['last_processed'])): ?>
                                    <?= format_date($item['last_processed'], 'M j, Y') ?>
                                <?php else: ?>
                                    <span class="text-muted small">Never</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="/recurring/<?= (int) $item['id'] ?>/edit"
                                   class="btn btn-sm btn-outline-primary py-0 px-1"
                                   title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="/recurring/<?= (int) $item['id'] ?>/delete"
                                      class="d-inline"
                                      onsubmit="return confirm('Delete this recurring expense?');">
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

<?php
$content = ob_get_clean();
$title   = 'Recurring Expenses';
require VIEW_PATH . '/layouts/app.php';
?>
