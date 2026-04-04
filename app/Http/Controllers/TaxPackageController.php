<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\MileageLog;
use App\Models\TaxProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaxPackageController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $userId = Auth::id();
        $profile = TaxProfile::where('user_id', $userId)->first();
        $data = $this->gatherTaxData($userId, $year);

        return view('tax.package', compact('year', 'profile', 'data'));
    }

    public function downloadProfitLoss(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $userId = Auth::id();
        $data = $this->gatherTaxData($userId, $year);
        $profile = TaxProfile::where('user_id', $userId)->first();
        $filename = "profit_loss_{$year}.csv";

        return response()->streamDownload(function () use ($data, $year, $profile) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Profit & Loss Statement']);
            fputcsv($out, [$profile->business_name ?? 'VQ Money User', "Tax Year {$year}"]);
            fputcsv($out, []);

            fputcsv($out, ['INCOME']);
            fputcsv($out, ['Gross Revenue', number_format($data['total_income'], 2, '.', '')]);
            fputcsv($out, []);

            fputcsv($out, ['EXPENSES']);
            foreach ($data['category_totals'] as $cat) {
                fputcsv($out, [$cat->category_name ?? 'Uncategorized', number_format($cat->total, 2, '.', '')]);
            }
            fputcsv($out, ['Total Expenses', number_format($data['total_expenses'], 2, '.', '')]);
            fputcsv($out, []);

            fputcsv($out, ['OTHER DEDUCTIONS']);
            fputcsv($out, ['Mileage Deduction', number_format($data['mileage_deduction'], 2, '.', '')]);
            fputcsv($out, ['Home Office Deduction', number_format($data['home_office_deduction'], 2, '.', '')]);
            fputcsv($out, []);

            fputcsv($out, ['NET PROFIT', number_format($data['net_profit'], 2, '.', '')]);
            fputcsv($out, ['Self-Employment Tax (est.)', number_format($data['self_employment_tax'], 2, '.', '')]);

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function downloadScheduleC(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $userId = Auth::id();
        $data = $this->gatherTaxData($userId, $year);
        $profile = TaxProfile::where('user_id', $userId)->first();
        $filename = "schedule_c_{$year}.csv";

        return response()->streamDownload(function () use ($data, $year, $profile) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['IRS Schedule C Summary']);
            fputcsv($out, [$profile->business_name ?? '', "Tax Year {$year}"]);
            fputcsv($out, ['EIN: ' . ($profile->ein ?? 'N/A')]);
            fputcsv($out, []);
            fputcsv($out, ['Line', 'Description', 'Amount']);

            fputcsv($out, ['1', 'Gross receipts or sales', number_format($data['total_income'], 2, '.', '')]);
            fputcsv($out, ['7', 'Gross income', number_format($data['total_income'], 2, '.', '')]);
            fputcsv($out, []);

            foreach ($data['schedule_c'] as $line) {
                fputcsv($out, [
                    $line->schedule_c_line,
                    $line->schedule_c_description,
                    number_format($line->total_amount, 2, '.', ''),
                ]);
            }

            fputcsv($out, []);
            fputcsv($out, ['28', 'Total expenses', number_format($data['total_expenses'], 2, '.', '')]);
            fputcsv($out, ['29', 'Tentative profit', number_format($data['net_profit'], 2, '.', '')]);

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function downloadCategoryDetail(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $userId = Auth::id();
        $filename = "category_detail_{$year}.csv";

        $expenses = DB::table('expenses as e')
            ->leftJoin('expense_categories as c', 'e.category_id', '=', 'c.id')
            ->where('e.user_id', $userId)
            ->where('e.type', 'debit')
            ->whereYear('e.expense_date', $year)
            ->select('c.name as category_name', 'e.expense_date', 'e.description', 'e.vendor', 'e.amount', 'e.notes')
            ->orderBy('c.name')
            ->orderBy('e.expense_date')
            ->get();

        return response()->streamDownload(function () use ($expenses, $year) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ["Expense Detail by Category - {$year}"]);
            fputcsv($out, []);
            fputcsv($out, ['Category', 'Date', 'Description', 'Vendor', 'Amount', 'Notes']);

            $currentCat = null;
            $catTotal = 0;

            foreach ($expenses as $row) {
                $cat = $row->category_name ?? 'Uncategorized';
                if ($currentCat !== null && $cat !== $currentCat) {
                    fputcsv($out, ['', '', '', 'Subtotal:', number_format($catTotal, 2, '.', ''), '']);
                    fputcsv($out, []);
                    $catTotal = 0;
                }
                $currentCat = $cat;
                $catTotal += (float) $row->amount;
                fputcsv($out, [
                    $cat,
                    $row->expense_date,
                    $row->description ?? '',
                    $row->vendor ?? '',
                    number_format((float) $row->amount, 2, '.', ''),
                    $row->notes ?? '',
                ]);
            }
            if ($currentCat !== null) {
                fputcsv($out, ['', '', '', 'Subtotal:', number_format($catTotal, 2, '.', ''), '']);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function downloadMileageLog(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $userId = Auth::id();
        $summary = MileageLog::getYearSummary($userId, $year);
        $filename = "mileage_log_{$year}.csv";

        return response()->streamDownload(function () use ($summary, $year) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ["IRS Mileage Log - {$year}"]);
            fputcsv($out, ['IRS Standard Rate: $0.70/mile']);
            fputcsv($out, []);
            fputcsv($out, ['Date', 'From', 'To', 'Business Purpose', 'Miles', 'Deduction']);

            foreach ($summary['logs'] as $log) {
                fputcsv($out, [
                    $log->trip_date->format('Y-m-d'),
                    $log->start_location,
                    $log->end_location,
                    $log->business_purpose,
                    number_format((float) $log->miles, 1),
                    number_format($log->deduction, 2, '.', ''),
                ]);
            }

            fputcsv($out, []);
            fputcsv($out, ['', '', '', 'TOTAL', number_format($summary['total_miles'], 1), number_format($summary['total_deduction'], 2, '.', '')]);

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function downloadHomeOffice(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $userId = Auth::id();
        $profile = TaxProfile::where('user_id', $userId)->first();
        $filename = "home_office_{$year}.csv";

        return response()->streamDownload(function () use ($profile, $year) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ["Home Office Deduction - {$year}"]);
            fputcsv($out, []);

            if ($profile && $profile->home_office) {
                fputcsv($out, ['Method', 'Simplified Method ($5/sqft, max 300 sqft)']);
                fputcsv($out, ['Office Square Footage', number_format((float) $profile->home_office_sqft, 0)]);
                fputcsv($out, ['Total Home Square Footage', number_format((float) $profile->home_total_sqft, 0)]);
                fputcsv($out, ['Business Use Percentage', $profile->homeOfficePercentage() . '%']);
                fputcsv($out, []);
                fputcsv($out, ['Deduction Amount', number_format($profile->homeOfficeDeduction(), 2, '.', '')]);
            } else {
                fputcsv($out, ['No home office configured. Set up in Tax Profile.']);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function downloadTurbotax(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $userId = Auth::id();
        $filename = "turbotax_import_{$year}.csv";

        $expenses = DB::table('expenses as e')
            ->leftJoin('expense_categories as c', 'e.category_id', '=', 'c.id')
            ->leftJoin('schedule_c_mappings as m', 'e.category_id', '=', 'm.category_id')
            ->where('e.user_id', $userId)
            ->where('e.type', 'debit')
            ->whereYear('e.expense_date', $year)
            ->select('e.expense_date', 'e.description', 'e.amount', 'c.name as category_name',
                     'm.schedule_c_line', 'm.schedule_c_description')
            ->orderBy('e.expense_date')
            ->get();

        return response()->streamDownload(function () use ($expenses, $year) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Date', 'Description', 'Amount', 'Category', 'Schedule C Line', 'Tax Description']);

            foreach ($expenses as $row) {
                fputcsv($out, [
                    $row->expense_date,
                    $row->description ?? '',
                    number_format((float) $row->amount, 2, '.', ''),
                    $row->category_name ?? '',
                    $row->schedule_c_line ?? '',
                    $row->schedule_c_description ?? '',
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function downloadQuickbooks(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $userId = Auth::id();
        $filename = "quickbooks_{$year}.iif";

        $expenses = DB::table('expenses as e')
            ->leftJoin('expense_categories as c', 'e.category_id', '=', 'c.id')
            ->where('e.user_id', $userId)
            ->whereYear('e.expense_date', $year)
            ->select('e.*', 'c.name as category_name')
            ->orderBy('e.expense_date')
            ->get();

        return response()->streamDownload(function () use ($expenses) {
            $out = fopen('php://output', 'w');
            fwrite($out, "!TRNS\tTRNSTYPE\tDATE\tACCNT\tAMOUNT\tMEMO\tNAME\n");
            fwrite($out, "!SPL\tTRNSTYPE\tDATE\tACCNT\tAMOUNT\tMEMO\tNAME\n");
            fwrite($out, "!ENDTRNS\n");

            foreach ($expenses as $row) {
                $date = date('m/d/Y', strtotime($row->expense_date));
                $acct = 'Expenses:' . ($row->category_name ?: 'Uncategorized');
                $amount = (float) $row->amount;
                $sign = $row->type === 'debit' ? 1 : -1;
                $amt = $amount * $sign;
                $memo = str_replace("\t", ' ', $row->description ?? '');
                $vendor = str_replace("\t", ' ', $row->vendor ?? '');

                fwrite($out, "TRNS\tGENERAL JOURNAL\t{$date}\t{$acct}\t" . number_format($amt, 2, '.', '') . "\t{$memo}\t{$vendor}\n");
                fwrite($out, "SPL\tGENERAL JOURNAL\t{$date}\tChecking\t" . number_format(-$amt, 2, '.', '') . "\t{$memo}\t{$vendor}\n");
                fwrite($out, "ENDTRNS\n");
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'application/octet-stream']);
    }

    private function gatherTaxData(int $userId, int $year): array
    {
        $profile = TaxProfile::where('user_id', $userId)->first();

        $scheduleCData = DB::table('expenses as e')
            ->join('schedule_c_mappings as m', 'e.category_id', '=', 'm.category_id')
            ->where('e.user_id', $userId)
            ->where('e.type', 'debit')
            ->whereYear('e.expense_date', $year)
            ->select('m.schedule_c_line', 'm.schedule_c_description',
                     DB::raw('SUM(e.amount) as total_amount'),
                     DB::raw('COUNT(*) as expense_count'))
            ->groupBy('m.schedule_c_line', 'm.schedule_c_description')
            ->orderBy('m.schedule_c_line')
            ->get();

        $categoryTotals = DB::table('expenses as e')
            ->leftJoin('expense_categories as c', 'e.category_id', '=', 'c.id')
            ->where('e.user_id', $userId)
            ->where('e.type', 'debit')
            ->whereYear('e.expense_date', $year)
            ->select('c.name as category_name', DB::raw('SUM(e.amount) as total'))
            ->groupBy('c.name')
            ->orderByDesc('total')
            ->get();

        $totalExpenses = $categoryTotals->sum('total');

        $totalIncome = Expense::where('user_id', $userId)
            ->where('type', 'credit')
            ->whereYear('expense_date', $year)
            ->sum('amount');

        $mileageSummary = MileageLog::getYearSummary($userId, $year);
        $homeOfficeDeduction = $profile?->homeOfficeDeduction() ?? 0;

        $netProfit = $totalIncome - $totalExpenses - $mileageSummary['total_deduction'] - $homeOfficeDeduction;
        $seTax = max(0, $netProfit * 0.9235) * 0.153;

        $expenseCount = Expense::where('user_id', $userId)->whereYear('expense_date', $year)->count();
        $receiptCount = Expense::where('user_id', $userId)
            ->whereYear('expense_date', $year)
            ->whereNotNull('receipt_path')
            ->where('receipt_path', '!=', '')
            ->count();

        $readinessScore = $expenseCount > 0
            ? round(($receiptCount / $expenseCount) * 100, 0)
            : 0;

        return [
            'schedule_c' => $scheduleCData,
            'category_totals' => $categoryTotals,
            'total_expenses' => $totalExpenses,
            'total_income' => $totalIncome,
            'mileage_summary' => $mileageSummary,
            'mileage_deduction' => $mileageSummary['total_deduction'],
            'home_office_deduction' => $homeOfficeDeduction,
            'net_profit' => $netProfit,
            'self_employment_tax' => $seTax,
            'expense_count' => $expenseCount,
            'receipt_count' => $receiptCount,
            'readiness_score' => $readinessScore,
        ];
    }
}
