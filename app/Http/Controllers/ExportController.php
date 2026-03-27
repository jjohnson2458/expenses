<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExportController extends Controller
{
    public function csv(Request $request)
    {
        $expenses = $this->getFilteredExpenses($request);
        $filename = 'expenses_' . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($expenses) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Date', 'Description', 'Category', 'Vendor', 'Type', 'Amount', 'Report', 'Notes']);

            foreach ($expenses as $row) {
                fputcsv($output, [
                    $row->expense_date ?? '',
                    $row->description ?? '',
                    $row->category_name ?? '',
                    $row->vendor ?? '',
                    $row->type ?? 'debit',
                    number_format((float) ($row->amount ?? 0), 2, '.', ''),
                    $row->report_title ?? '',
                    $row->notes ?? '',
                ]);
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function quickbooks(Request $request)
    {
        $expenses = $this->getFilteredExpenses($request);
        $filename = 'expenses_quickbooks_' . date('Y-m-d') . '.iif';

        return response()->streamDownload(function () use ($expenses) {
            $output = fopen('php://output', 'w');
            fwrite($output, "!TRNS\tTRNSTYPE\tDATE\tACCNT\tAMOUNT\tMEMO\tNAME\n");
            fwrite($output, "!SPL\tTRNSTYPE\tDATE\tACCNT\tAMOUNT\tMEMO\tNAME\n");
            fwrite($output, "!ENDTRNS\n");

            foreach ($expenses as $row) {
                $date = date('m/d/Y', strtotime($row->expense_date));
                $categoryAcct = 'Expenses:' . ($row->category_name ?: 'Uncategorized');
                $amount = (float) ($row->amount ?? 0);
                $memo = str_replace("\t", ' ', $row->description ?? '');
                $vendor = str_replace("\t", ' ', $row->vendor ?? '');
                $sign = ($row->type ?? 'debit') === 'debit' ? 1 : -1;
                $expenseAmount = $amount * $sign;

                fwrite($output, "TRNS\tGENERAL JOURNAL\t{$date}\t{$categoryAcct}\t" . number_format($expenseAmount, 2, '.', '') . "\t{$memo}\t{$vendor}\n");
                fwrite($output, "SPL\tGENERAL JOURNAL\t{$date}\tChecking\t" . number_format(-$expenseAmount, 2, '.', '') . "\t{$memo}\t{$vendor}\n");
                fwrite($output, "ENDTRNS\n");
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'application/octet-stream']);
    }

    public function googleCalendar(Request $request)
    {
        $expenses = $this->getFilteredExpenses($request);
        $filename = 'expenses_calendar_' . date('Y-m-d') . '.ics';

        return response()->streamDownload(function () use ($expenses) {
            $output = fopen('php://output', 'w');
            fwrite($output, "BEGIN:VCALENDAR\r\n");
            fwrite($output, "VERSION:2.0\r\n");
            fwrite($output, "PRODID:-//VQMoney//Expense Export//EN\r\n");
            fwrite($output, "CALSCALE:GREGORIAN\r\n");
            fwrite($output, "METHOD:PUBLISH\r\n");

            foreach ($expenses as $row) {
                $dtDate = date('Ymd', strtotime($row->expense_date));
                $uid = 'expense-' . ($row->id ?? uniqid()) . '@vqmoney';
                $dtstamp = gmdate('Ymd\THis\Z');
                $amount = number_format((float) ($row->amount ?? 0), 2);
                $type = ucfirst($row->type ?? 'debit');
                $summary = $this->icsEscape("{$type}: \${$amount} - {$row->description}");

                $descLines = ["Amount: \${$amount}", "Type: {$type}", "Category: " . ($row->category_name ?? 'Uncategorized')];
                if (!empty($row->vendor)) $descLines[] = "Vendor: {$row->vendor}";
                if (!empty($row->notes)) $descLines[] = "Notes: {$row->notes}";
                $icsDescription = $this->icsEscape(implode('\n', $descLines));

                fwrite($output, "BEGIN:VEVENT\r\n");
                fwrite($output, "UID:{$uid}\r\n");
                fwrite($output, "DTSTAMP:{$dtstamp}\r\n");
                fwrite($output, "DTSTART;VALUE=DATE:{$dtDate}\r\n");
                fwrite($output, "DTEND;VALUE=DATE:{$dtDate}\r\n");
                fwrite($output, "SUMMARY:{$summary}\r\n");
                fwrite($output, "DESCRIPTION:{$icsDescription}\r\n");
                fwrite($output, "CATEGORIES:{$this->icsEscape($row->category_name ?? 'Uncategorized')}\r\n");
                fwrite($output, "END:VEVENT\r\n");
            }

            fwrite($output, "END:VCALENDAR\r\n");
            fclose($output);
        }, $filename, ['Content-Type' => 'text/calendar; charset=UTF-8']);
    }

    public function reportCsv(int $id)
    {
        $report = ExpenseReport::findOrFail($id);
        $expenses = Expense::where('report_id', $id)->orderByDesc('expense_date')->get();
        $titleSlug = preg_replace('/[^a-zA-Z0-9_-]/', '_', $report->title ?? 'report');
        $titleSlug = preg_replace('/_+/', '_', trim($titleSlug, '_'));
        $filename = 'report_' . $titleSlug . '_' . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($report, $expenses) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Report', $report->title ?? '']);
            fputcsv($output, ['Generated', date('Y-m-d H:i:s')]);
            fputcsv($output, []);
            fputcsv($output, ['Date', 'Description', 'Category', 'Vendor', 'Type', 'Amount', 'Notes']);

            $total = 0;
            foreach ($expenses as $row) {
                $amount = (float) $row->amount;
                $total += $amount;
                fputcsv($output, [
                    $row->expense_date?->format('Y-m-d') ?? '',
                    $row->description ?? '',
                    $row->category?->name ?? '',
                    $row->vendor ?? '',
                    $row->type ?? 'debit',
                    number_format($amount, 2, '.', ''),
                    $row->notes ?? '',
                ]);
            }
            fputcsv($output, []);
            fputcsv($output, ['', '', '', '', 'TOTAL', number_format($total, 2, '.', ''), '']);
            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function getFilteredExpenses(Request $request)
    {
        $query = DB::table('expenses as e')
            ->leftJoin('expense_categories as c', 'e.category_id', '=', 'c.id')
            ->leftJoin('expense_reports as r', 'e.report_id', '=', 'r.id')
            ->select('e.*', 'c.name as category_name', 'r.title as report_title');

        if ($from = $request->get('from')) {
            $query->where('e.expense_date', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->where('e.expense_date', '<=', $to);
        }

        return $query->orderByDesc('e.expense_date')->orderByDesc('e.created_at')->get();
    }

    private function icsEscape(string $text): string
    {
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace(',', '\\,', $text);
        $text = str_replace(';', '\\;', $text);
        $text = str_replace("\r\n", '\n', $text);
        $text = str_replace("\n", '\n', $text);
        return $text;
    }
}
