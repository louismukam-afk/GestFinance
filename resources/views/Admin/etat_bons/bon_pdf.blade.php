<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bon de Commande #{{ $bon->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background: #f2f2f2; }
        h2, h3 { text-align: center; }
    </style>
</head>
<body>
<h2>Bon de Commande :  BC{{$bon->id}}</h2>
<h3>{{ $bon->nom_bon_commande }}</h3>

<p><strong>Description :</strong> {{ $bon->description_bon_commande }}</p>
<p><strong>Période :</strong> {{ $bon->date_debut }} → {{ $bon->date_fin }}</p>
<p><strong>Montant Total :</strong> {{ number_format($bon->montant_total,0,',',' ') }} FCFA</p>
<p><strong>Montant en lettre :</strong> {{ $bon->montant_lettre}} FCFA</p>
<p><strong>Montant Réalisé :</strong> {{ number_format($bon->montant_realise,0,',',' ') }} FCFA</p>
<p><strong>Reste :</strong> {{ number_format($bon->reste,0,',',' ') }} FCFA</p>
<p><strong>Personnel :</strong> {{ $bon->personnels->nom ?? 'N/A' }}</p>
<p><strong>Utilisateur :</strong> {{ $bon->user->name ?? 'N/A' }}</p>
<li class="list-group-item"><strong>Entite :</strong> {{ $bon->entites->nom_entite ?? 'N/A' }}</li>
<h3>Éléments du Bon</h3>
<table>
    <thead>
    <tr>
        <th>#</th>
        <th>Nom</th>
        <th>Description</th>
        <th>Quantité</th>
        <th>Prix Unitaire</th>
        <th>Montant Total</th>
        <th>Date Réalisation</th>
    </tr>
    </thead>
    <tbody>
    @foreach($elements as $i => $el)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $el->nom_element_bon_commande }}</td>
            <td>{{ $el->description_elements_bon_commande }}</td>
            <td>{{ $el->quantite_element_bon_commande }}</td>
            <td>{{ number_format($el->prix_unitaire_element_bon_commande,0,',',' ') }} FCFA</td>
            <td>{{ number_format($el->montant_total_element_bon_commande,0,',',' ') }} FCFA</td>
            <td>{{ $el->date_realisation }}</td>
        </tr>
    @endforeach
    <p>
        <strong>Montant total bon :</strong>
        {{ number_format($bon->montant_total, 0, ',', ' ') }} FCFA <br>

        <strong>Montant réalisé :</strong>
        {{ number_format($bon->montant_realise, 0, ',', ' ') }} FCFA <br>

        <strong>Reste :</strong>
        {{ number_format($bon->reste, 0, ',', ' ') }} FCFA
    </p>: {{ number_format($bon->montant_total - $elements->sum('montant_total_element_bon_commande')) }}
    </p>
    </tbody>
</table>
</body>
</html>
