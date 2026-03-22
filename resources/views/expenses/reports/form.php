<?php
/**
 * Expense Reports - Create / Edit Form
 *
 * @author J.J. Johnson <visionquest716@gmail.com>
 */
ob_start();

$isEdit = isset($report) && $report !== null;
$title  = $isEdit ? 'Edit Report' : 'New Report';
$action = $isEdit ? "/reports/{$report['id']}/update" : '/reports';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">
                <i class="bi bi-<?= $isEdit ? 'pencil-square' : 'plus-circle' ?> me-2"></i>
                <?= e($title) ?>
            </h2>
            <a href="/reports" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="<?= e($action) ?>">
                    <?= csrf_field() ?>

                    <!-- Title -->
                    <div class="mb-3">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control"
                               id="title"
                               name="title"
                               value="<?= e($report['title'] ?? '') ?>"
                               required
                               autofocus
                               placeholder="e.g., March 2026 Business Expenses">
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control"
                                  id="description"
                                  name="description"
                                  rows="3"
                                  placeholder="Optional notes about this report"><?= e($report['description'] ?? '') ?></textarea>
                    </div>

                    <!-- Status -->
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <?php
                            $statuses = ['draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'rejected' => 'Rejected'];
                            $currentStatus = $report['status'] ?? 'draft';
                            foreach ($statuses as $value => $label):
                            ?>
                                <option value="<?= $value ?>" <?= $currentStatus === $value ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Date Range -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="date_from" class="form-label">Date From</label>
                            <input type="date"
                                   class="form-control"
                                   id="date_from"
                                   name="date_from"
                                   value="<?= e($report['date_from'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="date_to" class="form-label">Date To</label>
                            <input type="date"
                                   class="form-control"
                                   id="date_to"
                                   name="date_to"
                                   value="<?= e($report['date_to'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="/reports" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>
                            <?= $isEdit ? 'Update Report' : 'Create Report' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
