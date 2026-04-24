<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Export PDF - Lignes Budgétaires Sorties</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background: #f2f2f2; }
        h2 { text-align: center; }
    </style>
</head>
<body>

<h2>Liste des Lignes Budgétaires Sorties</h2>

<table>
    <thead>
    <tr>
        <th>#</th>
        <th>Libellé</th>
        <th>Code</th>
        <th>N° Compte</th>
        <th>Description</th>
        <th>Date Création</th>
    </tr>
    </thead>
    <tbody>
    @php $i = 1; @endphp
    @foreach($lignes as $ligne)
        <tr>
            <td>{{ $i++ }}</td>
            <td>{{ $ligne->libelle_ligne_budgetaire_sortie }}</td>
            <td>{{ $ligne->code_ligne_budgetaire_sortie }}</td>
            <td>{{ $ligne->numero_compte_ligne_budgetaire_sortie }}</td>
            <td>{{ $ligne->description }}</td>
            <td>{{ $ligne->date_creation }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
