{{--
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport Données lignes budgétaires sorties</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background: #eee; }
    </style>
</head>
<body>
<h3 style="text-align: center;">Rapport - Données lignes budgétaires sorties</h3>
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
            <td>{{ $l->donnee_ligne_budgetaire_sortie }}</td>
            <td>{{ $l->code_donnee_ligne_budgetaire_sortie }}</td>
            <td>{{ $l->numero_donne_ligne_budgetaire_sortie }}</td>
            <td>{{ $l->description }}</td>
            <td>{{ $l->date_creation }}</td>
            <td>{{ $l->element_ligne_budgetaire_sorties->libelle_elements_ligne_budgetaire_sortie ?? '-' }}</td>
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
    <title>Rapport Données lignes budgétaires sorties</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2, h3 { margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background: #eee; }
    </style>
</head>
<body>
<h2 style="text-align:center;">Rapport - Données lignes budgétaires sorties</h2>

@foreach($grouped as $budget => $lignesByBudget)
    <h3>Budget : {{ $budget }}</h3>

    @foreach($lignesByBudget as $ligne => $donneesByLigne)
        <h4>Ligne : {{ $ligne }}</h4>

        @foreach($donneesByLigne as $donnee => $items)
            <h5>Donnée : {{ $donnee }}</h5>
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
                @foreach($items as $l)
                    <tr>
                        <td>{{ $l->donnee_ligne_budgetaire_sortie }}</td>
                        <td>{{ $l->code_donnee_ligne_budgetaire_sortie }}</td>
                        <td>{{ $l->numero_donnee_ligne_budgetaire_sortie }}</td>
                        <td>{{ $l->description }}</td>
                        <td>{{ $l->date_creation }}</td>
                        <td>{{ $l->element_ligne_budgetaire_sorties->libelle_elements_ligne_budgetaire_sortie ?? '-' }}</td>
                        <td>{{ number_format($l->montant, 0, ',', ' ') }} FCFA</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endforeach
    @endforeach
@endforeach
</body>
</html>
