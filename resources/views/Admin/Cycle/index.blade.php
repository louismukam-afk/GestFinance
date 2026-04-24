@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="text-primary">📘 {{ $title }}</h3>

        <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" href="#add_cycle">
            ➕ Nouveau Cycle
        </button>

        <div class="table-responsive mt-3">
            <table id="cyclesTable" class="table table-bordered table-striped">
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
                @foreach($cycles as $i => $cycle)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $cycle->nom_cycle }}</td>
                        <td>{{ $cycle->code_cycle }}</td>
                        <td>{{ $cycle->description }}</td>
                        <td>{{ $cycle->created_at->format('d/m/Y') }}</td>
                        <td>
                            <!-- Bouton Edit -->
                            <a href="#edit_cycle" data-toggle="modal" data-backdrop="false"
                               onclick="editCycle({{ $cycle->id }}, '{{ $cycle->nom_cycle }}', '{{ $cycle->code_cycle }}', '{{ $cycle->description }}')"
                               class="btn btn-xs btn-warning">
                                ✏️
                            </a>

                            <!-- Bouton Delete -->
                            <form action="{{ route('delete_cycle', $cycle->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')

                                <button class="btn btn-xs btn-danger" onclick="return confirm('Supprimer ce cycle ?')">🗑️</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Ajout -->
    <div class="modal fade" id="add_cycle">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('store_cycle') }}">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title">➕ Nouveau Cycle</h4>
                        <button type="button" class="close" data-dismiss="modal">x</button>

                    </div>

                    <div class="modal-body">
                        <div class="form-group"><label>Nom</label>
                            <input type="text" name="nom_cycle" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Code</label>
                            <input type="text" name="code_cycle" class="form-control" required>
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
    <div class="modal fade" id="edit_cycle">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('update_cycle') }}" id="editCycleForm">
                    @csrf

                    <input type="hidden" name="id" id="edit-id">
                    <div class="modal-header">
                        <h4 class="modal-title">✏️ Modifier Cycle</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group"><label>Nom</label>
                            <input type="text" name="nom_cycle" id="edit-nom" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Code</label>
                            <input type="text" name="code_cycle" id="edit-code" class="form-control" required>
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
        function editCycle(id, nom, code, description) {
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