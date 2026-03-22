<?php
/**
 * Category Form — Create / Edit an expense category
 */

$editing = isset($category) && $category !== null;

ob_start();
?>

<div class="row justify-content-center">
    <div class="col-lg-8 col-xl-6">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><?= $editing ? 'Edit Category' : 'New Category' ?></h2>
            <a href="/categories" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST"
                      action="<?= $editing ? '/categories/' . (int) $category['id'] : '/categories' ?>">
                    <?= csrf_field() ?>

                    <!-- Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label">
                            Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control"
                               id="name"
                               name="name"
                               required
                               maxlength="100"
                               value="<?= htmlspecialchars($editing ? $category['name'] : '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <!-- Name (Spanish) -->
                    <div class="mb-3">
                        <label for="name_es" class="form-label">Name (Spanish)</label>
                        <input type="text"
                               class="form-control"
                               id="name_es"
                               name="name_es"
                               maxlength="100"
                               value="<?= htmlspecialchars($editing ? ($category['name_es'] ?? '') : '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control"
                                  id="description"
                                  name="description"
                                  rows="3"
                                  maxlength="255"><?= htmlspecialchars($editing ? ($category['description'] ?? '') : '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <!-- Color -->
                    <div class="mb-3">
                        <label for="color" class="form-label">Color</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="color"
                                   class="form-control form-control-color"
                                   id="color"
                                   name="color"
                                   value="<?= htmlspecialchars($editing ? ($category['color'] ?? '#6c757d') : '#6c757d', ENT_QUOTES, 'UTF-8') ?>"
                                   title="Choose a category color">
                            <span class="text-muted small" id="colorHexLabel">
                                <?= htmlspecialchars($editing ? ($category['color'] ?? '#6c757d') : '#6c757d', ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>
                    </div>

                    <!-- Icon -->
                    <div class="mb-3">
                        <label for="icon" class="form-label">Icon</label>
                        <div class="input-group">
                            <span class="input-group-text" id="iconPreview">
                                <i class="bi <?= htmlspecialchars($editing ? ($category['icon'] ?? 'bi-tag') : 'bi-tag', ENT_QUOTES, 'UTF-8') ?>"></i>
                            </span>
                            <input type="text"
                                   class="form-control"
                                   id="icon"
                                   name="icon"
                                   placeholder="e.g. bi-cart, bi-house, bi-fuel-pump"
                                   value="<?= htmlspecialchars($editing ? ($category['icon'] ?? '') : '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="form-text">
                            Enter a <a href="https://icons.getbootstrap.com/" target="_blank" rel="noopener">Bootstrap Icons</a> class name (e.g. <code>bi-cart</code>).
                        </div>
                    </div>

                    <!-- Active -->
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="is_active"
                                   name="is_active"
                                   value="1"
                                   <?= ($editing ? !empty($category['is_active']) : true) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>

                    <!-- Sort Order -->
                    <div class="mb-4">
                        <label for="sort_order" class="form-label">Sort Order</label>
                        <input type="number"
                               class="form-control"
                               id="sort_order"
                               name="sort_order"
                               min="0"
                               step="1"
                               style="max-width:120px;"
                               value="<?= (int) ($editing ? ($category['sort_order'] ?? 0) : 0) ?>">
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>
                            <?= $editing ? 'Update Category' : 'Create Category' ?>
                        </button>
                        <a href="/categories" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<!-- Live preview helpers -->
<script>
$(function () {
    // Update icon preview on input change
    $('#icon').on('input', function () {
        var cls = $(this).val().trim() || 'bi-tag';
        $('#iconPreview i').attr('class', 'bi ' + cls);
    });

    // Update color hex label
    $('#color').on('input', function () {
        $('#colorHexLabel').text($(this).val());
    });
});
</script>

<?php
$content = ob_get_clean();
$title   = $editing ? 'Edit Category' : 'New Category';
require __DIR__ . '/../../layouts/app.php';
?>
