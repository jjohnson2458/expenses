<?php
/**
 * Pagination Partial
 *
 * Expects a $pagination array with keys:
 *   - page        (int) current page number
 *   - total_pages (int) total number of pages
 *   - per_page    (int) items per page
 *   - total       (int) total number of items
 *
 * Preserves existing query string parameters when building links.
 */

if (!isset($pagination) || $pagination['total_pages'] <= 1) {
    return;
}

$page       = (int) $pagination['page'];
$totalPages = (int) $pagination['total_pages'];
$perPage    = (int) $pagination['per_page'];
$total      = (int) $pagination['total'];

// Calculate "Showing X to Y of Z"
$from = (($page - 1) * $perPage) + 1;
$to   = min($page * $perPage, $total);

// Build base query string, removing 'page' so we can re-add it
$queryParams = $_GET;
unset($queryParams['page']);
$baseQuery = http_build_query($queryParams);
$separator = $baseQuery !== '' ? '&' : '';

/**
 * Build a pagination URL for a given page number.
 */
$pageUrl = function (int $p) use ($baseQuery, $separator): string {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $qs   = $baseQuery . $separator . 'page=' . $p;
    return htmlspecialchars($path . '?' . $qs, ENT_QUOTES, 'UTF-8');
};

/**
 * Determine which page numbers to display.
 * Shows first, last, current, and neighbors with ellipsis gaps.
 */
function paginationRange(int $current, int $last, int $neighbors = 2): array {
    $pages = [];

    for ($i = 1; $i <= $last; $i++) {
        if ($i === 1 || $i === $last || abs($i - $current) <= $neighbors) {
            $pages[] = $i;
        }
    }

    // Insert ellipsis markers (null) where there are gaps
    $result = [];
    $prev   = 0;
    foreach ($pages as $p) {
        if ($prev && $p - $prev > 1) {
            $result[] = null; // ellipsis
        }
        $result[] = $p;
        $prev     = $p;
    }

    return $result;
}

$range = paginationRange($page, $totalPages);
?>

<div class="d-flex flex-column flex-sm-row align-items-center justify-content-between mt-4 gap-2">
    <div class="pagination-info text-muted small">
        Showing <?= $from ?> to <?= $to ?> of <?= number_format($total) ?> results
    </div>

    <nav aria-label="Page navigation">
        <ul class="pagination pagination-sm mb-0">
            <!-- Previous -->
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link"
                   href="<?= $page > 1 ? $pageUrl($page - 1) : '#' ?>"
                   aria-label="Previous page"
                   <?= $page <= 1 ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>

            <?php foreach ($range as $p): ?>
                <?php if ($p === null): ?>
                    <li class="page-item disabled" aria-hidden="true">
                        <span class="page-link">&hellip;</span>
                    </li>
                <?php else: ?>
                    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                        <a class="page-link"
                           href="<?= $pageUrl($p) ?>"
                           <?= $p === $page ? 'aria-current="page"' : '' ?>>
                            <?= $p ?>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>

            <!-- Next -->
            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                <a class="page-link"
                   href="<?= $page < $totalPages ? $pageUrl($page + 1) : '#' ?>"
                   aria-label="Next page"
                   <?= $page >= $totalPages ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
</div>
