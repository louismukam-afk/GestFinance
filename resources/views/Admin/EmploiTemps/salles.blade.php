@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-primary">{{ $title }}</h3>
        <a href="{{ route('emploi_temps.index') }}" class="btn btn-secondary">Retour</a>
    </div>

    <form method="POST" action="{{ route('salles.store') }}" class="card p-3 mb-4">
        @csrf
        <div class="row">
            <div class="col-md-4"><label>Nom</label><input name="nom_salle" class="form-control" required></div>
            <div class="col-md-2"><label>Code</label><input name="code_salle" class="form-control"></div>
            <div class="col-md-2"><label>Capacite</label><input type="number" name="capacite" class="form-control" min="0"></div>
            <div class="col-md-2"><label>Statut</label><select name="statut" class="form-control"><option value="actif">Actif</option><option value="inactif">Inactif</option></select></div>
            <div class="col-md-2 d-flex align-items-end"><button class="btn btn-success w-100">Ajouter</button></div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark"><tr><th>Nom</th><th>Code</th><th>Capacite</th><th>Statut</th><th>Actions</th></tr></thead>
            <tbody>
            @foreach($salles as $salle)
                <tr>
                    <form method="POST" action="{{ route('salles.update', $salle) }}">
                        @csrf @method('PUT')
                        <td><input name="nom_salle" value="{{ $salle->nom_salle }}" class="form-control"></td>
                        <td><input name="code_salle" value="{{ $salle->code_salle }}" class="form-control"></td>
                        <td><input type="number" name="capacite" value="{{ $salle->capacite }}" class="form-control"></td>
                        <td><select name="statut" class="form-control"><option value="actif" {{ $salle->statut === 'actif' ? 'selected' : '' }}>Actif</option><option value="inactif" {{ $salle->statut === 'inactif' ? 'selected' : '' }}>Inactif</option></select></td>
                        <td><button class="btn btn-sm btn-primary">Modifier</button>
                    </form>
                            <form method="POST" action="{{ route('salles.destroy', $salle) }}" style="display:inline">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette salle ?')">Supprimer</button></form>
                        </td>
                </tr>
            @endforeach
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
