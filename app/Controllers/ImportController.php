<?php
/**
 * Import Controller — CSV/XLSX expense import
 *
 * @author J.J. Johnson <visionquest716@gmail.com>
 */

namespace App\Controllers;

use App\Models\Expense;
use App\Models\Category;

class ImportController extends Controller
{
    /**
     * Show the import form
     */
    public function index(): void
    {
        $this->requireAuth();

        $this->view('import.index');
    }

    /**
     * Process the uploaded CSV file and create expense records
     */
    public function process(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        // Validate file upload
        if (empty($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash('danger', 'Please select a valid file to import.');
            $this->redirect('/import');
            return;
        }

        $file = $_FILES['import_file'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, ['csv', 'xlsx'], true)) {
            $this->setFlash('danger', 'Only CSV and XLSX files are supported.');
            $this->redirect('/import');
            return;
        }

        if ($extension === 'xlsx') {
            $this->setFlash('danger', 'XLSX import is not yet supported. Please convert to CSV and try again.');
            $this->redirect('/import');
            return;
        }

        // Parse CSV
        $handle = fopen($file['tmp_name'], 'r');
        if ($handle === false) {
            $this->setFlash('danger', 'Unable to read the uploaded file.');
            $this->redirect('/import');
            return;
        }

        // Read header row
        $header = fgetcsv($handle);
        if ($header === false || empty($header)) {
            fclose($handle);
            $this->setFlash('danger', 'The CSV file appears to be empty.');
            $this->redirect('/import');
            return;
        }

        // Strip BOM if present
        if (isset($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        }

        // Normalize header names to lowercase for flexible matching
        $header = array_map(function ($h) {
            return strtolower(trim($h));
        }, $header);

        // Map expected columns to their indices
        $colMap = [
            'date'        => $this->findColumn($header, ['date', 'expense_date']),
            'description' => $this->findColumn($header, ['description', 'desc']),
            'category'    => $this->findColumn($header, ['category', 'cat']),
            'amount'      => $this->findColumn($header, ['amount', 'total']),
            'type'        => $this->findColumn($header, ['type']),
            'vendor'      => $this->findColumn($header, ['vendor', 'payee', 'merchant']),
            'notes'       => $this->findColumn($header, ['notes', 'memo', 'note']),
        ];

        // Require at minimum: date, description, amount
        if ($colMap['date'] === null || $colMap['description'] === null || $colMap['amount'] === null) {
            fclose($handle);
            $this->setFlash('danger', 'CSV must contain at least Date, Description, and Amount columns.');
            $this->redirect('/import');
            return;
        }

        // Build a category lookup by name (case-insensitive)
        $categoryModel = new Category();
        $categories = $categoryModel->getActive();
        $categoryLookup = [];
        foreach ($categories as $cat) {
            $categoryLookup[strtolower(trim($cat['name']))] = (int) $cat['id'];
        }

        $expenseModel = new Expense();
        $successes = 0;
        $failures  = 0;
        $errors    = [];
        $rowNum    = 1; // header was row 1

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;

            // Skip completely empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            $date        = trim($row[$colMap['date']] ?? '');
            $description = trim($row[$colMap['description']] ?? '');
            $amountRaw   = trim($row[$colMap['amount']] ?? '');
            $categoryStr = $colMap['category'] !== null ? trim($row[$colMap['category']] ?? '') : '';
            $type        = $colMap['type'] !== null ? strtolower(trim($row[$colMap['type']] ?? '')) : 'debit';
            $vendor      = $colMap['vendor'] !== null ? trim($row[$colMap['vendor']] ?? '') : '';
            $notes       = $colMap['notes'] !== null ? trim($row[$colMap['notes']] ?? '') : '';

            // Validate required fields
            if ($date === '' || $description === '' || $amountRaw === '') {
                $failures++;
                $errors[] = "Row {$rowNum}: Missing required field (date, description, or amount).";
                continue;
            }

            // Parse date — try multiple formats
            $parsedDate = $this->parseDate($date);
            if ($parsedDate === null) {
                $failures++;
                $errors[] = "Row {$rowNum}: Invalid date format '{$date}'.";
                continue;
            }

            // Parse amount — strip currency symbols and commas
            $amount = (float) str_replace([',', '$', ' '], '', $amountRaw);
            if ($amount <= 0) {
                $failures++;
                $errors[] = "Row {$rowNum}: Invalid amount '{$amountRaw}'.";
                continue;
            }

            // Normalize type
            if (!in_array($type, ['debit', 'credit'], true)) {
                $type = 'debit';
            }

            // Match category
            $categoryId = null;
            if ($categoryStr !== '') {
                $key = strtolower($categoryStr);
                $categoryId = $categoryLookup[$key] ?? null;
            }

            // Create expense record
            try {
                $expenseModel->create([
                    'description'  => $description,
                    'amount'       => $amount,
                    'expense_date' => $parsedDate,
                    'type'         => $type,
                    'category_id'  => $categoryId,
                    'vendor'       => $vendor !== '' ? $vendor : null,
                    'notes'        => $notes !== '' ? $notes : null,
                    'user_id'      => $_SESSION['user_id'],
                ]);
                $successes++;
            } catch (\Exception $ex) {
                $failures++;
                $errors[] = "Row {$rowNum}: Database error — " . $ex->getMessage();
            }
        }

        fclose($handle);

        // Build result message
        $message = "Import complete: {$successes} expense(s) imported successfully.";
        if ($failures > 0) {
            $message .= " {$failures} row(s) failed.";
            if (!empty($errors)) {
                $message .= '<br><small>' . implode('<br>', array_slice($errors, 0, 10)) . '</small>';
                if (count($errors) > 10) {
                    $message .= '<br><small>...and ' . (count($errors) - 10) . ' more error(s).</small>';
                }
            }
        }

        $flashType = $failures > 0 ? ($successes > 0 ? 'warning' : 'danger') : 'success';
        $this->setFlash($flashType, $message);
        $this->redirect('/import');
    }

    /**
     * Find a column index by trying multiple possible header names
     */
    private function findColumn(array $header, array $names): ?int
    {
        foreach ($names as $name) {
            $index = array_search($name, $header, true);
            if ($index !== false) {
                return (int) $index;
            }
        }
        return null;
    }

    /**
     * Parse a date string in various formats into Y-m-d
     */
    private function parseDate(string $date): ?string
    {
        // Try Y-m-d first (ISO)
        if (preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $date)) {
            $ts = strtotime($date);
            return $ts !== false ? date('Y-m-d', $ts) : null;
        }

        // Try m/d/Y or m-d-Y
        if (preg_match('#^(\d{1,2})[/\-](\d{1,2})[/\-](\d{2,4})$#', $date, $m)) {
            $year = strlen($m[3]) === 2 ? '20' . $m[3] : $m[3];
            $ts = mktime(0, 0, 0, (int) $m[1], (int) $m[2], (int) $year);
            return $ts !== false ? date('Y-m-d', $ts) : null;
        }

        // Fallback: let PHP try to parse it
        $ts = strtotime($date);
        return $ts !== false ? date('Y-m-d', $ts) : null;
    }
}
