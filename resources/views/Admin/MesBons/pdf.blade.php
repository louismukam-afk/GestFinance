<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { text-align: center; font-size: 24px; margin-bottom: 4px; }
        .subtitle { text-align: center; font-size: 14px; margin-bottom: 18px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 6px; }
        th { background: #222; color: #fff; font-size: 12px; }
        td { font-size: 11px; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>{{ $type === 'valides' ? 'MES BONS VALIDES' : 'MES BONS EN ATTENTE DE VALIDATION' }}</h1>
    <div class="subtitle">
        Utilisateur : {{ $user->name }}
        @if($dateDebut || $dateFin)
            | Periode : {{ $dateDebut ?? 'Debut' }} - {{ $dateFin ?? 'Fin' }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Bon</th>
                <th>Description</th>
                <th>Entite</th>
                <th>Personnel</th>
                <th>Montant</th>
                <th>Statut</th>
                <th>PDG/PDF</th>
                <th>DAF</th>
                <th>Achats</th>
                <th>Emetteur</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bons as $bon)
                <tr>
                    <td>{{ $bon->date_debut }}</td>
                    <td>{{ $bon->nom_bon_commande }}</td>
                    <td>{{ $bon->description_bon_commande }}</td>
                    <td>{{ $bon->entites->nom_entite ?? '-' }}</td>
                    <td>{{ $bon->personnels->nom ?? '-' }}</td>
                    <td class="right">{{ number_format($bon->montant_total, 0, ',', ' ') }}</td>
                    <td>{{ $bon->statut_bon_libelle }}</td>
                    <td>{{ $bon->validation_pdg ? 'Oui' : 'Non' }}</td>
                    <td>{{ $bon->validation_daf ? 'Oui' : 'Non' }}</td>
                    <td>{{ $bon->validation_achats ? 'Oui' : 'Non' }}</td>
                    <td>{{ $bon->validation_emetteur ? 'Oui' : 'Non' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="text-align: center;">Aucun bon trouve.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
