@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-primary">{{ $title }}</h3>
        <a href="{{ route('emploi_temps.index') }}" class="btn btn-secondary">Retour</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <style>
        .permanent-anomalie { color: #b91c1c; font-weight: 600; }
        .permanent-row-anomalie td { background-color: #fff5f5; }
    </style>

    <div class="card mb-4">
        <div class="card-header"><strong>Importer un fichier biometrie permanent</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('biometrie_permanents.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-3">
                        <label>Libelle</label>
                        <input type="text" name="libelle" class="form-control" placeholder="Pointage permanents mai 2026">
                    </div>
                    <div class="col-md-2">
                        <label>Date debut</label>
                        <input type="date" name="date_debut" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label>Date fin</label>
                        <input type="date" name="date_fin" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label>Fichier</label>
                        <input type="file" name="fichier" class="form-control" required>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-success w-100">Importer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><strong>Imports biometrie permanents</strong></div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Libelle</th>
                        <th>Periode</th>
                        <th>Statut</th>
                        <th>Pointages</th>
                        <th>Non associes</th>
                        <th>Consolidations</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($imports as $import)
                        <tr>
                            <td>{{ $import->libelle }}</td>
                            <td>{{ optional($import->date_debut)->format('d/m/Y') }} - {{ optional($import->date_fin)->format('d/m/Y') }}</td>
                            <td><span class="badge badge-info">{{ $import->statut }}</span></td>
                            <td>{{ number_format($import->total_lignes, 0, ',', ' ') }}</td>
                            <td>{{ number_format($import->total_non_associees, 0, ',', ' ') }}</td>
                            <td>{{ number_format($import->total_consolidees, 0, ',', ' ') }}</td>
                            <td>
                                <form method="POST" action="{{ route('biometrie_permanents.consolider', $import) }}" style="display:inline">
                                    @csrf
                                    <button class="btn btn-xs btn-primary" onclick="return confirm('Relancer la consolidation permanente ? Les anciennes lignes de cet import seront recalculees.')">Consolider / Reactualiser</button>
                                </form>
                                <form method="POST" action="{{ route('biometrie_permanents.clear', $import) }}" style="display:inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-warning" onclick="return confirm('Supprimer uniquement les consolidations permanentes de cet import ?')">Vider</button>
                                </form>
                                <a href="{{ route('biometrie_permanents.index', ['id_biometrie_import' => $import->id]) }}" class="btn btn-xs btn-info">Voir</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">Aucun import biometrie permanent.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><strong>Associer un nom biometrie a un personnel permanent</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('biometrie_heures.mapping') }}">
                @csrf
                <div class="row">
                    <div class="col-md-5">
                        <label>Nom dans le fichier biometrie</label>
                        <select id="permanent-biometrie-identity-select" class="form-control" required>
                            <option value="">-- Choisir le nom biometrie --</option>
                            @foreach($biometrieIdentites as $identity)
                                <option value="{{ $identity->nom_biometrie }}|{{ $identity->numero_biometrie }}">
                                    {{ $identity->nom_biometrie }} / No {{ $identity->numero_biometrie }} / {{ $identity->total }} pointage(s)
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="nom_biometrie" id="permanent-mapping-nom-biometrie">
                        <input type="hidden" name="numero_biometrie" id="permanent-mapping-numero-biometrie">
                    </div>
                    <div class="col-md-5">
                        <label>Personnel correspondant</label>
                        <select name="id_personnel" class="form-control" required>
                            <option value="">-- Choisir le personnel --</option>
                            @foreach($personnels as $personnel)
                                <option value="{{ $personnel->id }}">{{ $personnel->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-success w-100">Associer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <form method="GET" class="card p-3 mb-4">
        <div class="row">
            <div class="col-md-3">
                <label>Import</label>
                <select name="id_biometrie_import" class="form-control">
                    <option value="">Tous</option>
                    @foreach($imports as $import)
                        <option value="{{ $import->id }}" {{ request('id_biometrie_import') == $import->id ? 'selected' : '' }}>{{ $import->libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>Personnel permanent</label>
                <select name="id_personnel" class="form-control">
                    <option value="">Tous</option>
                    @foreach($personnels as $personnel)
                        <option value="{{ $personnel->id }}" {{ request('id_personnel') == $personnel->id ? 'selected' : '' }}>{{ $personnel->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>Recherche personnel</label>
                <input type="text" name="search_personnel" value="{{ request('search_personnel') }}" class="form-control" placeholder="Nom du personnel">
            </div>
            <div class="col-md-2"><label>Date debut</label><input type="date" name="date_debut" value="{{ request('date_debut') }}" class="form-control"></div>
            <div class="col-md-2"><label>Date fin</label><input type="date" name="date_fin" value="{{ request('date_fin') }}" class="form-control"></div>
            <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100">Filtrer</button></div>
        </div>
    </form>

    <div class="mb-3">
        <button type="button" onclick="window.print()" class="btn btn-dark">Imprimer</button>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>Personnel</th>
                    <th>Plage permanente</th>
                    <th>Heure prevue</th>
                    <th>Heure realisee</th>
                    <th>Prevu</th>
                    <th>Realise</th>
                    <th>Non comptabilise</th>
                    <th>Salaire jour</th>
                    <th>Montant du</th>
                    <th>Penalite</th>
                    <th>Statut</th>
                    <th>Observation</th>
                </tr>
            </thead>
            <tbody>
                @forelse($presences as $presence)
                    @php
                        $nonComptabilise = max(($presence->duree_prevue ?? 0) - ($presence->duree_realisee ?? 0), 0);
                        $isAnomalie = $nonComptabilise > 0 || $presence->penalite_montant > 0 || !empty($presence->observation);
                    @endphp
                    <tr class="{{ $isAnomalie ? 'permanent-row-anomalie' : '' }}">
                        <td>{{ optional($presence->date_presence)->format('d/m/Y') }}</td>
                        <td>{{ $presence->personnel->nom ?? '-' }}</td>
                        <td>{{ $presence->plage->libelle ?? '-' }}</td>
                        <td>{{ $presence->heure_debut_prevue ? substr($presence->heure_debut_prevue, 0, 5) : '-' }} - {{ $presence->heure_fin_prevue ? substr($presence->heure_fin_prevue, 0, 5) : '-' }}</td>
                        <td>{{ $presence->heure_debut_reelle ? substr($presence->heure_debut_reelle, 0, 5) : '-' }} - {{ $presence->heure_fin_reelle ? substr($presence->heure_fin_reelle, 0, 5) : '-' }}</td>
                        <td>{{ number_format($presence->duree_prevue, 2, ',', ' ') }} h</td>
                        <td>{{ number_format($presence->duree_realisee, 2, ',', ' ') }} h</td>
                        <td class="{{ $nonComptabilise > 0 ? 'permanent-anomalie' : '' }}">{{ number_format($nonComptabilise, 2, ',', ' ') }} h</td>
                        <td>{{ number_format($presence->salaire_journalier, 0, ',', ' ') }}</td>
                        <td>{{ number_format($presence->montant_du, 0, ',', ' ') }}</td>
                        <td class="{{ $presence->penalite_montant > 0 ? 'permanent-anomalie' : '' }}">{{ number_format($presence->penalite_montant, 0, ',', ' ') }}</td>
                        <td><span class="badge badge-{{ $presence->statut === 'present' ? 'success' : ($presence->statut === 'partiel' ? 'warning' : ($presence->statut === 'jour_paye' ? 'info' : 'danger')) }}">{{ $presence->statut }}</span></td>
                        <td class="{{ $isAnomalie ? 'permanent-anomalie' : '' }}">{{ $presence->observation }}</td>
                    </tr>
                @empty
                    <tr><td colspan="13" class="text-center text-muted">Aucune presence permanente consolidee.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card mt-4">
        <div class="card-header"><strong>Totaux par personnel</strong></div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Personnel</th>
                        <th>Jours</th>
                        <th>Heures prevues</th>
                        <th>Heures realisees</th>
                        <th>Non comptabilise</th>
                        <th>Salaire theorique</th>
                        <th>Montant du</th>
                        <th>Penalite</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($totaux as $total)
                        <tr>
                            <td>{{ $total['personnel'] }}</td>
                            <td>{{ $total['jours'] }}</td>
                            <td>{{ number_format($total['heures_prevues'], 2, ',', ' ') }} h</td>
                            <td>{{ number_format($total['heures_realisees'], 2, ',', ' ') }} h</td>
                            <td class="{{ ($total['heures_non_comptabilisees'] ?? 0) > 0 ? 'permanent-anomalie' : '' }}">{{ number_format($total['heures_non_comptabilisees'] ?? 0, 2, ',', ' ') }} h</td>
                            <td>{{ number_format($total['salaire_theorique'], 0, ',', ' ') }}</td>
                            <td>{{ number_format($total['montant_du'], 0, ',', ' ') }}</td>
                            <td class="{{ ($total['penalite_montant'] ?? 0) > 0 ? 'permanent-anomalie' : '' }}">{{ number_format($total['penalite_montant'], 0, ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">Aucun total disponible.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($unmatchedPointages->isNotEmpty())
        <div class="card mt-4 mb-4">
            <div class="card-header"><strong>Pointages non associes a un personnel</strong></div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Nom biometrie</th>
                            <th>No biometrie</th>
                            <th>Associer a</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($unmatchedPointages as $pointage)
                            <tr>
                                <form method="POST" action="{{ route('biometrie_heures.mapping') }}">
                                    @csrf
                                    <td>
                                        {{ $pointage->nom_biometrie }}
                                        <input type="hidden" name="nom_biometrie" value="{{ $pointage->nom_biometrie }}">
                                    </td>
                                    <td>
                                        {{ $pointage->numero_biometrie }}
                                        <input type="hidden" name="numero_biometrie" value="{{ $pointage->numero_biometrie }}">
                                    </td>
                                    <td>
                                        <select name="id_personnel" class="form-control" required>
                                            <option value="">-- Personnel --</option>
                                            @foreach($personnels as $personnel)
                                                <option value="{{ $personnel->id }}">{{ $personnel->nom }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><button class="btn btn-xs btn-success">Associer</button></td>
                                </form>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection

@section('breadcrumb')
<ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
    <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
    <li><a href="{{ route('emploi_temps.index') }}"><strong>Ressources humaines</strong></a></li>
    <li class="active"><strong>{{ $title }}</strong></li>
</ol>
@endsection

@section('scripts')
<script>
document.getElementById('permanent-biometrie-identity-select')?.addEventListener('change', function () {
    const parts = this.value.split('|');
    document.getElementById('permanent-mapping-nom-biometrie').value = parts[0] || '';
    document.getElementById('permanent-mapping-numero-biometrie').value = parts[1] || '';
});
</script>
@endsection
