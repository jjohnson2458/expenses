<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\MileageLog;
use App\Models\TaxProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuarterlyEstimateController extends Controller
{
    private const DEADLINES = [
        1 => '04-15', // Q1: Jan-Mar, due April 15
        2 => '06-15', // Q2: Apr-May, due June 15
        3 => '09-15', // Q3: Jun-Aug, due September 15
        4 => '01-15', // Q4: Sep-Dec, due January 15 (next year)
    ];

    public function index(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $userId = Auth::id();

        $quarters = DB::table('quarterly_estimates')
            ->where('user_id', $userId)
            ->where('tax_year', $year)
            ->orderBy('quarter')
            ->get()
            ->keyBy('quarter');

        // Calculate estimated tax for current year
        $estimate = $this->calculateEstimate($userId, $year);

        // IRS Direct Pay URL
        $irsPayUrl = 'https://directpay.irs.gov/directpay/payment?execution=e1s1';

        return view('tax.quarterly', compact('year', 'quarters', 'estimate', 'irsPayUrl'));
    }

    public function generate(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $userId = Auth::id();
        $estimate = $this->calculateEstimate($userId, $year);
        $perQuarter = $estimate['quarterly_amount'];

        foreach (self::DEADLINES as $q => $mmdd) {
            $dueYear = $q === 4 ? $year + 1 : $year;
            $dueDate = "{$dueYear}-{$mmdd}";

            DB::table('quarterly_estimates')->updateOrInsert(
                ['user_id' => $userId, 'tax_year' => $year, 'quarter' => $q],
                [
                    'estimated_amount' => $perQuarter,
                    'due_date' => $dueDate,
                    'updated_at' => now(),
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ]
            );
        }

        return redirect("/tax/quarterly?year={$year}")->with('flash', [
            'type' => 'success',
            'message' => "Quarterly estimates generated: \${$perQuarter}/quarter for {$year}.",
        ]);
    }

    public function markPaid(Request $request, int $id)
    {
        $request->validate([
            'paid_amount' => 'required|numeric|min:0',
            'paid_date' => 'required|date',
            'confirmation_number' => 'nullable|string|max:50',
        ]);

        $row = DB::table('quarterly_estimates')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$row) {
            abort(404);
        }

        DB::table('quarterly_estimates')->where('id', $id)->update([
            'paid_amount' => $request->input('paid_amount'),
            'paid_date' => $request->input('paid_date'),
            'confirmation_number' => $request->input('confirmation_number'),
            'updated_at' => now(),
        ]);

        return redirect("/tax/quarterly?year={$row->tax_year}")->with('flash', [
            'type' => 'success',
            'message' => "Q{$row->quarter} payment recorded.",
        ]);
    }

    private function calculateEstimate(int $userId, int $year): array
    {
        $profile = TaxProfile::where('user_id', $userId)->first();

        $totalIncome = Expense::where('user_id', $userId)
            ->where('type', 'credit')
            ->whereYear('expense_date', $year)
            ->sum('amount');

        $totalExpenses = Expense::where('user_id', $userId)
            ->where('type', 'debit')
            ->whereYear('expense_date', $year)
            ->sum('amount');

        $mileage = MileageLog::getYearSummary($userId, $year);
        $homeOffice = $profile?->homeOfficeDeduction() ?? 0;

        $netProfit = $totalIncome - $totalExpenses - $mileage['total_deduction'] - $homeOffice;
        $seTaxable = max(0, $netProfit * 0.9235);
        $seTax = $seTaxable * 0.153;

        // Estimated income tax (rough: 22% bracket for self-employed)
        $incomeTax = max(0, $netProfit * 0.22);

        $totalTax = $seTax + $incomeTax;
        $quarterly = round($totalTax / 4, 2);

        return [
            'net_profit' => $netProfit,
            'self_employment_tax' => round($seTax, 2),
            'estimated_income_tax' => round($incomeTax, 2),
            'total_estimated_tax' => round($totalTax, 2),
            'quarterly_amount' => $quarterly,
        ];
    }
}
