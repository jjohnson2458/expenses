<?php
/**
 * Admin - Error Log viewer
 */

ob_start();

$csrfToken = $_SESSION['csrf_token'] ?? '';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Error Log</h2>
    <?php if (!empty($errors)): ?>
        <form method="POST" action="/admin/errors/clear"
              onsubmit="return confirm('Are you sure you want to clear all error logs? This cannot be undone.');">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="btn btn-danger">
                <i class="bi bi-trash me-1"></i> Clear All Errors
            </button>
        </form>
    <?php endif; ?>
</div>

<?php if (empty($errors)): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
            <p class="mt-3 mb-0 fs-5 text-muted">No errors logged. Everything looks good!</p>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>Message</th>
                            <th style="width: 300px;">Context</th>
                            <th style="width: 170px;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($errors as $error): ?>
                            <tr>
                                <td class="text-muted">#<?= (int)$error['id'] ?></td>
                                <td>
                                    <span class="text-danger fw-semibold">
                                        <?= htmlspecialchars($error['message'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted small" title="<?= htmlspecialchars($error['context'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars(mb_strimwidth($error['context'] ?? '', 0, 80, '...'), ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="text-nowrap text-muted small">
                                    <?= htmlspecialchars($error['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
$title   = 'Error Log';
require __DIR__ . '/../layouts/app.php';
?>
