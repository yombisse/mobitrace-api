<h1>Relevé des Transactions - MobiTrace</h1>
<p><strong>Agent:</strong> {{ $agent->name }} ({{ $agent->code_agent ?? 'N/A' }})</p>
<p><strong>Période:</strong> du {{ $debut->format('d/m/Y') }} au {{ $fin->format('d/m/Y') }}</p>
@if($reseau)
<p><strong>Réseau:</strong> {{ $reseau }}</p>
@endif
<p><strong>Généré le:</strong> {{ $generatedAt->format('d/m/Y H:i:s') }}</p>
<hr />
