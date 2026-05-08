@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="text-primary">{{ $title }}</h3>
            <p class="text-muted mb-0">
                {{ $specialite->nom_specialite }} - {{ $specialite->code_specialite }}
                / {{ $contextLabels['cycle'] ?? '-' }}
                / {{ $contextLabels['filiere'] ?? '-' }}
                / {{ $contextLabels['niveau'] ?? '-' }}
                / {{ $contextLabels['annee'] ?? '-' }}
                / {{ $contextLabels['entite'] ?? '-' }}
            </p>
        </div>
        <a href="{{ route('programmes_specialites.configure', [
            'specialite' => $specialite->id,
            'id_cycle' => $context['id_cycle'],
            'id_filiere' => $context['id_filiere'],
            'id_niveau' => $context['id_niveau'],
        ]) }}" class="btn btn-secondary">Retour</a>
    </div>

    <div class="card mb-4">
        <div class="card-header"><strong>Matieres deja affectees a la specialite</strong></div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Matiere</th>
                        <th>Code specialite</th>
                        <th>Coefficient</th>
                        <th>Coefficient maximum</th>
                        <th>Type</th>
                        <th>Semestre</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($programmes as $programme)
                        <tr>
                            <form method="POST" action="{{ route('programmes_specialites.update', $programme) }}">
                                @csrf
                                @method('PUT')
                                <td>{{ $programme->matiere->nom_matiere ?? '-' }}</td>
                                <td><input type="text" name="code_matiere_specialite" value="{{ $programme->code_matiere_specialite }}" class="form-control"></td>
                                <td><input type="number" step="0.01" name="coefficient" value="{{ $programme->coefficient }}" class="form-control"></td>
                                <td><input type="number" step="0.01" name="coefficient_maximum" value="{{ $programme->coefficient_maximum }}" class="form-control"></td>
                                <td>
                                    <select name="type_matiere" class="form-control">
                                        @foreach(['transversale' => 'Transversale', 'professionnelle' => 'Professionnelle', 'fondamentale' => 'Fondamentale'] as $value => $label)
                                            <option value="{{ $value }}" {{ $programme->type_matiere === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="text" name="semestre" value="{{ $programme->semestre }}" class="form-control" placeholder="S1, S2..."></td>
                                <td>
                                    <button class="btn btn-sm btn-primary">Modifier</button>
                                    <a href="{{ route('groupes_matieres.create', $programme) }}" class="btn btn-sm btn-info">Groupe matiere</a>
                            </form>
                                    <form method="POST" action="{{ route('programmes_specialites.destroy', $programme) }}" style="display:inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Retirer cette matiere du programme ?')">Retirer</button>
                                    </form>
                                </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Aucune matiere affectee pour le moment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <form method="POST" action="{{ route('programmes_specialites.store', $specialite) }}">
        @csrf
        @foreach($context as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Ajouter des matieres au programme</strong>
                <button class="btn btn-success">Enregistrer la selection</button>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Rechercher une matiere</label>
                        <input type="text" id="matiere-search" class="form-control" placeholder="Nom ou code de matiere">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Choisir</th>
                                <th>Matiere</th>
                                <th>Code specialite</th>
                                <th>Coefficient</th>
                                <th>Coefficient maximum</th>
                                <th>Type</th>
                                <th>Semestre</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($matieres as $i => $matiere)
                                <tr class="matiere-row">
                                    <td>
                                        <input type="checkbox" name="programmes[{{ $i }}][selected]" value="1">
                                        <input type="hidden" name="programmes[{{ $i }}][id_matiere]" value="{{ $matiere->id }}">
                                    </td>
                                    <td>
                                        <strong>{{ $matiere->nom_matiere }}</strong><br>
                                        <small class="text-muted">{{ $matiere->code_matiere }}</small>
                                    </td>
                                    <td><input type="text" name="programmes[{{ $i }}][code_matiere_specialite]" class="form-control"></td>
                                    <td><input type="number" step="0.01" name="programmes[{{ $i }}][coefficient]" class="form-control"></td>
                                    <td><input type="number" step="0.01" name="programmes[{{ $i }}][coefficient_maximum]" class="form-control"></td>
                                    <td>
                                        <select name="programmes[{{ $i }}][type_matiere]" class="form-control">
                                            <option value="transversale">Transversale</option>
                                            <option value="professionnelle" selected>Professionnelle</option>
                                            <option value="fondamentale">Fondamentale</option>
                                        </select>
                                    </td>
                                    <td><input type="text" name="programmes[{{ $i }}][semestre]" class="form-control" placeholder="S1, S2..."></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Toutes les matieres sont deja affectees a cette specialite.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('matiere-search').addEventListener('input', function () {
    const term = this.value.toLowerCase();
    document.querySelectorAll('.matiere-row').forEach(function (row) {
        row.style.display = row.innerText.toLowerCase().includes(term) ? '' : 'none';
    });
});
</script>
@endsection

@section('breadcrumb')
<ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
    <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
    <li><a href="{{ route('specialite_management') }}"><strong>Specialites</strong></a></li>
    <li class="active"><strong>{{ $title }}</strong></li>
</ol>
@endsection
