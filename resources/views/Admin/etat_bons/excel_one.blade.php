<h3>Bon de Commande : {{ $bon->nom_bon_commande }}</h3>

<p><strong>Description :</strong> {{ $bon->description_bon_commande }}</p>
<p><strong>Personnel :</strong> {{ $bon->personnels->nom ?? 'N/A' }}</p>
<p><strong>Utilisateur :</strong> {{ $bon->user->name ?? 'N/A' }}</p>
<p><strong>Date Début :</strong> {{ $bon->date_debut }}</p>
<p><strong>Date Fin :</strong> {{ $bon->date_fin }}</p>
<p><strong>Montant Total :</strong> {{ number_format($bon->montant_total,0,',',' ') }}</p>

<h4>Éléments du Bon</h4>
<table>
    <thead>
    <tr>
        <th>#</th>
        <th>Nom Élément</th>
        <th>Description</th>
        <th>Quantité</th>
        <th>Prix Unitaire</th>
        <th>Montant Total</th>
    </tr>
    </thead>
    <tbody>
    @foreach($bon->element_bon_commandes as $i => $el)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $el->nom_element_bon_commande }}</td>
            <td>{{ $el->description_elements_bon_commande }}</td>
            <td>{{ $el->quantite_element_bon_commande }}</td>
            <td>{{ number_format($el->prix_unitaire_element_bon_commande,0,',',' ') }}</td>
            <td>{{ number_format($el->montant_total_element_bon_commande,0,',',' ') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
