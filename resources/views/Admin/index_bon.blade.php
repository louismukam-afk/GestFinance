@extends('layouts.app')
@section('content')

<div class="col-md-12">

    @if(session('success'))
        <div class="alert alert-success">{{ is_array(session('success')) ? implode(' ', session('success')) : session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" href="#add_bon">
        <span class="glyphicon glyphicon-plus"></span> Nouveau Bon de Commande
    </button>

    <a href="{{ route('export_bons') }}" class="btn btn-success" style="margin-bottom:15px;">
        <span class="glyphicon glyphicon-download"></span> Exporter Excel
    </a>

    <form method="GET" action="{{ route('bon_commande_management') }}" class="row" style="margin-top:15px; margin-bottom:15px;">
        <div class="col-md-2">
            <label>Date debut</label>
            <input type="date" name="date_debut" value="{{ request('date_debut') }}" class="form-control">
        </div>

        <div class="col-md-2">
            <label>Date fin</label>
            <input type="date" name="date_fin" value="{{ request('date_fin') }}" class="form-control">
        </div>

        <div class="col-md-2">
            <label>Statut</label>
            <select name="statut" class="form-control">
                <option value="">Tous</option>
                <option value="0" {{ request('statut') === '0' ? 'selected' : '' }}>En attente</option>
                <option value="1" {{ request('statut') === '1' ? 'selected' : '' }}>Valide</option>
                <option value="2" {{ request('statut') === '2' ? 'selected' : '' }}>Refuse</option>
            </select>
        </div>

        <div class="col-md-3">
            <label>Personnel</label>
            <select name="id_personnel" class="form-control">
                <option value="">Tous</option>
                @foreach($personnels as $personnel)
                    <option value="{{ $personnel->id }}" {{ request('id_personnel') == $personnel->id ? 'selected' : '' }}>
                        {{ $personnel->nom }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3" style="padding-top:25px;">
            <button class="btn btn-primary">Filtrer</button>
            <a href="{{ route('bon_commande_management') }}" class="btn btn-default">Reset</a>
        </div>
    </form>

    <div class="form-group" style="margin-top: 15px;">
        <input type="text" id="searchProduit" class="form-control" placeholder="Rechercher un bon de commande...">
    </div>

    @php $i = 1; @endphp

    <div id="bons_table_container">
        @foreach($bon_comandes as $personnel_id => $list_bons)

            <h3 class="text-info" style="margin-top:25px;">
                <span class="glyphicon glyphicon-user"></span>
                {{ $list_bons->first()->personnels->nom ?? 'Personnel #'.$personnel_id }}
            </h3>

            <div style="overflow-x:auto; max-width:100%; font-size: 60%;">
                <table id="tableProduits_{{ $personnel_id }}" class="table table-striped table-condensed table-bordered table-bons">
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
                        <th>Motif refus</th>
                        <th>Validations</th>
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

                            <td>
                                @if($bon->statut_bon_code == 1 && $bon->reste > 0)
                                    <span class="label label-warning">En attente financement</span>
                                @elseif($bon->statut_bon_code == 0)
                                    <span class="label label-warning">En attente</span>
                                @elseif($bon->statut_bon_code == 1)
                                    <span class="label label-success">Validé</span>
                                @elseif($bon->statut_bon_code == 2)
                                    <span class="label label-danger">Rejeté</span>
                                @else
                                    <span class="label label-default">Inconnu</span>
                                @endif
                            </td>

                            <td>{{ $bon->motif_refus ?: '-' }}</td>

                            <td>
                                @if($bon->validation_pdg == 0)
                                    <form action="{{ route('valider_pdg_bon', $bon->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-success" style="margin:2px;">Valider PDG</button>
                                    </form>
                                    <button type="button" class="btn btn-xs btn-danger" data-toggle="modal" data-target="#refus_pdg_{{ $bon->id }}" style="margin:2px;">Refuser PDG</button>
                                @endif

                                @if($bon->validation_daf == 0)
                                    <form action="{{ route('valider_daf_bon', $bon->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-info" style="margin:2px;">Valider DAF</button>
                                    </form>
                                    <button type="button" class="btn btn-xs btn-danger" data-toggle="modal" data-target="#refus_daf_{{ $bon->id }}" style="margin:2px;">Refuser DAF</button>
                                @endif

                                @if($bon->validation_achats == 0)
                                    <form action="{{ route('valider_achats_bon', $bon->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-warning" style="margin:2px;">Valider Achats</button>
                                    </form>
                                    <button type="button" class="btn btn-xs btn-danger" data-toggle="modal" data-target="#refus_achats_{{ $bon->id }}" style="margin:2px;">Refuser Achats</button>
                                @endif

                                @if($bon->validation_emetteur == 0)
                                    <form action="{{ route('valider_emetteur_bon', $bon->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-primary" style="margin:2px;">Valider Émetteur</button>
                                    </form>
                                    <button type="button" class="btn btn-xs btn-danger" data-toggle="modal" data-target="#refus_emetteur_{{ $bon->id }}" style="margin:2px;">Refuser Emetteur</button>
                                @endif

                                <a href="{{ route('element_bon.manage', $bon->id) }}"
                                   class="btn btn-xs btn-default" style="margin:2px; background-color:#eee;">
                                    ➕ Ajouter éléments
                                </a>
                            </td>

                            <td>
                                @php
                                    $bonEditData = [
                                        'id' => $bon->id,
                                        'nom' => $bon->nom_bon_commande,
                                        'description' => $bon->description_bon_commande,
                                        'date_debut' => $bon->date_debut,
                                        'date_fin' => $bon->date_fin,
                                        'date_entree' => $bon->date_entree_signature,
                                        'montant_total' => $bon->montant_total,
                                        'montant_realise' => $bon->montant_realise,
                                        'reste' => $bon->reste,
                                        'montant_lettre' => $bon->montant_lettre,
                                        'personnel' => $bon->id_personnel,
                                        'entite' => $bon->id_entite,
                                    ];
                                @endphp
                                <a href="#edit_bon" data-toggle="modal" data-backdrop="false"
                                   data-bon='@json($bonEditData)'
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

                        @foreach(['pdg' => 'PDG', 'daf' => 'DAF', 'achats' => 'Achats', 'emetteur' => 'Emetteur'] as $niveau => $label)
                            <div class="modal fade" id="refus_{{ $niveau }}_{{ $bon->id }}">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('refuser_bon', [$niveau, $bon->id]) }}">
                                            @csrf
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal">x</button>
                                                <h4 class="modal-title">Refus {{ $label }}</h4>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>{{ $bon->nom_bon_commande }}</strong></p>
                                                <textarea name="motif_refus" class="form-control" rows="4" required placeholder="Motif du refus"></textarea>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
                                                <button class="btn btn-danger">Confirmer le refus</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach

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

                            <button class="btn btn-success pull-right">
                                <span class="glyphicon glyphicon-plus"></span> Ajouter
                            </button>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL MODIFICATION --}}
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
    <li class="active"><strong>{{ $title }}</strong></li>
</ol>
@endsection

@section('scripts')

<script>
    $(function () {

        $('.table-bons').each(function () {

            if ($.fn.DataTable.isDataTable(this)) {
                $(this).DataTable().destroy();
            }

            $(this).DataTable({
                responsive: true,
                dom: 'Bfrtip',
                pageLength: 25,
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '📊 Excel',
                        className: 'btn btn-success btn-sm',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '📄 PDF',
                        className: 'btn btn-danger btn-sm',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    },
                    {
                        extend: 'print',
                        text: '🖨 Imprimer',
                        className: 'btn btn-info btn-sm',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    }
                ],
                language: {
                    url: "{{ asset('js/datatables/fr-FR.json') }}"
                }
            });

        });

    });

    function bindCalculReste(totalId, realiseId, resteId) {
        const total = document.getElementById(totalId);
        const realise = document.getElementById(realiseId);
        const reste = document.getElementById(resteId);

        if (!total || !realise || !reste) {
            return;
        }

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

    $('#edit_bon').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        let bon = button.data('bon') || {};

        if (typeof bon === 'string') {
            try {
                bon = JSON.parse(bon);
            } catch (e) {
                bon = {};
            }
        }

        $('#edit-id').val(bon.id || '');
        $('#edit-nom').val(bon.nom || '');
        $('#edit-desc').val(bon.description || '');
        $('#edit-date-debut').val(bon.date_debut || '');
        $('#edit-date-fin').val(bon.date_fin || '');
        $('#edit-date-entree').val(bon.date_entree || '');
        $('#edit-montant-total').val(bon.montant_total || 0);
        $('#edit-montant-realise').val(bon.montant_realise || 0);
        $('#edit-reste').val(bon.reste || 0);
        $('#edit-montant-lettre').val(bon.montant_lettre || '');
        $('#edit-personnel').val(bon.personnel || '');
        $('#edit-entite').val(bon.entite || '');

        $('#edit-montant-total').trigger('input');
    });

    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById("searchProduit");

        if (searchInput) {
            searchInput.addEventListener("keyup", function () {
                var value = this.value.toLowerCase();
                var rows = document.querySelectorAll(".table-bons tbody tr");

                rows.forEach(function (row) {
                    var text = row.innerText.toLowerCase();
                    row.style.display = text.includes(value) ? "" : "none";
                });
            });
        }
    });
</script>

@endsection
