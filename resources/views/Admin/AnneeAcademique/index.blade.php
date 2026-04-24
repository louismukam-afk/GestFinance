@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="text-primary">📅 {{ $title }}</h3>

        <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" href="#add_annee">
            ➕ Nouvelle Année Académique
        </button>

        <div class="table-responsive mt-3">
            <table id="anneesTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Description</th>
                    <th>Date création</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($annees as $i => $a)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $a->nom }}</td>
                        <td>{{ $a->description }}</td>
                        <td>{{ $a->created_at->format('d/m/Y') }}</td>
                        <td>
                            <a href="#edit_annee"
                               class="btn btn-xs btn-warning"
                               data-toggle="modal" data-backdrop="false"
                               data-id="{{ $a->id }}"
                               data-nom="{{ e($a->nom) }}"
                               data-description="{{ e($a->description) }}"
                               onclick="return openEditAnnee(this);">✏️</a>

                            <form action="{{ route('delete_annee_academique', $a->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-xs btn-danger" onclick="return confirm('Supprimer cette année académique ?')">🗑️</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Ajout --}}
    <div class="modal fade" id="add_annee">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('store_annee_academique') }}">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title">➕ Nouvelle Année Académique</h4>
                        <button type="button" class="close" data-dismiss="modal">x</button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nom <span class="text-danger">*</span></label>
                            <input type="text" name="nom" class="form-control" placeholder="2025-2026" required>
                        </div>
                        <div class="form-group">
                            <label>Description (optionnelle)</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Ex: Année charnière, réforme LMD, ..."></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-success">💾 Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Édition --}}
    <div class="modal fade" id="edit_annee">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('update_annee_academique') }}" id="editAnneeForm">
                    @csrf
                    <input type="hidden" name="id" id="edit-id">
                    <div class="modal-header">
                        <h4 class="modal-title">✏️ Modifier Année Académique</h4>
                        <button type="button" class="close" data-dismiss="modal">x</button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nom <span class="text-danger">*</span></label>
                            <input type="text" name="nom" id="edit-nom" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Description (optionnelle)</label>
                            <textarea name="description" id="edit-description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-primary">✔ Modifier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            // ——— DataTables init (avec garde)
            $(function() {
                try {
                    if (window.jQuery && $.fn && $.fn.DataTable) {
                        const $table = $('#anneesTable');
                        if ($table.length) {
                            $table.DataTable({
                                responsive: true,
                                dom: 'BfrTip',
                                pageLength: 25,
                                buttons: [
                                    { extend: 'excelHtml5', text: '📊 Excel', className: 'btn btn-success btn-sm',
                                        title: 'LISTE DES ANNEES ACADEMIQUES', exportOptions: { columns: ':not(:last-child)' } },
                                    { extend: 'pdfHtml5',   text: '📄 PDF',   className: 'btn btn-danger btn-sm',
                                        title: 'LISTE DES ANNEES ACADEMIQUES', exportOptions: { columns: ':not(:last-child)' } },
                                    { extend: 'print',      text: '🖨 Imprimer', className: 'btn btn-info btn-sm',
                                        title: 'LISTE DES ANNEES ACADEMIQUES', exportOptions: { columns: ':not(:last-child)' } },
                                ],
                                language: { url: "{{ asset('js/datatables/fr-FR.json') }}" }
                            });
                        }
                    } else {
                        console.warn('DataTables non chargé : vérifie jQuery + DataTables (CSS/JS + Buttons).');
                    }
                } catch (e) {
                    console.error('Erreur DataTables:', e);
                }
            });

            // ——— Ouverture modal édition via data-*
            window.openEditAnnee = function (el) {
                var $b = $(el);
                $('#edit-id').val($b.data('id'));
                $('#edit-nom').val($b.data('nom'));
                $('#edit-description').val($b.data('description'));
                $('#edit_annee').modal('show');
                return false;
            };
        })();
    </script>
@endsection

@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('dashboard') }}"><strong>Administration</strong></a></li>
        {{--<li><a href="{{ route('budget') }}"><strong>budget</strong></a></li>--}}

        <li class="active"><strong>{{ $title }}</strong></li>
    </ol>
@endsection
