@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="text-primary">🏦 {{ $title }}</h3>

        <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" href="#add_banque">
            ➕ Nouvelle Banque
        </button>

        <div class="table-responsive mt-3">
            <table id="banquesTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Téléphone</th>
                    <th>Localisation</th>
                    <th>Code</th>
                    <th>Email</th>
                    <th>Description</th>
                    <th>Date création</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($banques as $i => $banque)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $banque->nom_banque }}</td>
                        <td>{{ $banque->telephone }}</td>
                        <td>{{ $banque->localisation }}</td>
                        <td>{{ $banque->code }}</td>
                        <td>{{ $banque->email }}</td>
                        <td>{{ $banque->description }}</td>
                        <td>{{ $banque->created_at->format('d/m/Y') }}</td>
                        <td>
                            <!-- Bouton Edit -->
                            <a href="#edit_banque" data-toggle="modal" data-backdrop="false"
                               onclick="editBanque({{ $banque->id }}, '{{ $banque->nom_banque }}', '{{ $banque->telephone }}', '{{ $banque->localisation }}', '{{ $banque->code }}', '{{ $banque->email }}', '{{ $banque->description }}')"
                               class="btn btn-xs btn-warning">✏️</a>

                            <!-- Bouton Delete -->
                            <form action="{{ route('delete_banque', $banque->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-xs btn-danger" onclick="return confirm('Supprimer cette banque ?')">🗑️</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Ajout -->
    <div class="modal fade" id="add_banque">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('store_banque') }}">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title">➕ Nouvelle Banque</h4>
                        <button type="button" class="close" data-dismiss="modal">x</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group"><label>Nom</label>
                            <input type="text" name="nom_banque" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Téléphone</label>
                            <input type="text" name="telephone" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Localisation</label>
                            <input type="text" name="localisation" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Code</label>
                            <input type="text" name="code" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Email</label>
                            <input type="email" name="email" class="form-control">
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

    <!-- Modal Édition -->
    <div class="modal fade" id="edit_banque">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('update_banque') }}" id="editBanqueForm">
                    @csrf
                    <input type="hidden" name="id" id="edit-id">
                    <div class="modal-header">
                        <h4 class="modal-title">✏️ Modifier Banque</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group"><label>Nom</label>
                            <input type="text" name="nom_banque" id="edit-nom" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Téléphone</label>
                            <input type="text" name="telephone" id="edit-telephone" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Localisation</label>
                            <input type="text" name="localisation" id="edit-localisation" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Code</label>
                            <input type="text" name="code" id="edit-code" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Email</label>
                            <input type="email" name="email" id="edit-email" class="form-control">
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
        $(function () {
            const $table = $('#banquesTable');
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
                            title: 'LISTE DES BANQUES',
                            exportOptions: { columns: ':not(:last-child)' }
                        },
                        {
                            extend: 'pdfHtml5',
                            text: '📄 PDF',
                            className: 'btn btn-danger btn-sm',
                            title: 'LISTE DES BANQUES',
                            exportOptions: { columns: ':not(:last-child)' }
                        },
                        {
                            extend: 'print',
                            text: '🖨 Imprimer',
                            className: 'btn btn-info btn-sm',
                            title: 'LISTE DES BANQUES',
                            exportOptions: { columns: ':not(:last-child)' }
                        }
                    ],
                    language: {
                        url: "{{ asset('js/datatables/fr-FR.json') }}"
                    }
                });
            }
        });

        function editBanque(id, nom, telephone, localisation, code, email, description) {
            $('#edit-id').val(id);
            $('#edit-nom').val(nom);
            $('#edit-telephone').val(telephone);
            $('#edit-localisation').val(localisation);
            $('#edit-code').val(code);
            $('#edit-email').val(email);
            $('#edit-description').val(description);
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