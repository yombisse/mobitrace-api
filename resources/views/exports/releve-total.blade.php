<table border="0" cellpadding="0" cellspacing="0" style="width: 100%; margin-bottom: 15px;">
    <tr>
        <td style="width: 25%; background-color: #EAF7EF; padding: 10px; border-radius: 6px; text-align: center;">
            <div style="font-size: 6.5pt; color: #15803D; font-weight: normal; margin-bottom: 3px;">TRANSACTIONS</div>
            <div style="font-size: 10pt; color: #111827; font-weight: bold;">{{ $count }}</div>
        </td>
        <td style="width: 2%;"></td>
        <td style="width: 25%; background-color: #EAF7EF; padding: 10px; border-radius: 6px; text-align: center;">
            <div style="font-size: 6.5pt; color: #15803D; font-weight: normal; margin-bottom: 3px;">DÉPÔTS</div>
            <div style="font-size: 10pt; color: #111827; font-weight: bold;">+ {{ number_format((float) $totalDepots, 0, ',', ' ') }}</div>
        </td>
        <td style="width: 2%;"></td>
        <td style="width: 25%; background-color: #EAF7EF; padding: 10px; border-radius: 6px; text-align: center;">
            <div style="font-size: 6.5pt; color: #15803D; font-weight: normal; margin-bottom: 3px;">RETRAITS</div>
            <div style="font-size: 10pt; color: #111827; font-weight: bold;">− {{ number_format((float) $totalRetraits, 0, ',', ' ') }}</div>
        </td>
        <td style="width: 2%;"></td>
        <td style="width: 25%; background-color: #EAF7EF; padding: 10px; border-radius: 6px; text-align: center;">
            <div style="font-size: 6.5pt; color: #15803D; font-weight: normal; margin-bottom: 3px;">SOLDE NET</div>
            <div style="font-size: 10pt; color: #111827; font-weight: bold;">{{ $soldeNet >= 0 ? '+' : '' }}{{ number_format((float) $soldeNet, 0, ',', ' ') }}</div>
        </td>
    </tr>
</table>
