<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Export PDF - Budget {{ $budget->libelle_ligne_budget }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; }
        th { background: #f2f2f2; }
        h2 { text-align: center; color: #1391e8; }
    </style>
</head>
<body>
<h2>📑 Budget : {{ $budget->libelle_ligne_budget }}</h2>

<p><strong>Code :</strong> {{ $budget->code_budget }}</p>
<p><strong>Description :</strong> {{ $budget->description }}</p>
<p><strong>Période :</strong> {{ $budget->date_debut }} → {{ $budget->date_fin }}</p>
<p><strong>Date de création :</strong> {{ $budget->date_creation }}</p>
<p><strong>Montant global :</strong> {{ number_format($budget->montant_global, 0, ',', ' ') }} FCFA</p>
<p><strong>Utilisateur :</strong> {{ $budget->user->name ?? 'N/A' }}</p>

<hr>
<p style="text-align: center;">✅ Document généré automatiquement depuis GestFinance</p>
</body>
</html>
