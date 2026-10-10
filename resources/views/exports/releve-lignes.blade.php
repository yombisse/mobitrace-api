<table border="0" cellpadding="4" cellspacing="0" style="width: 100%; margin-top: 10px; border-collapse: collapse;">
    <thead>
        <tr>
            <th style="background-color: #0B3B2E; color: #FFFFFF; font-weight: bold; font-size: 7pt; padding: 7px 4px; border: 1px solid #0B3B2E; width: 22mm;">DATE/HEURE</th>
            <th style="background-color: #0B3B2E; color: #FFFFFF; font-weight: bold; font-size: 7pt; padding: 7px 4px; border: 1px solid #0B3B2E; width: 22mm;">RÉFÉRENCE</th>
            <th style="background-color: #0B3B2E; color: #FFFFFF; font-weight: bold; font-size: 7pt; padding: 7px 4px; border: 1px solid #0B3B2E; width: 26mm;">N° CLIENT</th>
            <th style="background-color: #0B3B2E; color: #FFFFFF; font-weight: bold; font-size: 7pt; padding: 7px 4px; border: 1px solid #0B3B2E; width: 30mm;">CLIENT</th>
            <th style="background-color: #0B3B2E; color: #FFFFFF; font-weight: bold; font-size: 7pt; padding: 7px 4px; border: 1px solid #0B3B2E; width: 22mm;">N° CNIB</th>
            <th style="background-color: #0B3B2E; color: #FFFFFF; font-weight: bold; font-size: 7pt; padding: 7px 4px; border: 1px solid #0B3B2E; width: 22mm;">DÉLIVRANCE</th>
            <th style="background-color: #0B3B2E; color: #FFFFFF; font-weight: bold; font-size: 7pt; padding: 7px 4px; border: 1px solid #0B3B2E; width: 20mm;">EXPIRATION</th>
            <th style="background-color: #0B3B2E; color: #FFFFFF; font-weight: bold; font-size: 7pt; padding: 7px 4px; border: 1px solid #0B3B2E; width: 22mm;">RÉSEAU</th>
            <th style="background-color: #0B3B2E; color: #FFFFFF; font-weight: bold; font-size: 7pt; padding: 7px 4px; border: 1px solid #0B3B2E; width: 14mm;">TYPE</th>
            <th style="background-color: #0B3B2E; color: #FFFFFF; font-weight: bold; font-size: 7pt; padding: 7px 4px; border: 1px solid #0B3B2E; width: 19mm;">MONTANT</th>
            @if($hasSoldeApresOperation)
            <th style="background-color: #0B3B2E; color: #FFFFFF; font-weight: bold; font-size: 7pt; padding: 7px 4px; border: 1px solid #0B3B2E; width: 21mm;">SOLDE APRÈS</th>
            @endif
            <th style="background-color: #0B3B2E; color: #FFFFFF; font-weight: bold; font-size: 7pt; padding: 7px 4px; border: 1px solid #0B3B2E; width: 26mm;">CONSENTEMENT</th>
        </tr>
    </thead>
    <tbody>
        @foreach($transactions as $index => $transaction)
        <tr style="border-bottom: 1px solid #E5E7EB;">
            <td style="color: #111827; font-size: 7.5pt; padding: 6px 4px; {{ $index % 2 === 0 ? 'background-color: #FCFCFC;' : '' }} border: 1px solid #E5E7EB; width: 22mm;">{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
            <td style="color: #111827; font-size: 7.5pt; padding: 6px 4px; {{ $index % 2 === 0 ? 'background-color: #FCFCFC;' : '' }} border: 1px solid #E5E7EB; width: 22mm;">{{ $transaction->reference }}</td>
            <td style="color: #111827; font-size: 7.5pt; padding: 6px 4px; {{ $index % 2 === 0 ? 'background-color: #FCFCFC;' : '' }} border: 1px solid #E5E7EB; width: 26mm;">{{ $transaction->client->telephone }}</td>
            <td style="color: #111827; font-size: 7.5pt; padding: 6px 4px; {{ $index % 2 === 0 ? 'background-color: #FCFCFC;' : '' }} border: 1px solid #E5E7EB; width: 30mm;">{{ trim(($transaction->client->nom ?? '') . ' ' . ($transaction->client->prenoms ?? '')) ?: '—' }}</td>
            <td style="color: {{ $transaction->client->numero_piece ? '#111827' : '#4B5563' }}; font-size: 7.5pt; padding: 6px 4px; background-color: #FAFAF7; border: 1px solid #E5E7EB; width: 22mm;">{{ $transaction->client->numero_piece ?? '—' }}</td>
            <td style="color: {{ $transaction->client->date_delivrance_piece ? '#111827' : '#4B5563' }}; font-size: 7.5pt; padding: 6px 4px; text-align: center; background-color: #FAFAF7; border: 1px solid #E5E7EB; width: 22mm;">{{ $transaction->client->date_delivrance_piece ? $transaction->client->date_delivrance_piece->format('d/m/Y') : '—' }}</td>
            <td style="color: {{ $transaction->client->date_expiration_piece ? '#111827' : '#4B5563' }}; font-size: 7.5pt; padding: 6px 4px; text-align: center; background-color: #FAFAF7; border: 1px solid #E5E7EB; width: 20mm;">{{ $transaction->client->date_expiration_piece ? $transaction->client->date_expiration_piece->format('d/m/Y') : '—' }}</td>
            <td style="color: #111827; font-size: 7.5pt; padding: 6px 4px; {{ $index % 2 === 0 ? 'background-color: #FCFCFC;' : '' }} border: 1px solid #E5E7EB; width: 22mm;">{{ $transaction->reseau->nom }}</td>
            <td style="color: #111827; font-size: 7.5pt; padding: 6px 4px; text-align: center; {{ $index % 2 === 0 ? 'background-color: #FCFCFC;' : '' }} border: 1px solid #E5E7EB; width: 14mm;">{{ $transaction->type_operation === 'depot' ? 'Dépôt' : 'Retrait' }}</td>
            <td style="color: #111827; font-size: 7.5pt; padding: 6px 4px; text-align: right; font-weight: bold; {{ $index % 2 === 0 ? 'background-color: #FCFCFC;' : '' }} border: 1px solid #E5E7EB; width: 19mm;">{{ number_format((float) $transaction->montant, 0, ',', ' ') }}</td>
            @if($hasSoldeApresOperation)
            <td style="color: #111827; font-size: 7.5pt; padding: 6px 4px; text-align: right; {{ $index % 2 === 0 ? 'background-color: #FCFCFC;' : '' }} border: 1px solid #E5E7EB; width: 21mm;">
                @if($transaction->solde_apres_operation !== null)
                    {{ number_format((float) $transaction->solde_apres_operation, 0, ',', ' ') }}
                @else
                    —
                @endif
            </td>
            @endif
            <td style="color: #111827; font-size: 7.5pt; padding: 6px 4px; text-align: center; {{ $index % 2 === 0 ? 'background-color: #FCFCFC;' : '' }} border: 1px solid #E5E7EB; width: 26mm;">
                @if($transaction->consentement_confirme_le)
                    ✓ {{ $transaction->consentement_confirme_le->format('H:i') }}
                @else
                    —
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<div style="margin-top: 15px; font-size: 7pt; color: #6B7280; font-style: italic;">
    Note : "—" indique une fiche client créée avant le scan CNIB ou un solde non renseigné. Le corps du tableau utilise une police de 7.5pt pour éviter tout retour à la ligne.
</div>
