@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-primary">{{ $title }}</h3>
        <a href="{{ route('emploi_temps.index') }}" class="btn btn-secondary">Retour</a>
    </div>

    <form method="POST" action="{{ route('plages_horaires.store') }}" class="card p-3 mb-4">
        @csrf
        <div class="row">
            <div class="col-md-3"><label>Libelle</label><input name="libelle" class="form-control" required></div>
            <div class="col-md-2"><label>Debut</label><input type="time" name="heure_debut" class="form-control" required></div>
            <div class="col-md-2"><label>Fin</label><input type="time" name="heure_fin" class="form-control" required></div>
            <div class="col-md-2"><label>Type</label><select name="type_plage" class="form-control"><option value="cours">Cours</option><option value="pause">Pause</option></select></div>
            <div class="col-md-2"><label>Personnel</label><select name="type_personnel" class="form-control"><option value="tous">Tous</option><option value="permanent">Permanent</option><option value="vacataire">Vacataire</option></select></div>
            <div class="col-md-2"><label>Periode</label><select name="periode_journee" class="form-control"><option value="jour">Jour</option><option value="soir">Soir</option></select></div>
            <div class="col-md-2"><label>Format</label><select name="format_plage" class="form-control"><option value="mixte">Mixte</option><option value="bloc_4h">Bloc 4h</option><option value="bloc_8h">Bloc 8h</option><option value="bloc_6h">Bloc 6h</option><option value="bloc_5h">Bloc 5h</option><option value="pause">Pause</option></select></div>
            <div class="col-md-1"><label>Duree</label><input type="number" step="0.01" name="duree_payable" class="form-control"></div>
            <div class="col-md-1"><label>Ordre</label><input type="number" name="ordre" class="form-control" value="0"></div>
            <div class="col-md-2 d-flex align-items-end"><button class="btn btn-success w-100">Ajouter</button></div>
            <input type="hidden" name="statut" value="actif">
        </div>
    </form>

    <table class="table table-bordered table-striped">
        <thead class="table-dark"><tr><th>Libelle</th><th>Debut</th><th>Fin</th><th>Duree</th><th>Type</th><th>Personnel</th><th>Periode</th><th>Format</th><th>Ordre</th><th>Statut</th><th>Actions</th></tr></thead>
        <tbody>
        @foreach($plages as $plage)
            <tr>
                <form method="POST" action="{{ route('plages_horaires.update', $plage) }}">
                    @csrf @method('PUT')
                    <td><input name="libelle" value="{{ $plage->libelle }}" class="form-control"></td>
                    <td><input type="time" name="heure_debut" value="{{ substr($plage->heure_debut, 0, 5) }}" class="form-control"></td>
                    <td><input type="time" name="heure_fin" value="{{ substr($plage->heure_fin, 0, 5) }}" class="form-control"></td>
                    <td><input type="number" step="0.01" name="duree_payable" value="{{ $plage->duree_payable }}" class="form-control"></td>
                    <td><select name="type_plage" class="form-control"><option value="cours" {{ $plage->type_plage === 'cours' ? 'selected' : '' }}>Cours</option><option value="pause" {{ $plage->type_plage === 'pause' ? 'selected' : '' }}>Pause</option></select></td>
                    <td><select name="type_personnel" class="form-control"><option value="tous" {{ ($plage->type_personnel ?? 'tous') === 'tous' ? 'selected' : '' }}>Tous</option><option value="permanent" {{ ($plage->type_personnel ?? '') === 'permanent' ? 'selected' : '' }}>Permanent</option><option value="vacataire" {{ ($plage->type_personnel ?? '') === 'vacataire' ? 'selected' : '' }}>Vacataire</option></select></td>
                    <td><select name="periode_journee" class="form-control"><option value="jour" {{ ($plage->periode_journee ?? 'jour') === 'jour' ? 'selected' : '' }}>Jour</option><option value="soir" {{ ($plage->periode_journee ?? '') === 'soir' ? 'selected' : '' }}>Soir</option></select></td>
                    <td><select name="format_plage" class="form-control"><option value="mixte" {{ ($plage->format_plage ?? 'mixte') === 'mixte' ? 'selected' : '' }}>Mixte</option><option value="bloc_4h" {{ ($plage->format_plage ?? '') === 'bloc_4h' ? 'selected' : '' }}>Bloc 4h</option><option value="bloc_8h" {{ ($plage->format_plage ?? '') === 'bloc_8h' ? 'selected' : '' }}>Bloc 8h</option><option value="bloc_6h" {{ ($plage->format_plage ?? '') === 'bloc_6h' ? 'selected' : '' }}>Bloc 6h</option><option value="bloc_5h" {{ ($plage->format_plage ?? '') === 'bloc_5h' ? 'selected' : '' }}>Bloc 5h</option><option value="pause" {{ ($plage->format_plage ?? '') === 'pause' ? 'selected' : '' }}>Pause</option></select></td>
                    <td><input type="number" name="ordre" value="{{ $plage->ordre }}" class="form-control"></td>
                    <td><select name="statut" class="form-control"><option value="actif" {{ $plage->statut === 'actif' ? 'selected' : '' }}>Actif</option><option value="inactif" {{ $plage->statut === 'inactif' ? 'selected' : '' }}>Inactif</option></select></td>
                    <td><button class="btn btn-sm btn-primary">Modifier</button>
                </form>
                        <form method="POST" action="{{ route('plages_horaires.destroy', $plage) }}" style="display:inline">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette plage ?')">Supprimer</button></form>
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
