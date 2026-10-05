<div style="font-size: 11pt; font-weight: bold; color: #333; margin: 15px 0 10px 0;">Ajustements de la session</div>
<table border="0" cellpadding="6" cellspacing="0" style="width: 100%; margin-top: 10px; border-collapse: collapse;">
    <thead>
        <tr>
            <th style="background-color: #0d4a2e; color: #FFFFFF; font-weight: bold; font-size: 7.5pt; padding: 8px 6px; border: 1px solid #0d4a2e; text-align: left;">Date/Heure</th>
            <th style="background-color: #0d4a2e; color: #FFFFFF; font-weight: bold; font-size: 7.5pt; padding: 8px 6px; border: 1px solid #0d4a2e; text-align: center;">Type</th>
            <th style="background-color: #0d4a2e; color: #FFFFFF; font-weight: bold; font-size: 7.5pt; padding: 8px 6px; border: 1px solid #0d4a2e; text-align: right;">Montant Float</th>
            <th style="background-color: #0d4a2e; color: #FFFFFF; font-weight: bold; font-size: 7.5pt; padding: 8px 6px; border: 1px solid #0d4a2e; text-align: right;">Montant Cash</th>
            <th style="background-color: #0d4a2e; color: #FFFFFF; font-weight: bold; font-size: 7.5pt; padding: 8px 6px; border: 1px solid #0d4a2e; text-align: left;">Motif</th>
        </tr>
    </thead>
    <tbody>
        @foreach($ajustements as $index => $ajustement)
        <tr style="border-bottom: 1px solid #E5E7EB;">
            <td style="color: #111827; font-size: 8pt; padding: 6px; {{ $index % 2 === 0 ? 'background-color: #FCFCFC;' : '' }} border: 1px solid #E5E7EB;">{{ $ajustement->created_at->format('d/m/Y H:i') }}</td>
            <td style="color: #111827; font-size: 8pt; padding: 6px; text-align: center; {{ $index % 2 === 0 ? 'background-color: #FCFCFC;' : '' }} border: 1px solid #E5E7EB;">{{ ucfirst($ajustement->type) }}</td>
            <td style="color: {{ $ajustement->montant_float >= 0 ? '#1F9D55' : '#D32F2F' }}; font-size: 8pt; padding: 6px; text-align: right; font-weight: bold; {{ $index % 2 === 0 ? 'background-color: #FCFCFC;' : '' }} border: 1px solid #E5E7EB;">{{ number_format((float) $ajustement->montant_float, 0, ',', ' ') }} FCFA</td>
            <td style="color: {{ $ajustement->montant_cash >= 0 ? '#1F9D55' : '#D32F2F' }}; font-size: 8pt; padding: 6px; text-align: right; font-weight: bold; {{ $index % 2 === 0 ? 'background-color: #FCFCFC;' : '' }} border: 1px solid #E5E7EB;">{{ number_format((float) $ajustement->montant_cash, 0, ',', ' ') }} FCFA</td>
            <td style="color: #111827; font-size: 8pt; padding: 6px; {{ $index % 2 === 0 ? 'background-color: #FCFCFC;' : '' }} border: 1px solid #E5E7EB;">{{ $ajustement->motif }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
