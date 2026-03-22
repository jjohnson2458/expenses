<?php
/**
 * Recurring Expense — Create / Edit Form
 *
 * Variables: $categories, $recurring (optional — present when editing)
 */

ob_start();

$isEdit      = isset($recurring);
$title       = $isEdit ? 'Edit Recurring Expense' : 'New Recurring Expense';
$formAction  = $isEdit ? '/recurring/' . (int) $recurring['id'] : '/recurring';
$type        = $isEdit ? ($recurring['type'] ?? 'debit') : 'debit';
$description = $isEdit ? ($recurring['description'] ?? '') : '';
$amount      = $isEdit ? ($recurring['amount'] ?? '') : '';
$categoryId  = $isEdit ? ($recurring['category_id'] ?? '') : '';
$vendor      = $isEdit ? ($recurring['vendor'] ?? '') : '';
$dayOfMonth  = $isEdit ? ($recurring['day_of_month'] ?? 1) : 1;
$isActive    = $isEdit ? ($recurring['is_active'] ?? 1) : 1;
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
                <form method="POST" action="<?= e($formAction) ?>">
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
                               value="<?= e($description) ?>" required>
                    </div>

                    <!-- Amount -->
                    <div class="mb-3">
                        <label for="amount" class="form-label fw-semibold">Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="amount" name="amount"
                                   step="0.01" min="0" value="<?= e($amount) ?>" required>
                        </div>
                    </div>

                    <!-- Category -->
                    <div class="mb-3">
                        <label for="category_id" class="form-label fw-semibold">Category</label>
                        <select class="form-select" id="category_id" name="category_id">
                            <option value="">-- Select Category --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= (int) $cat['id'] ?>"
                                    <?= (int) $categoryId === (int) $cat['id'] ? 'selected' : '' ?>>
                                    <?= e($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Vendor -->
                    <div class="mb-3">
                        <label for="vendor" class="form-label fw-semibold">Vendor</label>
                        <input type="text" class="form-control" id="vendor" name="vendor"
                               value="<?= e($vendor) ?>">
                    </div>

                    <!-- Day of Month -->
                    <div class="mb-3">
                        <label for="day_of_month" class="form-label fw-semibold">Day of Month <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="day_of_month" name="day_of_month"
                               min="1" max="31" value="<?= (int) $dayOfMonth ?>" required>
                        <div class="form-text">The day each month this expense is due (1-31).</div>
                    </div>

                    <!-- Active -->
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                   value="1" <?= $isActive ? 'checked' : '' ?>>
                            <label class="form-check-label fw-semibold" for="is_active">Active</label>
                        </div>
                        <div class="form-text">Inactive recurring expenses will not be processed.</div>
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i><?= $isEdit ? 'Update' : 'Create' ?>
                        </button>
                        <a href="/recurring" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title   = $isEdit ? 'Edit Recurring Expense' : 'New Recurring Expense';
require VIEW_PATH . '/layouts/app.php';
?>
