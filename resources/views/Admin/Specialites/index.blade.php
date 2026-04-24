@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="text-primary">📘 {{ $title }}</h3>

        <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" href="#add_specialite">
            ➕ Nouvelle Spécialité
        </button>

        <div class="table-responsive mt-3">
            <table id="specialitesTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Code</th>
                    <th>Filière</th>
                    <th>Capacité</th>
                    <th>Date création</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($specialites as $i => $sp)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $sp->nom_specialite }}</td>
                        <td>{{ $sp->code_specialite }}</td>
                        <td>{{ $sp->filiere->nom_filiere ?? 'N/A' }}</td>
                        <td>{{ $sp->capacite }}</td>
                        <td>{{ $sp->created_at->format('d/m/Y') }}</td>
                        <td>
                            <a href="#edit_specialite" data-toggle="modal" data-backdrop="false"
                               onclick="editSpecialite({{ $sp->id }}, '{{ $sp->nom_specialite }}', '{{ $sp->code_specialite }}', '{{ $sp->id_filiere }}', '{{ $sp->capacite }}')"
                               class="btn btn-xs btn-warning">✏️</a>

                            <form action="{{ route('delete_specialite', $sp->id) }}" method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-danger" onclick="return confirm('Supprimer cette spécialité ?')">🗑️</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Ajout -->
    <div class="modal fade" id="add_specialite">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('store_specialite') }}">
                    @csrf
                    <div class="modal-header"><h4>➕ Nouvelle Spécialité</h4></div>
                    <div class="modal-body">
                        <div class="form-group"><label>Nom</label>
                            <input type="text" name="nom_specialite" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Code</label>
                            <input type="text" name="code_specialite" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Filière</label>
                            <select name="id_filiere" class="form-control" required>
                                <option value="">-- Sélectionner --</option>
                                @foreach($filieres as $f)
                                    <option value="{{ $f->id }}">{{ $f->nom_filiere }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group"><label>Capacité</label>
                            <input type="number" name="capacite" class="form-control" min="0">
                        </div>
                    </div>
                    <div class="modal-footer"><button class="btn btn-success">💾 Enregistrer</button></div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Édition -->
    <div class="modal fade" id="edit_specialite">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('update_specialite') }}" id="editSpecialiteForm">
                    @csrf
                    <input type="hidden" name="id" id="edit-id">
                    <div class="modal-header"><h4>✏️ Modifier Spécialité</h4></div>
                    <div class="modal-body">
                        <div class="form-group"><label>Nom</label>
                            <input type="text" name="nom_specialite" id="edit-nom" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Code</label>
                            <input type="text" name="code_specialite" id="edit-code" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Filière</label>
                            <select name="id_filiere" id="edit-filiere" class="form-control" required>
                                @foreach($filieres as $f)
                                    <option value="{{ $f->id }}">{{ $f->nom_filiere }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group"><label>Capacité</label>
                            <input type="number" name="capacite" id="edit-capacite" class="form-control" min="0">
                        </div>
                    </div>
                    <div class="modal-footer"><button class="btn btn-primary">✔ Modifier</button></div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>

        $(function () {
            const $table = $('#specialitesTable');
            if ($table.length) {
                $table.DataTable({
                    responsive: true,
                    dom: 'Bfrtip',
                    pageLength: 25,
                    buttons: [
                        { extend: 'excelHtml5', text: '📊 Excel', className: 'btn btn-success btn-sm', exportOptions: { columns: ':not(:last-child)' }},
                        { extend: 'pdfHtml5', text: '📄 PDF', className: 'btn btn-danger btn-sm', exportOptions: { columns: ':not(:last-child)' }},
                        { extend: 'print', text: '🖨 Imprimer', className: 'btn btn-info btn-sm', exportOptions: { columns: ':not(:last-child)' }}
                    ],
                    language: { url: "{{ asset('js/datatables/fr-FR.json') }}" }
                });
            }
        });
        function editSpecialite(id, nom, code, filiere, capacite) {
            $('#edit-id').val(id);
            $('#edit-nom').val(nom);
            $('#edit-code').val(code);
            $('#edit-filiere').val(filiere);
            $('#edit-capacite').val(capacite);
        }
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