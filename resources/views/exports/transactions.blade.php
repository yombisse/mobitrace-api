<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Transactions - MobiTrace</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 18px;
            color: #333;
        }
        .header-info {
            margin: 5px 0;
        }
        .header-info strong {
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }
        td {
            border: 1px solid #ddd;
            padding: 6px;
        }
        .totals {
            margin-top: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        .consentement {
            color: #28a745;
            font-weight: bold;
        }
        .sans-consentement {
            color: #999;
        }
        .statut-enregistree {
            color: #28a745;
            font-weight: bold;
        }
        .statut-modifiee {
            color: #fd7e14;
            font-weight: bold;
        }
        .statut-annulee {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Export des Transactions - MobiTrace</h1>
        <div class="header-info">
            <strong>Agent:</strong> {{ $agent->name }} ({{ $agent->code_agent ?? 'N/A' }})
        </div>
        <div class="header-info">
            <strong>Période:</strong> du {{ $debut->format('d/m/Y') }} au {{ $fin->format('d/m/Y') }}
        </div>
        @if($reseau)
        <div class="header-info">
            <strong>Réseau:</strong> {{ $reseau }}
        </div>
        @endif
        <div class="header-info">
            <strong>Généré le:</strong> {{ $generatedAt->format('d/m/Y H:i:s') }}
        </div>
        <div class="header-info">
            <strong>Nombre de transactions:</strong> {{ $transactions->count() }}
        </div>
        <div class="header-info">
            <strong>Montant total:</strong> {{ number_format((float) $totalMontant, 2, ',', ' ') }} FCFA
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date/Heure</th>
                <th>Référence</th>
                <th>Numéro Client</th>
                <th>Réseau</th>
                <th>Type</th>
                <th>Montant (FCFA)</th>
                <th>Consentement</th>
                @if($hasSoldeApresOperation)
                <th>Solde après opération</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
            <tr>
                <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $transaction->id }}</td>
                <td>{{ $transaction->client->telephone }}</td>
                <td>{{ $transaction->reseau->nom }}</td>
                <td>{{ $transaction->type_operation === 'depot' ? 'Dépôt' : 'Retrait' }}</td>
                <td>{{ number_format((float) $transaction->montant, 2, ',', ' ') }}</td>
                <td>
                    @if($transaction->consentement_confirme_le)
                        <span class="consentement">✓ {{ $transaction->consentement_confirme_le->format('H:i') }}</span>
                    @else
                        <span class="sans-consentement">—</span>
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

    <div class="totals">
        <strong>Total:</strong> {{ number_format((float) $totalMontant, 2, ',', ' ') }} FCFA pour {{ $transactions->count() }} transaction(s)
    </div>
</body>
</html>
