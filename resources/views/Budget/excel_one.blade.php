<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Export Budget #{{ $budget->id }}</title>
</head>
<body>
<h2 style="text-align: center;">📑 Budget : {{ $budget->libelle_ligne_budget }}</h2>

<table border="1" cellspacing="0" cellpadding="5" width="100%">
    <tbody>
    <tr>
        <th align="left">Code</th>
        <td>{{ $budget->code_budget }}</td>
    </tr>
    <tr>
        <th align="left">Description</th>
        <td>{{ $budget->description }}</td>
    </tr>
    <tr>
        <th align="left">Date Début</th>
        <td>{{ $budget->date_debut }}</td>
    </tr>
    <tr>
        <th align="left">Date Fin</th>
        <td>{{ $budget->date_fin }}</td>
    </tr>
    <tr>
        <th align="left">Date Création</th>
        <td>{{ $budget->date_creation }}</td>
    </tr>
    <tr>
        <th align="left">Montant Global</th>
        <td>{{ number_format($budget->montant_global, 0, ',', ' ') }} FCFA</td>
    </tr>
    <tr>
        <th align="left">Utilisateur</th>
        <td>{{ $budget->user->name ?? 'N/A' }}</td>
    </tr>
    </tbody>
</table>
</body>
</html>
