@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="text-primary">{{ $title }}</h3>
            <p class="text-muted mb-0">
                Matiere principale : {{ $programme->matiere->nom_matiere ?? '-' }}
                / Specialite : {{ $programme->specialite->nom_specialite ?? '-' }}
                / {{ $programme->cycle->nom_cycle ?? '-' }}
                / {{ $programme->filiere->nom_filiere ?? '-' }}
                / {{ $programme->niveau->nom_niveau ?? '-' }}
                / {{ $programme->annee_academique->nom ?? '-' }}
                / {{ $programme->entite->nom_entite ?? '-' }}
            </p>
        </div>
        <a href="{{ route('programmes_specialites.edit', [
            'specialite' => $programme->id_specialite,
            'id_cycle' => $programme->id_cycle,
            'id_filiere' => $programme->id_filiere,
            'id_niveau' => $programme->id_niveau,
            'id_annee_academique' => $programme->id_annee_academique,
            'id_entite' => $programme->id_entite,
        ]) }}" class="btn btn-secondary">Retour programme</a>
    </div>

    <div class="card mb-4">
        <div class="card-header"><strong>Nouveau groupe</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('groupes_matieres.store', $programme) }}">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Module</label>
                        <input type="text" class="form-control" value="{{ $programme->matiere->nom_matiere ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-8">
                        <label>Description</label>
                        <input type="text" name="description" class="form-control">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Recherche</label>
                        <input type="text" id="matiere-search" class="form-control" placeholder="Rechercher une matiere du programme">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Choisir</th>
                                <th>Matiere</th>
                                <th>Code specialite</th>
                                <th>Type</th>
                                <th>Semestre</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($programmes as $item)
                                <tr class="matiere-row">
                                    <td><input type="checkbox" name="programmes[]" value="{{ $item->id }}"></td>
                                    <td>{{ $item->matiere->nom_matiere ?? '-' }}</td>
                                    <td>{{ $item->code_matiere_specialite }}</td>
                                    <td>{{ ucfirst($item->type_matiere) }}</td>
                                    <td>{{ $item->semestre }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">Aucune autre matiere disponible. Les matieres deja utilisees comme module ou comme element d'un groupe ne sont plus proposees.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <button class="btn btn-success">Enregistrer le groupe</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Groupes existants pour cette matiere</strong></div>
        <div class="card-body">
            @forelse($groupes as $groupe)
                <div class="border rounded p-2 mb-2">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>Module : {{ $programme->matiere->nom_matiere ?? $groupe->libelle_groupe }}</strong>
                            <div class="text-muted">{{ $groupe->description }}</div>
                        </div>
                        <form method="POST" action="{{ route('groupes_matieres.destroy', $groupe) }}">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Supprimer tout ce groupe matiere ?')">Supprimer le groupe</button>
                        </form>
                    </div>
                    <ul class="mb-0">
                        @foreach($groupe->lignes as $ligne)
                            <li class="mb-1">
                                {{ $ligne->programme->matiere->nom_matiere ?? '-' }}
                                <form method="POST" action="{{ route('groupes_matieres.lignes.destroy', $ligne) }}" style="display:inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-xs btn-danger" onclick="return confirm('Retirer cette matiere du groupe ?')">Retirer</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @empty
                <div class="text-muted">Aucun groupe cree pour cette matiere.</div>
            @endforelse
        </div>
    </div>
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
