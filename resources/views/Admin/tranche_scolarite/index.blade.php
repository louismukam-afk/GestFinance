@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="text-primary">📘 {{ $title }}</h3>

        <p>
            <strong>Cycle :</strong> {{ $scolarite->cycles->nom_cycle ?? 'N/A' }} |
            <strong>Filière :</strong> {{ $scolarite->filiere->nom_filiere ?? 'N/A' }} |
            <strong>Niveau :</strong> {{ $scolarite->niveaux->nom_niveau ?? 'N/A' }} |
            <strong>Spécialité :</strong> {{ $scolarite->specialites->nom_specialite ?? 'N/A' }}
        </p>

        <!-- Bouton ajout -->
        <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" href="#add_tranche">
            ➕ Nouvelle Tranche
        </button>

        <div class="table-responsive mt-3">
            <table id="tranchesTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Nom Tranche</th>
                    <th>Date Limite</th>
                    <th>Montant</th>
                    <th>Date Création</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($tranches as $i => $tranche)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $tranche->nom_tranche }}</td>
                        <td>{{ \Carbon\Carbon::parse($tranche->date_limite)->format('d/m/Y') }}</td>
                        <td>{{ number_format($tranche->montant_tranche, 0, ',', ' ') }} FCFA</td>
                        <td>{{ $tranche->created_at->format('d/m/Y') }}</td>
                        <td>
                            <!-- Bouton Edit -->
                            <a href="#edit_tranche" data-toggle="modal" data-backdrop="false"
                               onclick="editTranche({{ $tranche->id }}, '{{ $tranche->nom_tranche }}', '{{ $tranche->date_limite }}', '{{ $tranche->montant_tranche }}')"
                               class="btn btn-xs btn-warning">
                                ✏️
                            </a>

                            <!-- Bouton Delete -->
                            <form action="{{ url('tranches/'.$tranche->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-xs btn-danger" onclick="return confirm('Supprimer cette tranche ?')">🗑️</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Ajout -->
    <div class="modal fade" id="add_tranche">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('tranche_scolarite.store', $scolarite->id) }}">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title">➕ Nouvelle Tranche</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group"><label>Nom tranche</label>
                            <input type="text" name="nom_tranche" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Date limite</label>
                            <input type="date" name="date_limite" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Montant</label>
                            <input type="number" name="montant_tranche" class="form-control" required>
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
    <div class="modal fade" id="edit_tranche">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('tranche_scolarite.update', $scolarite->id) }}" id="editTrancheForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" id="edit-id">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">x</button>

                        <h4 class="modal-title">✏️ Modifier Tranche</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group"><label>Nom tranche</label>
                            <input type="text" name="nom_tranche" id="edit-nom" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Date limite</label>
                            <input type="date" name="date_limite" id="edit-date" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Montant</label>
                            <input type="number" name="montant_tranche" id="edit-montant" class="form-control" required>
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
        function editTranche(id, nom, date, montant) {
            $('#edit-id').val(id);
            $('#edit-nom').val(nom);
            $('#edit-date').val(date);
            $('#edit-montant').val(montant);

            let formAction = "{{ url('tranches') }}/" + id;
            $('#editTrancheForm').attr('action', formAction);

        }
    </script>
@endsection
@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('dashboard') }}"><strong>Administration</strong></a></li>
        <li><a href="{{ route('scolarite_management',$scolarite->id ) }}"><strong>Gestion des scolarités</strong></a></li>
        <li><a href="{{ route('tranche_scolarite_manage',$scolarite->id ) }}"><strong>Choix d'une opération sur la scolarité</strong></a></li>

        <li class="active"><strong>{{ $title }}</strong></li>
    </ol>
@endsection