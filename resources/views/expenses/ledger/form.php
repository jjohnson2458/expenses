<?php
/**
 * Expense Ledger — Create / Edit Form
 *
 * Variables: $categories, $reports, $expense (optional — present when editing)
 */

ob_start();

$isEdit       = isset($expense);
$title        = $isEdit ? 'Edit Expense' : 'New Expense';
$formAction   = $isEdit ? '/expenses/' . (int) $expense['id'] : '/expenses';
$type         = $isEdit ? ($expense['type'] ?? 'debit') : ($_GET['type'] ?? 'debit');
$description  = $isEdit ? ($expense['description'] ?? '') : ($_GET['description'] ?? '');
$amount       = $isEdit ? ($expense['amount'] ?? '') : ($_GET['amount'] ?? '');
$expenseDate  = $isEdit ? ($expense['expense_date'] ?? date('Y-m-d')) : ($_GET['date'] ?? date('Y-m-d'));
$categoryId   = $isEdit ? ($expense['category_id'] ?? '') : ($_GET['category_id'] ?? '');
$vendor       = $isEdit ? ($expense['vendor'] ?? '') : '';
$reportId     = $isEdit ? ($expense['report_id'] ?? '') : '';
$notes        = $isEdit ? ($expense['notes'] ?? '') : '';
?>

<div class="row justify-content-center">
    <div class="col-lg-8 col-xl-7">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-<?= $isEdit ? 'pencil-square' : 'plus-circle' ?> me-2"></i><?= e($title) ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= e($formAction) ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <!-- Type: Debit / Credit -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Type</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="type" id="typeDebit"
                                       value="debit" <?= $type === 'debit' ? 'checked' : '' ?>>
                                <label class="form-check-label text-danger fw-semibold" for="typeDebit">
                                    <i class="bi bi-arrow-down-circle me-1"></i>Debit
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="type" id="typeCredit"
                                       value="credit" <?= $type === 'credit' ? 'checked' : '' ?>>
                                <label class="form-check-label text-success fw-semibold" for="typeCredit">
                                    <i class="bi bi-arrow-up-circle me-1"></i>Credit
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="description" name="description"
                               value="<?= e($description) ?>" required autofocus
                               placeholder="e.g. Office supplies, Client lunch">
                    </div>

                    <!-- Amount -->
                    <div class="mb-3">
                        <label for="amount" class="form-label fw-semibold">Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="amount" name="amount"
                                   step="0.01" min="0" value="<?= e((string) $amount) ?>" required
                                   placeholder="0.00">
                        </div>
                    </div>

                    <!-- Date -->
                    <div class="mb-3">
                        <label for="expense_date" class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="expense_date" name="expense_date"
                               value="<?= e($expenseDate) ?>" required>
                    </div>

                    <!-- Category -->
                    <div class="mb-3">
                        <label for="category_id" class="form-label fw-semibold">Category</label>
                        <select class="form-select" id="category_id" name="category_id">
                            <option value="">-- Select Category --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= (int) $cat['id'] ?>"
                                    <?= $categoryId == $cat['id'] ? 'selected' : '' ?>>
                                    <?= e($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Vendor -->
                    <div class="mb-3">
                        <label for="vendor" class="form-label fw-semibold">Vendor</label>
                        <input type="text" class="form-control" id="vendor" name="vendor"
                               value="<?= e($vendor) ?>"
                               placeholder="e.g. Amazon, Walmart">
                    </div>

                    <!-- Report -->
                    <div class="mb-3">
                        <label for="report_id" class="form-label fw-semibold">Report</label>
                        <select class="form-select" id="report_id" name="report_id">
                            <option value="">None</option>
                            <?php foreach ($reports as $report): ?>
                                <option value="<?= (int) $report['id'] ?>"
                                    <?= $reportId == $report['id'] ? 'selected' : '' ?>>
                                    <?= e($report['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                        <label for="notes" class="form-label fw-semibold">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"
                                  placeholder="Optional notes..."><?= e($notes) ?></textarea>
                    </div>

                    <!-- Receipt (future use) -->
                    <div class="mb-4">
                        <label for="receipt" class="form-label fw-semibold">Receipt</label>
                        <input type="file" class="form-control" id="receipt" name="receipt"
                               accept="image/*,.pdf">
                        <div class="form-text">Upload a receipt image or PDF (optional).</div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex justify-content-between">
                        <a href="/expenses" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i><?= $isEdit ? 'Update Expense' : 'Create Expense' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require VIEW_PATH . '/layouts/app.php';
?>
