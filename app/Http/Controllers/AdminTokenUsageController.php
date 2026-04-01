<?php

namespace App\Http\Controllers;

use App\Models\ApiUsageLog;
use Illuminate\Support\Facades\DB;

class AdminTokenUsageController extends Controller
{
    public function index()
    {
        // Summary stats
        $allTime = DB::table('api_usage_log')->selectRaw('
            COUNT(*) as calls,
            COALESCE(SUM(total_tokens), 0) as tokens,
            COALESCE(SUM(estimated_cost_usd), 0) as cost
        ')->first();

        $today = DB::table('api_usage_log')
            ->whereDate('created_at', now()->toDateString())
            ->selectRaw('COUNT(*) as calls, COALESCE(SUM(total_tokens), 0) as tokens, COALESCE(SUM(estimated_cost_usd), 0) as cost')
            ->first();

        $thisMonth = DB::table('api_usage_log')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->selectRaw('COUNT(*) as calls, COALESCE(SUM(total_tokens), 0) as tokens, COALESCE(SUM(estimated_cost_usd), 0) as cost')
            ->first();

        // Daily usage (last 30 days)
        $dailyUsage = DB::table('api_usage_log')
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as calls, SUM(total_tokens) as tokens, SUM(estimated_cost_usd) as cost')
            ->groupByRaw('DATE(created_at)')
            ->orderByDesc('date')
            ->get();

        // Usage by feature
        $byFeature = DB::table('api_usage_log')
            ->selectRaw('feature, COUNT(*) as calls, SUM(total_tokens) as tokens, SUM(estimated_cost_usd) as cost, AVG(response_time_ms) as avg_ms')
            ->groupBy('feature')
            ->orderByDesc('cost')
            ->get();

        // Usage by user (top 20)
        $byUser = DB::table('api_usage_log')
            ->leftJoin('users', 'api_usage_log.user_id', '=', 'users.id')
            ->selectRaw('users.name, users.email, COUNT(*) as calls, SUM(api_usage_log.total_tokens) as tokens, SUM(api_usage_log.estimated_cost_usd) as cost')
            ->groupBy('users.name', 'users.email')
            ->orderByDesc('cost')
            ->limit(20)
            ->get();

        // Usage by model
        $byModel = DB::table('api_usage_log')
            ->selectRaw('model, COUNT(*) as calls, SUM(input_tokens) as input_tokens, SUM(output_tokens) as output_tokens, SUM(total_tokens) as tokens, SUM(estimated_cost_usd) as cost')
            ->groupBy('model')
            ->orderByDesc('cost')
            ->get();

        // Recent calls (last 100)
        $recentCalls = DB::table('api_usage_log')
            ->leftJoin('users', 'api_usage_log.user_id', '=', 'users.id')
            ->select('api_usage_log.*', 'users.name as user_name', 'users.email as user_email')
            ->orderByDesc('api_usage_log.created_at')
            ->limit(100)
            ->get();

        return view('admin.token-usage', compact(
            'allTime', 'today', 'thisMonth',
            'dailyUsage', 'byFeature', 'byUser', 'byModel', 'recentCalls'
        ));
    }
}
