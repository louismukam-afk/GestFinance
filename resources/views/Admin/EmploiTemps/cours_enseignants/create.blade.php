@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-primary">{{ $title }}</h3>
        <a href="{{ route('cours_enseignants.index') }}" class="btn btn-secondary">Retour</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ $cours ? route('cours_enseignants.update', $cours) : route('cours_enseignants.store') }}" class="card p-3">
        @csrf
        @if($cours) @method('PUT') @endif

        @if(!empty($contextLabels))
            <div class="alert alert-info">
                <strong>Contexte :</strong>
                {{ $contextLabels['cycle'] ?? '-' }} /
                {{ $contextLabels['filiere'] ?? '-' }} /
                {{ $contextLabels['niveau'] ?? '-' }} /
                {{ $contextLabels['annee'] ?? '-' }} /
                {{ $contextLabels['entite'] ?? '-' }}
            </div>
        @endif

        @if($programmes->isEmpty())
            <div class="alert alert-warning">
                Aucune matiere n'est affectee a cette specialite pour ce contexte. Ajoute d'abord les matieres dans le programme de specialite.
            </div>
        @endif

        <div class="row">
            <div class="col-md-4">
                <label>Enseignant</label>
                <input type="text" id="personnel-search" class="form-control mb-2" placeholder="Rechercher un enseignant">
                <select name="id_personnel" id="personnel-select" class="form-control" required>
                    <option value="">--</option>
                    @foreach($personnels as $personnel)
                        <option value="{{ $personnel->id }}" {{ old('id_personnel', optional($cours)->id_personnel) == $personnel->id ? 'selected' : '' }}>{{ $personnel->nom }} ({{ $personnel->type_personnel ?? 'permanent' }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-8">
                <label>Programme de specialite / matiere</label>
                <input type="text" id="programme-search" class="form-control mb-2" placeholder="Rechercher une matiere, un code ou un semestre">
                <select name="id_programme_specialite" id="programme-select" class="form-control" required>
                    <option value="">--</option>
                    @foreach($programmes as $programme)
                        <option value="{{ $programme->id }}" {{ old('id_programme_specialite', optional($cours)->id_programme_specialite) == $programme->id ? 'selected' : '' }}>
                            {{ $programme->matiere->nom_matiere ?? '-' }} /
                            {{ $programme->code_matiere_specialite ?? 'sans code' }} /
                            {{ $programme->specialite->nom_specialite ?? '-' }} /
                            {{ $programme->cycle->nom_cycle ?? '-' }} /
                            {{ $programme->niveau->nom_niveau ?? '-' }} /
                            {{ $programme->annee_academique->nom ?? '-' }} /
                            VH {{ $programme->volume_horaire ?? 0 }}h /
                            Semestre {{ $programme->semestre ?? '-' }} /
                            {{ ucfirst($programme->type_matiere) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-2">
                <label>Salle</label>
                <select name="id_salle" class="form-control" required>
                    <option value="">--</option>
                    @foreach($salles as $salle)
                        <option value="{{ $salle->id }}" {{ old('id_salle', optional($cours)->id_salle) == $salle->id ? 'selected' : '' }}>{{ $salle->nom_salle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label>Taux horaire</label>
                <select name="id_taux_horaire" class="form-control">
                    <option value="">Taux par defaut</option>
                    @foreach($tauxHoraires as $taux)
                        <option value="{{ $taux->id }}" {{ old('id_taux_horaire', optional($cours)->id_taux_horaire) == $taux->id ? 'selected' : '' }}>{{ $taux->libelle }} - {{ number_format($taux->montant, 0, ',', ' ') }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><label>Date debut</label><input type="date" name="date_debut" value="{{ old('date_debut', optional(optional($cours)->date_debut)->format('Y-m-d')) }}" class="form-control" required></div>
            <div class="col-md-2"><label>Date fin</label><input type="date" name="date_fin" value="{{ old('date_fin', optional(optional($cours)->date_fin)->format('Y-m-d')) }}" class="form-control" required></div>
            <div class="col-md-2">
                <label>Semestre</label>
                <select name="semestre" class="form-control" required>
                    <option value="">--</option>
                    <option value="1" {{ old('semestre', optional($cours)->semestre) == 1 ? 'selected' : '' }}>Semestre 1</option>
                    <option value="2" {{ old('semestre', optional($cours)->semestre) == 2 ? 'selected' : '' }}>Semestre 2</option>
                </select>
            </div>
            <div class="col-md-2"><label>Statut</label><select name="statut" class="form-control"><option value="actif" {{ old('statut', optional($cours)->statut ?? 'actif') === 'actif' ? 'selected' : '' }}>Actif</option><option value="inactif" {{ old('statut', optional($cours)->statut) === 'inactif' ? 'selected' : '' }}>Inactif</option></select></div>
        </div>

        <hr>
        <div class="d-flex justify-content-between align-items-center">
            <h4>Seances hebdomadaires</h4>
            <button type="button" class="btn btn-info" id="add-seance">Ajouter une seance</button>
        </div>

        <div id="seances">
            @php
                $oldSeances = old('seances');
                $rows = $oldSeances ?: ($cours ? $cours->seances->map(fn($s) => ['jour_semaine' => $s->jour_semaine, 'id_plage_horaire' => $s->id_plage_horaire])->toArray() : [['jour_semaine' => 1, 'id_plage_horaire' => '']]);
            @endphp
            @foreach($rows as $i => $row)
                <div class="row seance-row mb-2">
                    <div class="col-md-4">
                        <select name="seances[{{ $i }}][jour_semaine]" class="form-control" required>
                            @foreach($jours as $value => $label)
                                <option value="{{ $value }}" {{ ($row['jour_semaine'] ?? '') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <select name="seances[{{ $i }}][id_plage_horaire]" class="form-control" required>
                            <option value="">Plage horaire</option>
                            @foreach($plages as $plage)
                                <option value="{{ $plage->id }}" {{ ($row['id_plage_horaire'] ?? '') == $plage->id ? 'selected' : '' }}>{{ $plage->libelle }} ({{ substr($plage->heure_debut,0,5) }} - {{ substr($plage->heure_fin,0,5) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2"><button type="button" class="btn btn-danger remove-seance">Retirer</button></div>
                </div>
            @endforeach
        </div>

        <button class="btn btn-success mt-3">Enregistrer</button>
    </form>
</div>
@endsection

@section('scripts')
<script>
const programmeSearch = document.getElementById('programme-search');
const programmeSelect = document.getElementById('programme-select');
const programmeOptions = Array.from(programmeSelect.options).map(function (option) {
    return { value: option.value, text: option.text, selected: option.selected };
});
const personnelSearch = document.getElementById('personnel-search');
const personnelSelect = document.getElementById('personnel-select');
const personnelOptions = Array.from(personnelSelect.options).map(function (option) {
    return { value: option.value, text: option.text, selected: option.selected };
});

programmeSearch.addEventListener('input', function () {
    const term = this.value.toLowerCase().trim();
    const currentValue = programmeSelect.value;
    programmeSelect.innerHTML = '';

    programmeOptions.forEach(function (option) {
        if (option.value === '' || option.text.toLowerCase().includes(term)) {
            const node = new Option(option.text, option.value, false, option.value === currentValue);
            programmeSelect.add(node);
        }
    });
});

personnelSearch.addEventListener('input', function () {
    const term = this.value.toLowerCase().trim();
    const currentValue = personnelSelect.value;
    personnelSelect.innerHTML = '';

    personnelOptions.forEach(function (option) {
        if (option.value === '' || option.text.toLowerCase().includes(term)) {
            const node = new Option(option.text, option.value, false, option.value === currentValue);
            personnelSelect.add(node);
        }
    });
});

let seanceIndex = document.querySelectorAll('.seance-row').length;
document.getElementById('add-seance').addEventListener('click', function () {
    const first = document.querySelector('.seance-row');
    const clone = first.cloneNode(true);
    clone.querySelectorAll('select').forEach(function (select) {
        select.name = select.name.replace(/seances\[\d+\]/, 'seances[' + seanceIndex + ']');
    });
    document.getElementById('seances').appendChild(clone);
    seanceIndex++;
});
document.addEventListener('click', function (event) {
    if (event.target.classList.contains('remove-seance') && document.querySelectorAll('.seance-row').length > 1) {
        event.target.closest('.seance-row').remove();
    }
});
</script>
@endsection

@section('breadcrumb')
<ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
    <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
    <li><a href="{{ route('emploi_temps.index') }}"><strong>Ressources humaines</strong></a></li>
    <li><a href="{{ route('cours_enseignants.index') }}"><strong>Cours enseignants</strong></a></li>
    <li class="active"><strong>{{ $title }}</strong></li>
</ol>
@endsection
