<table border="0" cellpadding="0" cellspacing="0" style="width: 100%; margin-bottom: 16px;">
    <tr>
        <td style="width: 64%; background-color: #0B3B2E; padding: 20px 21px; color: #FFFFFF;">
            <div style="font-size: 9pt; font-weight: bold; color: #BFE8D2; letter-spacing: 1px;">MOBITRACE · RAPPORT D'ACTIVITÉ</div>
            <div style="font-size: 19pt; font-weight: bold; color: #FFFFFF; margin-top: 9px;">Relevé de transactions</div>
            <div style="font-size: 9pt; color: #E5F3EB; margin-top: 7px;">Journal détaillé des opérations enregistrées</div>
        </td>
        <td style="width: 36%; background-color: #EAF4EE; padding: 18px 18px; text-align: right;">
            <div style="font-size: 7pt; color: #527064; font-weight: bold;">PÉRIODE</div>
            <div style="font-size: 10pt; color: #0B3B2E; font-weight: bold; margin-top: 7px;">{{ $debut->format('d/m/Y') }} — {{ $fin->format('d/m/Y') }}</div>
            <div style="font-size: 7pt; color: #64748B; margin-top: 10px;">Généré le {{ $generatedAt->format('d/m/Y à H:i') }}</div>
        </td>
    </tr>
</table>

<table border="0" cellpadding="0" cellspacing="0" style="width: 100%; margin-bottom: 16px;">
    <tr>
        <td style="width: 50%; padding: 10px 14px 10px 0; border-bottom: 1px solid #D9E5DE;">
            <div style="font-size: 7pt; color: #64748B;">AGENT</div>
            <div style="font-size: 9pt; color: #0F172A; font-weight: bold; margin-top: 5px;">{{ $agent->name }} · {{ $agent->code_agent ?? 'Sans code' }}</div>
        </td>
        <td style="width: 50%; padding: 10px 0 10px 14px; border-bottom: 1px solid #D9E5DE;">
            <div style="font-size: 7pt; color: #64748B;">RÉSEAU</div>
            <div style="font-size: 9pt; color: #0F172A; font-weight: bold; margin-top: 5px;">{{ $reseau ?? 'Tous les réseaux' }}</div>
        </td>
    </tr>
</table>
