<table class="table table-striped table-condensed table-bordered">
    <thead>
    <tr>
        <th>#</th>
        <th>Nom</th>
        <th>Description</th>
        <th>Date début</th>
        <th>Date fin</th>
        <th>Date entrée signature</th>
        <th>Date validation</th>
        <th>Montant Total</th>
        <th>Montant Réalisé</th>
        <th>Reste</th>
        <th>Montant Lettre</th>
        <th>Personnel</th>
        <th>Entité</th>
        <th>Statut</th>
        <th></th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    @php $i = 1; @endphp
    @foreach($bon_comandes as $personnel_id => $group_bons)
        @foreach($group_bons as $bon)
        <tr>
            <td>{{ $i++ }}</td>
            <td>{{ $bon->nom_bon_commande }}</td>
            <td>{{ $bon->description_bon_commande }}</td>
            <td>{{ $bon->date_debut }}</td>
            <td>{{ $bon->date_fin }}</td>
            <td>{{ $bon->date_entree_signature }}</td>
            <td>{{ $bon->date_validation }}</td>
            <td>{{ number_format($bon->montant_total,0,',',' ') }} FCFA</td>
            <td>{{ number_format($bon->montant_realise,0,',',' ') }} FCFA</td>
            <td>{{ number_format($bon->reste,0,',',' ') }} FCFA</td>
            <td>{{ $bon->montant_lettre }}</td>
            <td>{{ $bon->personnels->nom ?? 'N/A' }}</td>
            <td>{{ $bon->entites->nom_entite ?? 'N/A' }}</td>

            {{-- ✅ Affichage clair du statut --}}
            <td>
                @if($bon->statuts == 0)
                    <span class="label label-warning">En attente</span>
                @elseif($bon->statuts == 1)
                    <span class="label label-success">Validé</span>
                @elseif($bon->statuts == 2)
                    <span class="label label-danger">Rejeté</span>
                @else
                    <span class="label label-default">Inconnu</span>
                @endif
            </td>

            <td>
                @if($bon->validation_pdg == 0)
                    <form action="{{ route('valider_pdg_bon', $bon->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-xs btn-success" style="margin:2px;">Valider PDG</button>
                    </form>
                @endif

                @if($bon->validation_daf == 0)
                    <form action="{{ route('valider_daf_bon', $bon->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-xs btn-info" style="margin:2px;">Valider DAF</button>
                    </form>
                @endif

                @if($bon->validation_achats == 0)
                    <form action="{{ route('valider_achats_bon', $bon->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-xs btn-warning" style="margin:2px;">Valider Achats</button>
                    </form>
                @endif

                @if($bon->validation_emetteur == 0)
                    <form action="{{ route('valider_emetteur_bon', $bon->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-xs btn-primary" style="margin:2px;">Valider Émetteur</button>
                    </form>
                @endif

                <a href="#" class="btn btn-xs btn-default" style="margin:2px; background-color:#eee;">
                    ➕ Ajouter éléments
                </a>
            </td>

            <td>
                <a href="#edit_bon" data-toggle="modal" data-backdrop="false"
                   onclick="edit_bon(
                   {{ $bon->id }},
                           '{{ $bon->nom_bon_commande }}',
                           '{{ $bon->description_bon_commande }}',
                           '{{ $bon->date_debut }}',
                           '{{ $bon->date_fin }}',
                           '{{ $bon->date_entree_signature }}',
                           '{{ $bon->montant_total }}',
                           '{{ $bon->montant_realise }}',
                           '{{ $bon->reste }}',
                           '{{ $bon->montant_lettre }}',
                           '{{ $bon->id_personnel }}',
                           '{{ $bon->id_entite }}'
                           )"
                   class="btn btn-xs btn-primary">
                    <span class="glyphicon glyphicon-edit"></span>
                </a>

                <form action="{{ route('delete_bon_commande', $bon->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-xs btn-danger" onclick="return confirm('Supprimer ce bon de commande ?')">
                        <span class="glyphicon glyphicon-trash"></span>
                    </button>
                </form>
            </td>
        </tr>
        @endforeach
    @endforeach
    </tbody>
</table>