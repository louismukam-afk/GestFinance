<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><style>
@page { margin: 32px; } body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #222; }
h1 { font-size: 18px; color: #183b58; } p { line-height: 1.5; } table { width: 100%; border-collapse: collapse; margin-top: 10px; }
th, td { border: 1px solid #bbb; padding: 5px; text-align: left; } th { background: #e9eef2; } tr { page-break-inside: avoid; }
</style></head><body>
<h1>Rapport de chargement biométrique</h1>
<p><strong>Import #{{ $import->id }}</strong> - {{ $import->libelle ?: 'Sans libellé' }}<br>Période : {{ $import->date_debut->format('d/m/Y') }} au {{ $import->date_fin->format('d/m/Y') }}<br>Analyse effectuée le {{ $apercu['date_analyse'] }}<br>Pointages conservés : {{ $import->total_lignes }} ; non associés : {{ $import->total_non_associees }}.
@if(isset($apercu['mode_complementaire']))<br>Mode : {{ $apercu['mode_complementaire'] ? 'import complémentaire' : 'import standard' }} ; pointages écartés : {{ $apercu['ecartes'] }} ; hors personnel avec cours : {{ $apercu['hors_personnel'] }}.@endif</p>
<p>Ce rapport analyse les emplois du temps et les consolidations existant au moment indiqué ci-dessus. Il ne valide pas la présence, la consolidation ou le paiement. Le nombre de pointages est celui de la journée entière avant éventuel filtrage, pas une attribution à cette plage. Les identités non associées doivent être vérifiées séparément. Les journées contenant une nouvelle plage ou un conflit sont conservées avec tous leurs pointages pour préserver les entrées et sorties partagées.</p>
<table><thead><tr><th>Enseignant</th><th>Date</th><th>Plage</th><th>Matière / spécialité</th><th>Pointages du jour</th><th>Analyse</th></tr></thead><tbody>
@forelse($apercu['lignes'] as $ligne)<tr><td>{{ $ligne['enseignant'] }}</td><td>{{ $ligne['date'] }}</td><td>{{ $ligne['plage'] }}</td><td>{{ $ligne['matiere'] }} / {{ $ligne['specialite'] }}</td><td>{{ $ligne['pointages_jour'] }}</td><td>@if($ligne['etat'] === 'conflit')Chevauchement à vérifier : {{ $ligne['raison'] }}@elseif($ligne['etat'] === 'deja_consolidee')Déjà consolidée : sera ignorée@elseNouvelle plage à examiner lors de la consolidation@endif</td></tr>
@empty<tr><td colspan="6">Aucune plage planifiée trouvée pour les enseignants associés à cet import.</td></tr>@endforelse
</tbody></table></body></html>
