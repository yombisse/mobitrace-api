<!-- SECTION "Résumé de la session" -->
<div style="margin-bottom: 15px;">
    <div style="font-size: 11pt; font-weight: bold; color: #333; margin-bottom: 10px;">Résumé de la session</div>
    <table border="0" cellpadding="0" cellspacing="0" style="width: 100%;">
        <tr>
            <td style="width: 50%; padding: 8px 10px 8px 0; border-right: 1px solid #e0e0e0;">
                <div style="font-size: 7pt; color: #666; margin-bottom: 2px;">Ouverture Float</div>
                <div style="font-size: 10pt; font-weight: bold; color: #000;">{{ number_format($session->solde_float_ouverture, 0, ',', ' ') }} FCFA</div>
            </td>
            <td style="width: 50%; padding: 8px 0 8px 10px;">
                <div style="font-size: 7pt; color: #666; margin-bottom: 2px;">Ouverture Cash</div>
                <div style="font-size: 10pt; font-weight: bold; color: #000;">{{ number_format($session->solde_cash_ouverture, 0, ',', ' ') }} FCFA</div>
            </td>
        </tr>
        <tr>
            <td style="width: 50%; padding: 8px 10px 8px 0; border-right: 1px solid #e0e0e0;">
                <div style="font-size: 7pt; color: #666; margin-bottom: 2px;">Statut</div>
                <div style="font-size: 10pt; font-weight: bold; color: {{ $session->statut === 'cloturee' ? '#1F9D55' : '#FF9800' }};">{{ ucfirst($session->statut) }}</div>
            </td>
            <td style="width: 50%; padding: 8px 0 8px 10px;">
                <div style="font-size: 7pt; color: #666; margin-bottom: 2px;">Transactions</div>
                <div style="font-size: 10pt; font-weight: bold; color: #000;">{{ $nombreTransactions }}</div>
            </td>
        </tr>
        <tr>
            <td style="width: 50%; padding: 8px 10px 8px 0; border-right: 1px solid #e0e0e0;">
                <div style="font-size: 7pt; color: #666; margin-bottom: 2px;">Total Dépôts</div>
                <div style="font-size: 10pt; font-weight: bold; color: #1F9D55;">{{ number_format($totalDepots, 0, ',', ' ') }} FCFA</div>
            </td>
            <td style="width: 50%; padding: 8px 0 8px 10px;">
                <div style="font-size: 7pt; color: #666; margin-bottom: 2px;">Total Retraits</div>
                <div style="font-size: 10pt; font-weight: bold; color: #D32F2F;">{{ number_format($totalRetraits, 0, ',', ' ') }} FCFA</div>
            </td>
        </tr>
        <tr>
            <td style="width: 50%; padding: 8px 10px 0 0; border-right: 1px solid #e0e0e0;">
                <div style="font-size: 7pt; color: #666; margin-bottom: 2px;">Ajustements</div>
                <div style="font-size: 10pt; font-weight: bold; color: #000;">{{ $nombreAjustements }}</div>
            </td>
            <td style="width: 50%; padding: 8px 0 0 10px;">
                <div style="font-size: 7pt; color: #666; margin-bottom: 2px;">Total Ajustements Float</div>
                <div style="font-size: 10pt; font-weight: bold; color: {{ $totalAjustementsFloat >= 0 ? '#1F9D55' : '#D32F2F' }};">{{ number_format($totalAjustementsFloat, 0, ',', ' ') }} FCFA</div>
            </td>
        </tr>
    </table>
</div>

@if($session->statut === 'cloturee')
<!-- SECTION "Solde e-money (float)" -->
<div style="margin-bottom: 15px;">
    <div style="font-size: 11pt; font-weight: bold; color: #333; margin-bottom: 10px;">Solde e-money (float)</div>
    <table border="0" cellpadding="8" cellspacing="0" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th style="background-color: #0d4a2e; color: #FFFFFF; font-weight: bold; font-size: 8pt; padding: 8px 10px; border: 1px solid #0d4a2e; text-align: left;">Libellé</th>
                <th style="background-color: #0d4a2e; color: #FFFFFF; font-weight: bold; font-size: 8pt; padding: 8px 10px; border: 1px solid #0d4a2e; text-align: right;">Montant FCFA</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="background-color: #f9f9f9; padding: 8px 10px; border: 1px solid #e0e0e0; font-size: 9pt;">Solde d'ouverture</td>
                <td style="background-color: #f9f9f9; padding: 8px 10px; border: 1px solid #e0e0e0; font-size: 9pt; text-align: right; font-weight: bold;">{{ number_format($session->solde_float_ouverture, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 10px; border: 1px solid #e0e0e0; font-size: 9pt;">Solde théorique</td>
                <td style="padding: 8px 10px; border: 1px solid #e0e0e0; font-size: 9pt; text-align: right; font-weight: bold;">{{ number_format($session->solde_float_theorique, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td style="background-color: #f9f9f9; padding: 8px 10px; border: 1px solid #e0e0e0; font-size: 9pt;">Solde réel constaté</td>
                <td style="background-color: #f9f9f9; padding: 8px 10px; border: 1px solid #e0e0e0; font-size: 9pt; text-align: right; font-weight: bold;">{{ number_format($session->solde_float_reel, 0, ',', ' ') }}</td>
            </tr>
        </tbody>
    </table>
    
    <!-- Encadré écart float -->
    <div style="margin-top: 10px; padding: 10px; border: 2px solid {{ $session->ecart_float < 0 ? '#D32F2F' : '#1F9D55' }}; border-radius: 5px; background-color: {{ $session->ecart_float < 0 ? '#fef2f2' : '#f0fdf4' }};">
        <table border="0" cellpadding="0" cellspacing="0" style="width: 100%;">
            <tr>
                <td style="width: 50%; padding-right: 10px;">
                    <div style="font-size: 9pt; font-weight: bold; color: #333;">Écart Float</div>
                </td>
                <td style="width: 50%; text-align: right;">
                    <div style="font-size: 12pt; font-weight: bold; color: {{ $session->ecart_float < 0 ? '#D32F2F' : '#1F9D55' }};">{{ number_format($session->ecart_float, 0, ',', ' ') }} FCFA</div>
                </td>
            </tr>
        </table>
    </div>
</div>

<!-- SECTION "Solde espèces (cash)" -->
<div style="margin-bottom: 15px;">
    <div style="font-size: 11pt; font-weight: bold; color: #333; margin-bottom: 10px;">Solde espèces (cash)</div>
    <table border="0" cellpadding="8" cellspacing="0" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th style="background-color: #0d4a2e; color: #FFFFFF; font-weight: bold; font-size: 8pt; padding: 8px 10px; border: 1px solid #0d4a2e; text-align: left;">Libellé</th>
                <th style="background-color: #0d4a2e; color: #FFFFFF; font-weight: bold; font-size: 8pt; padding: 8px 10px; border: 1px solid #0d4a2e; text-align: right;">Montant FCFA</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="background-color: #f9f9f9; padding: 8px 10px; border: 1px solid #e0e0e0; font-size: 9pt;">Solde d'ouverture</td>
                <td style="background-color: #f9f9f9; padding: 8px 10px; border: 1px solid #e0e0e0; font-size: 9pt; text-align: right; font-weight: bold;">{{ number_format($session->solde_cash_ouverture, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 10px; border: 1px solid #e0e0e0; font-size: 9pt;">Solde théorique</td>
                <td style="padding: 8px 10px; border: 1px solid #e0e0e0; font-size: 9pt; text-align: right; font-weight: bold;">{{ number_format($session->solde_cash_theorique, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td style="background-color: #f9f9f9; padding: 8px 10px; border: 1px solid #e0e0e0; font-size: 9pt;">Solde réel constaté</td>
                <td style="background-color: #f9f9f9; padding: 8px 10px; border: 1px solid #e0e0e0; font-size: 9pt; text-align: right; font-weight: bold;">{{ number_format($session->solde_cash_reel, 0, ',', ' ') }}</td>
            </tr>
        </tbody>
    </table>
    
    <!-- Encadré écart cash -->
    <div style="margin-top: 10px; padding: 10px; border: 2px solid {{ $session->ecart_cash < 0 ? '#D32F2F' : '#1F9D55' }}; border-radius: 5px; background-color: {{ $session->ecart_cash < 0 ? '#fef2f2' : '#f0fdf4' }};">
        <table border="0" cellpadding="0" cellspacing="0" style="width: 100%;">
            <tr>
                <td style="width: 50%; padding-right: 10px;">
                    <div style="font-size: 9pt; font-weight: bold; color: #333;">Écart Cash</div>
                </td>
                <td style="width: 50%; text-align: right;">
                    <div style="font-size: 12pt; font-weight: bold; color: {{ $session->ecart_cash < 0 ? '#D32F2F' : '#1F9D55' }};">{{ number_format($session->ecart_cash, 0, ',', ' ') }} FCFA</div>
                </td>
            </tr>
        </table>
    </div>
</div>

@if($session->justification)
<!-- SECTION Justification -->
<div style="margin-bottom: 15px;">
    <div style="font-size: 11pt; font-weight: bold; color: #333; margin-bottom: 10px;">Justification des écarts</div>
    <div style="padding: 10px; background-color: #fff3cd; border: 1px solid #ffeeba; border-radius: 5px;">
        <div style="font-size: 9pt; color: #856404;">{{ $session->justification }}</div>
    </div>
</div>
@endif
@endif
