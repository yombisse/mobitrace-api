<table border="0" cellpadding="0" cellspacing="0" style="width: 100%; margin-top: 4px; margin-bottom: 18px;">
    <tr>
        <td style="width: 19.2%; background-color: #F1F5F3; padding: 13px 9px; border-bottom: 2px solid #CBD9D1; text-align: center;">
            <div style="font-size: 7pt; color: #64748B; font-weight: bold; margin-bottom: 4px;">OPÉRATIONS</div>
            <div style="font-size: 12pt; color: #0F172A; font-weight: bold; margin-top: 6px;">{{ $count }}</div>
        </td>
        <td style="width: 1%;"></td>
        <td style="width: 19.2%; background-color: #EAF4EE; padding: 13px 9px; border-bottom: 2px solid #74B895; text-align: center;">
            <div style="font-size: 7pt; color: #397153; font-weight: bold; margin-bottom: 4px;">VOLUME TOTAL</div>
            <div style="font-size: 9pt; color: #0F172A; font-weight: bold; margin-top: 6px;">{{ number_format((float) $totalMontant, 0, ',', ' ') }} FCFA</div>
        </td>
        <td style="width: 1%;"></td>
        <td style="width: 19.2%; background-color: #EAF4EE; padding: 13px 9px; border-bottom: 2px solid #74B895; text-align: center;">
            <div style="font-size: 7pt; color: #397153; font-weight: bold; margin-bottom: 4px;">TOTAL DÉPÔTS</div>
            <div style="font-size: 9pt; color: #166534; font-weight: bold; margin-top: 6px;">{{ number_format((float) $totalDepots, 0, ',', ' ') }} FCFA</div>
        </td>
        <td style="width: 1%;"></td>
        <td style="width: 19.2%; background-color: #FDF0EF; padding: 13px 9px; border-bottom: 2px solid #E7A6A0; text-align: center;">
            <div style="font-size: 7pt; color: #9F3832; font-weight: bold; margin-bottom: 4px;">TOTAL RETRAITS</div>
            <div style="font-size: 9pt; color: #B42318; font-weight: bold; margin-top: 6px;">{{ number_format((float) $totalRetraits, 0, ',', ' ') }} FCFA</div>
        </td>
        <td style="width: 1%;"></td>
        <td style="width: 19.2%; background-color: #EAF4EE; padding: 13px 9px; border-bottom: 2px solid #0B3B2E; text-align: center;">
            <div style="font-size: 7pt; color: #397153; font-weight: bold; margin-bottom: 4px;">SOLDE NET</div>
            <div style="font-size: 9pt; color: #0B3B2E; font-weight: bold; margin-top: 6px;">{{ $soldeNet >= 0 ? '+' : '−' }}{{ number_format(abs((float) $soldeNet), 0, ',', ' ') }} FCFA</div>
        </td>
    </tr>
</table>
