<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Lignes Budgétaires - Export PDF</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
<h2>Lignes budgétaires - Liste complète</h2>
<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Libellé</th>
        <th>Code</th>
        <th>N° Compte</th>
        <th>Description</th>
        <th>Date création</th>
    </tr>
    </thead>
    <tbody>
    @foreach($lignes as $ligne)
        <tr>
            <td>{{ $ligne->id }}</td>
            <td>{{ $ligne->libelle_ligne_budgetaire_entree }}</td>
            <td>{{ $ligne->code_ligne_budgetaire_entree }}</td>
            <td>{{ $ligne->numero_compte_ligne_budgetaire_entree }}</td>
            <td>{{ $ligne->description }}</td>
            <td>{{ $ligne->date_creation }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
