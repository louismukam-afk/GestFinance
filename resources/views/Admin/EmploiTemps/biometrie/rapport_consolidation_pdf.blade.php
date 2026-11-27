<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><style>
@page { margin: 32px; } body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #222; }
h1 { font-size: 18px; color: #183b58; } h2 { font-size: 12px; margin-top: 20px; color: #183b58; }
.alerte { padding: 8px; background: #fff2df; border-left: 3px solid #c77d13; }
table { width: 100%; border-collapse: collapse; margin-top: 8px; } th, td { border: 1px solid #bbb; padding: 5px; text-align: left; } th { background: #e9eef2; } tr { page-break-inside: avoid; }
</style></head><body>
<h1>Rapport de tentative de consolidation</h1>
<p><strong>Import #{{ $rapport['import_id'] }}</strong> - {{ $rapport['libelle'] ?: 'Sans libellé' }}<br>Période : {{ $rapport['date_debut'] }} au {{ $rapport['date_fin'] }}<br>Tentative du {{ $rapport['date_tentative'] }}<br>Résultat : <strong>{{ $rapport['statut'] === 'reussite' ? 'Réussite' : 'Échec' }}</strong></p>
@if($rapport['statut'] === 'echec')
<p class="alerte">La tentative a été annulée : aucune des séances traitées pendant cette tentative n'a été enregistrée. Une consolidation antérieure éventuelle est conservée.</p>
<h2>Raisons de l'échec</h2><ul>@foreach($rapport['erreurs'] as $erreur)<li>{{ $erreur }}</li>@endforeach</ul>
@else
<p>{{ count($rapport['traitees']) }} séance(s) consolidée(s) et {{ count($rapport['ignorees']) }} séance(s) déjà présentes dans un autre import.</p>
@endif
@if($rapport['statut'] === 'reussite' && count($rapport['traitees']))
<h2>Séances consolidées</h2><table><thead><tr><th>Enseignant</th><th>Date</th><th>Plage</th><th>Matière</th><th>Spécialité</th></tr></thead><tbody>
@foreach($rapport['traitees'] as $seance)<tr><td>{{ $seance['enseignant'] }}</td><td>{{ $seance['date'] }}</td><td>{{ $seance['plage'] }}</td><td>{{ $seance['matiere'] }}</td><td>{{ $seance['specialite'] }}</td></tr>@endforeach
</tbody></table>@endif
@if(count($rapport['ignorees']))<h2>Séances ignorées</h2><table><thead><tr><th>Enseignant</th><th>Date</th><th>Plage</th><th>Matière</th><th>Spécialité</th><th>Raison</th></tr></thead><tbody>
@foreach($rapport['ignorees'] as $seance)<tr><td>{{ $seance['enseignant'] }}</td><td>{{ $seance['date'] }}</td><td>{{ $seance['plage'] }}</td><td>{{ $seance['matiere'] }}</td><td>{{ $seance['specialite'] }}</td><td>{{ $seance['raison'] }}</td></tr>@endforeach
</tbody></table>@endif
</body></html>
