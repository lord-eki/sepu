<h2>Account Statement</h2>
<p>Name: {{ $account->member->first_name }} {{ $account->member->last_name }}</p>
<p>Account No: {{ $account->account_number }}</p>
<p>Balance: KES {{ number_format($account->balance) }}</p>
<p>Period: {{ $period['from'] }} - {{ $period['to'] }}</p>

<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>Date & Time</th>
            <th>Type</th>
            <th>Amount</th>
            <th>Balance</th>
            <th>Status</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        @foreach($transactions as $tx)
        <tr>
            <td>{{ $tx->created_at }}</td>
            <td>{{ str_replace('_', ' ', $tx->transaction_type) }}</td>
            <td>{{ number_format($tx->amount) }}</td>
            <td>{{ number_format($tx->balance_after) }}</td>
            <td>{{ $tx->status }}</td>
            <td>{{ $tx->description ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
