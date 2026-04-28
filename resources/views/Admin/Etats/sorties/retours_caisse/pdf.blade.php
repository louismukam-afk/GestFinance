<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Retours en caisse</title>
    <style>
        body { font-family: DejaVu Sans; font-size: 11px; }
        h3 { text-align: center; font-size: 22px; margin: 8px 0; }
        .summary { text-align: center; font-size: 14px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 5px; font-size: 10px; }
        th { background: #EEE; }
        .right { text-align: right; }
        .total { font-weight: bold; background: #F0F0F0; }
    </style>
</head>
<body>
<h3>{{ $currentUserOnly ? 'MES RETOURS EN CAISSE' : 'RETOURS EN CAISSE' }}</h3>
<p class="summary">
    Periode : {{ $dateDebut ?? 'Debut' }} - {{ $dateFin ?? now()->format('Y-m-d') }}
    |
    Total : <strong>{{ number_format($total, 0, ',', ' ') }} FCFA</strong>
</p>
<table>
    <thead>
    <tr>
        <th>Date</th>
        <th>Numero</th>
        <th>Bon</th>
         <th>Motif décaissement</th>
        <th>Caisse</th>
        <th>Budget</th>
        <th>Ligne</th>
        <th>Element</th>
        <th>Donnee</th>
        <th>Annee</th>
        <th>Utilisateur</th>
        <th>Motif</th>
        <th class="right">Montant</th>
    </tr>
    </thead>
    <tbody>
    @foreach($retours as $retour)
        <tr>
            <td>{{ $retour->date_retour }}</td>
            <td>{{ $retour->numero_retour }}</td>
            <td>{{ $retour->bon->nom_bon_commande ?? '-' }}</td>
            <td>{{ $retour->decaissement->motif ?? '-' }}</td>
            <td>{{ $retour->caisse->nom_caisse ?? '-' }}</td>
            <td>{{ $retour->budget->libelle_ligne_budget ?? '-' }}</td>
            <td>{{ $retour->ligne_budgetaire_sortie->libelle_ligne_budgetaire_sortie ?? '-' }}</td>
            <td>{{ $retour->element_ligne_budgetaire_sortie->libelle_elements_ligne_budgetaire_sortie ?? '-' }}</td>
            <td>{{ $retour->donnee_ligne_budgetaire_sortie->donnee_ligne_budgetaire_sortie ?? '-' }}</td>
            <td>{{ $retour->annee_academique->nom ?? '-' }}</td>
            <td>{{ $retour->user->name ?? '-' }}</td>
            <td>{{ $retour->motif ?? '-' }}</td>
            <td class="right">{{ number_format($retour->montant, 0, ',', ' ') }}</td>
        </tr>
    @endforeach
    <tr class="total">
        <td colspan="11">TOTAL</td>
        <td class="right">{{ number_format($total, 0, ',', ' ') }}</td>
    </tr>
    </tbody>
</table>
</body>
</html>
