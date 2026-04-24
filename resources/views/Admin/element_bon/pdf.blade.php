<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Éléments du Bon {{ $bon->nom_bon_commande }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid black; padding: 6px; text-align: left; }
        th { background: #eee; }
    </style>
</head>
<body>
<h3>Éléments du Bon : {{ $bon->nom_bon_commande }}</h3>
<p><strong>Montant du Bon :</strong> {{ number_format($bon->montant_total, 0, ',', ' ') }} FCFA</p>

<table>
    <thead>
    <tr>
        <th>#</th>
        <th>Nom Élément</th>
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
            <td>{{ number_format($el->prix_unitaire_element_bon_commande, 0, ',', ' ') }} FCFA</td>
            <td>{{ number_format($el->montant_total_element_bon_commande, 0, ',', ' ') }} FCFA</td>
            <td>{{ $el->date_realisation }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
