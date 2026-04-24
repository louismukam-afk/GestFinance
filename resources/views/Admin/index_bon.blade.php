@extends('layouts.app')
@section('content')

    <div class="col-md-12">

        <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" href="#add_bon">
            <span class="glyphicon glyphicon-plus"></span> Nouveau Bon de Commande
        </button>
        <a href="{{ route('export_bons') }}" class="btn btn-success" style="margin-bottom:15px;">
            <span class="glyphicon glyphicon-download"></span> Exporter Excel
        </a>

        <div class="form-group" style="margin-top: 15px;">
            <input type="text" id="searchProduit" class="form-control" placeholder="Rechercher un bon de commande...">
        </div>
        <!-- Conteneur où le tableau sera injecté -->
        {{--<div id="bons_table_container">
            @include('Admin.partials_bons_table', ['bon_comandes' => $bon_comandes])
        </div>--}}
        @php $i = 1; @endphp
         <div id="bons_table_container">
            @foreach($bon_comandes as $personnel_id => $list_bons)
                <h3 class="text-info" style="margin-top:25px;">
                    <span class="glyphicon glyphicon-user"></span>
                    {{ $list_bons->first()->personnels->nom ?? 'Personnel #'.$personnel_id }}
                </h3>

                <div style="overflow-x:auto; max-width:100%; font-size: 60%;">
                    <table  id="tableProduits" class="table table-striped table-condensed table-bordered table-bons">
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
                        @foreach($list_bons as $bon)
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
                                      {{--  <a href="{{ route('element_bon.create', $bon->id) }}"
                                           class="btn btn-xs btn-default" style="margin:2px; background-color:#eee;">
                                            ➕ Ajouter éléments
                                        </a>--}}
                                        <a href="{{ route('element_bon.manage', $bon->id) }}"
                                           class="btn btn-xs btn-default" style="margin:2px; background-color:#eee;">
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
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
        {{-- MODAL AJOUT --}}
        <div class="modal fade" id="add_bon">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">x</button>
                        <h4 class="modal-title">Nouveau Bon de Commande</h4>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="{{ route('store_bon_commande') }}">
                            @csrf
                            <fieldset>
                                <div class="form-group">
                                    <label>Nom du bon :</label>
                                    <input type="text" class="form-control" name="nom_bon_commande" required>
                                </div>

                                <div class="form-group">
                                    <label>Description :</label>
                                    <textarea class="form-control" name="description_bon_commande" required></textarea>
                                </div>

                                <div class="form-group">
                                    <label>Date début :</label>
                                    <input type="date" class="form-control" name="date_debut" required>
                                </div>

                                <div class="form-group">
                                    <label>Date fin :</label>
                                    <input type="date" class="form-control" name="date_fin" required>
                                </div>

                                <div class="form-group">
                                    <label>Date entrée signature :</label>
                                    <input type="date" class="form-control" name="date_entree_signature" required>
                                </div>

                              {{--  <div class="form-group">
                                    <label>Date validation :</label>
                                    <input type="date" class="form-control" name="date_validation" required>
                                </div>--}}

                                <div class="form-group">
                                    <label>Montant total :</label>
                                    <input type="number" class="form-control" id="montant_total" name="montant_total" required>
                                </div>

                                <div class="form-group">
                                    <label>Montant réalisé :</label>
                                    <input type="number" class="form-control" id="montant_realise" name="montant_realise">
                                </div>

                                <div class="form-group">
                                    <label>Reste :</label>
                                    <input type="number" class="form-control" id="reste" name="reste" readonly>
                                </div>

                                <div class="form-group">
                                    <label>Montant en lettres :</label>
                                    <input type="text" class="form-control" name="montant_lettre" required>
                                </div>

                                <div class="form-group">
                                    <label>Personnel :</label>
                                    <select class="form-control" name="id_personnel" required>
                                        @foreach($personnels as $personnel)
                                            <option value="{{ $personnel->id }}">{{ $personnel->nom }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Entité :</label>
                                    <select class="form-control" name="id_entite" required>
                                        @foreach($entites as $entite)
                                            <option value="{{ $entite->id }}">{{ $entite->nom_entite }}</option>
                                        @endforeach
                                    </select>
                                </div>

                              {{--  <div class="form-group">
                                    <label>Statut :</label>
                                    <select class="form-control" name="statuts" required>
                                        <option value="0">En attente</option>
                                        <option value="1">Validé</option>
                                    </select>
                                </div>--}}

                                <button class="btn btn-success pull-right">
                                    <span class="glyphicon glyphicon-plus"></span> Ajouter
                                </button>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
        </div>


        <div class="modal fade" id="edit_bon">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">x</button>
                        <h4 class="modal-title">Modifier Bon de Commande</h4>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="{{ route('update_bon_commande') }}">
                            @csrf
                            <input type="hidden" id="edit-id" name="id">
                            <fieldset>
                                <div class="form-group">
                                    <label>Nom du bon :</label>
                                    <input type="text" id="edit-nom" class="form-control" name="nom_bon_commande" required>
                                </div>

                                <div class="form-group">
                                    <label>Description :</label>
                                    <textarea id="edit-desc" class="form-control" name="description_bon_commande" required></textarea>
                                </div>

                                <div class="form-group">
                                    <label>Date début :</label>
                                    <input type="date" id="edit-date-debut" class="form-control" name="date_debut" required>
                                </div>

                                <div class="form-group">
                                    <label>Date fin :</label>
                                    <input type="date" id="edit-date-fin" class="form-control" name="date_fin" required>
                                </div>

                                <div class="form-group">
                                    <label>Date entrée signature :</label>
                                    <input type="date" id="edit-date-entree" class="form-control" name="date_entree_signature" required>
                                </div>

                                <div class="form-group">
                                    <label>Montant total :</label>
                                    <input type="number" id="edit-montant-total" class="form-control" name="montant_total" required>
                                </div>

                                <div class="form-group">
                                    <label>Montant réalisé :</label>
                                    <input type="number" id="edit-montant-realise" class="form-control" name="montant_realise">
                                </div>

                                <div class="form-group">
                                    <label>Reste :</label>
                                    <input type="number" id="edit-reste" class="form-control" name="reste" readonly>
                                </div>

                                <div class="form-group">
                                    <label>Montant en lettres :</label>
                                    <input type="text" id="edit-montant-lettre" class="form-control" name="montant_lettre" required>
                                </div>

                                <div class="form-group">
                                    <label>Personnel :</label>
                                    <select id="edit-personnel" class="form-control" name="id_personnel" required>
                                        @foreach($personnels as $personnel)
                                            <option value="{{ $personnel->id }}">{{ $personnel->nom }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Entité :</label>
                                    <select id="edit-entite" class="form-control" name="id_entite" required>
                                        @foreach($entites as $entite)
                                            <option value="{{ $entite->id }}">{{ $entite->nom_entite }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <button class="btn btn-primary pull-right">
                                    <span class="glyphicon glyphicon-pencil"></span> Modifier
                                </button>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>



@endsection
@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('bon_commande_management') }}"><strong>Gestion des bons de commandes</strong></a></li>
        {{--<li><a href="{{ route('budget') }}"><strong>budget</strong></a></li>--}}

        <li class="active"><strong>{{ $title }}</strong></li>
    </ol>
@endsection

@section('scripts')


    <script>

        $(function () {
            const $table = $('#tableProduits');
            if ($table.length) {
                $table.DataTable({
                    responsive: true,
                    dom: 'Bfrtip',
                    pageLength: 25,
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            text: '📊 Excel',
                            className: 'btn btn-success btn-sm',
                            exportOptions: { columns: ':not(:last-child)' }
                        },
                        {
                            extend: 'pdfHtml5',
                            text: '📄 PDF',
                            className: 'btn btn-danger btn-sm',
                            exportOptions: { columns: ':not(:last-child)' }
                        },
                        {
                            extend: 'print',
                            text: '🖨 Imprimer',
                            className: 'btn btn-info btn-sm',
                            exportOptions: { columns: ':not(:last-child)' }
                        }
                    ],
                    language: {
                        url: "{{ asset('js/datatables/fr-FR.json') }}"
                    }
                });
            }
        });
        // ✅ Calcul automatique du reste
        function bindCalculReste(totalId, realiseId, resteId) {
            const total = document.getElementById(totalId);
            const realise = document.getElementById(realiseId);
            const reste = document.getElementById(resteId);

            function calcul() {
                const t = parseFloat(total.value) || 0;
                const r = parseFloat(realise.value) || 0;
                reste.value = t - r;
            }

            total.addEventListener('input', calcul);
            realise.addEventListener('input', calcul);
        }

        document.addEventListener('DOMContentLoaded', function () {
            bindCalculReste('montant_total', 'montant_realise', 'reste');
            bindCalculReste('edit-montant-total', 'edit-montant-realise', 'edit-reste');
        });

        // ✅ Pré-remplissage formulaire edit
        function edit_bon(id, nom, description, date_debut, date_fin, date_entree_signature, montant_total, montant_realise, reste_value, montant_lettre, id_personnel, id_entite) {
            $('#edit-id').val(id);
            $('#edit-nom').val(nom);
            $('#edit-desc').val(description);
            $('#edit-date-debut').val(date_debut);
            $('#edit-date-fin').val(date_fin);
            $('#edit-date-entree').val(date_entree_signature);
            $('#edit-montant-total').val(montant_total);
            $('#edit-montant-realise').val(montant_realise);
            $('#edit-reste').val(reste_value);
            $('#edit-montant-lettre').val(montant_lettre);
            $('#edit-personnel').val(id_personnel);
            $('#edit-entite').val(id_entite);
        }

        document.getElementById("searchProduit").addEventListener("keyup", function () {
            var value = this.value.toLowerCase();
            var rows = document.querySelectorAll("#tableProduits tbody tr");

            rows.forEach(function (row) {
                var text = row.innerText.toLowerCase();
                row.style.display = text.includes(value) ? "" : "none";
            });
        });

    </script>
@endsection
