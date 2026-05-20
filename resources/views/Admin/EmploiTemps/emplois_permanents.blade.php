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

    <form method="POST" action="{{ route('emplois_permanents.store') }}" class="card p-3 mb-4">
        @csrf
        <div class="row">
            <div class="col-md-3">
                <label>Personnel permanent</label>
                <select name="id_personnel" class="form-control" required>
                    <option value="">-- Choisir --</option>
                    @foreach($personnels as $personnel)
                        <option value="{{ $personnel->id }}">{{ $personnel->nom }} - {{ str_replace('_', ' ', $personnel->horaire_travail ?? '-') }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
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
            <div class="col-md-2">
                <label>Annee academique</label>
                <select name="id_annee_academique" class="form-control">
                    <option value="">-- Toutes --</option>
                    @foreach($annees as $annee)
                        <option value="{{ $annee->id }}">{{ $annee->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label>Entite</label>
                <select name="id_entite" class="form-control">
                    <option value="">-- Toutes --</option>
                    @foreach($entites as $entite)
                        <option value="{{ $entite->id }}">{{ $entite->nom_entite }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label>Statut</label>
                <select name="statut" class="form-control">
                    <option value="actif">Actif</option>
                    <option value="inactif">Inactif</option>
                </select>
            </div>

            <div class="col-md-6 mt-2">
                <label>Jours</label><br>
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
                        <form method="POST" action="{{ route('emplois_permanents.update', $emploi) }}">
                            @csrf @method('PUT')
                            <td>
                                <select name="id_personnel" class="form-control">
                                    @foreach($personnels as $personnel)
                                        <option value="{{ $personnel->id }}" {{ $emploi->id_personnel == $personnel->id ? 'selected' : '' }}>{{ $personnel->nom }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select name="jour_semaine" class="form-control">
                                    @foreach($jours as $value => $label)
                                        <option value="{{ $value }}" {{ $emploi->jour_semaine == $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select name="id_plage_horaire" class="form-control">
                                    @foreach($plages as $plage)
                                        <option value="{{ $plage->id }}" {{ $emploi->id_plage_horaire == $plage->id ? 'selected' : '' }}>{{ $plage->libelle }} ({{ substr($plage->heure_debut, 0, 5) }} - {{ substr($plage->heure_fin, 0, 5) }})</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select name="id_annee_academique" class="form-control mb-1">
                                    <option value="">-- Toutes --</option>
                                    @foreach($annees as $annee)
                                        <option value="{{ $annee->id }}" {{ $emploi->id_annee_academique == $annee->id ? 'selected' : '' }}>{{ $annee->nom }}</option>
                                    @endforeach
                                </select>
                                <select name="id_entite" class="form-control">
                                    <option value="">-- Toutes --</option>
                                    @foreach($entites as $entite)
                                        <option value="{{ $entite->id }}" {{ $emploi->id_entite == $entite->id ? 'selected' : '' }}>{{ $entite->nom_entite }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="date" name="date_debut" value="{{ optional($emploi->date_debut)->format('Y-m-d') }}" class="form-control mb-1" required>
                                <input type="date" name="date_fin" value="{{ optional($emploi->date_fin)->format('Y-m-d') }}" class="form-control">
                            </td>
                            <td>
                                <select name="statut" class="form-control mb-1">
                                    <option value="actif" {{ $emploi->statut === 'actif' ? 'selected' : '' }}>Actif</option>
                                    <option value="inactif" {{ $emploi->statut === 'inactif' ? 'selected' : '' }}>Inactif</option>
                                </select>
                                <input type="text" name="observations" value="{{ $emploi->observations }}" class="form-control" placeholder="Observations">
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary">Modifier</button>
                        </form>
                                <form method="POST" action="{{ route('emplois_permanents.destroy', $emploi) }}" style="display:inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cet emploi permanent ?')">Supprimer</button>
                                </form>
                            </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">Aucun emploi permanent affecte.</td></tr>
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
