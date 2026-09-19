<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; margin-top: 20px;">
    <thead>
        <tr>
            <th style="background-color: #f5f5f5;">Date/Heure</th>
            <th style="background-color: #f5f5f5;">Référence</th>
            <th style="background-color: #f5f5f5;">Numéro Client</th>
            <th style="background-color: #f5f5f5;">Réseau</th>
            <th style="background-color: #f5f5f5;">Type</th>
            <th style="background-color: #f5f5f5;">Montant (FCFA)</th>
            <th style="background-color: #f5f5f5;">Consentement</th>
            @if($hasSoldeApresOperation)
            <th style="background-color: #f5f5f5;">Solde après opération</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach($transactions as $transaction)
        <tr>
            <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
            <td>{{ $transaction->reference }}</td>
            <td>{{ $transaction->client->telephone }}</td>
            <td>{{ $transaction->reseau->nom }}</td>
            <td>{{ $transaction->type_operation === 'depot' ? 'Dépôt' : 'Retrait' }}</td>
            <td>{{ number_format((float) $transaction->montant, 2, ',', ' ') }}</td>
            <td>
                @if($transaction->consentement_confirme_le)
                    ✓ {{ $transaction->consentement_confirme_le->format('H:i') }}
                @else
                    —
                @endif
            </td>
            @if($hasSoldeApresOperation)
            <td>
                @if($transaction->solde_apres_operation !== null)
                    {{ number_format((float) $transaction->solde_apres_operation, 2, ',', ' ') }}
                @else
                    —
                @endif
            </td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>
