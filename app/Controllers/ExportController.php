<?php
/**
 * Export Controller — CSV, QuickBooks IIF, and iCalendar exports
 *
 * @author J.J. Johnson <visionquest716@gmail.com>
 */

namespace App\Controllers;

use App\Models\Expense;
use App\Models\ExpenseReport;
use PDO;

class ExportController extends Controller
{
    /**
     * Export all expenses as CSV download
     * Supports optional date filters via GET params: from, to
     */
    public function csv(): void
    {
        $this->requireAuth();

        $expenseModel = new Expense();
        $expenses = $this->getFilteredExpenses($expenseModel);

        $filename = 'expenses_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // UTF-8 BOM for Excel compatibility
        fwrite($output, "\xEF\xBB\xBF");

        // Header row
        fputcsv($output, ['Date', 'Description', 'Category', 'Vendor', 'Type', 'Amount', 'Report', 'Notes']);

        foreach ($expenses as $row) {
            fputcsv($output, [
                $row['expense_date'] ?? '',
                $row['description'] ?? '',
                $row['category_name'] ?? '',
                $row['vendor'] ?? '',
                $row['type'] ?? 'debit',
                number_format((float) ($row['amount'] ?? 0), 2, '.', ''),
                $row['report_title'] ?? '',
                $row['notes'] ?? '',
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Export expenses in QuickBooks IIF format
     */
    public function quickbooks(): void
    {
        $this->requireAuth();

        $expenseModel = new Expense();
        $expenses = $this->getFilteredExpenses($expenseModel);

        $filename = 'expenses_quickbooks_' . date('Y-m-d') . '.iif';

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // IIF header rows
        fwrite($output, "!TRNS\tTRNSTYPE\tDATE\tACCNT\tAMOUNT\tMEMO\tNAME\n");
        fwrite($output, "!SPL\tTRNSTYPE\tDATE\tACCNT\tAMOUNT\tMEMO\tNAME\n");
        fwrite($output, "!ENDTRNS\n");

        foreach ($expenses as $row) {
            $date = date('m/d/Y', strtotime($row['expense_date']));
            $categoryAcct = 'Expenses:' . ($row['category_name'] ?: 'Uncategorized');
            $amount = (float) ($row['amount'] ?? 0);
            $memo = str_replace("\t", ' ', $row['description'] ?? '');
            $vendor = str_replace("\t", ' ', $row['vendor'] ?? '');

            // Debits are positive amounts on the expense account
            $sign = ($row['type'] ?? 'debit') === 'debit' ? 1 : -1;
            $expenseAmount = $amount * $sign;

            // TRNS line — the expense account entry
            fwrite($output, "TRNS\tGENERAL JOURNAL\t{$date}\t{$categoryAcct}\t" .
                number_format($expenseAmount, 2, '.', '') . "\t{$memo}\t{$vendor}\n");

            // SPL line — the offsetting entry (checking/bank account)
            fwrite($output, "SPL\tGENERAL JOURNAL\t{$date}\tChecking\t" .
                number_format(-$expenseAmount, 2, '.', '') . "\t{$memo}\t{$vendor}\n");

            fwrite($output, "ENDTRNS\n");
        }

        fclose($output);
        exit;
    }

    /**
     * Export expenses as iCalendar (.ics) file
     */
    public function googleCalendar(): void
    {
        $this->requireAuth();

        $expenseModel = new Expense();
        $expenses = $this->getFilteredExpenses($expenseModel);

        $filename = 'expenses_calendar_' . date('Y-m-d') . '.ics';

        header('Content-Type: text/calendar; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // iCalendar header
        fwrite($output, "BEGIN:VCALENDAR\r\n");
        fwrite($output, "VERSION:2.0\r\n");
        fwrite($output, "PRODID:-//MyExpenses//Expense Export//EN\r\n");
        fwrite($output, "CALSCALE:GREGORIAN\r\n");
        fwrite($output, "METHOD:PUBLISH\r\n");

        foreach ($expenses as $row) {
            $dtDate = date('Ymd', strtotime($row['expense_date']));
            $uid = 'expense-' . ($row['id'] ?? uniqid()) . '@myexpenses';
            $dtstamp = gmdate('Ymd\THis\Z');
            $amount = number_format((float) ($row['amount'] ?? 0), 2);
            $type = ucfirst($row['type'] ?? 'debit');
            $description = $row['description'] ?? '';
            $category = $row['category_name'] ?? 'Uncategorized';
            $vendor = $row['vendor'] ?? '';

            $summary = $this->icsEscape("{$type}: \${$amount} - {$description}");
            $descLines = [];
            $descLines[] = "Amount: \${$amount}";
            $descLines[] = "Type: {$type}";
            $descLines[] = "Category: {$category}";
            if ($vendor !== '') {
                $descLines[] = "Vendor: {$vendor}";
            }
            if (!empty($row['notes'])) {
                $descLines[] = "Notes: " . $row['notes'];
            }
            $icsDescription = $this->icsEscape(implode('\n', $descLines));

            fwrite($output, "BEGIN:VEVENT\r\n");
            fwrite($output, "UID:{$uid}\r\n");
            fwrite($output, "DTSTAMP:{$dtstamp}\r\n");
            fwrite($output, "DTSTART;VALUE=DATE:{$dtDate}\r\n");
            fwrite($output, "DTEND;VALUE=DATE:{$dtDate}\r\n");
            fwrite($output, "SUMMARY:{$summary}\r\n");
            fwrite($output, "DESCRIPTION:{$icsDescription}\r\n");
            fwrite($output, "CATEGORIES:{$this->icsEscape($category)}\r\n");
            fwrite($output, "END:VEVENT\r\n");
        }

        fwrite($output, "END:VCALENDAR\r\n");

        fclose($output);
        exit;
    }

    /**
     * Export a specific report's expenses as CSV
     */
    public function reportCsv(int $id): void
    {
        $this->requireAuth();

        $reportModel = new ExpenseReport();
        $report = $reportModel->find($id);

        if (!$report) {
            http_response_code(404);
            $this->view('pages.404');
            return;
        }

        $expenseModel = new Expense();
        $expenses = $expenseModel->getByReport($id);

        // Slug-ify the report title for the filename
        $titleSlug = preg_replace('/[^a-zA-Z0-9_-]/', '_', $report['title'] ?? 'report');
        $titleSlug = preg_replace('/_+/', '_', trim($titleSlug, '_'));
        $filename = 'report_' . $titleSlug . '_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // UTF-8 BOM
        fwrite($output, "\xEF\xBB\xBF");

        // Report metadata
        fputcsv($output, ['Report', $report['title'] ?? '']);
        fputcsv($output, ['Generated', date('Y-m-d H:i:s')]);
        fputcsv($output, []); // blank separator row

        // Header row
        fputcsv($output, ['Date', 'Description', 'Category', 'Vendor', 'Type', 'Amount', 'Notes']);

        $total = 0;
        foreach ($expenses as $row) {
            $amount = (float) ($row['amount'] ?? 0);
            $total += $amount;

            fputcsv($output, [
                $row['expense_date'] ?? '',
                $row['description'] ?? '',
                $row['category_name'] ?? '',
                $row['vendor'] ?? '',
                $row['type'] ?? 'debit',
                number_format($amount, 2, '.', ''),
                $row['notes'] ?? '',
            ]);
        }

        // Total row
        fputcsv($output, []);
        fputcsv($output, ['', '', '', '', 'TOTAL', number_format($total, 2, '.', ''), '']);

        fclose($output);
        exit;
    }

    /**
     * Fetch expenses with optional date filters, joined with category and report names
     */
    private function getFilteredExpenses(Expense $expenseModel): array
    {
        $from = $_GET['from'] ?? '';
        $to   = $_GET['to'] ?? '';

        $conditions = [];
        $params = [];

        if ($from !== '') {
            $conditions[] = "e.expense_date >= :from";
            $params['from'] = $from;
        }
        if ($to !== '') {
            $conditions[] = "e.expense_date <= :to";
            $params['to'] = $to;
        }

        $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $db = $expenseModel->getDb();

        $sql = "SELECT e.*, c.name AS category_name, r.title AS report_title
                FROM expenses e
                LEFT JOIN expense_categories c ON e.category_id = c.id
                LEFT JOIN expense_reports r ON e.report_id = r.id
                {$where}
                ORDER BY e.expense_date DESC, e.created_at DESC";

        $stmt = $db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Escape text for iCalendar format
     */
    private function icsEscape(string $text): string
    {
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace(',', '\\,', $text);
        $text = str_replace(';', '\\;', $text);
        $text = str_replace("\r\n", '\n', $text);
        $text = str_replace("\n", '\n', $text);
        return $text;
    }
}
