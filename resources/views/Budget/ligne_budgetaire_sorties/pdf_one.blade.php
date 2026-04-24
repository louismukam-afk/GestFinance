<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Export PDF - Ligne Budgétaire Sortie</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background: #f2f2f2; width: 30%; }
        h2 { text-align: center; }
    </style>
</head>
<body>

<h2>Détails de la Ligne Budgétaire Sortie</h2>

<table>
    <tr>
        <th>Libellé</th>
        <td>{{ $ligne->libelle_ligne_budgetaire_sortie }}</td>
    </tr>
    <tr>
        <th>Code</th>
        <td>{{ $ligne->code_ligne_budgetaire_sortie }}</td>
    </tr>
    <tr>
        <th>N° Compte</th>
        <td>{{ $ligne->numero_compte_ligne_budgetaire_sortie }}</td>
    </tr>
    <tr>
        <th>Description</th>
        <td>{{ $ligne->description }}</td>
    </tr>
    <tr>
        <th>Date Création</th>
        <td>{{ $ligne->date_creation }}</td>
    </tr>
</table>

</body>
</html>
