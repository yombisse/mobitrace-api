<div style="font-size: 11pt; font-weight: bold; color: #0B3B2E; margin: 17px 0 10px 0;">Détail des transactions</div>
<table border="0" cellpadding="8" cellspacing="0" style="width: 100%; margin-top: 12px; border-collapse: collapse;">
    <thead>
        <tr>
            <th style="background-color: #0B3B2E; color: #FFFFFF; font-weight: bold; font-size: 8pt; padding: 8px 7px; border: 1px solid #0B3B2E; text-align: left;">DATE / HEURE</th>
            <th style="background-color: #0B3B2E; color: #FFFFFF; font-weight: bold; font-size: 8pt; padding: 8px 7px; border: 1px solid #0B3B2E; text-align: left;">RÉFÉRENCE</th>
            <th style="background-color: #0B3B2E; color: #FFFFFF; font-weight: bold; font-size: 8pt; padding: 8px 7px; border: 1px solid #0B3B2E; text-align: left;">CLIENT</th>
            <th style="background-color: #0B3B2E; color: #FFFFFF; font-weight: bold; font-size: 8pt; padding: 8px 7px; border: 1px solid #0B3B2E; text-align: center;">TYPE</th>
            <th style="background-color: #0B3B2E; color: #FFFFFF; font-weight: bold; font-size: 8pt; padding: 8px 7px; border: 1px solid #0B3B2E; text-align: right;">MONTANT</th>
        </tr>
    </thead>
    <tbody>
        @foreach($transactions as $index => $transaction)
        <tr style="border-bottom: 1px solid #E5E7EB;">
            <td style="color: #334155; font-size: 8.5pt; padding: 8px 7px; {{ $index % 2 === 0 ? 'background-color: #F3F7F5;' : '' }} border: 1px solid #D9E5DE;">{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
            <td style="color: #334155; font-size: 8.5pt; padding: 8px 7px; {{ $index % 2 === 0 ? 'background-color: #F3F7F5;' : '' }} border: 1px solid #D9E5DE;">{{ $transaction->reference }}</td>
            <td style="color: #334155; font-size: 8.5pt; padding: 8px 7px; {{ $index % 2 === 0 ? 'background-color: #F3F7F5;' : '' }} border: 1px solid #D9E5DE;">{{ trim(($transaction->client->nom ?? '') . ' ' . ($transaction->client->prenoms ?? '')) ?: '—' }}</td>
            <td style="color: #334155; font-size: 8.5pt; padding: 8px 7px; text-align: center; {{ $index % 2 === 0 ? 'background-color: #F3F7F5;' : '' }} border: 1px solid #D9E5DE;">{{ $transaction->type_operation === 'depot' ? 'Dépôt' : 'Retrait' }}</td>
            <td style="color: {{ $transaction->type_operation === 'depot' ? '#166534' : '#B42318' }}; font-size: 8.5pt; padding: 8px 7px; text-align: right; font-weight: bold; {{ $index % 2 === 0 ? 'background-color: #F3F7F5;' : '' }} border: 1px solid #D9E5DE;">{{ number_format((float) $transaction->montant, 0, ',', ' ') }} FCFA</td>
        </tr>
        @endforeach
    </tbody>
</table>
