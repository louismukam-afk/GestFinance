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

    <div class="card p-3 mb-4">
        <form method="GET" action="{{ route('emplois_permanents.index') }}">
            <div class="row">
                <div class="col-md-4">
                    <label>Rechercher un personnel</label>
                    <input type="text" name="search_personnel" class="form-control" value="{{ request('search_personnel') }}" placeholder="Nom du personnel">
                </div>
                <div class="col-md-4">
                    <label>Visualiser un emploi du temps</label>
                    <select name="id_personnel" class="form-control">
                        <option value="">-- Tous les permanents --</option>
                        @foreach($personnels as $personnel)
                            <option value="{{ $personnel->id }}" {{ request('id_personnel') == $personnel->id ? 'selected' : '' }}>
                                {{ $personnel->nom }} - {{ str_replace('_', ' ', $personnel->horaire_travail ?? '-') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button class="btn btn-primary mr-2">Filtrer</button>
                    <a href="{{ route('emplois_permanents.index') }}" class="btn btn-default mr-2">Reinitialiser</a>
                    @if($selectedPersonnel)
                        <a href="{{ route('emplois_permanents.personnel.pdf', $selectedPersonnel) }}" class="btn btn-danger">PDF</a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <form method="POST" action="{{ route('emplois_permanents.store') }}" class="card p-3 mb-4">
        @csrf
        <h5 class="mb-3">Creer un emploi du temps</h5>
        <div class="row">
            <div class="col-md-12">
                <label>Personnel(s) permanent(s)</label>
                <div class="border rounded p-2" style="max-height: 180px; overflow:auto">
                    @foreach($personnels as $personnel)
                        <label class="mr-3 mb-2" style="min-width: 260px">
                            <input type="checkbox" name="id_personnels[]" value="{{ $personnel->id }}">
                            {{ $personnel->nom }} - {{ str_replace('_', ' ', $personnel->horaire_travail ?? '-') }}
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="col-md-3 mt-2">
                <label>Plage horaire</label>
                <select name="id_plage_horaire" class="form-control" required>
                    <option value="">-- Choisir --</option>
                    @foreach($plages as $plage)
                        <option value="{{ $plage->id }}">
                            {{ $plage->libelle }} ({{ substr($plage->heure_debut, 0, 5) }} - {{ substr($plage->heure_fin, 0, 5) }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mt-2">
                <label>Annee academique</label>
                <select name="id_annee_academique" class="form-control">
                    <option value="">-- Toutes --</option>
                    @foreach($annees as $annee)
                        <option value="{{ $annee->id }}">{{ $annee->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mt-2">
                <label>Entite</label>
                <select name="id_entite" class="form-control">
                    <option value="">-- Toutes --</option>
                    @foreach($entites as $entite)
                        <option value="{{ $entite->id }}">{{ $entite->nom_entite }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mt-2">
                <label>Statut</label>
                <select name="statut" class="form-control">
                    <option value="actif">Actif</option>
                    <option value="inactif">Inactif</option>
                </select>
            </div>
            <div class="col-md-6 mt-2">
                <label>Jours de travail</label><br>
                @foreach($jours as $value => $label)
                    <label class="mr-3">
                        <input type="checkbox" name="jours[]" value="{{ $value }}"> {{ $label }}
                    </label>
                @endforeach
            </div>
            <div class="col-md-2 mt-2">
                <label>Date debut</label>
                <input type="date" name="date_debut" class="form-control" required>
            </div>
            <div class="col-md-2 mt-2">
                <label>Date fin</label>
                <input type="date" name="date_fin" class="form-control">
            </div>
            <div class="col-md-2 mt-2 d-flex align-items-end">
                <button class="btn btn-success w-100">Affecter</button>
            </div>
            <div class="col-md-12 mt-2">
                <label>Observations</label>
                <input type="text" name="observations" class="form-control">
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Personnel</th>
                    <th>Mode horaire</th>
                    <th>Jour</th>
                    <th>Plage</th>
                    <th>Annee / Entite</th>
                    <th>Periode</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($emplois as $emploi)
                    <tr>
                        <td>{{ $emploi->personnel->nom ?? '-' }}</td>
                        <td>
                            {{ str_replace('_', ' ', $emploi->personnel->horaire_travail ?? '-') }}<br>
                            <small>{{ $emploi->personnel->mode_horaire ?? 'strict' }} / {{ $emploi->personnel->categorie_horaire ?? 'standard' }}</small>
                        </td>
                        <td>{{ $jours[$emploi->jour_semaine] ?? '-' }}</td>
                        <td>{{ $emploi->plage->libelle ?? '-' }} {{ $emploi->plage ? '(' . substr($emploi->plage->heure_debut, 0, 5) . ' - ' . substr($emploi->plage->heure_fin, 0, 5) . ')' : '' }}</td>
                        <td>{{ $emploi->annee_academique->nom ?? 'Toutes' }} / {{ $emploi->entite->nom_entite ?? 'Toutes' }}</td>
                        <td>{{ optional($emploi->date_debut)->format('d/m/Y') }} - {{ optional($emploi->date_fin)->format('d/m/Y') ?: '...' }}</td>
                        <td><span class="badge badge-{{ $emploi->statut === 'actif' ? 'success' : 'secondary' }}">{{ $emploi->statut }}</span></td>
                        <td>
                            @if($emploi->id_personnel)
                                <a href="{{ route('emplois_permanents.personnel.edit', $emploi->id_personnel) }}" class="btn btn-sm btn-primary">Modifier</a>
                                <a href="{{ route('emplois_permanents.personnel.pdf', $emploi->id_personnel) }}" class="btn btn-sm btn-danger">PDF</a>
                            @else
                                <span class="text-muted">Personnel introuvable</span>
                            @endif
                            <form method="POST" action="{{ route('emplois_permanents.destroy', $emploi) }}" style="display:inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-warning" onclick="return confirm('Supprimer uniquement cette ligne ?')">Retirer</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted">Aucun emploi permanent affecte.</td></tr>
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
