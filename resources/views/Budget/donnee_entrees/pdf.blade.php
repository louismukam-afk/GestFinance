<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Données budgétaires d’entrée</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background: #eee; }
        h4 { margin-top: 20px; }
    </style>
</head>
<body>
<h3 style="text-align: center;">Rapport - Données budgétaires d’entrée</h3>

@foreach($grouped as $budget => $lignes)
    <h4>📘 Budget : {{ $budget }}</h4>
    @foreach($lignes as $ligne => $donnees)
        <h5>🔹 Ligne budgétaire : {{ $ligne }}</h5>
        <table>
            <thead>
            <tr>
                <th>Libellé</th>
                <th>Code</th>
                <th>Compte</th>
                <th>Montant</th>
                <th>Date</th>
            </tr>
            </thead>
            <tbody>
            @php $total = 0; @endphp
            @foreach($donnees as $d)
                <tr>
                    <td>{{ $d->donnee_ligne_budgetaire_entree }}</td>
                    <td>{{ $d->code_donnee_budgetaire_entree }}</td>
                    <td>{{ $d->numero_donnee_budgetaire_entree }}</td>
                    <td>{{ number_format($d->montant, 0, ',', ' ') }} FCFA</td>
                    <td>{{ $d->date_creation }}</td>
                </tr>
                @php $total += $d->montant; @endphp
            @endforeach
            <tr>
                <td colspan="3"><strong>Total Ligne</strong></td>
                <td colspan="2"><strong>{{ number_format($total, 0, ',', ' ') }} FCFA</strong></td>
            </tr>
            </tbody>
        </table>
    @endforeach
@endforeach
</body>
</html>
