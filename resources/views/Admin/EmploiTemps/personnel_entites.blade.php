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

    <form method="POST" action="{{ route('personnel_entites.store') }}" class="card p-3 mb-4">
        @csrf
        <div class="row">
            <div class="col-md-3">
                <label>Personnel</label>
                <select name="id_personnel" class="form-control" required>
                    <option value="">-- Choisir --</option>
                    @foreach($personnels as $personnel)
                        <option value="{{ $personnel->id }}">{{ $personnel->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>Entite</label>
                <select name="id_entite" class="form-control" required>
                    <option value="">-- Choisir --</option>
                    @foreach($entites as $entite)
                        <option value="{{ $entite->id }}">{{ $entite->nom_entite }}</option>
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
            <div class="col-md-2"><label>Date debut</label><input type="date" name="date_debut" class="form-control"></div>
            <div class="col-md-2"><label>Date fin</label><input type="date" name="date_fin" class="form-control"></div>
            <div class="col-md-2 mt-2">
                <label>Statut</label>
                <select name="statut" class="form-control"><option value="actif">Actif</option><option value="inactif">Inactif</option></select>
            </div>
            <div class="col-md-2 mt-2 d-flex align-items-end"><button class="btn btn-success w-100">Affecter</button></div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark"><tr><th>Personnel</th><th>Entite</th><th>Annee</th><th>Periode</th><th>Statut</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($affectations as $affectation)
                    <tr>
                        <form method="POST" action="{{ route('personnel_entites.update', $affectation) }}">
                            @csrf @method('PUT')
                            <td><select name="id_personnel" class="form-control">@foreach($personnels as $personnel)<option value="{{ $personnel->id }}" {{ $affectation->id_personnel == $personnel->id ? 'selected' : '' }}>{{ $personnel->nom }}</option>@endforeach</select></td>
                            <td><select name="id_entite" class="form-control">@foreach($entites as $entite)<option value="{{ $entite->id }}" {{ $affectation->id_entite == $entite->id ? 'selected' : '' }}>{{ $entite->nom_entite }}</option>@endforeach</select></td>
                            <td><select name="id_annee_academique" class="form-control"><option value="">-- Toutes --</option>@foreach($annees as $annee)<option value="{{ $annee->id }}" {{ $affectation->id_annee_academique == $annee->id ? 'selected' : '' }}>{{ $annee->nom }}</option>@endforeach</select></td>
                            <td>
                                <input type="date" name="date_debut" value="{{ optional($affectation->date_debut)->format('Y-m-d') }}" class="form-control mb-1">
                                <input type="date" name="date_fin" value="{{ optional($affectation->date_fin)->format('Y-m-d') }}" class="form-control">
                            </td>
                            <td><select name="statut" class="form-control"><option value="actif" {{ $affectation->statut === 'actif' ? 'selected' : '' }}>Actif</option><option value="inactif" {{ $affectation->statut === 'inactif' ? 'selected' : '' }}>Inactif</option></select></td>
                            <td><button class="btn btn-sm btn-primary">Modifier</button>
                        </form>
                                <form method="POST" action="{{ route('personnel_entites.destroy', $affectation) }}" style="display:inline">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette affectation ?')">Supprimer</button></form>
                            </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">Aucune affectation.</td></tr>
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
