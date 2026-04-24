<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détail Budget</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2 { text-align: center; margin-bottom: 20px; }
        ul { list-style: none; padding: 0; }
        li { margin-bottom: 8px; }
    </style>
</head>
<body>
<h2>📑 Détail du Budget</h2>

<ul>
    <li><strong>Libellé :</strong> {{ $budget->libelle_ligne_budget }}</li>
    <li><strong>Code :</strong> {{ $budget->code_budget }}</li>
    <li><strong>Description :</strong> {{ $budget->description }}</li>
    <li><strong>Période :</strong> {{ $budget->date_debut }} → {{ $budget->date_fin }}</li>
    <li><strong>Montant Global :</strong> {{ number_format($budget->montant_global,0,',',' ') }} FCFA</li>
    <li><strong>Date Création :</strong> {{ $budget->date_creation }}</li>
    <li><strong>Utilisateur :</strong> {{ $budget->user->name ?? 'N/A' }}</li>
</ul>
</body>
</html>
