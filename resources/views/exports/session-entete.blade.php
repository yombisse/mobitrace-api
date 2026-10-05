<!-- BANDEAU D'EN-TÊTE -->
<div style="background-color: #e8f5e9; padding: 10px; margin-bottom: 15px; border-bottom: 2px solid #0d4a2e; text-align: center;">
    <div style="color: #333;">
        <div style="font-size: 18pt; font-weight: bold; margin-bottom: 1px; color: #0d4a2e;">MobiTrace</div>
        <div style="font-size: 9pt; color: #666; margin-bottom: 6px;">Votre registre numérique</div>
        <div style="font-size: 14pt; font-weight: bold; margin-bottom: 2px; color: #333;">Bilan de session de caisse</div>
        <div style="font-size: 9pt; color: #666;">{{ $session->agentReseau->reseau->nom }} ({{ $session->agentReseau->reseau->code }}) · Session {{ ucfirst($session->statut) }}</div>
    </div>
</div>

<!-- BANDEAU INFOS (grille 2x2) -->
<div style="background-color: #ffffff; padding: 8px 20px; margin-bottom: 15px;">
    <table border="0" cellpadding="0" cellspacing="0" style="width: 100%;">
        <tr>
            <td style="width: 50%; padding: 5px 15px 5px 0; border-right: 1px solid #e0e0e0;">
                <div style="font-size: 7pt; color: #666; margin-bottom: 2px;">Agent</div>
                <div style="font-size: 9pt; font-weight: bold; color: #000;">{{ $session->agentReseau->user->name }} ({{ $session->agentReseau->user->code_agent ?? 'N/A' }})</div>
            </td>
            <td style="width: 50%; padding: 5px 0 5px 15px;">
                <div style="font-size: 7pt; color: #666; margin-bottom: 2px;">Réseau</div>
                <div style="font-size: 9pt; font-weight: bold; color: #000;">{{ $session->agentReseau->reseau->nom }} ({{ $session->agentReseau->reseau->code }})</div>
            </td>
        </tr>
        <tr>
            <td style="width: 50%; padding: 5px 15px 5px 0; border-right: 1px solid #e0e0e0;">
                <div style="font-size: 7pt; color: #666; margin-bottom: 2px;">Date session</div>
                <div style="font-size: 9pt; font-weight: bold; color: #000;">{{ \Carbon\Carbon::parse($session->date)->format('d/m/Y') }}</div>
            </td>
            <td style="width: 50%; padding: 5px 0 5px 15px;">
                <div style="font-size: 7pt; color: #666; margin-bottom: 2px;">Généré le</div>
                <div style="font-size: 9pt; font-weight: bold; color: #000;">{{ $generatedAt->format('d/m/Y H:i') }}</div>
            </td>
        </tr>
    </table>
</div>
