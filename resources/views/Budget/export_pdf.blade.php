<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Export PDF - Budgets</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
<h2 style="text-align: center;">📑 Liste des Budgets</h2>

<table>
    <thead>
    <tr>
        <th>#</th>
        <th>Libellé</th>
        <th>Code</th>
        <th>Description</th>
        <th>Date Début</th>
        <th>Date Fin</th>
        <th>Montant Global</th>
        <th>Utilisateur</th>
    </tr>
    </thead>
    <tbody>
    @foreach($budgets as $i => $budget)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $budget->libelle_ligne_budget }}</td>
            <td>{{ $budget->code_budget }}</td>
            <td>{{ $budget->description }}</td>
            <td>{{ $budget->date_debut }}</td>
            <td>{{ $budget->date_fin }}</td>
            <td>{{ number_format($budget->montant_global, 0, ',', ' ') }} FCFA</td>
            <td>{{ $budget->user->name ?? 'N/A' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
