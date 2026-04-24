@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="text-primary">🏷️ {{ $title }}</h3>

        <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" href="#add_fonction">
            ➕ Nouvelle Fonction
        </button>

        <div class="table-responsive mt-3">
            <table id="fonctionsTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Nom de la fonction</th>
                    <th>Description</th>
                    <th>Date de création</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($fonctions as $i => $f)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $f->nom_fonction }}</td>
                        <td>{{ $f->description }}</td>
                        <td>{{ $f->created_at->format('d/m/Y') }}</td>
                        <td>
                            <a href="#edit_fonction"
                               class="btn btn-xs btn-warning"
                               data-toggle="modal" data-backdrop="false"
                               data-id="{{ $f->id }}"
                               data-nom="{{ e($f->nom_fonction) }}"
                               data-description="{{ e($f->description) }}"
                               onclick="return openEditFonction(this);">✏️</a>

                            <form action="{{ route('delete_fonction', $f->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-xs btn-danger" onclick="return confirm('Supprimer cette fonction ?')">🗑️</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Ajout --}}
    <div class="modal fade" id="add_fonction">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('store_fonction') }}">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title">➕ Nouvelle Fonction</h4>
                        <button type="button" class="close" data-dismiss="modal">x</button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nom de la fonction <span class="text-danger">*</span></label>
                            <input type="text" name="nom_fonction" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Description (optionnelle)</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
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
    <div class="modal fade" id="edit_fonction">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('update_fonction') }}" id="editFonctionForm">
                    @csrf
                    <input type="hidden" name="id" id="edit-id">
                    <div class="modal-header">
                        <h4 class="modal-title">✏️ Modifier Fonction</h4>
                        <button type="button" class="close" data-dismiss="modal">x</button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nom de la fonction <span class="text-danger">*</span></label>
                            <input type="text" name="nom_fonction" id="edit-nom" class="form-control" required>
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
            // ——— DataTables init (avec garde au cas où assets manquent)
            $(function() {
                try {
                    if (window.jQuery && $.fn && $.fn.DataTable) {
                        const $table = $('#fonctionsTable');
                        if ($table.length) {
                            $table.DataTable({
                                responsive: true,
                                dom: 'BfrTip',
                                pageLength: 25,
                                buttons: [
                                    { extend: 'excelHtml5', text: '📊 Excel', className: 'btn btn-success btn-sm',
                                        title: 'LISTE DES FONCTIONS', exportOptions: { columns: ':not(:last-child)' } },
                                    { extend: 'pdfHtml5',   text: '📄 PDF',   className: 'btn btn-danger btn-sm',
                                        title: 'LISTE DES FONCTIONS', exportOptions: { columns: ':not(:last-child)' } },
                                    { extend: 'print',      text: '🖨 Imprimer', className: 'btn btn-info btn-sm',
                                        title: 'LISTE DES FONCTIONS', exportOptions: { columns: ':not(:last-child)' } },
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

            // ——— Ouverture modal édition depuis data-*
            window.openEditFonction = function (el) {
                var $b = $(el);
                $('#edit-id').val($b.data('id'));
                $('#edit-nom').val($b.data('nom'));
                $('#edit-description').val($b.data('description'));
                $('#edit_fonction').modal('show');
                return false;
            };
        })();
    </script>
@endsection

@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('personnel') }}"><strong>Gestion du personnel</strong></a></li>
        <li class="active"><strong>{{ $title }}</strong></li>
    </ol>
@endsection
