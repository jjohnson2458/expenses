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
        return view('import.index');
    }

    public function process(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('import_file');
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
            'type' => $this->findColumn($header, ['type']),
            'vendor' => $this->findColumn($header, ['vendor', 'payee', 'merchant']),
            'notes' => $this->findColumn($header, ['notes', 'memo', 'note']),
        ];

        if ($colMap['date'] === null || $colMap['description'] === null || $colMap['amount'] === null) {
            fclose($handle);
            return redirect('/import')->with('flash', ['type' => 'danger', 'message' => 'CSV must contain at least Date, Description, and Amount columns.']);
        }

        $categoryLookup = [];
        foreach (Category::active()->get() as $cat) {
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
