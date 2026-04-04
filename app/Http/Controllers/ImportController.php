<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImportController extends Controller
{
    public function index()
    {
        $categories = Category::active()->forUser()->orderBy('name')->get();
        return view('import.index', compact('categories'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|max:5120',
        ]);

        $file = $request->file('import_file');
        $ext = strtolower($file->getClientOriginalExtension());

        if (in_array($ext, ['ofx', 'qfx', 'qbo'])) {
            return $this->processOfx($file, $request);
        }

        if (in_array($ext, ['csv', 'txt'])) {
            return $this->processCsv($file, $request);
        }

        return redirect('/import')->with('flash', [
            'type' => 'danger',
            'message' => "Unsupported file type: .{$ext}. Please upload CSV, OFX, QFX, or QBO.",
        ]);
    }

    private function processOfx($file, Request $request)
    {
        $content = file_get_contents($file->getPathname());
        if ($content === false) {
            return redirect('/import')->with('flash', ['type' => 'danger', 'message' => 'Unable to read the uploaded file.']);
        }

        $transactions = $this->parseOfxTransactions($content);
        if (empty($transactions)) {
            return redirect('/import')->with('flash', ['type' => 'danger', 'message' => 'No transactions found in the file.']);
        }

        $defaultCategoryId = $request->input('category_id') ?: null;
        $userId = Auth::id();

        // Gather existing FITIDs for this user to skip duplicates
        $existingFitIds = Expense::where('user_id', $userId)
            ->whereNotNull('fitid')
            ->pluck('fitid')
            ->flip()
            ->toArray();

        $successes = 0;
        $skipped = 0;
        $failures = 0;
        $errors = [];

        foreach ($transactions as $i => $txn) {
            $rowLabel = $i + 1;

            // Skip duplicates by FITID
            if (!empty($txn['fitid']) && isset($existingFitIds[$txn['fitid']])) {
                $skipped++;
                continue;
            }

            $parsedDate = $this->parseOfxDate($txn['date'] ?? '');
            if ($parsedDate === null) {
                $failures++;
                $errors[] = "Transaction {$rowLabel}: Invalid date '{$txn['date']}'.";
                continue;
            }

            $amount = abs((float) $txn['amount']);
            if ($amount == 0) {
                $failures++;
                $errors[] = "Transaction {$rowLabel}: Zero amount.";
                continue;
            }

            $type = ((float) $txn['amount'] >= 0) ? 'credit' : 'debit';
            $description = trim($txn['name'] ?? 'Unknown');
            $memo = trim($txn['memo'] ?? '');

            try {
                Expense::create([
                    'description' => $description,
                    'amount' => $amount,
                    'expense_date' => $parsedDate,
                    'type' => $type,
                    'category_id' => $defaultCategoryId,
                    'notes' => $memo !== '' ? $memo : null,
                    'fitid' => $txn['fitid'] ?? null,
                    'user_id' => $userId,
                ]);
                $successes++;
            } catch (\Exception $ex) {
                $failures++;
                $errors[] = "Transaction {$rowLabel}: " . $ex->getMessage();
            }
        }

        $message = "Import complete: {$successes} transaction(s) imported.";
        if ($skipped > 0) $message .= " {$skipped} duplicate(s) skipped.";
        if ($failures > 0) {
            $message .= " {$failures} failed.";
            if (!empty($errors)) {
                $message .= '<br><small>' . implode('<br>', array_slice($errors, 0, 10)) . '</small>';
            }
        }

        $flashType = $failures > 0 ? ($successes > 0 ? 'warning' : 'danger') : 'success';
        return redirect('/import')->with('flash', ['type' => $flashType, 'message' => $message]);
    }

    private function parseOfxTransactions(string $content): array
    {
        $transactions = [];

        // Split on STMTTRN blocks
        preg_match_all('/<STMTTRN>(.*?)<\/STMTTRN>/si', $content, $matches);
        if (empty($matches[1])) return [];

        foreach ($matches[1] as $block) {
            $txn = [];
            $txn['type'] = $this->extractOfxValue($block, 'TRNTYPE');
            $txn['date'] = $this->extractOfxValue($block, 'DTPOSTED');
            $txn['amount'] = $this->extractOfxValue($block, 'TRNAMT');
            $txn['fitid'] = $this->extractOfxValue($block, 'FITID');
            $txn['name'] = $this->extractOfxValue($block, 'NAME');
            $txn['memo'] = $this->extractOfxValue($block, 'MEMO');
            $transactions[] = $txn;
        }

        return $transactions;
    }

    private function extractOfxValue(string $block, string $tag): string
    {
        // OFX SGML: <TAG>value (no closing tag, value ends at next < or newline)
        if (preg_match('/<' . preg_quote($tag, '/') . '>([^<\r\n]+)/i', $block, $m)) {
            return trim($m[1]);
        }
        return '';
    }

    private function parseOfxDate(string $date): ?string
    {
        // OFX dates: YYYYMMDD or YYYYMMDDHHMMSS or YYYYMMDDHHMMSS.XXX
        if (preg_match('/^(\d{4})(\d{2})(\d{2})/', $date, $m)) {
            $ts = mktime(0, 0, 0, (int) $m[2], (int) $m[3], (int) $m[1]);
            return $ts !== false ? date('Y-m-d', $ts) : null;
        }
        return null;
    }

    private function processCsv($file, Request $request)
    {
        $handle = fopen($file->getPathname(), 'r');

        if ($handle === false) {
            return redirect('/import')->with('flash', ['type' => 'danger', 'message' => 'Unable to read the uploaded file.']);
        }

        $header = fgetcsv($handle);
        if ($header === false || empty($header)) {
            fclose($handle);
            return redirect('/import')->with('flash', ['type' => 'danger', 'message' => 'The CSV file appears to be empty.']);
        }

        if (isset($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        }

        $header = array_map(fn($h) => strtolower(trim($h)), $header);

        $colMap = [
            'date' => $this->findColumn($header, ['date', 'expense_date']),
            'description' => $this->findColumn($header, ['description', 'desc']),
            'category' => $this->findColumn($header, ['category', 'cat']),
            'amount' => $this->findColumn($header, ['amount', 'total']),
            'type' => $this->findColumn($header, ['type', 'transaction type']),
            'vendor' => $this->findColumn($header, ['vendor', 'payee', 'merchant']),
            'notes' => $this->findColumn($header, ['notes', 'memo', 'note']),
        ];

        if ($colMap['date'] === null || $colMap['description'] === null || $colMap['amount'] === null) {
            fclose($handle);
            return redirect('/import')->with('flash', ['type' => 'danger', 'message' => 'CSV must contain at least Date, Description, and Amount columns.']);
        }

        $categoryLookup = [];
        foreach (Category::active()->forUser()->get() as $cat) {
            $categoryLookup[strtolower(trim($cat->name))] = $cat->id;
        }

        $successes = 0;
        $failures = 0;
        $errors = [];
        $rowNum = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (empty(array_filter($row))) continue;

            $date = trim($row[$colMap['date']] ?? '');
            $description = trim($row[$colMap['description']] ?? '');
            $amountRaw = trim($row[$colMap['amount']] ?? '');
            $categoryStr = $colMap['category'] !== null ? trim($row[$colMap['category']] ?? '') : '';
            $type = $colMap['type'] !== null ? strtolower(trim($row[$colMap['type']] ?? '')) : 'debit';
            $vendor = $colMap['vendor'] !== null ? trim($row[$colMap['vendor']] ?? '') : '';
            $notes = $colMap['notes'] !== null ? trim($row[$colMap['notes']] ?? '') : '';

            if ($date === '' || $description === '' || $amountRaw === '') {
                $failures++;
                $errors[] = "Row {$rowNum}: Missing required field.";
                continue;
            }

            $parsedDate = $this->parseDate($date);
            if ($parsedDate === null) {
                $failures++;
                $errors[] = "Row {$rowNum}: Invalid date '{$date}'.";
                continue;
            }

            $amount = (float) str_replace([',', '$', ' '], '', $amountRaw);
            if ($amount < 0) {
                $type = 'debit';
                $amount = abs($amount);
            }
            if ($amount <= 0) {
                $failures++;
                $errors[] = "Row {$rowNum}: Invalid amount '{$amountRaw}'.";
                continue;
            }

            if (!in_array($type, ['debit', 'credit'])) $type = 'debit';

            $categoryId = null;
            if ($categoryStr !== '') {
                $categoryId = $categoryLookup[strtolower($categoryStr)] ?? null;
            }

            try {
                Expense::create([
                    'description' => $description,
                    'amount' => $amount,
                    'expense_date' => $parsedDate,
                    'type' => $type,
                    'category_id' => $categoryId,
                    'vendor' => $vendor !== '' ? $vendor : null,
                    'notes' => $notes !== '' ? $notes : null,
                    'user_id' => Auth::id(),
                ]);
                $successes++;
            } catch (\Exception $ex) {
                $failures++;
                $errors[] = "Row {$rowNum}: " . $ex->getMessage();
            }
        }

        fclose($handle);

        $message = "Import complete: {$successes} expense(s) imported successfully.";
        if ($failures > 0) {
            $message .= " {$failures} row(s) failed.";
            if (!empty($errors)) {
                $message .= '<br><small>' . implode('<br>', array_slice($errors, 0, 10)) . '</small>';
            }
        }

        $flashType = $failures > 0 ? ($successes > 0 ? 'warning' : 'danger') : 'success';

        return redirect('/import')->with('flash', ['type' => $flashType, 'message' => $message]);
    }

    private function findColumn(array $header, array $names): ?int
    {
        foreach ($names as $name) {
            $index = array_search($name, $header, true);
            if ($index !== false) return (int) $index;
        }
        return null;
    }

    private function parseDate(string $date): ?string
    {
        if (preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $date)) {
            $ts = strtotime($date);
            return $ts !== false ? date('Y-m-d', $ts) : null;
        }

        if (preg_match('#^(\d{1,2})[/\-](\d{1,2})[/\-](\d{2,4})$#', $date, $m)) {
            $year = strlen($m[3]) === 2 ? '20' . $m[3] : $m[3];
            $ts = mktime(0, 0, 0, (int) $m[1], (int) $m[2], (int) $year);
            return $ts !== false ? date('Y-m-d', $ts) : null;
        }

        $ts = strtotime($date);
        return $ts !== false ? date('Y-m-d', $ts) : null;
    }
}
