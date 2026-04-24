@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="text-primary">📘 {{ $title }}</h3>

        <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" href="#add_niveau">
            ➕ Nouveau Niveau
        </button>

        <div class="table-responsive mt-3">
            <table id="niveauxTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Code</th>
                    <th>Cycle</th>
                    <th>Date création</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($niveaux as $i => $n)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $n->nom_niveau }}</td>
                        <td>{{ $n->code_niveau }}</td>
                        <td>{{ $n->cycles->nom_cycle ?? 'N/A' }}</td>
                        <td>{{ $n->created_at->format('d/m/Y') }}</td>
                        <td>
                            <a href="#edit_niveau" data-toggle="modal" data-backdrop="false"
                               onclick="editNiveau({{ $n->id }}, '{{ $n->nom_niveau }}', '{{ $n->code_niveau }}', '{{ $n->id_cycle }}')"
                               class="btn btn-xs btn-warning">✏️</a>

                            <form action="{{ route('delete_niveau', $n->id) }}" method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-danger" onclick="return confirm('Supprimer ce niveau ?')">🗑️</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Ajout -->
    <div class="modal fade" id="add_niveau">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('store_niveau') }}">
                    @csrf
                    <div class="modal-header"><h4>➕ Nouveau Niveau</h4></div>
                    <div class="modal-body">
                        <div class="form-group"><label>Nom</label>
                            <input type="text" name="nom_niveau" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Code</label>
                            <input type="text" name="code_niveau" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Cycle</label>
                            <select name="id_cycle" class="form-control" required>
                                <option value="">-- Sélectionner --</option>
                                @foreach($cycles as $c)
                                    <option value="{{ $c->id }}">{{ $c->nom_cycle }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer"><button class="btn btn-success">💾 Enregistrer</button></div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Édition -->
    <div class="modal fade" id="edit_niveau">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('update_niveau') }}" id="editNiveauForm">
                    @csrf
                    <input type="hidden" name="id" id="edit-id">
                    <div class="modal-header"><h4>✏️ Modifier Niveau</h4></div>
                    <div class="modal-body">
                        <div class="form-group"><label>Nom</label>
                            <input type="text" name="nom_niveau" id="edit-nom" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Code</label>
                            <input type="text" name="code_niveau" id="edit-code" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Cycle</label>
                            <select name="id_cycle" id="edit-cycle" class="form-control" required>
                                @foreach($cycles as $c)
                                    <option value="{{ $c->id }}">{{ $c->nom_cycle }}</option>
                                @endforeach
                            </select>
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
        function editNiveau(id, nom, code, cycle) {
            $('#edit-id').val(id);
            $('#edit-nom').val(nom);
            $('#edit-code').val(code);
            $('#edit-cycle').val(cycle);
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