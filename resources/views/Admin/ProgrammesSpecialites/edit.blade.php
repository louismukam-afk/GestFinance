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
                        <th>Volume horaire</th>
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
                                <td>
                                    <select name="semestre" class="form-control">
                                        <option value="">-- Choisir --</option>
                                        @foreach(['S1', 'S2', 'S3', 'S4', 'S5', 'S6', 'S7', 'S8', 'S9', 'S10'] as $semestre)
                                            <option value="{{ $semestre }}" {{ $programme->semestre === $semestre ? 'selected' : '' }}>{{ $semestre }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" step="0.5" name="volume_horaire" value="{{ $programme->volume_horaire }}" class="form-control"></td>
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
                            <td colspan="8" class="text-center text-muted">Aucune matiere affectee pour le moment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <form method="POST" action="{{ route('programmes_specialites.store', $specialite) }}" id="programme-form">
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
                                <th>Volume horaire</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($matieres as $i => $matiere)
                                <tr class="matiere-row">
                                    <td>
                                        <input type="checkbox" name="programmes[{{ $i }}][selected]" value="1" class="matiere-selected">
                                        <input type="hidden" name="programmes[{{ $i }}][id_matiere]" value="{{ $matiere->id }}" class="matiere-field">
                                    </td>
                                    <td>
                                        <strong>{{ $matiere->nom_matiere }}</strong><br>
                                        <small class="text-muted">{{ $matiere->code_matiere }}</small>
                                    </td>
                                    <td><input type="text" name="programmes[{{ $i }}][code_matiere_specialite]" class="form-control matiere-field"></td>
                                    <td><input type="number" step="0.01" name="programmes[{{ $i }}][coefficient]" class="form-control matiere-field" value="1"></td>
                                    <td><input type="number" step="0.01" name="programmes[{{ $i }}][coefficient_maximum]" class="form-control matiere-field" value="1"></td>
                                    <td>
                                        <select name="programmes[{{ $i }}][type_matiere]" class="form-control matiere-field">
                                            <option value="transversale">Transversale</option>
                                            <option value="professionnelle" selected>Professionnelle</option>
                                            <option value="fondamentale">Fondamentale</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="programmes[{{ $i }}][semestre]" class="form-control matiere-field">
                                            <option value="">-- Choisir --</option>
                                            @foreach(['S1', 'S2', 'S3', 'S4', 'S5', 'S6', 'S7', 'S8', 'S9', 'S10'] as $semestre)
                                                <option value="{{ $semestre }}">{{ $semestre }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="number" step="0.5" name="programmes[{{ $i }}][volume_horaire]" class="form-control matiere-field"></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Toutes les matieres sont deja affectees a cette specialite.</td>
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

document.getElementById('programme-form').addEventListener('submit', function () {
    document.querySelectorAll('.matiere-row').forEach(function (row) {
        const checked = row.querySelector('.matiere-selected').checked;

        row.querySelectorAll('.matiere-field').forEach(function (field) {
            field.disabled = !checked;
        });
    });
});
</script>
@endsection

@section('breadcrumb')
<ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
    <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
    <li><a href="{{ route('emploi_temps.index') }}"><strong>Ressources humaines</strong></a></li>
    <li><a href="{{ route('programmes_specialites.index') }}"><strong>Programmes de specialite</strong></a></li>
    <li class="active"><strong>{{ $title }}</strong></li>
</ol>
@endsection
