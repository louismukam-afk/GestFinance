<table>
    <li class="list-group-item"><strong>Entite :</strong> {{ $bon->entites->nom_entite ?? 'N/A' }}</li>
    <p>
        <strong>Montant total bon :</strong>
        {{ number_format($bon->montant_total, 0, ',', ' ') }} FCFA <br>

        <strong>Montant réalisé :</strong>
        {{ number_format($bon->montant_realise, 0, ',', ' ') }} FCFA <br>

        <strong>Reste :</strong>
        {{ number_format($bon->reste, 0, ',', ' ') }} FCFA
    </p>
    <thead>
    <tr>
        <th>#</th>
        <th>Nom</th>
        <th>Description</th>
        <th>Date début</th>
        <th>Date fin</th>
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
            <td>{{ $bon->description_bon_commande }}</td>
            <td>{{ $bon->date_debut }}</td>
            <td>{{ $bon->date_fin }}</td>
            <td>{{ $bon->montant_total }}</td>
            <td>{{ $bon->personnels->nom ?? 'N/A' }}</td>
            <td>{{ $bon->user->name ?? 'N/A' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
