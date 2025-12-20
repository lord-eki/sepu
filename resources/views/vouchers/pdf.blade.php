<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payment Voucher - {{ $voucher->voucher_number }}</title>

<style>
    body {
        font-family: Helvetica, Arial, sans-serif;
        margin: 0;
        padding: 0;
        color: #0A2342;
        font-size: 13px;
    }

    .container {
        width: 92%;
        margin: 20px auto;
        border: 1px solid #0A2342;
        padding: 20px;
        border-radius: 14px;
    }

    .letterhead {
        text-align: center;
        border-bottom: 2px solid #0A2342;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }

    .letterhead img {
        height: 70px;
        margin-bottom: 5px;
    }

    .letterhead h2 {
        margin: 0;
        font-size: 20px;
        letter-spacing: 1px;
    }

    .letterhead p {
        margin: 2px 0;
        font-size: 11px;
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }

    .header h1 {
        font-size: 22px;
        margin: 0;
    }

    .voucher-number {
        background: #0A2342;
        color: white;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 14px;
    }

    .section {
        margin-bottom: 20px;
    }

    .section h3 {
        font-size: 15px;
        border-bottom: 1.5px solid #0A2342;
        padding-bottom: 4px;
        margin-bottom: 10px;
    }

    .section p {
        margin: 4px 0;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    th, td {
        border: 1px solid #0A2342;
        padding: 8px;
        text-align: left;
    }

    th {
        background: #0A2342;
        color: white;
        font-size: 12px;
    }

    .footer {
        display: flex;
        justify-content: space-between;
        margin-top: 45px;
    }

    .signature {
        width: 45%;
        text-align: center;
    }

    .signature-line {
        border-top: 1px solid #0A2342;
        margin-top: 35px;
    }

    .notes {
        margin-top: 20px;
        font-size: 12px;
        color: #444;
    }

    .watermark {
        position: fixed;
        top: 40%;
        left: 20%;
        font-size: 80px;
        color: rgba(10,35,66,0.08);
        transform: rotate(-30deg);
        z-index: -1;
    }
</style>
</head>

<body>

@if($voucher->status !== 'paid')
    <div class="watermark">NOT PAID</div>
@endif

<div class="container">

    <!-- LETTERHEAD -->
    <div class="letterhead">
        <img src="{{ public_path('apple-touch-icon1.png') }}" alt="SEPU">
        <h2>SEPU SACCO</h2>
        <p>P.O. Box 020 – Nairobi, Kenya</p>
        <p>Tel: 0720-000-999 | Email: info@sepusacco.co.ke</p>
    </div>

    <!-- HEADER -->
    <div class="header">
        <h1>Payment Voucher</h1>
        <div class="voucher-number">
            #{{ $voucher->voucher_number }}
        </div>
    </div>

    <!-- VOUCHER DETAILS -->
    <div class="section">
        <h3>Voucher Details</h3>
        <p><strong>Date:</strong> {{ $voucher->created_at->format('d M Y') }}</p>
        <p><strong>Status:</strong> {{ strtoupper($voucher->status) }}</p>
        <p><strong>Created By:</strong> {{ optional($voucher->creator)->name ?? 'System' }}</p>
    </div>

    <!-- PAYMENT INFORMATION -->
    <div class="section">
        <h3>Payment Information</h3>

        <table>
            <thead>
                <tr>
                    <th>Payee</th>
                    <th>Budget Item</th>
                    <th>Amount (KES)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                    @php
                        $member = optional($voucher->loan)->member;
                    @endphp

                    @if($member)
                        {{ $member->full_name }} (Member ID: {{ $member->membership_id ?? 'N/A' }})
                    @else
                        {{ $voucher->payee_name ?? 'N/A' }}
                    @endif
                </td>

                    <td>{{ optional($voucher->budgetItem)->item_name ?? 'N/A' }}</td>
                    <td>{{ number_format($voucher->amount, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- SIGNATURES -->
    <div class="footer">
        <div class="signature">
            <p>
                <strong>Approved By</strong><br>
                {{ optional($voucher->approver)->name ?? 'Pending Approval' }}
            </p>
            <div class="signature-line"></div>
        </div>

        <div class="signature">
            <p>
                <strong>Paid By</strong><br>
                {{ optional($voucher->payer)->name ?? 'Not Paid' }}
            </p>
            <div class="signature-line"></div>
        </div>
    </div>

    <!-- NOTES -->
    @if($voucher->approval_notes)
        <div class="notes">
            <strong>Approval Notes:</strong><br>
            {{ $voucher->approval_notes }}
        </div>
    @endif

</div>
</body>
</html>
