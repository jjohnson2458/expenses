<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnomalyController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $filter = $request->get('type', 'all');

        $duplicates = $this->findDuplicates($userId);
        $unusual = $this->findUnusualAmounts($userId);
        $uncategorized = $this->findUncategorized($userId);

        $allAnomalies = collect();
        if ($filter === 'all' || $filter === 'duplicate') {
            $allAnomalies = $allAnomalies->merge($duplicates);
        }
        if ($filter === 'all' || $filter === 'unusual') {
            $allAnomalies = $allAnomalies->merge($unusual);
        }
        if ($filter === 'all' || $filter === 'uncategorized') {
            $allAnomalies = $allAnomalies->merge($uncategorized);
        }

        $counts = [
            'duplicates' => $duplicates->count(),
            'unusual' => $unusual->count(),
            'uncategorized' => $uncategorized->count(),
            'total' => $duplicates->count() + $unusual->count() + $uncategorized->count(),
        ];

        return view('expenses.anomalies', compact('allAnomalies', 'counts', 'filter'));
    }

    public function dismiss(Request $request, int $id)
    {
        // Mark expense as reviewed (not anomalous)
        DB::table('expenses')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->update(['notes' => DB::raw("CONCAT(COALESCE(notes, ''), ' [reviewed]')")]);

        return back()->with('flash', ['type' => 'success', 'message' => 'Expense marked as reviewed.']);
    }

    private function findDuplicates(int $userId)
    {
        // Find expenses with same amount, same date, similar description
        $dupes = DB::select("
            SELECT e1.id, e1.description, e1.amount, e1.expense_date, e1.vendor,
                   e1.category_id, c.name as category_name, c.color as category_color,
                   e1.type, 'duplicate' as anomaly_type,
                   CONCAT('Possible duplicate: same amount (\$', FORMAT(e1.amount, 2), ') on ', e1.expense_date) as anomaly_reason
            FROM expenses e1
            INNER JOIN expenses e2 ON e1.user_id = e2.user_id
                AND e1.id < e2.id
                AND e1.amount = e2.amount
                AND e1.expense_date = e2.expense_date
                AND e1.type = e2.type
            LEFT JOIN expense_categories c ON e1.category_id = c.id
            WHERE e1.user_id = ?
            AND e1.notes NOT LIKE '%[reviewed]%'
            AND e1.expense_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY e1.id, e1.description, e1.amount, e1.expense_date, e1.vendor,
                     e1.category_id, c.name, c.color, e1.type
            ORDER BY e1.expense_date DESC
            LIMIT 50
        ", [$userId]);

        return collect($dupes);
    }

    private function findUnusualAmounts(int $userId)
    {
        // Find expenses where amount is > 2x the average for that category
        $unusual = DB::select("
            SELECT e.id, e.description, e.amount, e.expense_date, e.vendor,
                   e.category_id, c.name as category_name, c.color as category_color,
                   e.type, 'unusual' as anomaly_type,
                   CONCAT('Amount \$', FORMAT(e.amount, 2), ' is ',
                          ROUND(e.amount / cat_avg.avg_amount, 1),
                          'x the category average of \$', FORMAT(cat_avg.avg_amount, 2)) as anomaly_reason
            FROM expenses e
            LEFT JOIN expense_categories c ON e.category_id = c.id
            INNER JOIN (
                SELECT category_id, AVG(amount) as avg_amount, STDDEV(amount) as std_amount
                FROM expenses
                WHERE user_id = ? AND type = 'debit' AND category_id IS NOT NULL
                GROUP BY category_id
                HAVING COUNT(*) >= 3
            ) cat_avg ON e.category_id = cat_avg.category_id
            WHERE e.user_id = ?
            AND e.type = 'debit'
            AND e.amount > (cat_avg.avg_amount + 2 * GREATEST(cat_avg.std_amount, cat_avg.avg_amount * 0.5))
            AND e.notes NOT LIKE '%[reviewed]%'
            AND e.expense_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            ORDER BY e.expense_date DESC
            LIMIT 50
        ", [$userId, $userId]);

        return collect($unusual);
    }

    private function findUncategorized(int $userId)
    {
        $rows = DB::table('expenses as e')
            ->leftJoin('expense_categories as c', 'e.category_id', '=', 'c.id')
            ->where('e.user_id', $userId)
            ->whereNull('e.category_id')
            ->where('e.expense_date', '>=', DB::raw('DATE_SUB(CURDATE(), INTERVAL 6 MONTH)'))
            ->select(
                'e.id', 'e.description', 'e.amount', 'e.expense_date', 'e.vendor',
                'e.category_id', 'e.type',
                DB::raw("NULL as category_name"),
                DB::raw("NULL as category_color"),
                DB::raw("'uncategorized' as anomaly_type"),
                DB::raw("'No category assigned' as anomaly_reason")
            )
            ->orderByDesc('e.expense_date')
            ->limit(50)
            ->get();

        return $rows;
    }
}
