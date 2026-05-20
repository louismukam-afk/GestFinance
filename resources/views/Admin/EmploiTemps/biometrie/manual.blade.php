@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-primary">{{ $title }}</h3>
        <div>
            <a href="{{ route('biometrie_heures.index') }}" class="btn btn-secondary">Retour</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif
    @if($imports->isEmpty())
        <div class="alert alert-info">
            Aucun import manuel n'existe encore. Choisis <strong>Nouvel import manuel</strong>, renseigne la periode, puis enregistre une premiere heure realisee.
        </div>
    @endif
    @if($cours->isEmpty())
        <div class="alert alert-warning">
            Aucun cours enseignant programme n'est disponible. Cree d'abord les cours enseignants et leurs seances avant de saisir les heures realisees.
        </div>
    @else
        <div class="alert alert-secondary">
            {{ number_format($cours->count(), 0, ',', ' ') }} cours enseignant(s) disponible(s) pour la saisie manuelle.
        </div>
    @endif

    <form method="POST" action="{{ route('biometrie_heures.manual.store') }}" class="card p-3">
        @csrf

        <fieldset class="border p-3 mb-4">
            <legend class="w-auto px-2">Import manuel</legend>
            <div class="row">
                <div class="col-md-4">
                    <label>Reutiliser un import manuel</label>
                    <select name="id_biometrie_import" id="id_biometrie_import" class="form-control">
                        <option value="">-- Nouvel import manuel --</option>
                        @foreach($imports as $import)
                            <option value="{{ $import->id }}" data-debut="{{ optional($import->date_debut)->format('Y-m-d') }}" data-fin="{{ optional($import->date_fin)->format('Y-m-d') }}" {{ (old('id_biometrie_import') ?: request('id_biometrie_import')) == $import->id ? 'selected' : '' }}>
                                {{ $import->libelle }} ({{ optional($import->date_debut)->format('d/m/Y') }} - {{ optional($import->date_fin)->format('d/m/Y') }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Libelle nouvel import</label>
                    <input type="text" name="libelle" class="form-control" value="{{ old('libelle') }}" placeholder="Saisie manuelle mai 2026">
                </div>
                <div class="col-md-2">
                    <label>Date debut import</label>
                    <input type="date" name="date_debut_import" id="date_debut_import" class="form-control" value="{{ old('date_debut_import') }}">
                </div>
                <div class="col-md-2">
                    <label>Date fin import</label>
                    <input type="date" name="date_fin_import" id="date_fin_import" class="form-control" value="{{ old('date_fin_import') }}">
                </div>
            </div>
        </fieldset>

        <fieldset class="border p-3 mb-4">
            <legend class="w-auto px-2">Cours et seance</legend>
            <div class="row">
                <div class="col-md-8">
                    <label>Cours enseignant</label>
                    <input type="text" id="cours_search" class="form-control mb-2" placeholder="Rechercher enseignant, matiere ou specialite">
                    <select name="id_cours_enseignant" id="id_cours_enseignant" class="form-control" required>
                        <option value="">-- Choisir le cours --</option>
                        @foreach($cours as $item)
                            <option value="{{ $item->id }}" {{ old('id_cours_enseignant') == $item->id ? 'selected' : '' }}>
                                {{ $item->personnel->nom ?? '-' }} | {{ $item->programme->matiere->nom_matiere ?? '-' }} | {{ $item->programme->specialite->nom_specialite ?? '-' }} | {{ optional($item->date_debut)->format('d/m/Y') }} - {{ optional($item->date_fin)->format('d/m/Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Seance / plage</label>
                    <select name="id_seance_cours" id="id_seance_cours" class="form-control" required>
                        <option value="">-- Choisir la seance --</option>
                    </select>
                </div>
            </div>
        </fieldset>

        <fieldset class="border p-3 mb-4">
            <legend class="w-auto px-2">Heures realisees</legend>
            <div class="row">
                <div class="col-md-3">
                    <label>Date seance</label>
                    <input type="date" name="date_seance" class="form-control" value="{{ old('date_seance') }}" required>
                </div>
                <div class="col-md-2">
                    <label>Debut realise</label>
                    <input type="time" name="heure_debut_reelle" id="heure_debut_reelle" class="form-control" value="{{ old('heure_debut_reelle') }}" required>
                </div>
                <div class="col-md-2">
                    <label>Fin realisee</label>
                    <input type="time" name="heure_fin_reelle" id="heure_fin_reelle" class="form-control" value="{{ old('heure_fin_reelle') }}" required>
                </div>
                <div class="col-md-2">
                    <label>Duree calculee</label>
                    <input type="text" id="duree_calculee" class="form-control" value="0 h" readonly>
                </div>
                <div class="col-md-3">
                    <label>Observation</label>
                    <input type="text" name="observation" class="form-control" value="{{ old('observation') }}">
                </div>
            </div>
            <small class="text-muted d-block mt-2">
                La duree est plafonnee a la plage prevue du cours : une saisie qui deborde l'horaire programme ne comptera pas plus que le volume prevu.
            </small>
        </fieldset>

        <button class="btn btn-success">Enregistrer l'heure realisee</button>
    </form>

    <div class="card mt-4">
        <div class="card-header"><strong>Imports manuels existants</strong></div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Libelle</th>
                        <th>Periode</th>
                        <th>Heures saisies</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($imports as $import)
                        <tr>
                            <td>{{ $import->libelle }}</td>
                            <td>{{ optional($import->date_debut)->format('d/m/Y') }} - {{ optional($import->date_fin)->format('d/m/Y') }}</td>
                            <td>{{ number_format($import->total_consolidees, 0, ',', ' ') }}</td>
                            <td>
                                <a href="{{ route('biometrie_heures.manual.create', ['id_biometrie_import' => $import->id]) }}" class="btn btn-xs btn-primary">Utiliser</a>
                                <a href="{{ route('biometrie_heures.index', ['id_biometrie_import' => $import->id]) }}" class="btn btn-xs btn-info">Voir decompte</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">Aucun import manuel pour le moment.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header"><strong>Heures manuelles saisies</strong></div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Import</th>
                        <th>Date</th>
                        <th>Enseignant</th>
                        <th>Matiere</th>
                        <th>Plage prevue</th>
                        <th>Heures realisees</th>
                        <th>Duree</th>
                        <th>Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($heuresManuelles as $heure)
                        <tr>
                            <td>{{ $heure->import->libelle ?? '-' }}</td>
                            <td>{{ optional($heure->date_seance)->format('d/m/Y') }}</td>
                            <td>{{ $heure->personnel->nom ?? '-' }}</td>
                            <td>{{ $heure->cours->programme->matiere->nom_matiere ?? '-' }}</td>
                            <td>{{ substr($heure->heure_debut_prevue, 0, 5) }} - {{ substr($heure->heure_fin_prevue, 0, 5) }}</td>
                            <td>{{ substr($heure->heure_debut_reelle, 0, 5) }} - {{ substr($heure->heure_fin_reelle, 0, 5) }}</td>
                            <td>{{ number_format($heure->duree_realisee, 2, ',', ' ') }} h</td>
                            <td>{{ number_format($heure->montant_total, 0, ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">Aucune heure manuelle saisie.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('breadcrumb')
<ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
    <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
    <li><a href="{{ route('emploi_temps.index') }}"><strong>Ressources humaines</strong></a></li>
    <li><a href="{{ route('biometrie_heures.index') }}"><strong>Decompte heures</strong></a></li>
    <li class="active"><strong>{{ $title }}</strong></li>
</ol>
@endsection

@section('scripts')
<script>
const coursData = @json($coursData);
const coursSelect = document.getElementById('id_cours_enseignant');
const seanceSelect = document.getElementById('id_seance_cours');
const debutInput = document.getElementById('heure_debut_reelle');
const finInput = document.getElementById('heure_fin_reelle');
const dureeInput = document.getElementById('duree_calculee');

function minutes(value) {
    if (!value || !value.includes(':')) return null;
    const parts = value.split(':');
    return parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
}

function selectedSeance() {
    const data = coursData[coursSelect.value];
    if (!data) return null;
    return data.seances.find(item => String(item.id) === String(seanceSelect.value));
}

function refreshSeances() {
    const oldValue = "{{ old('id_seance_cours') }}";
    seanceSelect.innerHTML = '<option value="">-- Choisir la seance --</option>';
    const data = coursData[coursSelect.value];
    if (!data) {
        calculerDuree();
        return;
    }
    data.seances.forEach(item => {
        const option = document.createElement('option');
        option.value = item.id;
        option.textContent = (item.jour_label || '-') + ' - ' + item.libelle;
        option.dataset.debut = item.debut;
        option.dataset.fin = item.fin;
        if (String(oldValue) === String(item.id)) option.selected = true;
        seanceSelect.appendChild(option);
    });
    calculerDuree();
}

function calculerDuree() {
    const seance = selectedSeance();
    const realStart = minutes(debutInput.value);
    const realEnd = minutes(finInput.value);
    if (!seance || realStart === null || realEnd === null || realEnd <= realStart) {
        dureeInput.value = '0 h';
        return;
    }
    const plannedStart = minutes(seance.debut);
    const plannedEnd = minutes(seance.fin);
    const clippedStart = Math.max(realStart, plannedStart);
    const clippedEnd = Math.min(realEnd, plannedEnd);
    const duration = clippedEnd > clippedStart ? ((clippedEnd - clippedStart) / 60) : 0;
    dureeInput.value = duration.toFixed(2).replace('.', ',') + ' h';
}

document.getElementById('id_biometrie_import').addEventListener('change', function () {
    const option = this.selectedOptions[0];
    document.getElementById('date_debut_import').value = option?.dataset.debut || '';
    document.getElementById('date_fin_import').value = option?.dataset.fin || '';
});
document.getElementById('cours_search').addEventListener('input', function () {
    const needle = this.value.toLowerCase();
    Array.from(coursSelect.options).forEach(option => {
        if (!option.value) return;
        option.hidden = !option.textContent.toLowerCase().includes(needle);
    });
});
coursSelect.addEventListener('change', refreshSeances);
seanceSelect.addEventListener('change', calculerDuree);
debutInput.addEventListener('input', calculerDuree);
finInput.addEventListener('input', calculerDuree);
document.getElementById('id_biometrie_import').dispatchEvent(new Event('change'));
refreshSeances();
</script>
@endsection
