<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Transaction Statement</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #0B2B40;
            font-size: 12px;
            margin: 0;
            padding: 25px;
            line-height: 1.5;
            background: #fff;
        }

        /* ================= LOGO ================= */
        .logo {
            text-align: center;
            margin-bottom: 10px;
        }

        .logo img {
            width: 90px;
        }

        /* ================= HEADER ================= */
        h2 {
            text-align: center;
            margin: 5px 0 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* ================= SUMMARY ================= */
        .summary {
            background: #f4f7fb;
            padding: 14px;
            border-radius: 10px;
            margin-bottom: 18px;
            border: 1px solid #e2e8f0;
        }

        .summary p {
            margin: 4px 0;
        }

        .summary strong {
            color: #0B2B40;
        }

        .summary-grid {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            font-weight: 600;
        }

        /* ================= TABLE ================= */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        th {
            background: #0B2B40;
            color: white;
            padding: 10px;
            text-transform: uppercase;
            font-size: 10px;
        }

        td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .right {
            text-align: right;
        }

        /* ================= BADGES ================= */
        .badge {
            padding: 3px 6px;
            border-radius: 5px;
            font-size: 10px;
            background: #eef2ff;
            color: #1e3a8a;
        }

        .credit {
            color: #0a7a2f;
            font-weight: bold;
        }

        .debit {
            color: #c0392b;
            font-weight: bold;
        }

        /* ================= FOOTER ================= */
        .footer {
            margin-top: 25px;
            text-align: center;
            font-size: 10px;
            color: #777;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }

        .log {
            font-size: 9px;
            color: #999;
            margin-top: 4px;
        }
    </style>
</head>

<body>

    <!-- ================= LOGO ================= -->
    <div class="logo">
        <img src="{{ public_path('apple-touch-icon1.png') }}" alt="Logo">
    </div>

    <h2>Transaction Statement</h2>

    <!-- ================= SUMMARY ================= -->
    <div class="summary">

        <p>
            <strong>Member:</strong>
            {{ $member->first_name }} {{ $member->last_name }}
        </p>

        <p>
            <strong>Period:</strong>
            {{ $from }} → {{ $to }}
        </p>

        <div class="summary-grid">
            <span>Total: {{ count($transactions) }}</span>

            <span class="credit">
                Credits:
                KES {{ number_format(
                    $transactions->whereIn('transaction_type', ['deposit','credit'])->sum('amount')
                ) }}
            </span>

            <span class="debit">
                Debits:
                KES {{ number_format(
                    $transactions->whereIn('transaction_type', ['withdrawal','debit'])->sum('amount')
                ) }}
            </span>
        </div>
    </div>

    <!-- ================= TABLE ================= -->
    <table>

        <thead>
            <tr>
                <th>Date</th>
                <th>Account</th>
                <th>Type</th>
                <th class="right">Debit</th>
                <th class="right">Credit</th>
                <th class="right">Balance</th>
                <th>Status</th>
            </tr>
        </thead>

        <tbody>

            @foreach($transactions as $tx)
            <tr>

                <td>
                    {{ \Carbon\Carbon::parse($tx->created_at)->format('d M Y H:i') }}
                </td>

                <td>
                    {{ $tx->account->account_number ?? '-' }}
                </td>

                <td>
                    <span class="badge">
                        {{ ucfirst($tx->transaction_type) }}
                    </span>
                </td>

                <!-- DEBIT -->
                <td class="right debit">
                    @if(in_array($tx->transaction_type, ['withdrawal','debit']))
                        KES {{ number_format($tx->amount) }}
                    @else
                        -
                    @endif
                </td>

                <!-- CREDIT -->
                <td class="right credit">
                    @if(in_array($tx->transaction_type, ['deposit','credit']))
                        KES {{ number_format($tx->amount) }}
                    @else
                        -
                    @endif
                </td>

                <!-- BALANCE -->
                <td class="right">
                    KES {{ number_format($tx->balance_after) }}
                </td>

                <!-- STATUS -->
                <td>
                    @php
                        $status = strtolower($tx->status);
                    @endphp

                    <span class="
                        @if($status === 'completed') credit
                        @elseif($status === 'pending') badge
                        @else debit
                        @endif
                    ">
                        {{ ucfirst($tx->status) }}
                    </span>
                </td>

            </tr>
            @endforeach

        </tbody>

    </table>

    <!-- ================= FOOTER ================= -->
    <div class="footer">
        <div>This is a system-generated statement and is valid without signature.</div>

        <div class="log">
            Generated on {{ now()->format('d M Y, h:i A') }}
        </div>
    </div>

</body>
</html>