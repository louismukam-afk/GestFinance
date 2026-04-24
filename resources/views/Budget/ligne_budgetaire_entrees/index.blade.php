@extends('layouts.app')
@section('content')

    <div class="col-md-12">

        {{-- ✅ Bouton ajout --}}
        <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" href="#add_ligne">
            <span class="glyphicon glyphicon-plus"></span> Nouvelle Ligne Budgétaire
        </button>

        {{-- ✅ Export Excel --}}
        <a href="{{ route('ligne_budgetaire_entrees.exportExcel') }}" class="btn btn-success" style="margin-bottom:15px;">
            <span class="glyphicon glyphicon-download"></span> Exporter Excel
        </a>

        {{-- ✅ Export PDF --}}
        <a href="{{ route('ligne_budgetaire_entrees.exportPdf') }}" class="btn btn-danger" style="margin-bottom:15px;">
            <span class="glyphicon glyphicon-file"></span> Exporter PDF
        </a>

        {{-- ✅ Recherche --}}
        <div class="form-group" style="margin-top: 15px;">
            <input type="text" id="searchLigne" class="form-control" placeholder="Rechercher une ligne budgétaire...">
        </div>

        @php $i = 1; @endphp
        <div id="lignes_table_container">
            {{--<div style="overflow-x:auto; max-width:100%; font-size: 90%;">--}}
                <table id="tableLignes" class="table table-striped table-condensed table-bordered">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Libellé</th>
                        <th>Code</th>
                        <th>N° Compte</th>
                        <th>Description</th>
                        <th>Date création</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($lignes as $ligne)
                        <tr>
                            <td>{{ $i++ }}</td>
                            <td>{{ $ligne->libelle_ligne_budgetaire_entree }}</td>
                            <td>{{ $ligne->code_ligne_budgetaire_entree }}</td>
                            <td>{{ $ligne->numero_compte_ligne_budgetaire_entree }}</td>
                            <td>{{ $ligne->description }}</td>
                            <td>{{ $ligne->date_creation }}</td>
                            <td>
                                {{-- ✅ Modifier --}}
                                <a href="#edit_ligne" data-toggle="modal" data-backdrop="false"
                                   onclick="edit_ligne(
                                   {{ $ligne->id }},
                                           '{{ $ligne->libelle_ligne_budgetaire_entree }}',
                                           '{{ $ligne->code_ligne_budgetaire_entree }}',
                                           '{{ $ligne->numero_compte_ligne_budgetaire_entree }}',
                                           '{{ $ligne->description }}',
                                           '{{ $ligne->date_creation }}'
                                           )"
                                   class="btn btn-xs btn-primary">
                                    <span class="glyphicon glyphicon-edit"></span>
                                </a>
                                {{-- ✅ Gérer les éléments --}}
                                <a href="{{ route('element_entrees.manage', $ligne->id) }}" class="btn btn-xs btn-default" style="margin:2px; background-color:#eee;">
                                    ➕ Éléments
                                </a>
                                {{-- ✅ Supprimer --}}
                                <form action="{{ route('ligne_budgetaire_entrees.destroy', $ligne->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-xs btn-danger" onclick="return confirm('Supprimer cette ligne ?')">
                                        <span class="glyphicon glyphicon-trash"></span>
                                    </button>
                                </form>

                                {{-- ✅ Export individuel --}}
                                <a href="{{ route('ligne_budgetaire_entrees.exportPdfOne', $ligne->id) }}" class="btn btn-xs btn-danger">
                                    <span class="glyphicon glyphicon-file"></span> PDF
                                </a>
                                <a href="{{ route('ligne_budgetaire_entrees.exportExcelOne', $ligne->id) }}" class="btn btn-xs btn-success">
                                    <span class="glyphicon glyphicon-download"></span> Excel
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        {{--</div>--}}

        {{-- ✅ MODAL AJOUT --}}
        <div class="modal fade" id="add_ligne">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">x</button>
                        <h4 class="modal-title">Nouvelle Ligne Budgétaire</h4>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="{{ route('ligne_budgetaire_entrees.store') }}">
                            @csrf
                            <fieldset>
                                <div class="form-group">
                                    <label>Libellé :</label>
                                    <input type="text" class="form-control" name="libelle_ligne_budgetaire_entree" required>
                                </div>
                                <div class="form-group">
                                    <label>Code :</label>
                                    <input type="text" class="form-control" name="code_ligne_budgetaire_entree" required>
                                </div>
                                <div class="form-group">
                                    <label>N° Compte :</label>
                                    <input type="text" class="form-control" name="numero_compte_ligne_budgetaire_entree" required>
                                </div>
                                <div class="form-group">
                                    <label>Description :</label>
                                    <textarea class="form-control" name="description"></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Date création :</label>
                                    <input type="date" class="form-control" name="date_creation" required>
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

        {{-- ✅ MODAL EDIT --}}
        <div class="modal fade" id="edit_ligne">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">x</button>
                        <h4 class="modal-title">Modifier Ligne Budgétaire</h4>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="{{ route('ligne_budgetaire_entrees.update', 0) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" id="edit-id" name="id">
                            <fieldset>
                                <div class="form-group">
                                    <label>Libellé :</label>
                                    <input type="text" id="edit-libelle" class="form-control" name="libelle_ligne_budgetaire_entree" required>
                                </div>
                                <div class="form-group">
                                    <label>Code :</label>
                                    <input type="text" id="edit-code" class="form-control" name="code_ligne_budgetaire_entree" required>
                                </div>
                                <div class="form-group">
                                    <label>N° Compte :</label>
                                    <input type="text" id="edit-compte" class="form-control" name="numero_compte_ligne_budgetaire_entree" required>
                                </div>
                                <div class="form-group">
                                    <label>Description :</label>
                                    <textarea id="edit-description" class="form-control" name="description"></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Date création :</label>
                                    <input type="date" id="edit-date" class="form-control" name="date_creation" required>
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

@section('scripts')
    <script>

        // ✅ Pré-remplissage formulaire edit
        function edit_ligne(id, libelle, code, compte, description, date_creation) {
            $('#edit-id').val(id);
            $('#edit-libelle').val(libelle);
            $('#edit-code').val(code);
            $('#edit-compte').val(compte);
            $('#edit-description').val(description);
            $('#edit-date').val(date_creation);

            // Corriger l’action du form
            let form = $('#edit_ligne form');
            form.attr('action', "{{ url('ligne_budgetaire_entrees') }}/" + id);
        }

        $(function () {
            const $table = $('#tableLignes');
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
                            title: 'LISTE DES LIGNES ENTREES',
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


        // ✅ Recherche globale
        document.getElementById("searchLigne").addEventListener("keyup", function () {
            var value = this.value.toLowerCase();
            var rows = document.querySelectorAll("#tableLignes tbody tr");

            rows.forEach(function (row) {
                var text = row.innerText.toLowerCase();
                row.style.display = text.includes(value) ? "" : "none";
            });
        });
    </script>
@endsection
@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('budget') }}"><strong>budget</strong></a></li>
        {{--<li><a href="{{ route('budget') }}"><strong>budget</strong></a></li>--}}

   <li class="active"><strong>{{ $title }}</strong></li>
    </ol>
@endsection