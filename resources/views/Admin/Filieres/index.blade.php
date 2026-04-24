@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="text-primary">📘 {{ $title }}</h3>

        <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" href="#add_filiere">
            ➕ Nouvelle Filière
        </button>

        <div class="table-responsive mt-3">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Code</th>
                    <th>Description</th>
                    <th>Date création</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($filieres as $i => $filiere)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $filiere->nom_filiere }}</td>
                        <td>{{ $filiere->code_filiere }}</td>
                        <td>{{ $filiere->description }}</td>
                        <td>{{ $filiere->created_at->format('d/m/Y') }}</td>
                        <td>
                            <!-- Bouton Edit -->
                            <a href="#edit_filiere" data-toggle="modal" data-backdrop="false"
                               onclick="editFiliere({{ $filiere->id }}, '{{ $filiere->nom_filiere }}', '{{ $filiere->code_filiere }}', '{{ $filiere->description }}')"
                               class="btn btn-xs btn-warning">
                                ✏️
                            </a>

                            <!-- Bouton Delete -->
                            <form action="{{ route('delete_filiere', $filiere->id) }}" method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-danger" onclick="return confirm('Supprimer cette filière ?')">🗑️</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Ajout -->
    <div class="modal fade" id="add_filiere">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('store_filiere') }}">
                    @csrf
                    <div class="modal-header"><h4 class="modal-title">➕ Nouvelle Filière</h4></div>
                    <div class="modal-body">
                        <div class="form-group"><label>Nom</label>
                            <input type="text" name="nom_filiere" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Code</label>
                            <input type="text" name="code_filiere" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Description</label>
                            <textarea name="description" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer"><button class="btn btn-success">💾 Enregistrer</button></div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Édition -->
    <div class="modal fade" id="edit_filiere">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('update_filiere') }}" id="editFiliereForm">
                    @csrf
                    <input type="hidden" name="id" id="edit-id">
                    <div class="modal-header"><h4 class="modal-title">✏️ Modifier Filière</h4></div>
                    <div class="modal-body">
                        <div class="form-group"><label>Nom</label>
                            <input type="text" name="nom_filiere" id="edit-nom" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Code</label>
                            <input type="text" name="code_filiere" id="edit-code" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Description</label>
                            <textarea name="description" id="edit-description" class="form-control"></textarea>
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
        function editFiliere(id, nom, code, description) {
            $('#edit-id').val(id);
            $('#edit-nom').val(nom);
            $('#edit-code').val(code);
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