<h3>Bon de Commande : {{ $bon->nom_bon_commande }}</h3>
<p>Description : {{ $bon->description_bon_commande }}</p>
<p>Période : {{ $bon->date_debut }} au {{ $bon->date_fin }}</p>
<p>Montant Total : {{ $bon->montant_total }}</p>
<p>Personnel : {{ $bon->personnels->nom ?? 'N/A' }}</p>
<p>Utilisateur : {{ $bon->user->name ?? 'N/A' }}</p>

<h4>Éléments du Bon</h4>
<table>
    <thead>
    <tr>
        <th>#</th>
        <th>Élément</th>
        <th>Description</th>
        <th>Quantité</th>
        <th>PU</th>
        <th>Montant Total</th>
    </tr>
    </thead>
    <tbody>
    @foreach($elements as $i => $el)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $el->nom_element_bon_commande }}</td>
            <td>{{ $el->description_elements_bon_commande }}</td>
            <td>{{ $el->quantite_element_bon_commande }}</td>
            <td>{{ $el->prix_unitaire_element_bon_commande }}</td>
            <td>{{ $el->montant_total_element_bon_commande }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
