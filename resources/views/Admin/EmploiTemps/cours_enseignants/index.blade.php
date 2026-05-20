@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-primary">{{ $title }}</h3>
        <div>
            <a href="{{ route('emploi_temps.index') }}" class="btn btn-secondary">Retour</a>
            <a href="{{ route('cours_enseignants.contextes') }}" class="btn btn-success">Programmer un cours</a>
            <a href="{{ route('cours_enseignants.export', request()->query()) }}" class="btn btn-info">Export Excel</a>
        </div>
    </div>

    <form method="GET" class="card p-3 mb-4">
        <div class="row">
            <div class="col-md-3">
                <label>Enseignant</label>
                <select name="id_personnel" class="form-control">
                    <option value="">Tous</option>
                    @foreach($personnels as $personnel)
                        <option value="{{ $personnel->id }}" {{ request('id_personnel') == $personnel->id ? 'selected' : '' }}>{{ $personnel->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><label>Date debut</label><input type="date" name="date_debut" value="{{ request('date_debut') }}" class="form-control"></div>
            <div class="col-md-2"><label>Date fin</label><input type="date" name="date_fin" value="{{ request('date_fin') }}" class="form-control"></div>
            <div class="col-md-2"><label>Statut</label><select name="statut" class="form-control"><option value="">Tous</option><option value="actif" {{ request('statut') === 'actif' ? 'selected' : '' }}>Actif</option><option value="inactif" {{ request('statut') === 'inactif' ? 'selected' : '' }}>Inactif</option></select></div>
            <div class="col-md-3 d-flex align-items-end"><button class="btn btn-primary">Filtrer</button></div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Enseignant</th><th>Matiere</th><th>Classe</th><th>Salle</th><th>Periode</th><th>Semestre</th><th>Volume</th><th>Realise</th><th>Taux</th><th>Seances</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cours as $item)
                    @php
                        $realise = $item->seances->where('statut', 'realise')->sum('duree_heures');
                    @endphp
                    <tr>
                        <td>{{ $item->personnel->nom ?? '-' }}<br><small>{{ $item->personnel->type_personnel ?? '-' }}</small></td>
                        <td>{{ $item->programme->matiere->nom_matiere ?? '-' }}</td>
                        <td>{{ $item->programme->specialite->nom_specialite ?? '-' }} / {{ $item->programme->niveau->nom_niveau ?? '-' }}</td>
                        <td>{{ $item->salle->nom_salle ?? '-' }}</td>
                        <td>{{ optional($item->date_debut)->format('d/m/Y') }} - {{ optional($item->date_fin)->format('d/m/Y') }}<br><span class="badge badge-{{ $item->statut === 'actif' ? 'success' : 'secondary' }}">{{ $item->statut }}</span></td>
                        <td>{{ $item->semestre ? 'Semestre '.$item->semestre : '-' }}</td>
                        <td>{{ number_format($item->volume_horaire_prevu, 1, ',', ' ') }} h</td>
                        <td>{{ number_format($realise, 1, ',', ' ') }} h</td>
                        <td>{{ optional($item->taux_horaire)->libelle ?? 'Defaut' }}</td>
                        <td>
                            @foreach($item->seances as $seance)
                                <div>{{ [1=>'Lun',2=>'Mar',3=>'Mer',4=>'Jeu',5=>'Ven',6=>'Sam',7=>'Dim'][$seance->jour_semaine] ?? '-' }} {{ optional($seance->plage)->heure_debut }}-{{ optional($seance->plage)->heure_fin }}</div>
                            @endforeach
                        </td>
                        <td>
                            <a href="{{ route('cours_enseignants.edit', $item) }}" class="btn btn-xs btn-primary">Modifier</a>
                            <form method="POST" action="{{ route('cours_enseignants.destroy', $item) }}" style="display:inline">@csrf @method('DELETE')<button class="btn btn-xs btn-danger" onclick="return confirm('Supprimer ce cours ?')">Supprimer</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="text-center text-muted">Aucun cours programme.</td></tr>
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
