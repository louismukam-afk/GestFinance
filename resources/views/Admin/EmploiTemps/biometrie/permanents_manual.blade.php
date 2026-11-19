@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-primary">{{ $title }}</h3>
        <a href="{{ route('biometrie_permanents.index') }}" class="btn btn-secondary">Retour</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif
    @if($emplois->isEmpty())
        <div class="alert alert-warning">Aucun emploi permanent actif n'est disponible.</div>
    @endif

    <form method="POST" action="{{ route('biometrie_permanents.manual.store') }}" class="card p-3">
        @csrf

        <fieldset class="border p-3 mb-4">
            <legend class="w-auto px-2">Lot manuel</legend>
            <div class="row">
                <div class="col-md-4">
                    <label>Reutiliser un lot</label>
                    <select name="id_biometrie_import" id="id_biometrie_import" class="form-control">
                        <option value="">-- Nouveau lot manuel --</option>
                        @foreach($imports as $import)
                            <option value="{{ $import->id }}" data-debut="{{ optional($import->date_debut)->format('Y-m-d') }}" data-fin="{{ optional($import->date_fin)->format('Y-m-d') }}" {{ (old('id_biometrie_import') ?: request('id_biometrie_import')) == $import->id ? 'selected' : '' }}>
                                {{ $import->libelle }} ({{ optional($import->date_debut)->format('d/m/Y') }} - {{ optional($import->date_fin)->format('d/m/Y') }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Libelle du nouveau lot</label>
                    <input type="text" name="libelle" class="form-control" value="{{ old('libelle') }}" placeholder="Presences manuelles aout 2026">
                </div>
                <div class="col-md-2">
                    <label>Date debut</label>
                    <input type="date" name="date_debut_import" id="date_debut_import" class="form-control" value="{{ old('date_debut_import') }}">
                </div>
                <div class="col-md-2">
                    <label>Date fin</label>
                    <input type="date" name="date_fin_import" id="date_fin_import" class="form-control" value="{{ old('date_fin_import') }}">
                </div>
            </div>
        </fieldset>

        <fieldset class="border p-3 mb-4">
            <legend class="w-auto px-2">Personnel et plage</legend>
            <div class="row">
                <div class="col-md-4">
                    <label>Rechercher</label>
                    <input type="text" id="emploi_search" class="form-control" placeholder="Nom du personnel">
                </div>
                <div class="col-md-8">
                    <label>Emploi permanent</label>
                    <select name="id_emploi_permanent" id="id_emploi_permanent" class="form-control" required>
                        <option value="">-- Choisir le personnel et la plage --</option>
                        @foreach($emplois as $emploi)
                            <option value="{{ $emploi->id }}" {{ old('id_emploi_permanent') == $emploi->id ? 'selected' : '' }}>
                                {{ $emploi->personnel->nom ?? '-' }} | {{ $jours[$emploi->jour_semaine] ?? '-' }} | {{ $emploi->plage->libelle ?? '-' }} ({{ substr($emploi->plage->heure_debut ?? '', 0, 5) }} - {{ substr($emploi->plage->heure_fin ?? '', 0, 5) }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </fieldset>

        <fieldset class="border p-3 mb-4">
            <legend class="w-auto px-2">Presence realisee</legend>
            <div class="row">
                <div class="col-md-3">
                    <label>Date</label>
                    <input type="date" name="date_presence" class="form-control" value="{{ old('date_presence') }}" required>
                </div>
                <div class="col-md-2">
                    <label>Debut reel</label>
                    <input type="time" name="heure_debut_reelle" id="heure_debut_reelle" class="form-control" value="{{ old('heure_debut_reelle') }}" required>
                </div>
                <div class="col-md-2">
                    <label>Fin reelle</label>
                    <input type="time" name="heure_fin_reelle" id="heure_fin_reelle" class="form-control" value="{{ old('heure_fin_reelle') }}" required>
                </div>
                <div class="col-md-2">
                    <label>Duree indicative</label>
                    <input type="text" id="duree_calculee" class="form-control" value="0 h" readonly>
                </div>
                <div class="col-md-3">
                    <label>Observation</label>
                    <input type="text" name="observation" class="form-control" value="{{ old('observation') }}">
                </div>
            </div>
        </fieldset>

        <button class="btn btn-success">Enregistrer la presence</button>
    </form>

    <div class="card mt-4">
        <div class="card-header"><strong>Lots manuels existants</strong></div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr><th>Libelle</th><th>Periode</th><th>Presences</th><th>Action</th></tr>
                </thead>
                <tbody>
                    @forelse($imports as $import)
                        <tr>
                            <td>{{ $import->libelle }}</td>
                            <td>{{ optional($import->date_debut)->format('d/m/Y') }} - {{ optional($import->date_fin)->format('d/m/Y') }}</td>
                            <td>{{ number_format($import->total_consolidees, 0, ',', ' ') }}</td>
                            <td>
                                <a href="{{ route('biometrie_permanents.manual.create', ['id_biometrie_import' => $import->id]) }}" class="btn btn-xs btn-primary">Utiliser</a>
                                <a href="{{ route('biometrie_permanents.index', ['id_biometrie_import' => $import->id]) }}" class="btn btn-xs btn-info">Voir le decompte</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">Aucun lot manuel.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header"><strong>Presences saisies manuellement</strong></div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr><th>Lot</th><th>Date</th><th>Personnel</th><th>Plage</th><th>Heures prevues</th><th>Heures realisees</th><th>Duree</th><th>Statut</th></tr>
                </thead>
                <tbody>
                    @forelse($presencesManuelles as $presence)
                        <tr>
                            <td>{{ $presence->import->libelle ?? '-' }}</td>
                            <td>{{ optional($presence->date_presence)->format('d/m/Y') }}</td>
                            <td>{{ $presence->personnel->nom ?? '-' }}</td>
                            <td>{{ $presence->plage->libelle ?? '-' }}</td>
                            <td>{{ substr($presence->heure_debut_prevue ?? '', 0, 5) }} - {{ substr($presence->heure_fin_prevue ?? '', 0, 5) }}</td>
                            <td>{{ substr($presence->heure_debut_reelle ?? '', 0, 5) }} - {{ substr($presence->heure_fin_reelle ?? '', 0, 5) }}</td>
                            <td>{{ number_format($presence->duree_realisee, 2, ',', ' ') }} h</td>
                            <td><span class="badge badge-{{ $presence->statut === 'present' ? 'success' : ($presence->statut === 'partiel' ? 'warning' : 'danger') }}">{{ $presence->statut }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">Aucune presence manuelle saisie.</td></tr>
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
    <li><a href="{{ route('biometrie_permanents.index') }}"><strong>Biometrie permanents</strong></a></li>
    <li class="active"><strong>{{ $title }}</strong></li>
</ol>
@endsection

@section('scripts')
<script>
const emploisData = @json($emploisData);
const emploiSelect = document.getElementById('id_emploi_permanent');
const debutInput = document.getElementById('heure_debut_reelle');
const finInput = document.getElementById('heure_fin_reelle');
const dureeInput = document.getElementById('duree_calculee');

function minutes(value) {
    if (!value || !value.includes(':')) return null;
    const parts = value.split(':');
    return parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
}

function calculerDuree() {
    const emploi = emploisData[emploiSelect.value];
    let debut = minutes(debutInput.value);
    let fin = minutes(finInput.value);
    if (!emploi || debut === null || fin === null) {
        dureeInput.value = '0 h';
        return;
    }
    if (fin <= debut) fin += 24 * 60;
    dureeInput.value = ((fin - debut) / 60).toFixed(2).replace('.', ',') + ' h';
}

document.getElementById('id_biometrie_import').addEventListener('change', function () {
    const option = this.selectedOptions[0];
    document.getElementById('date_debut_import').value = option?.dataset.debut || '';
    document.getElementById('date_fin_import').value = option?.dataset.fin || '';
});
document.getElementById('emploi_search').addEventListener('input', function () {
    const needle = this.value.toLowerCase();
    Array.from(emploiSelect.options).forEach(option => {
        if (!option.value) return;
        option.hidden = !option.textContent.toLowerCase().includes(needle);
    });
});
emploiSelect.addEventListener('change', function () {
    const emploi = emploisData[this.value];
    if (emploi) {
        debutInput.value = emploi.debut || '';
        finInput.value = emploi.fin || '';
    }
    calculerDuree();
});
debutInput.addEventListener('input', calculerDuree);
finInput.addEventListener('input', calculerDuree);
document.getElementById('id_biometrie_import').dispatchEvent(new Event('change'));
calculerDuree();
</script>
@endsection
