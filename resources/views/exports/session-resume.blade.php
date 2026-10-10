<table border="0" cellpadding="0" cellspacing="0" style="width: 100%; margin-bottom: 16px;">
    <tr>
        <td style="width: 49%; background-color: #F1F5F3; padding: 13px 14px; border-bottom: 2px solid #CBD9D1;">
            <div style="font-size: 8pt; color: #64748B; font-weight: bold; margin-bottom: 4px;">TRANSACTIONS</div>
            <div style="font-size: 13pt; color: #0F172A; font-weight: bold; margin-top: 6px;">{{ $nombreTransactions }}</div>
            <div style="font-size: 8pt; color: #64748B; margin-top: 4px;">{{ number_format((float) $totalDepots, 0, ',', ' ') }} FCFA de dépôts</div>
        </td>
        <td style="width: 2%;"></td>
        <td style="width: 49%; background-color: #FDF0EF; padding: 13px 14px; border-bottom: 2px solid #E7A6A0;">
            <div style="font-size: 8pt; color: #9F3832; font-weight: bold; margin-bottom: 4px;">RETRAITS</div>
            <div style="font-size: 13pt; color: #B42318; font-weight: bold; margin-top: 6px;">{{ number_format((float) $totalRetraits, 0, ',', ' ') }} FCFA</div>
            <div style="font-size: 8pt; color: #64748B; margin-top: 4px;">Volume des retraits enregistrés</div>
        </td>
    </tr>
</table>

<table border="0" cellpadding="0" cellspacing="0" style="width: 100%; margin-bottom: 18px;">
    <tr>
        <td style="width: 24%; padding: 10px; background-color: #F8FAF9;">
            <div style="font-size: 8pt; color: #64748B;">STATUT</div>
            <div style="font-size: 10pt; color: {{ $session->statut === 'cloturee' ? '#166534' : '#9A6700' }}; font-weight: bold; margin-top: 5px;">{{ ucfirst($session->statut) }}</div>
        </td>
        <td style="width: 1%;"></td>
        <td style="width: 24%; padding: 10px; background-color: #F8FAF9;">
            <div style="font-size: 8pt; color: #64748B;">OUVERTURE · FLOAT</div>
            <div style="font-size: 9.5pt; color: #0F172A; font-weight: bold; margin-top: 5px;">{{ number_format((float) $session->solde_float_ouverture, 0, ',', ' ') }} FCFA</div>
        </td>
        <td style="width: 1%;"></td>
        <td style="width: 24%; padding: 10px; background-color: #F8FAF9;">
            <div style="font-size: 8pt; color: #64748B;">OUVERTURE · CASH</div>
            <div style="font-size: 9.5pt; color: #0F172A; font-weight: bold; margin-top: 5px;">{{ number_format((float) $session->solde_cash_ouverture, 0, ',', ' ') }} FCFA</div>
        </td>
        <td style="width: 1%;"></td>
        <td style="width: 25%; padding: 10px; background-color: #F8FAF9;">
            <div style="font-size: 8pt; color: #64748B;">AJUSTEMENTS</div>
            <div style="font-size: 9.5pt; color: #0F172A; font-weight: bold; margin-top: 5px;">{{ $nombreAjustements }} opération(s)</div>
        </td>
    </tr>
</table>

<div style="font-size: 11pt; font-weight: bold; color: #0B3B2E; margin: 12px 0 9px 0;">Mouvements d'ajustement</div>
<table border="0" cellpadding="8" cellspacing="0" style="width: 100%; margin-bottom: 17px; border-collapse: collapse;">
    <thead>
        <tr>
            <th style="width: 34%; background-color: #0B3B2E; color: #FFFFFF; font-size: 8pt; padding: 8px; text-align: left;">INDICATEUR</th>
            <th style="width: 33%; background-color: #0B3B2E; color: #FFFFFF; font-size: 8pt; padding: 8px; text-align: right;">FLOAT</th>
            <th style="width: 33%; background-color: #0B3B2E; color: #FFFFFF; font-size: 8pt; padding: 8px; text-align: right;">CASH</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="background-color: #F1F5F3; border: 1px solid #D9E5DE; color: #334155; font-size: 9pt; padding: 8px;">Total des ajustements</td>
            <td style="background-color: #F1F5F3; border: 1px solid #D9E5DE; color: {{ $totalAjustementsFloat >= 0 ? '#166534' : '#B42318' }}; font-size: 9pt; font-weight: bold; padding: 8px; text-align: right;">{{ number_format((float) $totalAjustementsFloat, 0, ',', ' ') }} FCFA</td>
            <td style="background-color: #F1F5F3; border: 1px solid #D9E5DE; color: {{ $totalAjustementsCash >= 0 ? '#166534' : '#B42318' }}; font-size: 9pt; font-weight: bold; padding: 8px; text-align: right;">{{ number_format((float) $totalAjustementsCash, 0, ',', ' ') }} FCFA</td>
        </tr>
    </tbody>
</table>

@if($session->statut === 'cloturee')
<div style="font-size: 11pt; font-weight: bold; color: #0B3B2E; margin: 12px 0 9px 0;">Rapprochement à la clôture</div>
<table border="0" cellpadding="8" cellspacing="0" style="width: 100%; margin-bottom: 16px; border-collapse: collapse;">
    <thead>
        <tr>
            <th style="width: 34%; background-color: #0B3B2E; color: #FFFFFF; font-size: 8pt; padding: 8px; text-align: left;">SOLDE</th>
            <th style="width: 22%; background-color: #0B3B2E; color: #FFFFFF; font-size: 8pt; padding: 8px; text-align: right;">THÉORIQUE</th>
            <th style="width: 22%; background-color: #0B3B2E; color: #FFFFFF; font-size: 8pt; padding: 8px; text-align: right;">RÉEL</th>
            <th style="width: 22%; background-color: #0B3B2E; color: #FFFFFF; font-size: 8pt; padding: 8px; text-align: right;">ÉCART</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="background-color: #F8FAF9; border: 1px solid #D9E5DE; color: #334155; font-size: 9pt; padding: 8px;">Float</td>
            <td style="border: 1px solid #D9E5DE; color: #0F172A; font-size: 9pt; padding: 8px; text-align: right;">{{ number_format((float) $session->solde_float_theorique, 0, ',', ' ') }}</td>
            <td style="border: 1px solid #D9E5DE; color: #0F172A; font-size: 9pt; padding: 8px; text-align: right;">{{ number_format((float) $session->solde_float_reel, 0, ',', ' ') }}</td>
            <td style="background-color: {{ $session->ecart_float < 0 ? '#FDF0EF' : '#EAF4EE' }}; border: 1px solid #D9E5DE; color: {{ $session->ecart_float < 0 ? '#B42318' : '#166534' }}; font-size: 9pt; font-weight: bold; padding: 8px; text-align: right;">{{ number_format((float) $session->ecart_float, 0, ',', ' ') }}</td>
        </tr>
        <tr>
            <td style="background-color: #F8FAF9; border: 1px solid #D9E5DE; color: #334155; font-size: 9pt; padding: 8px;">Cash</td>
            <td style="border: 1px solid #D9E5DE; color: #0F172A; font-size: 9pt; padding: 8px; text-align: right;">{{ number_format((float) $session->solde_cash_theorique, 0, ',', ' ') }}</td>
            <td style="border: 1px solid #D9E5DE; color: #0F172A; font-size: 9pt; padding: 8px; text-align: right;">{{ number_format((float) $session->solde_cash_reel, 0, ',', ' ') }}</td>
            <td style="background-color: {{ $session->ecart_cash < 0 ? '#FDF0EF' : '#EAF4EE' }}; border: 1px solid #D9E5DE; color: {{ $session->ecart_cash < 0 ? '#B42318' : '#166534' }}; font-size: 9pt; font-weight: bold; padding: 8px; text-align: right;">{{ number_format((float) $session->ecart_cash, 0, ',', ' ') }}</td>
        </tr>
    </tbody>
</table>

@if($session->justification)
<table border="0" cellpadding="8" cellspacing="0" style="width: 100%; margin-bottom: 10px; background-color: #FFF8E6; border-left: 3px solid #D99A00;">
    <tr>
        <td style="font-size: 7pt; color: #805B00; font-weight: bold;">JUSTIFICATION DE CLÔTURE</td>
    </tr>
    <tr>
        <td style="font-size: 8pt; color: #59400A;">{{ $session->justification }}</td>
    </tr>
</table>
@endif
@endif

@if($session->justification_ouverture)
<table border="0" cellpadding="8" cellspacing="0" style="width: 100%; margin-bottom: 10px; background-color: #FFF8E6; border-left: 3px solid #D99A00;">
    <tr>
        <td style="font-size: 7pt; color: #805B00; font-weight: bold;">JUSTIFICATION DE L'OUVERTURE</td>
    </tr>
    <tr>
        <td style="font-size: 8pt; color: #59400A;">{{ $session->justification_ouverture }}</td>
    </tr>
</table>
@endif
