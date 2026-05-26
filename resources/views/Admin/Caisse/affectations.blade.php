@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>{{ $title }}</h3>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">Nouvelle affectation</div>
            <div class="card-body">
                <form method="POST" action="{{ route('caisses.affectations.store') }}" class="row g-3">
                    @csrf
                    <div class="col-md-4">
                        <label>Utilisateur</label>
                        <select name="id_user" class="form-control" required>
                            <option value="">-- Choisir --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Caisse</label>
                        <select name="id_caisse" class="form-control" required>
                            <option value="">-- Choisir --</option>
                            @foreach($caisses as $caisse)
                                <option value="{{ $caisse->id }}">
                                    {{ $caisse->nom_caisse }}
                                    @if($caisse->type_caisse == 0)
                                        - Entree
                                    @elseif($caisse->type_caisse == 1)
                                        - Sortie
                                    @else
                                        - Centrale
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Droits</label>
                        <div class="form-control">
                            <label class="me-3">
                                <input type="checkbox" name="peut_encaisser" value="1"> Encaisser
                            </label>
                            <label>
                                <input type="checkbox" name="peut_decaisser" value="1"> Decaisser
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label>Date debut</label>
                        <input type="date" name="date_debut" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label>Date fin</label>
                        <input type="date" name="date_fin" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label>Statut</label>
                        <select name="actif" class="form-control">
                            <option value="1">Actif</option>
                            <option value="0">Inactif</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Observation</label>
                        <input type="text" name="observation" class="form-control">
                    </div>
                    <div class="col-md-12">
                        <button class="btn btn-success">Enregistrer</button>
                        <a href="{{ route('caisse_management') }}" class="btn btn-secondary">Retour aux caisses</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                <tr>
                    <th>Utilisateur</th>
                    <th>Caisse</th>
                    <th>Type</th>
                    <th>Encaissement</th>
                    <th>Decaissement</th>
                    <th>Periode</th>
                    <th>Statut</th>
                    <th>Observation</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @forelse($affectations as $affectation)
                    <tr>
                        <td>{{ $affectation->user->name ?? '-' }}</td>
                        <td>{{ $affectation->caisse->nom_caisse ?? '-' }}</td>
                        <td>
                            @if(optional($affectation->caisse)->type_caisse == 0)
                                Entree
                            @elseif(optional($affectation->caisse)->type_caisse == 1)
                                Sortie
                            @else
                                Centrale
                            @endif
                        </td>
                        <td>{{ $affectation->peut_encaisser ? 'Oui' : 'Non' }}</td>
                        <td>{{ $affectation->peut_decaisser ? 'Oui' : 'Non' }}</td>
                        <td>
                            {{ optional($affectation->date_debut)->format('d/m/Y') ?? 'Debut' }}
                            -
                            {{ optional($affectation->date_fin)->format('d/m/Y') ?? 'Illimitee' }}
                        </td>
                        <td>{{ $affectation->actif ? 'Actif' : 'Inactif' }}</td>
                        <td>{{ $affectation->observation ?: '-' }}</td>
                        <td>
                            <form method="POST" action="{{ route('caisses.affectations.destroy', $affectation->id) }}" onsubmit="return confirm('Supprimer cette affectation ?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">Aucune affectation.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('dashboard') }}"><strong>Administration</strong></a></li>
        <li><a href="{{ route('caisse_management') }}"><strong>Caisses</strong></a></li>
        <li class="active"><strong>Affectations</strong></li>
    </ol>
@endsection
