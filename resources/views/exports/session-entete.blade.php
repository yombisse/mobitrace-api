<table border="0" cellpadding="0" cellspacing="0" style="width: 100%; margin-bottom: 17px;">
    <tr>
        <td style="width: 64%; background-color: #0B3B2E; padding: 20px 21px;">
            <div style="font-size: 9pt; font-weight: bold; color: #BFE8D2;">MOBITRACE · RAPPORT DE CAISSE</div>
            <div style="font-size: 20pt; font-weight: bold; color: #FFFFFF; margin-top: 9px;">Bilan de session</div>
            <div style="font-size: 9pt; color: #E5F3EB; margin-top: 7px;">{{ $session->agentReseau->reseau->nom }} · {{ \Carbon\Carbon::parse($session->date)->format('d/m/Y') }}</div>
        </td>
        <td style="width: 36%; background-color: #EAF4EE; padding: 18px 18px; text-align: right;">
            <div style="font-size: 7pt; color: #527064; font-weight: bold;">STATUT DE LA SESSION</div>
            <div style="font-size: 13pt; color: {{ $session->statut === 'cloturee' ? '#166534' : '#9A6700' }}; font-weight: bold; margin-top: 7px;">{{ strtoupper($session->statut) }}</div>
            <div style="font-size: 8pt; color: #64748B; margin-top: 10px;">Édité le {{ $generatedAt->format('d/m/Y à H:i') }}</div>
        </td>
    </tr>
</table>

<table border="0" cellpadding="0" cellspacing="0" style="width: 100%; margin-bottom: 17px;">
    <tr>
        <td style="width: 50%; padding: 10px 14px 10px 0; border-bottom: 1px solid #D9E5DE;">
            <div style="font-size: 8pt; color: #64748B;">AGENT</div>
            <div style="font-size: 10pt; color: #0F172A; font-weight: bold; margin-top: 5px;">{{ $session->agentReseau->user->name }} · {{ $session->agentReseau->user->code_agent ?? 'Sans code' }}</div>
        </td>
        <td style="width: 50%; padding: 10px 0 10px 14px; border-bottom: 1px solid #D9E5DE;">
            <div style="font-size: 8pt; color: #64748B;">RÉSEAU</div>
            <div style="font-size: 10pt; color: #0F172A; font-weight: bold; margin-top: 5px;">{{ $session->agentReseau->reseau->nom }} · {{ $session->agentReseau->reseau->code }}</div>
        </td>
    </tr>
    <tr>
        <td style="width: 50%; padding: 10px 14px 0 0;">
            <div style="font-size: 8pt; color: #64748B;">OUVERTE LE</div>
            <div style="font-size: 9pt; color: #334155; margin-top: 5px;">{{ $session->ouverte_at?->format('d/m/Y à H:i') ?? '—' }}</div>
        </td>
        <td style="width: 50%; padding: 10px 0 0 14px;">
            <div style="font-size: 8pt; color: #64748B;">CLÔTURÉE LE</div>
            <div style="font-size: 9pt; color: #334155; margin-top: 5px;">{{ $session->cloturee_at?->format('d/m/Y à H:i') ?? 'Session en cours' }}</div>
        </td>
    </tr>
</table>
