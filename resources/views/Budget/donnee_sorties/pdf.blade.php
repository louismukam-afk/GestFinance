{{--
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Données budgétaires de sortie</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background: #eee; }
    </style>
</head>
<body>
<h3 style="text-align: center;">Rapport - Données budgétaires de sortie</h3>
<table>
    <thead>
    <tr>
        <th>Libellé</th>
        <th>Code</th>
        <th>Compte</th>
        <th>Montant</th>
        <th>Budget</th>
        <th>Ligne budgétaire</th>
        <th>Date</th>
    </tr>
    </thead>
    <tbody>
    @foreach($donnees as $d)
        <tr>
            <td>{{ $d->donnee_ligne_budgetaire_sortie }}</td>
            <td>{{ $d->code_donnee_budgetaire_sortie }}</td>
            <td>{{ $d->numero_donnee_budgetaire_sortie }}</td>
            <td>{{ number_format($d->montant, 0, ',', ' ') }} FCFA</td>
            <td>{{ $d->budgets->libelle_ligne_budget ?? '-' }}</td>
            <td>{{ $d->ligne_budgetaire_sortie->libelle_ligne_budgetaire_sortie ?? '-' }}</td>
            <td>{{ $d->date_creation }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
--}}
        <!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport - Données budgétaires de sortie</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h3, h4, h5 { margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background: #eee; }
        .total-ligne { background: #f9f9f9; font-weight: bold; }
        .total-budget { background: #d9edf7; font-weight: bold; }
    </style>
</head>
<body>
<h3 style="text-align: center;">📊 Rapport - Données budgétaires de sortie</h3>

@forelse($grouped as $budget => $lignes)
    <h4>📘 Budget : {{ $budget }}</h4>
    @php $totalBudget = 0; @endphp

    @foreach($lignes as $ligne => $donnees)
        <h5>📌 Ligne budgétaire : {{ $ligne }}</h5>

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
            @php $totalLigne = 0; @endphp
            @foreach($donnees as $d)
                <tr>
                    <td>{{ $d->donnee_ligne_budgetaire_sortie }}</td>
                    <td>{{ $d->code_donnee_budgetaire_sortie }}</td>
                    <td>{{ $d->numero_donnee_budgetaire_sortie }}</td>
                    <td>{{ number_format($d->montant, 0, ',', ' ') }} FCFA</td>
                    <td>{{ $d->date_creation }}</td>
                </tr>
                @php $totalLigne += $d->montant; @endphp
            @endforeach
            <tr class="total-ligne">
                <td colspan="3" style="text-align: right;">Total ligne :</td>
                <td colspan="2">{{ number_format($totalLigne, 0, ',', ' ') }} FCFA</td>
            </tr>
            </tbody>
        </table>

        @php $totalBudget += $totalLigne; @endphp
    @endforeach

    <div class="total-budget">
        💰 Total Budget "{{ $budget }}" : {{ number_format($totalBudget, 0, ',', ' ') }} FCFA
    </div>
    <br>
@empty
    <p style="text-align: center; color: red;">Aucune donnée trouvée</p>
@endforelse
</body>
</html>
