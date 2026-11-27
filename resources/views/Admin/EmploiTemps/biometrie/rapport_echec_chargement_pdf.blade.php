<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><style>body{font-family:DejaVu Sans,sans-serif;font-size:11px;line-height:1.5} h1{color:#8b1e1e;font-size:20px}</style></head><body>
<h1>Échec du chargement biométrique</h1><p>Tentative du {{ $rapport['date'] }}.</p>
<p>Le chargement n'a pas abouti. Aucun nouveau résultat de consolidation n'a été produit par cette tentative.</p>
<ul>@foreach($rapport['erreurs'] as $erreur)<li>{{ $erreur }}</li>@endforeach</ul>
</body></html>
