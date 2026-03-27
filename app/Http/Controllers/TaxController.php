<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\MileageLog;
use App\Models\TaxProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaxController extends Controller
{
    public function profile()
    {
        $profile = TaxProfile::firstOrCreate(
            ['user_id' => Auth::id()],
            ['filing_status' => 'single', 'business_entity' => 'sole_prop']
        );

        $states = DB::table('state_sales_tax')->orderBy('state_name')->get();

        return view('tax.profile', compact('profile', 'states'));
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'filing_status' => 'required|in:single,married_joint,married_separate,head_of_household',
            'state' => 'nullable|string|size:2',
            'business_entity' => 'required|in:sole_prop,llc,s_corp,c_corp',
            'business_name' => 'nullable|string|max:255',
            'ein' => 'nullable|string|max:20',
        ]);

        $profile = TaxProfile::updateOrCreate(
            ['user_id' => Auth::id()],
            $request->only([
                'filing_status', 'state', 'business_entity', 'business_name', 'ein',
                'fiscal_year_start', 'track_mileage', 'home_office',
                'home_office_sqft', 'home_total_sqft',
            ])
        );

        return redirect('/tax/profile')->with('flash', [
            'type' => 'success',
            'message' => 'Tax profile updated.',
        ]);
    }

    public function mileage()
    {
        $year = request('year', date('Y'));
        $summary = MileageLog::getYearSummary(Auth::id(), $year);

        return view('tax.mileage', compact('summary', 'year'));
    }

    public function storeMileage(Request $request)
    {
        $request->validate([
            'trip_date' => 'required|date',
            'start_location' => 'required|string|max:255',
            'end_location' => 'required|string|max:255',
            'business_purpose' => 'required|string|max:500',
            'miles' => 'required|numeric|min:0.1',
        ]);

        MileageLog::create([
            'user_id' => Auth::id(),
            'trip_date' => $request->trip_date,
            'start_location' => $request->start_location,
            'end_location' => $request->end_location,
            'business_purpose' => $request->business_purpose,
            'miles' => $request->miles,
            'irs_rate' => 0.70, // 2025 IRS rate
            'round_trip' => $request->boolean('round_trip'),
        ]);

        return redirect('/tax/mileage')->with('flash', [
            'type' => 'success',
            'message' => 'Trip logged successfully.',
        ]);
    }

    public function destroyMileage(int $id)
    {
        MileageLog::where('id', $id)->where('user_id', Auth::id())->delete();

        return redirect('/tax/mileage')->with('flash', [
            'type' => 'success',
            'message' => 'Trip deleted.',
        ]);
    }

    public function summary()
    {
        $year = request('year', date('Y'));
        $userId = Auth::id();
        $profile = TaxProfile::where('user_id', $userId)->first();

        // Get expenses by Schedule C line
        $scheduleCData = DB::table('expenses as e')
            ->join('schedule_c_mappings as m', 'e.category_id', '=', 'm.category_id')
            ->where('e.user_id', $userId)
            ->where('e.type', 'debit')
            ->whereYear('e.expense_date', $year)
            ->select(
                'm.schedule_c_line',
                'm.schedule_c_description',
                DB::raw('SUM(e.amount) as total_amount'),
                DB::raw('COUNT(*) as expense_count')
            )
            ->groupBy('m.schedule_c_line', 'm.schedule_c_description')
            ->orderBy('m.schedule_c_line')
            ->get();

        // Total expenses
        $totalExpenses = $scheduleCData->sum('total_amount');

        // Total income (credits)
        $totalIncome = Expense::where('user_id', $userId)
            ->where('type', 'credit')
            ->whereYear('expense_date', $year)
            ->sum('amount');

        // Mileage deduction
        $mileageSummary = MileageLog::getYearSummary($userId, $year);

        // Home office deduction
        $homeOfficeDeduction = $profile?->homeOfficeDeduction() ?? 0;

        // Net profit
        $netProfit = $totalIncome - $totalExpenses - $mileageSummary['total_deduction'] - $homeOfficeDeduction;

        // Self-employment tax (15.3% on 92.35% of net profit)
        $seTaxableIncome = max(0, $netProfit * 0.9235);
        $selfEmploymentTax = $seTaxableIncome * 0.153;

        // Quarterly estimates
        $quarters = DB::table('quarterly_estimates')
            ->where('user_id', $userId)
            ->where('tax_year', $year)
            ->orderBy('quarter')
            ->get();

        return view('tax.summary', compact(
            'year', 'profile', 'scheduleCData', 'totalExpenses', 'totalIncome',
            'mileageSummary', 'homeOfficeDeduction', 'netProfit',
            'selfEmploymentTax', 'quarters'
        ));
    }
}
