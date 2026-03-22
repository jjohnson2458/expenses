<?php
/**
 * Expense Reports - Print View (standalone, no sidebar/layout)
 *
 * @author J.J. Johnson <visionquest716@gmail.com>
 */

$reportId    = (int) $report['id'];
$totalAmount = (float) ($report['total_amount'] ?? 0);

// Calculate debit/credit totals
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
$netAmount = $totalCredits - $totalDebits;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($report['title']) ?> - MyExpenses Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: #333;
            line-height: 1.5;
            padding: 2rem;
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
        }

        /* Header */
        .report-header {
            border-bottom: 3px solid #1a1c2e;
            padding-bottom: 1.5rem;
            margin-bottom: 2rem;
        }
        .company-info {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }
        .company-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a1c2e;
        }
        .app-name {
            font-size: 0.9rem;
            color: #6c757d;
            font-weight: 400;
        }
        .report-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1a1c2e;
            margin-bottom: 0.5rem;
        }
        .report-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
            font-size: 0.95rem;
            color: #555;
        }
        .report-meta strong {
            color: #333;
        }
        .status-label {
            display: inline-block;
            padding: 0.15rem 0.6rem;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-draft     { background: #e9ecef; color: #6c757d; }
        .status-submitted { background: #cfe2ff; color: #084298; }
        .status-approved  { background: #d1e7dd; color: #0f5132; }
        .status-rejected  { background: #f8d7da; color: #842029; }

        /* Description */
        .report-description {
            margin-top: 1rem;
            padding: 0.75rem 1rem;
            background: #f8f9fa;
            border-left: 3px solid #dee2e6;
            font-size: 0.95rem;
            color: #555;
        }

        /* Table */
        .expenses-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
            font-size: 0.9rem;
        }
        .expenses-table th {
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            padding: 0.75rem 0.5rem;
            text-align: left;
            font-weight: 600;
            color: #333;
        }
        .expenses-table td {
            padding: 0.6rem 0.5rem;
            border-bottom: 1px solid #eee;
        }
        .expenses-table .text-end {
            text-align: right;
        }
        .expenses-table .text-center {
            text-align: center;
        }
        .expenses-table tbody tr:last-child td {
            border-bottom: 2px solid #dee2e6;
        }
        .type-credit {
            color: #198754;
            font-weight: 600;
        }
        .type-debit {
            color: #dc3545;
            font-weight: 600;
        }

        /* Summary */
        .report-summary {
            margin-top: 1.5rem;
            display: flex;
            justify-content: flex-end;
        }
        .summary-table {
            border-collapse: collapse;
            min-width: 280px;
        }
        .summary-table td {
            padding: 0.5rem 1rem;
            font-size: 0.95rem;
        }
        .summary-table .label {
            text-align: right;
            font-weight: 600;
            color: #555;
        }
        .summary-table .value {
            text-align: right;
            font-weight: 600;
        }
        .summary-table .total-row td {
            border-top: 2px solid #333;
            font-size: 1.1rem;
            color: #1a1c2e;
            padding-top: 0.75rem;
        }

        /* Footer */
        .print-footer {
            margin-top: 3rem;
            padding-top: 1rem;
            border-top: 1px solid #dee2e6;
            font-size: 0.8rem;
            color: #999;
            display: flex;
            justify-content: space-between;
        }

        /* Print-specific styles */
        @media print {
            body {
                padding: 0;
                font-size: 11pt;
            }
            .no-print {
                display: none !important;
            }
            .report-header {
                border-bottom-color: #000;
            }
            .expenses-table th {
                background: #f0f0f0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .status-label {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .report-description {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

    <!-- Close/Back button (hidden when printing) -->
    <div class="no-print" style="margin-bottom: 1rem;">
        <button onclick="window.print()" style="padding: 0.5rem 1rem; cursor: pointer; border: 1px solid #ccc; border-radius: 4px; background: #fff; font-size: 0.9rem;">
            Print Report
        </button>
        <a href="/reports/<?= $reportId ?>" style="padding: 0.5rem 1rem; text-decoration: none; color: #555; font-size: 0.9rem; margin-left: 0.5rem;">
            &larr; Back to Report
        </a>
    </div>

    <!-- Report Header -->
    <div class="report-header">
        <div class="company-info">
            <div>
                <div class="company-name">VisionQuest Services LLC</div>
                <div class="app-name">MyExpenses</div>
            </div>
            <div style="text-align: right; font-size: 0.85rem; color: #888;">
                Report #<?= $reportId ?>
            </div>
        </div>

        <div class="report-title"><?= e($report['title']) ?></div>

        <div class="report-meta">
            <div>
                <strong>Status:</strong>
                <span class="status-label status-<?= e($report['status'] ?? 'draft') ?>">
                    <?= ucfirst(e($report['status'] ?? 'draft')) ?>
                </span>
            </div>
            <?php if (!empty($report['date_from']) || !empty($report['date_to'])): ?>
                <div>
                    <strong>Period:</strong>
                    <?php if (!empty($report['date_from']) && !empty($report['date_to'])): ?>
                        <?= format_date($report['date_from']) ?> &mdash; <?= format_date($report['date_to']) ?>
                    <?php elseif (!empty($report['date_from'])): ?>
                        From <?= format_date($report['date_from']) ?>
                    <?php else: ?>
                        Through <?= format_date($report['date_to']) ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <div>
                <strong>Expenses:</strong> <?= count($linkedExpenses) ?>
            </div>
        </div>

        <?php if (!empty($report['description'])): ?>
            <div class="report-description"><?= nl2br(e($report['description'])) ?></div>
        <?php endif; ?>
    </div>

    <!-- Expenses Table -->
    <?php if (empty($linkedExpenses)): ?>
        <p style="text-align: center; color: #999; padding: 2rem 0;">No expenses in this report.</p>
    <?php else: ?>
        <table class="expenses-table">
            <thead>
                <tr>
                    <th style="width: 8%;">#</th>
                    <th style="width: 14%;">Date</th>
                    <th>Description</th>
                    <th style="width: 16%;">Category</th>
                    <th class="text-center" style="width: 10%;">Type</th>
                    <th class="text-end" style="width: 14%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($linkedExpenses as $i => $expense): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= format_date($expense['expense_date'] ?? $expense['created_at']) ?></td>
                    <td><?= e($expense['description'] ?? '') ?></td>
                    <td>
                        <?php
                        $catId = $expense['category_id'] ?? null;
                        echo $catId && isset($categoryMap[$catId]) ? e($categoryMap[$catId]) : 'Uncategorized';
                        ?>
                    </td>
                    <td class="text-center">
                        <?php
                        $amt = (float) $expense['amount'];
                        $type = $expense['type'] ?? ($amt < 0 ? 'debit' : 'credit');
                        ?>
                        <span class="type-<?= $type ?>">
                            <?= ucfirst($type) ?>
                        </span>
                    </td>
                    <td class="text-end"><?= format_currency((float) $expense['amount']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Summary -->
        <div class="report-summary">
            <table class="summary-table">
                <tr>
                    <td class="label">Total Debits:</td>
                    <td class="value" style="color: #dc3545;"><?= format_currency($totalDebits) ?></td>
                </tr>
                <tr>
                    <td class="label">Total Credits:</td>
                    <td class="value" style="color: #198754;"><?= format_currency($totalCredits) ?></td>
                </tr>
                <tr class="total-row">
                    <td class="label">Net:</td>
                    <td class="value"><?= format_currency($netAmount) ?></td>
                </tr>
            </table>
        </div>
    <?php endif; ?>

    <!-- Print Footer -->
    <div class="print-footer">
        <span>&copy; <?= date('Y') ?> VisionQuest Services LLC</span>
        <span>Printed on <?= date('F j, Y \a\t g:i A') ?></span>
    </div>

    <script>
        window.addEventListener('load', function() {
            window.print();
        });
    </script>
</body>
</html>
