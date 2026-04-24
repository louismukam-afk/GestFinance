<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ligne Budgétaire - Export PDF</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background: #f0f0f0; width: 200px; }
    </style>
</head>
<body>
<h2>Détails de la ligne budgétaire</h2>
<table>
    <tr><th>ID</th><td>{{ $ligne->id }}</td></tr>
    <tr><th>Libellé</th><td>{{ $ligne->libelle_ligne_budgetaire_entree }}</td></tr>
    <tr><th>Code</th><td>{{ $ligne->code_ligne_budgetaire_entree }}</td></tr>
    <tr><th>N° Compte</th><td>{{ $ligne->numero_compte_ligne_budgetaire_entree }}</td></tr>
    <tr><th>Description</th><td>{{ $ligne->description }}</td></tr>
    <tr><th>Date Création</th><td>{{ $ligne->date_creation }}</td></tr>
</table>
</body>
</html>
