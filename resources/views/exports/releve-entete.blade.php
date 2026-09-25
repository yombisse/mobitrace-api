<div style="background-color: #1F9D55; padding: 12px 25px 10px 25px; margin-bottom: 15px; border-radius: 8px;">
    <div style="color: #FFFFFF; margin-bottom: 8px;">
        <div style="font-size: 14pt; font-weight: bold;">MobiTrace</div>
        <div style="font-size: 7.5pt; font-weight: normal;">Export des transactions — Journal d'audit</div>
    </div>
    <table border="0" cellpadding="0" cellspacing="0" style="width: 100%;">
        <tr>
            <td style="width: 25%; color: #FFFFFF; padding-right: 10px;">
                <div style="font-size: 6.5pt; color: #E8F5E9; font-weight: normal;">AGENT</div>
                <div style="font-size: 9pt; font-weight: bold;">{{ $agent->name }} ({{ $agent->code_agent ?? 'N/A' }})</div>
            </td>
            <td style="width: 25%; color: #FFFFFF; padding-right: 10px;">
                <div style="font-size: 6.5pt; color: #E8F5E9; font-weight: normal;">PÉRIODE EXPORTÉE</div>
                <div style="font-size: 9pt; font-weight: bold;">{{ $debut->format('d/m/Y') }} - {{ $fin->format('d/m/Y') }}</div>
            </td>
            <td style="width: 25%; color: #FFFFFF; padding-right: 10px;">
                <div style="font-size: 6.5pt; color: #E8F5E9; font-weight: normal;">RÉSEAU</div>
                <div style="font-size: 9pt; font-weight: bold;">{{ $reseau ?? 'Tous' }}</div>
            </td>
            <td style="width: 25%; color: #FFFFFF;">
                <div style="font-size: 6.5pt; color: #E8F5E9; font-weight: normal;">GÉNÉRÉ LE</div>
                <div style="font-size: 9pt; font-weight: bold;">{{ $generatedAt->format('d/m/Y H:i') }}</div>
            </td>
        </tr>
    </table>
</div>
