<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h1 { text-align: center; font-size: 22px; margin-bottom: 4px; }
        .subtitle { text-align: center; margin-bottom: 14px; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #333; padding: 4px; word-wrap: break-word; }
        th { background: #222; color: white; font-size: 9px; }
        td { font-size: 8px; }
        .right { text-align: right; }
        .totals td { font-weight: bold; background: #eee; }
    </style>
</head>
<body>
    <h1>ETAT DES ETUDIANTS ET DE LA SCOLARITE</h1>
    <div class="subtitle">
        Type : {{ ucfirst(str_replace('_', ' ', $typeRapport)) }}
        @if($dateDebut || $dateFin)
            | Periode : {{ $dateDebut ?? 'Debut' }} - {{ $dateFin ?? 'Fin' }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Matricule</th>
                <th>Etudiant</th>
                <th>Cycle</th>
                <th>Filiere</th>
                <th>Niveau</th>
                <th>Specialite</th>
                <th>Annee</th>
                <th>Entite</th>
                <th>Budget</th>
                <th>Ligne</th>
                <th>Element</th>
                <th>Donnee budgetaire</th>
                <th>Donnee ligne</th>
                <th>Tranche</th>
                <th>Facture</th>
                <th>Paye</th>
                <th>Reste</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row['matricule'] ?: '-' }}</td>
                    <td>{{ $row['etudiant']->nom ?? '-' }}</td>
                    <td>{{ $row['cycle'] ?: '-' }}</td>
                    <td>{{ $row['filiere'] ?: '-' }}</td>
                    <td>{{ $row['niveau'] ?: '-' }}</td>
                    <td>{{ $row['specialite'] ?: '-' }}</td>
                    <td>{{ $row['annee'] ?: '-' }}</td>
                    <td>{{ $row['entite'] ?: '-' }}</td>
                    <td>{{ $row['budget'] ?: '-' }}</td>
                    <td>{{ $row['ligne'] ?: '-' }}</td>
                    <td>{{ $row['element'] ?: '-' }}</td>
                    <td>{{ $row['donnee_budgetaire'] ?: '-' }}</td>
                    <td>{{ $row['donnee_ligne'] ?: '-' }}</td>
                    <td>{{ $row['tranche'] ?: '-' }}</td>
                    <td class="right">{{ number_format($row['facture'], 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($row['paye'], 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($row['reste'], 0, ',', ' ') }}</td>
                    <td>{{ $row['statut_paiement'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="18" style="text-align: center;">Aucune donnee trouvee.</td>
                </tr>
            @endforelse
            <tr class="totals">
                <td colspan="14">Totaux</td>
                <td class="right">{{ number_format($totalFacture, 0, ',', ' ') }}</td>
                <td class="right">{{ number_format($totalPaye, 0, ',', ' ') }}</td>
                <td class="right">{{ number_format($totalReste, 0, ',', ' ') }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
