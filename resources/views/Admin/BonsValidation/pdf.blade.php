<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h1 { text-align: center; font-size: 20px; margin-bottom: 4px; }
        .subtitle { text-align: center; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 4px; }
        th { background: #222; color: #fff; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>VALIDATION DES BONS - {{ strtoupper($niveauLabel) }}</h1>
    <div class="subtitle">
        @if($dateDebut || $dateFin)
            Periode : {{ $dateDebut ?? 'Debut' }} - {{ $dateFin ?? 'Fin' }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Bon</th>
                <th>Description</th>
                <th>Emetteur</th>
                <th>Entite</th>
                <th>Personnel</th>
                <th>Montant</th>
                <th>Statut</th>
                <th>PDG</th>
                <th>DAF</th>
                <th>Achats</th>
                <th>Emetteur</th>
                <th>Motif refus</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bons as $bon)
                <tr>
                    <td>{{ $bon->date_debut }}</td>
                    <td>{{ $bon->nom_bon_commande }}</td>
                    <td>{{ $bon->description_bon_commande }}</td>
                    <td>{{ $bon->user->name ?? '-' }}</td>
                    <td>{{ $bon->entites->nom_entite ?? '-' }}</td>
                    <td>{{ $bon->personnels->nom ?? '-' }}</td>
                    <td class="right">{{ number_format($bon->montant_total, 0, ',', ' ') }}</td>
                    <td>{{ $bon->statut_bon_libelle }}</td>
                    <td>{{ $bon->validationState('pdg') }}</td>
                    <td>{{ $bon->validationState('daf') }}</td>
                    <td>{{ $bon->validationState('achats') }}</td>
                    <td>{{ $bon->validationState('emetteur') }}</td>
                    <td>{{ $bon->motif_refus ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" style="text-align:center;">Aucun bon trouve.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
