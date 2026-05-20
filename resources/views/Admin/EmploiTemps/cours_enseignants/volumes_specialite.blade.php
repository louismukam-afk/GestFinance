@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-primary">{{ $title }}</h3>
        <div>
            <a href="{{ route('cours_enseignants.volumes_specialite_pdf', request()->query()) }}" class="btn btn-danger">PDF</a>
            <button type="button" onclick="window.print()" class="btn btn-dark">Imprimer</button>
            <a href="{{ route('emploi_temps.index') }}" class="btn btn-secondary">Retour</a>
        </div>
    </div>

    <form method="GET" class="card p-3 mb-4">
        <div class="row">
            <div class="col-md-2">
                <label>Cycle</label>
                <select name="id_cycle" class="form-control">
                    <option value="">Tous</option>
                    @foreach($cycles as $cycle)
                        <option value="{{ $cycle->id }}" {{ request('id_cycle') == $cycle->id ? 'selected' : '' }}>{{ $cycle->nom_cycle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label>Filiere</label>
                <select name="id_filiere" class="form-control">
                    <option value="">Toutes</option>
                    @foreach($filieres as $filiere)
                        <option value="{{ $filiere->id }}" {{ request('id_filiere') == $filiere->id ? 'selected' : '' }}>{{ $filiere->nom_filiere }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label>Niveau</label>
                <select name="id_niveau" class="form-control">
                    <option value="">Tous</option>
                    @foreach($niveaux as $niveau)
                        <option value="{{ $niveau->id }}" {{ request('id_niveau') == $niveau->id ? 'selected' : '' }}>{{ $niveau->nom_niveau }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label>Specialite</label>
                <select name="id_specialite" class="form-control">
                    <option value="">Toutes</option>
                    @foreach($specialites as $specialite)
                        <option value="{{ $specialite->id }}" {{ request('id_specialite') == $specialite->id ? 'selected' : '' }}>{{ $specialite->nom_specialite }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label>Semestre</label>
                <select name="semestre" class="form-control">
                    <option value="">Tous</option>
                    @foreach($semestres as $value => $label)
                        <option value="{{ $value }}" {{ request('semestre') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
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
            <div class="col-md-2">
                <label>Entite</label>
                <select name="id_entite" class="form-control">
                    <option value="">Toutes</option>
                    @foreach($entites as $entite)
                        <option value="{{ $entite->id }}" {{ request('id_entite') == $entite->id ? 'selected' : '' }}>{{ $entite->nom_entite }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mt-2">
                <label>Date debut</label>
                <input type="date" name="date_debut" value="{{ request('date_debut') }}" class="form-control">
            </div>
            <div class="col-md-2 mt-2">
                <label>Date fin</label>
                <input type="date" name="date_fin" value="{{ request('date_fin') }}" class="form-control">
            </div>
            <div class="col-md-2 mt-2 d-flex align-items-end"><button class="btn btn-primary w-100">Filtrer</button></div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Specialite</th>
                    <th>Cycle / Filiere / Niveau</th>
                    <th>Annee / Entite</th>
                    <th>Matiere</th>
                    <th>Code</th>
                    <th>Semestre</th>
                    <th>Type</th>
                    <th>Prevu</th>
                    <th>Heures realisees</th>
                    <th>Reste</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    @php $reste = max($row['prevu'] - $row['realise'], 0); @endphp
                    <tr>
                        <td>{{ $row['specialite'] }}</td>
                        <td>{{ $row['cycle'] }}<br><small>{{ $row['filiere'] }} / {{ $row['niveau'] }}</small></td>
                        <td>{{ $row['annee'] }}<br><small>{{ $row['entite'] }}</small></td>
                        <td>{{ $row['matiere'] }}</td>
                        <td>{{ $row['code'] ?? '-' }}</td>
                        <td>{{ $row['semestre'] ?? '-' }}</td>
                        <td>{{ ucfirst($row['type'] ?? '-') }}</td>
                        <td>{{ number_format($row['prevu'], 1, ',', ' ') }} h</td>
                        <td>{{ number_format($row['realise'], 1, ',', ' ') }} h</td>
                        <td>{{ number_format($reste, 1, ',', ' ') }} h</td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center text-muted">Aucune matiere trouvee.</td></tr>
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
