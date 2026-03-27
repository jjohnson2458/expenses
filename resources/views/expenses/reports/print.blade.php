<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $report['title'] ?? 'Expense Report' }} - VQ Money</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.5;
            padding: 0.5in;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #1a1c2e;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 20px;
            color: #1a1c2e;
            margin-bottom: 2px;
        }

        .header .company {
            font-size: 14px;
            color: #4e73df;
            font-weight: bold;
        }

        .header .subtitle {
            color: #666;
            font-size: 11px;
        }

        .report-info {
            margin-bottom: 20px;
        }

        .report-info table {
            width: 100%;
        }

        .report-info td {
            padding: 3px 10px 3px 0;
        }

        .report-info .label {
            font-weight: bold;
            color: #555;
            width: 120px;
        }

        .expenses-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .expenses-table th {
            background: #1a1c2e;
            color: #fff;
            padding: 8px 10px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
        }

        .expenses-table th:last-child {
            text-align: right;
        }

        .expenses-table td {
            padding: 6px 10px;
            border-bottom: 1px solid #ddd;
        }

        .expenses-table td:last-child {
            text-align: right;
            font-weight: bold;
        }

        .expenses-table tr:nth-child(even) {
            background: #f9f9f9;
        }

        .totals {
            float: right;
            width: 250px;
        }

        .totals table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals td {
            padding: 5px 10px;
            border-bottom: 1px solid #eee;
        }

        .totals .total-label {
            font-weight: bold;
            color: #555;
        }

        .totals .total-amount {
            text-align: right;
            font-weight: bold;
        }

        .totals .grand-total td {
            border-top: 2px solid #1a1c2e;
            border-bottom: 2px solid #1a1c2e;
            font-size: 14px;
            color: #1a1c2e;
            padding: 8px 10px;
        }

        .footer {
            clear: both;
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #999;
            font-size: 10px;
        }

        .no-print { display: none; }

        @media screen {
            body { max-width: 800px; margin: 0 auto; }
            .no-print {
                display: block;
                text-align: center;
                margin-bottom: 20px;
            }
            .no-print button {
                padding: 10px 30px;
                background: #4e73df;
                color: #fff;
                border: none;
                border-radius: 5px;
                font-size: 14px;
                cursor: pointer;
                margin: 0 5px;
            }
            .no-print button:hover { background: #224abe; }
            .no-print button.secondary {
                background: #6c757d;
            }
        }

        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print();">Print Report</button>
        <button class="secondary" onclick="window.close();">Close</button>
    </div>

    <div class="header">
        <div class="company">VisionQuest Services LLC</div>
        <h1>{{ $report['title'] ?? 'Expense Report' }}</h1>
        <div class="subtitle">
            @if(!empty($report['start_date'] ?? ''))
                {{ $report['start_date'] }}
                @if(!empty($report['end_date'] ?? ''))
                    to {{ $report['end_date'] }}
                @endif
            @endif
        </div>
    </div>

    <div class="report-info">
        <table>
            <tr>
                <td class="label">Report ID:</td>
                <td>#{{ $report['id'] ?? '' }}</td>
                <td class="label">Status:</td>
                <td>{{ ucfirst($report['status'] ?? 'draft') }}</td>
            </tr>
            <tr>
                <td class="label">Created:</td>
                <td>{{ $report['created_at'] ?? '' }}</td>
                <td class="label">Expenses:</td>
                <td>{{ count($expenses ?? []) }}</td>
            </tr>
            @if(!empty($report['description'] ?? ''))
            <tr>
                <td class="label">Description:</td>
                <td colspan="3">{{ $report['description'] }}</td>
            </tr>
            @endif
        </table>
    </div>

    <table class="expenses-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Description</th>
                <th>Category</th>
                <th>Vendor</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses ?? [] as $expense)
            <tr>
                <td>{{ $expense->date ?? $expense['date'] ?? '' }}</td>
                <td>{{ $expense->description ?? $expense['description'] ?? '' }}</td>
                <td>{{ $expense->category_name ?? $expense['category_name'] ?? '' }}</td>
                <td>{{ $expense->vendor ?? $expense['vendor'] ?? '' }}</td>
                <td>
                    {{ ($expense->type ?? $expense['type'] ?? 'debit') === 'credit' ? '+' : '-' }}${{ number_format($expense->amount ?? $expense['amount'] ?? 0, 2) }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; color: #999;">No expenses in this report</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td class="total-label">Total Debits:</td>
                <td class="total-amount" style="color: #e74a3b;">${{ number_format($totalDebits ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td class="total-label">Total Credits:</td>
                <td class="total-amount" style="color: #1cc88a;">${{ number_format($totalCredits ?? 0, 2) }}</td>
            </tr>
            <tr class="grand-total">
                <td class="total-label">Net Total:</td>
                <td class="total-amount">${{ number_format($report['total'] ?? 0, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Generated by VQ Money &mdash; &copy; 2026 VisionQuest Services LLC</p>
        <p>Printed on {{ date('F j, Y \a\t g:i A') }}</p>
    </div>
</body>
</html>
