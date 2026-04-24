<table>
    <thead>
    <tr>
        <th>#</th>
        <th>Nom</th>
        <th>Description</th>
        <th>Personnel</th>
        <th>Utilisateur</th>
        <th>Date Début</th>
        <th>Date Fin</th>
        <th>Montant Total</th>
        <th>Montant Réalisé</th>
        <th>Reste</th>
        <th>Statut</th>
    </tr>
    </thead>
    <tbody>
    @foreach($bons as $i => $bon)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $bon->nom_bon_commande }}</td>
            <td>{{ $bon->description_bon_commande }}</td>
            <td>{{ $bon->personnels->nom ?? 'N/A' }}</td>
            <td>{{ $bon->user->name ?? 'N/A' }}</td>
            <td>{{ $bon->date_debut }}</td>
            <td>{{ $bon->date_fin }}</td>
            <td>{{ number_format($bon->montant_total,0,',',' ') }}</td>
            <td>{{ number_format($bon->montant_realise,0,',',' ') }}</td>
            <td>{{ number_format($bon->reste,0,',',' ') }}</td>
            <td>
                @if($bon->statuts == 1)
                    Validé
                @elseif($bon->statuts == 2)
                    Rejeté
                @else
                    En attente
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
