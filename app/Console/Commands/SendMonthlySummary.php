<?php

namespace App\Console\Commands;

use App\Models\Budget;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendMonthlySummary extends Command
{
    protected $signature = 'expenses:monthly-summary {--user= : Specific user ID} {--test : Preview without sending}';
    protected $description = 'Send monthly expense summary email to all users (run on the 5th of each month)';

    public function handle()
    {
        $targetUserId = $this->option('user');
        $isTest = $this->option('test');

        $query = User::query();
        if ($targetUserId) {
            $query->where('id', $targetUserId);
        }

        $users = $query->get();
        $sent = 0;

        foreach ($users as $user) {
            $html = $this->buildSummaryHtml($user);

            if ($isTest) {
                $this->info("--- Summary for {$user->name} ({$user->email}) ---");
                $this->line(strip_tags(str_replace(['<br>', '</tr>', '</td>'], ["\n", "\n", " | "], $html)));
                continue;
            }

            // Use claude_messenger for email delivery
            $subject = 'VQ Money: Your ' . date('F Y', strtotime('-1 month')) . ' Expense Summary';
            $escaped_subject = escapeshellarg($subject);
            $escaped_body = escapeshellarg($html);
            $escaped_email = escapeshellarg($user->email);

            exec("php C:/xampp/htdocs/claude_messenger/notify.php -s {$escaped_subject} -b {$escaped_body} -t {$escaped_email} -p claude_expenses 2>&1", $output, $code);

            if ($code === 0) {
                $sent++;
                $this->info("Sent summary to {$user->email}");
            } else {
                $this->error("Failed to send to {$user->email}: " . implode(' ', $output));
            }
        }

        if (!$isTest) {
            $this->info("Monthly summaries sent: {$sent}");
        }

        return 0;
    }

    private function buildSummaryHtml(User $user): string
    {
        $userId = $user->id;
        $lastMonth = date('Y-m', strtotime('-1 month'));
        $lastMonthName = date('F Y', strtotime('-1 month'));
        $year = date('Y', strtotime('-1 month'));
        $month = date('m', strtotime('-1 month'));

        // Total spending
        $totalSpend = DB::table('expenses')
            ->where('user_id', $userId)
            ->where('type', 'debit')
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month)
            ->sum('amount');

        $totalCredits = DB::table('expenses')
            ->where('user_id', $userId)
            ->where('type', 'credit')
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month)
            ->sum('amount');

        $transactionCount = DB::table('expenses')
            ->where('user_id', $userId)
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month)
            ->count();

        // Previous month for comparison
        $prevMonth = date('Y-m', strtotime('-2 months'));
        $prevYear = date('Y', strtotime('-2 months'));
        $prevMo = date('m', strtotime('-2 months'));

        $prevSpend = DB::table('expenses')
            ->where('user_id', $userId)
            ->where('type', 'debit')
            ->whereYear('expense_date', $prevYear)
            ->whereMonth('expense_date', $prevMo)
            ->sum('amount');

        $changePercent = $prevSpend > 0
            ? round((($totalSpend - $prevSpend) / $prevSpend) * 100, 1)
            : 0;
        $changeDir = $changePercent > 0 ? 'up' : ($changePercent < 0 ? 'down' : 'flat');
        $changeColor = $changePercent > 0 ? '#e74a3b' : ($changePercent < 0 ? '#1cc88a' : '#858796');

        // Category breakdown
        $categories = DB::table('expenses as e')
            ->leftJoin('expense_categories as c', 'e.category_id', '=', 'c.id')
            ->where('e.user_id', $userId)
            ->where('e.type', 'debit')
            ->whereYear('e.expense_date', $year)
            ->whereMonth('e.expense_date', $month)
            ->select('c.name as category_name', 'c.color', DB::raw('SUM(e.amount) as total'))
            ->groupBy('c.name', 'c.color')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Budget status
        $budgets = Budget::where('user_id', $userId)
            ->where('budget_month', $lastMonth)
            ->with('category')
            ->get();

        $overBudget = $budgets->filter(fn($b) => $b->percent_used > 100);

        // Build HTML
        $html = "<div style='font-family:Inter,Arial,sans-serif;max-width:600px;margin:0 auto;'>";
        $html .= "<div style='background:#4e73df;color:#fff;padding:20px 24px;border-radius:8px 8px 0 0;'>";
        $html .= "<h2 style='margin:0;font-size:20px;'>VQ Money Monthly Summary</h2>";
        $html .= "<p style='margin:4px 0 0;opacity:0.85;'>{$lastMonthName}</p>";
        $html .= "</div>";

        $html .= "<div style='background:#fff;padding:24px;border:1px solid #e3e6f0;'>";

        // Stats row
        $html .= "<table style='width:100%;border-collapse:collapse;margin-bottom:20px;'><tr>";
        $html .= "<td style='text-align:center;padding:12px;'><div style='color:#858796;font-size:11px;text-transform:uppercase;'>Total Spent</div><div style='font-size:24px;font-weight:700;color:#e74a3b;'>\${$this->fmt($totalSpend)}</div></td>";
        $html .= "<td style='text-align:center;padding:12px;'><div style='color:#858796;font-size:11px;text-transform:uppercase;'>Credits</div><div style='font-size:24px;font-weight:700;color:#1cc88a;'>\${$this->fmt($totalCredits)}</div></td>";
        $html .= "<td style='text-align:center;padding:12px;'><div style='color:#858796;font-size:11px;text-transform:uppercase;'>Transactions</div><div style='font-size:24px;font-weight:700;color:#4e73df;'>{$transactionCount}</div></td>";
        $html .= "</tr></table>";

        // Trend
        if ($prevSpend > 0) {
            $arrow = $changePercent > 0 ? '&#9650;' : ($changePercent < 0 ? '&#9660;' : '&#8212;');
            $html .= "<p style='text-align:center;color:{$changeColor};font-size:14px;margin-bottom:20px;'>{$arrow} " . abs($changePercent) . "% {$changeDir} from " . date('F', strtotime('-2 months')) . "</p>";
        }

        // Category breakdown
        if ($categories->count() > 0) {
            $html .= "<h3 style='font-size:15px;border-bottom:1px solid #e3e6f0;padding-bottom:8px;'>Spending by Category</h3>";
            $html .= "<table style='width:100%;border-collapse:collapse;'>";
            foreach ($categories as $cat) {
                $catName = $cat->category_name ?? 'Uncategorized';
                $color = $cat->color ?? '#6c757d';
                $pctOfTotal = $totalSpend > 0 ? round(($cat->total / $totalSpend) * 100, 0) : 0;
                $html .= "<tr><td style='padding:6px 0;'><span style='display:inline-block;width:10px;height:10px;border-radius:50%;background:{$color};margin-right:8px;'></span>{$catName}</td>";
                $html .= "<td style='text-align:right;padding:6px 0;font-weight:600;'>\${$this->fmt($cat->total)}</td>";
                $html .= "<td style='text-align:right;padding:6px 0;color:#858796;width:50px;'>{$pctOfTotal}%</td></tr>";
            }
            $html .= "</table>";
        }

        // Budget alerts
        if ($overBudget->count() > 0) {
            $html .= "<div style='margin-top:20px;padding:12px;background:#fff3cd;border-left:4px solid #ffc107;border-radius:4px;'>";
            $html .= "<strong style='color:#856404;'>Budget Alerts</strong><br>";
            foreach ($overBudget as $b) {
                $html .= "<span style='color:#856404;'>" . ($b->category->name ?? 'Unknown') . ": \${$this->fmt($b->spent)} of \${$this->fmt($b->amount)} (" . number_format($b->percent_used, 0) . "%)</span><br>";
            }
            $html .= "</div>";
        }

        $html .= "<div style='margin-top:24px;text-align:center;'>";
        $html .= "<a href='https://vqmoney.com/dashboard' style='display:inline-block;background:#4e73df;color:#fff;padding:10px 24px;text-decoration:none;border-radius:5px;font-weight:600;'>View Dashboard</a>";
        $html .= "</div>";

        $html .= "</div>";
        $html .= "<div style='padding:12px 24px;text-align:center;color:#858796;font-size:11px;border:1px solid #e3e6f0;border-top:none;border-radius:0 0 8px 8px;background:#f8f9fc;'>";
        $html .= "VQ Money &bull; VisionQuest Services LLC &bull; <a href='https://vqmoney.com' style='color:#4e73df;'>vqmoney.com</a>";
        $html .= "</div>";
        $html .= "</div>";

        return $html;
    }

    private function fmt(float $amount): string
    {
        return number_format($amount, 2);
    }
}
