@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="text-primary">🏦 {{ $title }}</h3>

        <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" href="#add_caisse">
            ➕ Nouvelle Caisse
        </button>

        <div class="table-responsive mt-3">
            <table id="caissesTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Code</th>
                    <th>Type de Caisse</th>
                    <th>status  Caisse</th>
                    <th>Description</th>
                    <th>Date création</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($caisses as $i => $caisse)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $caisse->nom_caisse }}</td>
                        <td>{{ $caisse->code_caisse }}</td>
                        <td>
                            @if($caisse->type_caisse == 0)
                                <span class="badge badge-success">Entrée</span>
                            @elseif($caisse->type_caisse == 1)
                                <span class="badge badge-danger">Sortie</span>
                            @else
                                <span class="badge badge-primary">Centrale</span>
                            @endif
                        </td>
                        <td>
                            @if($caisse->status_caisse == 0)
                                <span class="badge badge-success">Ouverte</span>
                            @else
                                <span class="badge badge-secondary">Fermée</span>
                            @endif
                        </td>
                        <td>{{ $caisse->description }}</td>
                        <td>{{ $caisse->created_at->format('d/m/Y') }}</td>
                        <td>
                            <!-- Edit -->
                            <a href="#edit_caisse" data-toggle="modal" data-backdrop="false"
                               onclick="editCaisse({{ $caisse->id }}, '{{ $caisse->nom_caisse }}', '{{ $caisse->code_caisse }}','{{$caisse->type_caisse}}', '{{$caisse->status_caisse}}','{{ $caisse->description }}')"
                               class="btn btn-xs btn-warning">✏️</a>

                            <!-- Delete -->
                            <form action="{{ route('delete_caisse', $caisse->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-xs btn-danger" onclick="return confirm('Supprimer cette caisse ?')">🗑️</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Ajout -->
    <div class="modal fade" id="add_caisse">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('store_caisse') }}">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title">➕ Nouvelle Caisse</h4>
                        <button type="button" class="close" data-dismiss="modal">x</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group"><label>Nom</label>
                            <input type="text" name="nom_caisse" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Code</label>
                            <input type="text" name="code_caisse" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Type de caisse</label>
                            <select name="type_caisse" class="form-control" required>
                                <option value="0">Entrée</option>
                                <option value="1">Sortie</option>
                                <option value="2">Centrale</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Status  caisse</label>
                            <select name="status_caisse" class="form-control" required>
                                <option value="0">Ouverte</option>
                                <option value="1">Fermée</option>
                            </select>
                        </div>
                        <div class="form-group"><label>Description</label>
                            <textarea name="description" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-success">💾 Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edition -->
    <div class="modal fade" id="edit_caisse">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('update_caisse') }}" id="editCaisseForm">
                    @csrf
                    <input type="hidden" name="id" id="edit-id">
                    <div class="modal-header">
                        <h4 class="modal-title">✏️ Modifier Caisse</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group"><label>Nom</label>
                            <input type="text" name="nom_caisse" id="edit-nom" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Code</label>
                            <input type="text" name="code_caisse" id="edit-code" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label>Type de caisse</label>
                            <select name="type_caisse" id="edit-type_caisse" class="form-control" required>
                                <option value="0">Entrée</option>
                                <option value="1">Sortie</option>
                                <option value="2">Centrale</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Status caisse</label>
                            <select name="status_caisse" id="id-edit-status_caisse" class="form-control" required>
                                <option value="0">Ouverte</option>
                                <option value="1">Fermée</option>
                            </select>
                        </div>
                        <div class="form-group"><label>Description</label>
                            <textarea name="description" id="edit-description" class="form-control"></textarea>
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
        function editCaisse(id, nom, code,type_caisse,status_caisse, description) {
            $('#edit-id').val(id);
            $('#edit-nom').val(nom);
            $('#edit-code').val(code);
            $('#edit-type_caisse').val(type_caisse);
            $('#id-edit-status_caisse').val(status_caisse);
            $('#edit-description').val(description);
        }


        $(function () {
            const $table = $('#caissesTable');
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
                            title: 'LISTE DES CAISSES',
                            exportOptions: { columns: ':not(:last-child)' }
                        },
                        {
                            extend: 'pdfHtml5',
                            text: '📄 PDF',
                            className: 'btn btn-danger btn-sm',
                            title: 'LISTE DES CAISSES',
                            exportOptions: { columns: ':not(:last-child)' }
                        },
                        {
                            extend: 'print',
                            text: '🖨 Imprimer',
                            className: 'btn btn-info btn-sm',
                            title: 'LISTE DES CAISSES',
                            exportOptions: { columns: ':not(:last-child)' }
                        }
                    ],
                    language: {
                        url: "{{ asset('js/datatables/fr-FR.json') }}"
                    }
                });
            }
        });
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