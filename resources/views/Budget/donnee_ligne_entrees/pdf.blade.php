{{--
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Données lignes budgétaires entrée</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background: #eee; }
    </style>
</head>
<body>
<h3 style="text-align: center;">Rapport - Données lignes budgétaires entrée</h3>
<table>
    <thead>
    <tr>
        <th>Libellé</th>
        <th>Code</th>
        <th>Compte</th>
        <th>Description</th>
        <th>Date</th>
        <th>Élément</th>
    </tr>
    </thead>
    <tbody>
    @foreach($lignes as $l)
        <tr>
            <td>{{ $l->donnee_ligne_budgetaire_entree }}</td>
            <td>{{ $l->code_donnee_ligne_budgetaire_entree }}</td>
            <td>{{ $l->numero_donne_ligne_budgetaire_entree }}</td>
            <td>{{ $l->description }}</td>
            <td>{{ $l->date_creation }}</td>
            <td>{{ $l->element_ligne_budgetaire_entrees->libelle_elements_ligne_budgetaire_entree ?? '-' }}</td>
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
    <title>Données lignes budgétaires entrée</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h3, h4, h5, h6 { margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; margin-bottom: 10px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; }
        th { background: #eee; }
        .total { font-weight: bold; background: #f5f5f5; }
    </style>
</head>
<body>
<h3 style="text-align: center;">📑 Rapport - Données lignes budgétaires entrée</h3>
<p><strong>Donnée parente :</strong> {{ $donnee->donnee_ligne_budgetaire_entree }}</p>

@forelse($grouped as $budget => $byBudget)
    <h4>💰 Budget : {{ $budget }}</h4>
    @php $totalBudget = 0; @endphp

    @foreach($byBudget as $ligne => $byLigne)
        <h5>📌 Ligne budgétaire : {{ $ligne }}</h5>
        @php $totalLigne = 0; @endphp

        @foreach($byLigne as $donneeLabel => $items)
            <h6>📝 Donnée : {{ $donneeLabel }}</h6>
            <table>
                <thead>
                <tr>
                    <th>Libellé</th>
                    <th>Code</th>
                    <th>Compte</th>
                    <th>Description</th>
                    <th>Date</th>
                    <th>Élément</th>
                    <th>Montant</th>
                </tr>
                </thead>
                <tbody>
                @php $totalDonnee = 0; @endphp
                @foreach($items as $l)
                    <tr>
                        <td>{{ $l->donnee_ligne_budgetaire_entree }}</td>
                        <td>{{ $l->code_donnee_ligne_budgetaire_entree }}</td>
                        <td>{{ $l->numero_donne_ligne_budgetaire_entree }}</td>
                        <td>{{ $l->description }}</td>
                        <td>{{ $l->date_creation }}</td>
                        <td>{{ $l->element_ligne_budgetaire_entrees->libelle_elements_ligne_budgetaire_entree ?? '-' }}</td>
                        <td>{{ number_format($l->montant, 0, ',', ' ') }}</td>
                    </tr>
                    @php $totalDonnee += $l->montant; @endphp
                @endforeach
                <tr class="total">
                    <td colspan="6" style="text-align:right;">TOTAL Donnée</td>
                    <td>{{ number_format($totalDonnee, 0, ',', ' ') }}</td>
                </tr>
                @php $totalLigne += $totalDonnee; @endphp
                </tbody>
            </table>
        @endforeach

        <p class="total">📌 Total Ligne « {{ $ligne }} » : {{ number_format($totalLigne, 0, ',', ' ') }}</p>
        @php $totalBudget += $totalLigne; @endphp
    @endforeach

    <p class="total">💰 Total Budget « {{ $budget }} » : {{ number_format($totalBudget, 0, ',', ' ') }}</p>
@empty
    <p>Aucune donnée disponible</p>
@endforelse
</body>
</html>
