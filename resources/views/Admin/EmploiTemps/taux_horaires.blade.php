@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-primary">{{ $title }}</h3>
        <a href="{{ route('emploi_temps.index') }}" class="btn btn-secondary">Retour</a>
    </div>

    <form method="POST" action="{{ route('taux_horaires.store') }}" class="card p-3 mb-4">
        @csrf
        <div class="row">
            <div class="col-md-4"><label>Libelle</label><input name="libelle" class="form-control" required></div>
            <div class="col-md-3"><label>Montant horaire</label><input type="number" step="0.01" name="montant" class="form-control" required></div>
            <div class="col-md-2"><label>Statut</label><select name="statut" class="form-control"><option value="actif">Actif</option><option value="inactif">Inactif</option></select></div>
            <div class="col-md-1 d-flex align-items-end"><label><input type="checkbox" name="par_defaut" value="1"> Defaut</label></div>
            <div class="col-md-2 d-flex align-items-end"><button class="btn btn-success w-100">Ajouter</button></div>
        </div>
    </form>

    <table class="table table-bordered table-striped">
        <thead class="table-dark"><tr><th>Libelle</th><th>Montant</th><th>Defaut</th><th>Statut</th><th>Actions</th></tr></thead>
        <tbody>
        @foreach($taux as $item)
            <tr>
                <form method="POST" action="{{ route('taux_horaires.update', $item) }}">
                    @csrf @method('PUT')
                    <td><input name="libelle" value="{{ $item->libelle }}" class="form-control"></td>
                    <td><input type="number" step="0.01" name="montant" value="{{ $item->montant }}" class="form-control"></td>
                    <td><input type="checkbox" name="par_defaut" value="1" {{ $item->par_defaut ? 'checked' : '' }}></td>
                    <td><select name="statut" class="form-control"><option value="actif" {{ $item->statut === 'actif' ? 'selected' : '' }}>Actif</option><option value="inactif" {{ $item->statut === 'inactif' ? 'selected' : '' }}>Inactif</option></select></td>
                    <td><button class="btn btn-sm btn-primary">Modifier</button>
                </form>
                        <form method="POST" action="{{ route('taux_horaires.destroy', $item) }}" style="display:inline">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce taux ?')">Supprimer</button></form>
                    </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection

@section('breadcrumb')
<ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
    <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
    <li><a href="{{ route('emploi_temps.index') }}"><strong>Ressources humaines</strong></a></li>
    <li class="active"><strong>{{ $title }}</strong></li>
</ol>
@endsection
