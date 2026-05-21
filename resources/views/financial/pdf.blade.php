<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Financial Report — {{ $monthDate->format('F Y') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #111827;
            background: #fff;
            padding: 40px 44px;
            line-height: 1.5;
        }

        /* ── Header ── */
        .header {
            display: table;
            width: 100%;
            padding-bottom: 18px;
            border-bottom: 2px solid #e5e7eb;
            margin-bottom: 24px;
        }
        .header-left  { display: table-cell; vertical-align: middle; }
        .header-right { display: table-cell; vertical-align: middle; text-align: right; }
        .header-left h1 {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
        }
        .header-left .subtitle {
            font-size: 12px;
            color: #6b7280;
            margin-top: 3px;
        }
        .header-right .generated {
            font-size: 10px;
            color: #9ca3af;
        }

        /* ── Table ── */
        .section-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #6b7280;
            margin-bottom: 10px;
        }

        table.tx-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11.5px;
        }
        table.tx-table thead th {
            background: #f3f4f6;
            padding: 8px 10px;
            text-align: left;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
            border-bottom: 1px solid #e5e7eb;
        }
        table.tx-table thead th.right { text-align: right; }
        table.tx-table tbody td {
            padding: 9px 10px;
            border-bottom: 1px solid #f3f4f6;
            color: #374151;
            vertical-align: middle;
        }
        table.tx-table tbody tr:last-child td { border-bottom: none; }
        table.tx-table tbody tr:nth-child(even) td { background: #fafafa; }

        .pill {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 99px;
            font-size: 10px;
            font-weight: 600;
        }
        .pill-income  { background: #dcfce7; color: #16a34a; }
        .pill-expense { background: #fee2e2; color: #dc2626; }

        .amount-income  { color: #16a34a; font-weight: 600; text-align: right; }
        .amount-expense { color: #dc2626; font-weight: 600; text-align: right; }

        .empty-state {
            text-align: center;
            padding: 32px 0;
            color: #9ca3af;
            font-size: 12px;
        }

        /* ── Totals ── */
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
        }
        .totals-table td {
            padding: 8px 10px;
            font-size: 11.5px;
            border-top: 2px solid #e5e7eb;
        }
        .totals-table .spacer { width: 100%; }
        .totals-label { font-weight: 600; color: #374151; white-space: nowrap; padding-left: 10px; }
        .totals-value { font-weight: 700; white-space: nowrap; text-align: right; padding-right: 10px; }
        .t-income  { color: #16a34a; }
        .t-expense { color: #dc2626; }
        .t-net-pos { color: #2563eb; }
        .t-net-neg { color: #dc2626; }

        /* ── Footer ── */
        .footer {
            margin-top: 36px;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
            display: table;
            width: 100%;
        }
        .footer-left  { display: table-cell; font-size: 10px; color: #9ca3af; }
        .footer-right { display: table-cell; text-align: right; font-size: 10px; color: #9ca3af; }
    </style>
</head>
<body>

    {{-- ── Header ── --}}
    <div class="header">
        <div class="header-left">
            <h1>Financial Report</h1>
            <div class="subtitle">{{ $monthDate->format('F Y') }} &nbsp;·&nbsp; {{ $user->name }}</div>
        </div>
        <div class="header-right">
            <div class="generated">Generated {{ now()->format('d M Y, H:i') }}</div>
        </div>
    </div>

    {{-- ── Transaction list ── --}}
    <div class="section-title">Transaction History — {{ $monthDate->format('F Y') }}</div>

    @if ($transactions->isEmpty())
        <div class="empty-state">No transactions recorded for this month.</div>
    @else
        <table class="tx-table">
            <thead>
                <tr>
                    <th style="width:24px;">#</th>
                    <th>Title</th>
                    <th style="width:80px;">Type</th>
                    <th class="right" style="width:110px;">Amount</th>
                    <th style="width:96px;">Date</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transactions as $i => $tx)
                    <tr>
                        <td style="color:#9ca3af;">{{ $i + 1 }}</td>
                        <td style="font-weight:600;">{{ $tx->title }}</td>
                        <td>
                            <span class="pill {{ $tx->type === 'income' ? 'pill-income' : 'pill-expense' }}">
                                {{ ucfirst($tx->type) }}
                            </span>
                        </td>
                        <td class="{{ $tx->type === 'income' ? 'amount-income' : 'amount-expense' }}">
                            {{ $tx->type === 'income' ? '+' : '−' }}{{ number_format($tx->amount, 2) }}
                        </td>
                        <td style="color:#6b7280;">{{ $tx->transacted_at->format('d M Y') }}</td>
                        <td style="color:#9ca3af;">{{ $tx->note ?: '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- ── Totals ── --}}
        <table class="totals-table">
            <tr>
                <td class="spacer"></td>
                <td class="totals-label t-income">Total Income</td>
                <td class="totals-value t-income">+{{ number_format($monthIncome, 2) }}</td>
                <td class="totals-label t-expense">Total Expense</td>
                <td class="totals-value t-expense">−{{ number_format($monthExpense, 2) }}</td>
                <td class="totals-label" style="color:#111827;">Net</td>
                <td class="totals-value {{ $monthNet >= 0 ? 't-net-pos' : 't-net-neg' }}">
                    {{ $monthNet >= 0 ? '+' : '' }}{{ number_format($monthNet, 2) }}
                </td>
            </tr>
        </table>
    @endif

    {{-- ── Footer ── --}}
    <div class="footer">
        <div class="footer-left">{{ config('app.name') }} &nbsp;·&nbsp; Financial Report</div>
        <div class="footer-right">{{ $monthDate->format('F Y') }}</div>
    </div>

</body>
</html>
