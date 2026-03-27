<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Category;
use App\Models\ExpenseReport;
use App\Services\ReceiptOcrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));
        $from = $request->get('from', '');
        $to = $request->get('to', '');
        $categoryFilter = $request->get('category', '');

        $categories = Category::active()->ordered()->get();

        $query = DB::table('expenses as e')
            ->leftJoin('expense_categories as c', 'e.category_id', '=', 'c.id')
            ->leftJoin('expense_reports as r', 'e.report_id', '=', 'r.id')
            ->select('e.*', 'c.name as category_name', 'c.color as category_color', 'r.title as report_title');

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('e.description', 'like', "%{$q}%")
                  ->orWhere('e.vendor', 'like', "%{$q}%")
                  ->orWhere('e.notes', 'like', "%{$q}%");
            });
        }

        if ($from !== '') {
            $query->where('e.expense_date', '>=', $from);
        }
        if ($to !== '') {
            $query->where('e.expense_date', '<=', $to);
        }
        if ($categoryFilter !== '') {
            $query->where('e.category_id', (int) $categoryFilter);
        }

        // Get totals for filtered results — build a fresh query with same conditions
        $totalsQuery = DB::table('expenses as e');
        if ($q !== '') {
            $totalsQuery->where(function ($w) use ($q) {
                $w->where('e.description', 'like', "%{$q}%")
                  ->orWhere('e.vendor', 'like', "%{$q}%")
                  ->orWhere('e.notes', 'like', "%{$q}%");
            });
        }
        if ($from !== '') $totalsQuery->where('e.expense_date', '>=', $from);
        if ($to !== '') $totalsQuery->where('e.expense_date', '<=', $to);
        if ($categoryFilter !== '') $totalsQuery->where('e.category_id', (int) $categoryFilter);

        $totals = $totalsQuery->selectRaw("
            COALESCE(SUM(CASE WHEN e.type = 'debit' THEN e.amount ELSE 0 END), 0) AS total_debits,
            COALESCE(SUM(CASE WHEN e.type = 'credit' THEN e.amount ELSE 0 END), 0) AS total_credits
        ")->first();

        $expenses = $query->orderByDesc('e.expense_date')
            ->orderByDesc('e.created_at')
            ->paginate(20)
            ->appends($request->query());

        $filters = compact('q', 'from', 'to');
        $filters['category'] = $categoryFilter;

        return view('expenses.ledger.index', compact('expenses', 'categories', 'totals', 'filters'));
    }

    public function create()
    {
        $categories = Category::active()->ordered()->get();
        $reports = ExpenseReport::orderBy('title', 'asc')->get();

        return view('expenses.ledger.form', compact('categories', 'reports'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:500',
            'amount' => 'required|numeric',
            'expense_date' => 'required|date',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:10240',
        ]);

        $data = $this->buildExpenseData($request);
        $expense = Expense::create($data);

        if (!empty($data['report_id'])) {
            ExpenseReport::find($data['report_id'])?->updateTotal();
        }

        return redirect('/expenses')->with('flash', ['type' => 'success', 'message' => 'Expense created successfully.']);
    }

    public function edit(int $id)
    {
        $expense = Expense::findOrFail($id);
        $categories = Category::active()->ordered()->get();
        $reports = ExpenseReport::orderBy('title', 'asc')->get();

        return view('expenses.ledger.form', compact('expense', 'categories', 'reports'));
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'description' => 'required|string|max:500',
            'amount' => 'required|numeric',
            'expense_date' => 'required|date',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:10240',
        ]);

        $expense = Expense::findOrFail($id);
        $oldReportId = $expense->report_id;
        $data = $this->buildExpenseData($request);
        $expense->update($data);

        // Update totals for both old and new reports
        if ($oldReportId) {
            ExpenseReport::find($oldReportId)?->updateTotal();
        }
        if (!empty($data['report_id']) && $data['report_id'] != $oldReportId) {
            ExpenseReport::find($data['report_id'])?->updateTotal();
        }

        return redirect('/expenses')->with('flash', ['type' => 'success', 'message' => 'Expense updated successfully.']);
    }

    public function destroy(int $id)
    {
        $expense = Expense::findOrFail($id);
        $reportId = $expense->report_id;
        $expense->delete();

        if ($reportId) {
            ExpenseReport::find($reportId)?->updateTotal();
        }

        return redirect('/expenses')->with('flash', ['type' => 'success', 'message' => 'Expense deleted successfully.']);
    }

    public function scanReceipt(Request $request)
    {
        $request->validate([
            'receipt' => 'required|file|mimes:jpg,jpeg,png,gif,webp|max:10240',
        ]);

        $file = $request->file('receipt');
        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
        $file->storeAs('receipts', $filename, 'public');
        $receiptPath = 'receipts/' . $filename;

        $ocr = new ReceiptOcrService();
        $result = $ocr->processReceipt($receiptPath);

        if (!$result) {
            return response()->json([
                'success' => false,
                'receipt_path' => $receiptPath,
                'message' => 'OCR processing unavailable. Receipt saved — fill in details manually.',
            ]);
        }

        return response()->json([
            'success' => true,
            'receipt_path' => $receiptPath,
            'data' => $result,
        ]);
    }

    public function voiceInput(Request $request)
    {
        $text = trim($request->input('text', ''));

        if ($text === '') {
            return response()->json(['error' => 'No text provided'], 400);
        }

        // Parse amount
        $amount = null;
        if (preg_match('/\$?([\d,]+(?:\.\d{1,2})?)/', $text, $m)) {
            $amount = (float) str_replace(',', '', $m[1]);
        }

        // Parse date
        $date = date('Y-m-d');
        if (preg_match('/yesterday/i', $text)) {
            $date = date('Y-m-d', strtotime('-1 day'));
        } elseif (preg_match('/(\d{1,2})[\/\-](\d{1,2})(?:[\/\-](\d{2,4}))?/', $text, $dm)) {
            $year = isset($dm[3]) ? (strlen($dm[3]) === 2 ? '20' . $dm[3] : $dm[3]) : date('Y');
            $date = sprintf('%s-%02d-%02d', $year, (int) $dm[1], (int) $dm[2]);
        }

        // Match category
        $categories = Category::active()->get();
        $matchedCategory = null;
        $lowerText = strtolower($text);

        foreach ($categories as $cat) {
            if (stripos($lowerText, strtolower($cat->name)) !== false) {
                $matchedCategory = $cat;
                break;
            }
        }

        // Build description
        $description = $text;
        $description = preg_replace('/\$?[\d,]+(?:\.\d{1,2})?/', '', $description);
        $description = preg_replace('/\b(today|yesterday)\b/i', '', $description);
        $description = preg_replace('/\d{1,2}[\/\-]\d{1,2}(?:[\/\-]\d{2,4})?/', '', $description);
        if ($matchedCategory) {
            $description = preg_replace('/\b' . preg_quote($matchedCategory->name, '/') . '\b/i', '', $description);
        }
        $description = preg_replace('/\b(for|at|on|spent|paid|bought|in)\b/i', '', $description);
        $description = trim(preg_replace('/\s{2,}/', ' ', $description));

        // Determine type
        $type = 'debit';
        if (preg_match('/\b(income|received|refund|credit|earned)\b/i', $text)) {
            $type = 'credit';
        }

        return response()->json([
            'description' => $description ?: null,
            'amount' => $amount,
            'date' => $date,
            'type' => $type,
            'category_id' => $matchedCategory?->id,
            'category' => $matchedCategory?->name,
        ]);
    }

    private function buildExpenseData(Request $request): array
    {
        $data = [
            'description' => trim($request->description),
            'amount' => (float) $request->amount,
            'expense_date' => $request->expense_date,
            'type' => in_array($request->type, ['debit', 'credit']) ? $request->type : 'debit',
            'user_id' => Auth::id(),
            'category_id' => $request->category_id ?: null,
            'vendor' => $request->vendor ?: null,
            'report_id' => $request->report_id ?: null,
            'notes' => $request->notes ?: null,
        ];

        // Handle receipt upload
        if ($request->hasFile('receipt') && $request->file('receipt')->isValid()) {
            $file = $request->file('receipt');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
            $file->storeAs('receipts', $filename, 'public');
            $data['receipt_path'] = 'receipts/' . $filename;
        }

        return $data;
    }
}
