<div style="font-size: 11pt; font-weight: bold; color: #0B3B2E; margin: 17px 0 10px 0;">Détail des ajustements</div>
<table border="0" cellpadding="8" cellspacing="0" style="width: 100%; margin-top: 12px; border-collapse: collapse;">
    <thead>
        <tr>
            <th style="background-color: #0B3B2E; color: #FFFFFF; font-weight: bold; font-size: 8pt; padding: 8px 7px; border: 1px solid #0B3B2E; text-align: left;">DATE / HEURE</th>
            <th style="background-color: #0B3B2E; color: #FFFFFF; font-weight: bold; font-size: 8pt; padding: 8px 7px; border: 1px solid #0B3B2E; text-align: center;">TYPE</th>
            <th style="background-color: #0B3B2E; color: #FFFFFF; font-weight: bold; font-size: 8pt; padding: 8px 7px; border: 1px solid #0B3B2E; text-align: right;">FLOAT</th>
            <th style="background-color: #0B3B2E; color: #FFFFFF; font-weight: bold; font-size: 8pt; padding: 8px 7px; border: 1px solid #0B3B2E; text-align: right;">CASH</th>
            <th style="background-color: #0B3B2E; color: #FFFFFF; font-weight: bold; font-size: 8pt; padding: 8px 7px; border: 1px solid #0B3B2E; text-align: left;">MOTIF</th>
        </tr>
    </thead>
    <tbody>
        @foreach($ajustements as $index => $ajustement)
        <tr style="border-bottom: 1px solid #E5E7EB;">
            <td style="color: #334155; font-size: 8.5pt; padding: 8px 7px; {{ $index % 2 === 0 ? 'background-color: #F3F7F5;' : '' }} border: 1px solid #D9E5DE;">{{ $ajustement->created_at->format('d/m/Y H:i') }}</td>
            <td style="color: #334155; font-size: 8.5pt; padding: 8px 7px; text-align: center; {{ $index % 2 === 0 ? 'background-color: #F3F7F5;' : '' }} border: 1px solid #D9E5DE;">{{ ucfirst($ajustement->type) }}</td>
            <td style="color: {{ $ajustement->montant_float >= 0 ? '#166534' : '#B42318' }}; font-size: 8.5pt; padding: 8px 7px; text-align: right; font-weight: bold; {{ $index % 2 === 0 ? 'background-color: #F3F7F5;' : '' }} border: 1px solid #D9E5DE;">{{ number_format((float) $ajustement->montant_float, 0, ',', ' ') }} FCFA</td>
            <td style="color: {{ $ajustement->montant_cash >= 0 ? '#166534' : '#B42318' }}; font-size: 8.5pt; padding: 8px 7px; text-align: right; font-weight: bold; {{ $index % 2 === 0 ? 'background-color: #F3F7F5;' : '' }} border: 1px solid #D9E5DE;">{{ number_format((float) $ajustement->montant_cash, 0, ',', ' ') }} FCFA</td>
            <td style="color: #334155; font-size: 8.5pt; padding: 8px 7px; {{ $index % 2 === 0 ? 'background-color: #F3F7F5;' : '' }} border: 1px solid #D9E5DE;">{{ $ajustement->motif }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
