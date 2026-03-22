<?php
/**
 * Category Index — sortable list of expense categories
 */

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Expense Categories</h2>
    <a href="/categories/create" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add Category
    </a>
</div>

<?php if (empty($categories)): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-1"></i> No categories found.
        <a href="/categories/create">Create your first category</a>.
    </div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="categoriesTable">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px"></th>
                        <th style="width:50px">Color</th>
                        <th style="width:50px">Icon</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th style="width:100px" class="text-center">Status</th>
                        <th style="width:140px" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="sortableCategories">
                    <?php foreach ($categories as $cat): ?>
                    <tr data-id="<?= (int) $cat['id'] ?>">
                        <td class="drag-handle text-center" style="cursor:grab;">
                            <i class="bi bi-grip-vertical text-muted"></i>
                        </td>
                        <td>
                            <span class="d-inline-block rounded-circle border"
                                  style="width:24px;height:24px;background:<?= htmlspecialchars($cat['color'] ?? '#6c757d', ENT_QUOTES, 'UTF-8') ?>;"
                                  title="<?= htmlspecialchars($cat['color'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></span>
                        </td>
                        <td>
                            <?php if (!empty($cat['icon'])): ?>
                                <i class="bi <?= htmlspecialchars($cat['icon'], ENT_QUOTES, 'UTF-8') ?> fs-5"></i>
                            <?php else: ?>
                                <span class="text-muted">&mdash;</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-muted small">
                            <?= htmlspecialchars($cat['description'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        </td>
                        <td class="text-center">
                            <?php if (!empty($cat['is_active'])): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a href="/categories/<?= (int) $cat['id'] ?>/edit"
                               class="btn btn-sm btn-outline-primary me-1"
                               title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="/categories/<?= (int) $cat['id'] ?>/delete"
                                  class="d-inline"
                                  onsubmit="return confirm('Delete this category?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include __DIR__ . '/../../partials/pagination.php'; ?>

    <!-- Drag-and-drop sorting via jQuery UI Sortable -->
    <script>
    $(function () {
        if (typeof $.fn.sortable === 'undefined') {
            // Load jQuery UI Sortable on demand
            $.getScript('https://cdn.jsdelivr.net/npm/jquery-ui@1.13.2/dist/jquery-ui.min.js', initSortable);
        } else {
            initSortable();
        }

        function initSortable() {
            $('#sortableCategories').sortable({
                handle: '.drag-handle',
                axis: 'y',
                cursor: 'grabbing',
                opacity: 0.8,
                update: function () {
                    var order = [];
                    $('#sortableCategories tr').each(function (index) {
                        order.push({ id: $(this).data('id'), position: index });
                    });

                    $.ajax({
                        url: '/categories/reorder',
                        method: 'POST',
                        contentType: 'application/json',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: JSON.stringify(order),
                        success: function () {
                            // Optionally show a toast
                        },
                        error: function () {
                            alert('Failed to save sort order. Please reload and try again.');
                        }
                    });
                }
            });
        }
    });
    </script>
<?php endif; ?>

<?php
$content = ob_get_clean();
$title   = 'Expense Categories';
require __DIR__ . '/../../layouts/app.php';
?>
