@extends('layouts.app')

@section('content')
    <div class="container">

        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h3>
                    @if($currentUserOnly)
                        Etat de caisse de {{ $userName ?? auth()->user()->name }} de la periode du {{ $dateDebut ?? 'Debut' }} au {{ $dateFin }}
                    @else
                        Etat de caisse global de la periode du {{ $dateDebut ?? 'Debut' }} au {{ $dateFin }}
                    @endif
                </h3>
                <p class="text-muted">Synthese des entrees, sorties et soldes par caisse sur une periode.</p>
            </div>
            <a href="{{ route('etat_sorties.mon_etat_caisse') }}" class="btn btn-dark">
                Mon etat de caisse
            </a>
        </div>

        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
                <label>Caisse</label>
                <select name="id_caisse" class="form-control">
                    <option value="">Toutes</option>
                    @foreach($caisses as $c)
                        <option value="{{ $c->id }}" {{ request('id_caisse') == $c->id ? 'selected' : '' }}>
                            {{ $c->nom_caisse }}
                        </option>
                    @endforeach
                </select>
            </div>

            @unless($currentUserOnly)
                <div class="col-md-3">
                    <label>Utilisateur</label>
                    <select name="id_user" class="form-control">
                        <option value="">Tous</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ request('id_user') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endunless

            <div class="col-md-3">
                <label>Budget</label>
                <select name="id_budget" class="form-control">
                    <option value="">Tous</option>
                    @foreach($budgets as $b)
                        <option value="{{ $b->id }}" {{ request('id_budget') == $b->id ? 'selected' : '' }}>
                            {{ $b->libelle_ligne_budget }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>Entite</label>
                <select name="id_entite" class="form-control">
                    <option value="">Toutes</option>
                    @foreach($entites as $e)
                        <option value="{{ $e->id }}" {{ request('id_entite') == $e->id ? 'selected' : '' }}>
                            {{ $e->nom_entite }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>Annee academique</label>
                <select name="id_annee_academique" class="form-control">
                    <option value="">Toutes</option>
                    @foreach($annees as $a)
                        <option value="{{ $a->id }}" {{ request('id_annee_academique') == $a->id ? 'selected' : '' }}>
                            {{ $a->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>Date debut</label>
                <input type="date" name="date_debut" value="{{ request('date_debut') }}" class="form-control">
            </div>

            <div class="col-md-3">
                <label>Date fin</label>
                <input type="date" name="date_fin" value="{{ request('date_fin') }}" class="form-control">
            </div>

            <div class="col-md-12 text-center mt-3">
                <button class="btn btn-success">Filtrer</button>
                <a href="{{ $currentUserOnly ? route('etat_sorties.mon_etat_caisse') : route('etat_sorties.etat_caisse') }}"
                   class="btn btn-secondary">
                    Reset
                </a>
                <a href="{{ $currentUserOnly ? route('etat_sorties.mon_etat_caisse.pdf', request()->query()) : route('etat_sorties.etat_caisse.pdf', request()->query()) }}"
                   class="btn btn-danger">
                    PDF
                </a>
                <a href="{{ $currentUserOnly ? route('etat_sorties.mon_etat_caisse.excel', request()->query()) : route('etat_sorties.etat_caisse.excel', request()->query()) }}"
                   class="btn btn-success">
                    Excel
                </a>
            </div>
        </form>

        <div class="alert alert-info">
            <strong>Periode :</strong> {{ $dateDebut ?? 'Debut' }} - {{ $dateFin }}
            |
            <strong>Total entrees :</strong> {{ number_format($totalEntrees, 0, ',', ' ') }} FCFA
            |
            <strong>Total sorties :</strong> {{ number_format($totalSorties, 0, ',', ' ') }} FCFA
            |
            <strong>Solde :</strong> {{ number_format($solde, 0, ',', ' ') }} FCFA
        </div>

        @forelse($operationsGrouped as $caisse => $lignes)
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    {{ $caisse }}
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>Operation</th>
                            <th>Numero</th>
                            <th>Motif</th>
                            <th>Budget</th>
                            <th>Ligne budgetaire</th>
                            <th>Element</th>
                            <th>Donnee</th>
                            <th>Entite</th>
                            <th>Annee</th>
                            <th>Utilisateur</th>
                            <th class="text-end">Entree</th>
                            <th class="text-end">Sortie</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php $tEntree = 0; $tSortie = 0; @endphp
                        @foreach($lignes as $op)
                            @php
                                $tEntree += $op['entree'];
                                $tSortie += $op['sortie'];
                            @endphp
                            <tr>
                                <td>{{ $op['date'] }}</td>
                                <td>{{ $op['operation'] }}</td>
                                <td>{{ $op['numero'] }}</td>
                                <td>{{ $op['motif'] ?: '-' }}</td>
                                <td>{{ $op['budget'] ?: '-' }}</td>
                                <td>{{ $op['ligne'] ?: '-' }}</td>
                                <td>{{ $op['element'] ?: '-' }}</td>
                                <td>{{ $op['donnee'] ?: '-' }}</td>
                                <td>{{ $op['entite'] ?: '-' }}</td>
                                <td>{{ $op['annee'] ?: '-' }}</td>
                                <td>{{ $op['utilisateur'] ?: '-' }}</td>
                                <td class="text-end text-success">{{ number_format($op['entree'], 0, ',', ' ') }}</td>
                                <td class="text-end text-danger">{{ number_format($op['sortie'], 0, ',', ' ') }}</td>
                            </tr>
                        @endforeach
                        <tr class="table-secondary fw-bold">
                            <td colspan="11">TOTAL {{ $caisse }}</td>
                            <td class="text-end">{{ number_format($tEntree, 0, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format($tSortie, 0, ',', ' ') }}</td>
                        </tr>
                        <tr class="table-info fw-bold">
                            <td colspan="11">SOLDE {{ $caisse }}</td>
                            <td colspan="2" class="text-end">{{ number_format($tEntree - $tSortie, 0, ',', ' ') }} FCFA</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="alert alert-warning text-center">
                Aucune operation trouvee pour les criteres selectionnes.
            </div>
        @endforelse
    </div>
@endsection

@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent; padding: 4px 10px">
        <li class="breadcrumb-item">
            <a href="{{ route('home') }}"><strong>Accueil</strong></a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('etat_sorties.index') }}"><strong>Etats budgetaires sorties</strong></a>
        </li>
        <li class="breadcrumb-item active">
            <strong>{{ $currentUserOnly ? 'Mon etat de caisse' : 'Etat des caisses' }}</strong>
        </li>
    </ol>
@endsection
