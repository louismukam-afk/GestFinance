<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        h2 { margin: 0 0 6px 0; color: #1f4e79; }
        .muted { color: #666; }
        .meta { margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 6px; vertical-align: top; }
        th { background: #1f4e79; color: #fff; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; background: #e8f1fb; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h2>{{ $title }}</h2>
    <div class="meta">
        <strong>Personnel :</strong> {{ $personnel->nom }}<br>
        <strong>Type :</strong> {{ ucfirst($personnel->type_personnel ?? 'permanent') }}<br>
        <strong>Horaire :</strong> {{ str_replace('_', ' ', $personnel->horaire_travail ?? '-') }}<br>
        <strong>Mode horaire :</strong> {{ $personnel->mode_horaire ?? 'strict' }} / {{ $personnel->categorie_horaire ?? 'standard' }}<br>
        <span class="muted">Document genere le {{ now()->format('d/m/Y H:i') }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Jour</th>
                <th>Plage</th>
                <th>Heures</th>
                <th>Annee / Entite</th>
                <th>Periode</th>
                <th>Statut</th>
                <th>Observations</th>
            </tr>
        </thead>
        <tbody>
            @forelse($emplois as $emploi)
                <tr>
                    <td>{{ $jours[$emploi->jour_semaine] ?? '-' }}</td>
                    <td>{{ $emploi->plage->libelle ?? '-' }}</td>
                    <td>
                        {{ $emploi->plage ? substr($emploi->plage->heure_debut, 0, 5) : '-' }}
                        -
                        {{ $emploi->plage ? substr($emploi->plage->heure_fin, 0, 5) : '-' }}
                    </td>
                    <td>{{ $emploi->annee_academique->nom ?? 'Toutes' }} / {{ $emploi->entite->nom_entite ?? 'Toutes' }}</td>
                    <td>{{ optional($emploi->date_debut)->format('d/m/Y') }} - {{ optional($emploi->date_fin)->format('d/m/Y') ?: '...' }}</td>
                    <td><span class="badge">{{ $emploi->statut }}</span></td>
                    <td>{{ $emploi->observations }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="right">Aucun emploi permanent affecte.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
