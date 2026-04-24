<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>État des Budgets</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid black; }
        th { background: #f2f2f2; text-align: center; }
        td { padding: 5px; text-align: center; }
        h2 { text-align: center; margin-bottom: 10px; }
    </style>
</head>
<body>
<h2>📑 État des Budgets</h2>
@if($date_debut && $date_fin)
    <p><strong>Période :</strong> {{ $date_debut }} → {{ $date_fin }}</p>
@endif

<table>
    <thead>
    <tr>
        <th>#</th>
        <th>Libellé</th>
        <th>Période</th>
        <th>Montant Global</th>
        <th>Code Budget</th>
        <th>Date Création</th>
        <th>Utilisateur</th>
    </tr>
    </thead>
    <tbody>
    @foreach($budgets as $i => $budget)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $budget->libelle_ligne_budget }}</td>
            <td>{{ $budget->date_debut }} → {{ $budget->date_fin }}</td>
            <td>{{ number_format($budget->montant_global,0,',',' ') }} FCFA</td>
            <td>{{ $budget->code_budget }}</td>
            <td>{{ $budget->date_creation }}</td>
            <td>{{ $budget->user->name ?? 'N/A' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
