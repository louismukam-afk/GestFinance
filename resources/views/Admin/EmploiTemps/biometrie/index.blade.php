@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-primary">{{ $title }}</h3>
        <div>
            <a href="{{ route('biometrie_heures.manual.create') }}" class="btn btn-primary">Saisie manuelle</a>
            <a href="{{ route('emploi_temps.index') }}" class="btn btn-secondary">Retour</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <style>
        .biometrie-anomalie {
            color: #b91c1c;
            font-weight: 600;
        }
        .biometrie-row-anomalie td {
            background-color: #fff5f5;
        }
    </style>

    <div class="card mb-4">
        <div class="card-header"><strong>Importer un fichier biometrie</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('biometrie_heures.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-3">
                        <label>Libelle</label>
                        <input type="text" name="libelle" class="form-control" placeholder="Controle acces avril 2026">
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
        <div class="card-header"><strong>Imports biometrie</strong></div>
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
                                @if($import->statut === 'manuel')
                                    <a href="{{ route('biometrie_heures.manual.create', ['id_biometrie_import' => $import->id]) }}" class="btn btn-xs btn-primary">Ajouter une heure</a>
                                @else
                                <form method="POST" action="{{ route('biometrie_heures.consolider', $import) }}" style="display:inline">
                                    @csrf
                                    <button class="btn btn-xs btn-primary" onclick="return confirm('Relancer la consolidation ? Les anciennes lignes de cet import seront recalculées.')">Consolider / Reactualiser</button>
                                </form>
                                @endif
                                <form method="POST" action="{{ route('biometrie_heures.clear', $import) }}" style="display:inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-warning" onclick="return confirm('Supprimer uniquement les consolidations de cet import ?')">Vider</button>
                                </form>
                                <form method="POST" action="{{ route('biometrie_heures.destroy', $import) }}" style="display:inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-danger" onclick="return confirm('Supprimer cet import et toutes ses données ?')">Supprimer</button>
                                </form>
                                <a href="{{ route('biometrie_heures.index', ['id_biometrie_import' => $import->id]) }}" class="btn btn-xs btn-info">Voir</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">Aucun import biometrie.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><strong>Associer un nom biometrie a un enseignant</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('biometrie_heures.mapping') }}">
                @csrf
                <div class="row">
                    <div class="col-md-5">
                        <label>Nom dans le fichier biometrie</label>
                        <select id="biometrie-identity-select" class="form-control" required>
                            <option value="">-- Choisir le nom biometrie --</option>
                            @foreach($biometrieIdentites as $identity)
                                <option value="{{ $identity->nom_biometrie }}|{{ $identity->numero_biometrie }}">
                                    {{ $identity->nom_biometrie }} / No {{ $identity->numero_biometrie }} / {{ $identity->total }} pointage(s)
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="nom_biometrie" id="mapping-nom-biometrie">
                        <input type="hidden" name="numero_biometrie" id="mapping-numero-biometrie">
                    </div>
                    <div class="col-md-5">
                        <label>Enseignant correspondant</label>
                        <select name="id_personnel" class="form-control" required>
                            <option value="">-- Choisir l'enseignant --</option>
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
                <label>Enseignant</label>
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
        <a href="{{ route('biometrie_heures.excel', request()->query()) }}" class="btn btn-success">Export Excel</a>
        <a href="{{ route('biometrie_heures.pdf', request()->query()) }}" class="btn btn-danger">Export PDF</a>
        <button type="button" onclick="window.print()" class="btn btn-dark">Imprimer</button>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>Enseignant</th>
                    <th>Matiere</th>
                    <th>Plage prevue</th>
                    <th>Heure debut realisee</th>
                    <th>Heure fin realisee</th>
                    <th>Prevu</th>
                    <th>Realise</th>
                    <th>Non comptabilise</th>
                    <th>Taux</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th>Observation</th>
                </tr>
            </thead>
            <tbody>
                @forelse($heures as $heure)
                    @php
                        $nonComptabilise = max(($heure->duree_prevue ?? 0) - ($heure->duree_realisee ?? 0), 0);
                        $isAnomalie = $nonComptabilise > 0 || !empty($heure->observation);
                    @endphp
                    <tr class="{{ $isAnomalie ? 'biometrie-row-anomalie' : '' }}">
                        <td>{{ optional($heure->date_seance)->format('d/m/Y') }}</td>
                        <td>{{ $heure->personnel->nom ?? '-' }}</td>
                        <td>{{ $heure->cours->programme->matiere->nom_matiere ?? '-' }}</td>
                        <td>{{ substr($heure->heure_debut_prevue, 0, 5) }} - {{ substr($heure->heure_fin_prevue, 0, 5) }}</td>
                        <td>{{ $heure->heure_debut_reelle ? substr($heure->heure_debut_reelle, 0, 5) : '-' }}</td>
                        <td>{{ $heure->heure_fin_reelle ? substr($heure->heure_fin_reelle, 0, 5) : '-' }}</td>
                        <td>{{ number_format($heure->duree_prevue, 2, ',', ' ') }} h</td>
                        <td>{{ number_format($heure->duree_realisee, 2, ',', ' ') }} h</td>
                        <td class="{{ $nonComptabilise > 0 ? 'biometrie-anomalie' : '' }}">{{ number_format($nonComptabilise, 2, ',', ' ') }} h</td>
                        <td>{{ number_format($heure->montant_taux, 0, ',', ' ') }}</td>
                        <td>{{ number_format($heure->montant_total, 0, ',', ' ') }}</td>
                        <td><span class="badge badge-{{ $heure->statut === 'realise' ? 'success' : 'warning' }}">{{ $heure->statut }}</span></td>
                        <td class="{{ $isAnomalie ? 'biometrie-anomalie' : '' }}">{{ $heure->observation }}</td>
                    </tr>
                @empty
                    <tr><td colspan="13" class="text-center text-muted">Aucune heure consolidee.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($unmatchedPointages->isNotEmpty())
        <div class="card mt-4 mb-4">
            <div class="card-header"><strong>Pointages non associes a un enseignant</strong></div>
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
                                            <option value="">-- Enseignant --</option>
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

    <div class="card mt-4">
        <div class="card-header"><strong>Totaux par enseignant</strong></div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Enseignant</th>
                        <th>Total heures prevues</th>
                        <th>Total heures realisees</th>
                        <th>Total non comptabilise</th>
                        <th>Montant total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($totaux as $total)
                        <tr>
                            <td>{{ $total['enseignant'] }}</td>
                            <td>{{ number_format($total['heures_prevues'], 2, ',', ' ') }} h</td>
                            <td>{{ number_format($total['heures_realisees'], 2, ',', ' ') }} h</td>
                            <td class="{{ ($total['heures_non_comptabilisees'] ?? 0) > 0 ? 'biometrie-anomalie' : '' }}">{{ number_format($total['heures_non_comptabilisees'] ?? 0, 2, ',', ' ') }} h</td>
                            <td>{{ number_format($total['montant_total'], 0, ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Aucun total disponible.</td></tr>
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
    <li class="active"><strong>{{ $title }}</strong></li>
</ol>
@endsection

@section('scripts')
<script>
document.getElementById('biometrie-identity-select')?.addEventListener('change', function () {
    const parts = this.value.split('|');
    document.getElementById('mapping-nom-biometrie').value = parts[0] || '';
    document.getElementById('mapping-numero-biometrie').value = parts[1] || '';
});
</script>
@endsection
