<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>État des Bons de Commande</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background: #f2f2f2; }
        h2 { text-align: center; color: #1391e8; }
    </style>
</head>
<body>
<h2>État des Bons de Commande</h2>
<p>Période : {{ request('date_debut') }} → {{ request('date_fin') }}</p>
<li class="list-group-item"><strong>Entite :</strong> {{ $bon->entites->nom_entite ?? 'N/A' }}</li>
<table>
    <thead>
    <tr>
        <th>#</th>
        <th>Nom</th>
        <th>Période</th>
        <th>Montant Total</th>
        <th>Personnel</th>
        <th>Utilisateur</th>
    </tr>
    </thead>
    <tbody>
    @foreach($bons as $i => $bon)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $bon->nom_bon_commande }}</td>
            <td>{{ $bon->date_debut }} → {{ $bon->date_fin }}</td>
            <td>{{ number_format($bon->montant_total,0,',',' ') }} FCFA</td>
            <p><strong>Montant en lettre :</strong> {{ $bon->montant_lettre}} FCFA</p>
            <td>{{ $bon->personnels->nom ?? 'N/A' }}</td>
            <td>{{ $bon->user->name ?? 'N/A' }}</td>
        </tr>
    @endforeach
    </tbody>
    <p>
        <strong>Montant total bon :</strong>
        {{ number_format($bon->montant_total, 0, ',', ' ') }} FCFA <br>

        <strong>Montant réalisé :</strong>
        {{ number_format($bon->montant_realise, 0, ',', ' ') }} FCFA <br>

        <strong>Reste :</strong>
        {{ number_format($bon->reste, 0, ',', ' ') }} FCFA
    </p>
</table>
</body>
</html>
