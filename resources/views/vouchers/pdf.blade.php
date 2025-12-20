<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payment Voucher - {{ $voucher->voucher_number }}</title>
<style>
    body { font-family: 'Helvetica', Arial, sans-serif; margin: 0; padding: 0; color: #0A2342; }
    .container { width: 90%; margin: 20px auto; border: 1px solid #0A2342; padding: 20px; border-radius: 15px; }
    .letterhead { text-align: center; margin-bottom: 20px; }
    .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .header h1 { font-size: 24px; color: #0A2342; margin: 0; }
    .header .voucher-number { font-size: 16px; background: #0A2342; color: white; padding: 5px 10px; border-radius: 8px; }
    .section { margin-bottom: 20px; }
    .section h2 { font-size: 18px; border-bottom: 2px solid #0A2342; padding-bottom: 5px; margin-bottom: 10px; }
    .section p { margin: 4px 0; }
    .details-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .details-table th, .details-table td { border: 1px solid #0A2342; padding: 8px; text-align: left; }
    .details-table th { background-color: #0A2342; color: white; }
    .footer { display: flex; justify-content: space-between; margin-top: 40px; }
    .signature { text-align: center; margin-top: 40px; }
    .signature-line { border-top: 1px solid #0A2342; width: 200px; margin: 0 auto; }
    .notes { margin-top: 20px; font-size: 12px; color: #555; }
</style>
</head>
<body>
<div class="container">

    <!-- Letterhead -->
    <div class="letterhead">
        <img src="{{ public_path('images/sepu_logo.png') }}" alt="LOGO" style="height: 80px; margin-bottom: 5px;">
        <h2 style="margin:0; color:#0A2342;">SEPU SACCO</h2>
        <p style="margin:0; font-size:12px; color:#0A2342;">P.O. Box 020 – Nairobi, Kenya | Phone: 0720-000-999</p>
    </div>

    <div class="header">
        <h1>Payment Voucher</h1>
        <div class="voucher-number">#{{ $voucher->voucher_number }}</div>
    </div>

    <div class="section">
        <h2>Voucher Details</h2>
        <p><strong>Date:</strong> {{ $voucher->created_at->format('d M Y') }}</p>
        <p><strong>Status:</strong> {{ ucfirst($voucher->status) }}</p>
        <p><strong>Created By:</strong> {{ $voucher->creator->name ?? 'N/A' }}</p>
    </div>

    <div class="section">
        <h2>Payment Information</h2>
        <table class="details-table">
            <tr>
                <th>Payee</th>
                <th>Budget Item</th>
                <th>Amount</th>
            </tr>
            <tr>
                <td>{{ $voucher->loan && $voucher->loan->member ? $voucher->loan->member->full_name : 'N/A' }}</td>
                <td>{{ $voucher->budgetItem ? $voucher->budgetItem->name : 'N/A' }}</td>
                <td>{{ number_format($voucher->amount, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <div class="signature">
            <p>Approved By</p>
            <div class="signature-line"></div>
        </div>
        <div class="signature">
            <p>Paid By</p>
            <div class="signature-line"></div>
        </div>
    </div>

    @if($voucher->approval_notes)
    <div class="notes">
        <strong>Approval Notes:</strong> {{ $voucher->approval_notes }}
    </div>
    @endif
</div>
</body>
</html>
