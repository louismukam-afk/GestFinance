@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-primary">{{ $title }}</h3>
        <a href="{{ route('emploi_temps.index') }}" class="btn btn-secondary">Retour</a>
    </div>

    @forelse($scolaritesGrouped as $groupe => $scolarites)
        <div class="card mb-3">
            <div class="card-header"><strong>{{ $groupe }}</strong></div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Specialite</th>
                            <th>Niveau</th>
                            <th>Annee academique</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($scolarites as $scolarite)
                            <tr>
                                <td>{{ $scolarite->specialites->nom_specialite ?? '-' }}</td>
                                <td>{{ $scolarite->niveaux->nom_niveau ?? '-' }}</td>
                                <td>{{ $scolarite->annee_academique->nom ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('programmes_specialites.configure', [
                                        'specialite' => $scolarite->id_specialite,
                                        'id_cycle' => $scolarite->id_cycle,
                                        'id_filiere' => $scolarite->id_filiere,
                                        'id_niveau' => $scolarite->id_niveau,
                                    ]) }}" class="btn btn-xs btn-primary">
                                        Affecter les matieres
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="alert alert-info">Aucune scolarite configuree pour construire les programmes.</div>
    @endforelse
</div>
@endsection

@section('breadcrumb')
<ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
    <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
    <li><a href="{{ route('emploi_temps.index') }}"><strong>Ressources humaines</strong></a></li>
    <li class="active"><strong>{{ $title }}</strong></li>
</ol>
@endsection
