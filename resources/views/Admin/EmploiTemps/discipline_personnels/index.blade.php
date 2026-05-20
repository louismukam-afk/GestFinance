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

    <form method="GET" class="card p-3 mb-4">
        <div class="row">
            <div class="col-md-3">
                <label>Personnel</label>
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
            <div class="col-md-2">
                <label>Type</label>
                <select name="type_discipline" class="form-control">
                    <option value="">Tous</option>
                    <option value="absence" {{ request('type_discipline') === 'absence' ? 'selected' : '' }}>Absence</option>
                    <option value="retard" {{ request('type_discipline') === 'retard' ? 'selected' : '' }}>Retard</option>
                </select>
            </div>
            <div class="col-md-2">
                <label>Statut</label>
                <select name="statut" class="form-control">
                    <option value="">Tous</option>
                    <option value="non_justifie" {{ request('statut') === 'non_justifie' ? 'selected' : '' }}>Non justifie</option>
                    <option value="justifie" {{ request('statut') === 'justifie' ? 'selected' : '' }}>Justifie</option>
                    <option value="annule" {{ request('statut') === 'annule' ? 'selected' : '' }}>Annule</option>
                </select>
            </div>
            <div class="col-md-2">
                <label>Annee academique</label>
                <select name="id_annee_academique" class="form-control">
                    <option value="">Toutes</option>
                    @foreach($annees as $annee)
                        <option value="{{ $annee->id }}" {{ request('id_annee_academique') == $annee->id ? 'selected' : '' }}>{{ $annee->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>Entite</label>
                <select name="id_entite" class="form-control">
                    <option value="">Toutes</option>
                    @foreach($entites as $entite)
                        <option value="{{ $entite->id }}" {{ request('id_entite') == $entite->id ? 'selected' : '' }}>{{ $entite->nom_entite }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mt-2"><label>Date debut</label><input type="date" name="date_debut" value="{{ request('date_debut') }}" class="form-control"></div>
            <div class="col-md-2 mt-2"><label>Date fin</label><input type="date" name="date_fin" value="{{ request('date_fin') }}" class="form-control"></div>
            <div class="col-md-2 mt-2 d-flex align-items-end"><button class="btn btn-primary w-100">Filtrer</button></div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>Personnel</th>
                    <th>Type</th>
                    <th>Cours / Plage</th>
                    <th>Duree / Retard</th>
                    <th>Motif</th>
                    <th>Statut</th>
                    <th>Justification</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($disciplines as $discipline)
                    <tr>
                        <td>{{ optional($discipline->date_discipline)->format('d/m/Y') }}</td>
                        <td>{{ $discipline->personnel->nom ?? '-' }}</td>
                        <td>{{ ucfirst($discipline->type_discipline) }}</td>
                        <td>
                            {{ $discipline->cours->programme->matiere->nom_matiere ?? '-' }}<br>
                            <small>{{ $discipline->seance->plage->libelle ?? '-' }}</small>
                        </td>
                        <td>
                            {{ number_format($discipline->duree_heures, 2, ',', ' ') }} h<br>
                            <small>{{ $discipline->minutes_retard }} min</small>
                        </td>
                        <td>{{ $discipline->motif }}</td>
                        <td><span class="badge badge-{{ $discipline->statut === 'justifie' ? 'success' : ($discipline->statut === 'annule' ? 'secondary' : 'danger') }}">{{ $discipline->statut }}</span></td>
                        <td>
                            {{ $discipline->motif_justification }}<br>
                            @foreach(($discipline->preuves ?? []) as $preuve)
                                <a href="{{ asset($preuve) }}" target="_blank">Piece</a>
                            @endforeach
                        </td>
                        <td style="min-width:260px">
                            <form method="POST" action="{{ route('discipline_personnels.justify', $discipline) }}" enctype="multipart/form-data" class="mb-2">
                                @csrf
                                <input type="date" name="date_justification" class="form-control mb-1" value="{{ now()->format('Y-m-d') }}" required>
                                <textarea name="motif_justification" class="form-control mb-1" placeholder="Motif de justification" required></textarea>
                                <input type="file" name="preuves[]" class="form-control mb-1" multiple accept=".jpg,.jpeg,.png,.pdf">
                                <button class="btn btn-xs btn-success">Justifier</button>
                            </form>
                            <form method="POST" action="{{ route('discipline_personnels.update', $discipline) }}">
                                @csrf @method('PUT')
                                <input type="hidden" name="motif" value="{{ $discipline->motif }}">
                                <select name="statut" class="form-control mb-1">
                                    <option value="non_justifie" {{ $discipline->statut === 'non_justifie' ? 'selected' : '' }}>Non justifie</option>
                                    <option value="justifie" {{ $discipline->statut === 'justifie' ? 'selected' : '' }}>Justifie</option>
                                    <option value="annule" {{ $discipline->statut === 'annule' ? 'selected' : '' }}>Annule</option>
                                </select>
                                <button class="btn btn-xs btn-primary">Mettre a jour</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted">Aucun element disciplinaire.</td></tr>
                @endforelse
            </tbody>
        </table>
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
