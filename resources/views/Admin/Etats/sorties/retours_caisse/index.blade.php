{{--@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h3>{{ $currentUserOnly ? 'Mes retours en caisse' : 'Liste des retours en caisse' }}</h3>
                <p class="text-muted">Suivi des montants retournes en caisse centrale.</p>
            </div>
            <a href="{{ route('retour_caisses.create') }}" class="btn btn-success">Nouveau retour</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
                <label>Date debut</label>
                <input type="date" name="date_debut" value="{{ request('date_debut') }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label>Date fin</label>
                <input type="date" name="date_fin" value="{{ request('date_fin') }}" class="form-control">
            </div>
            @unless($currentUserOnly)
                <div class="col-md-3">
                    <label>Utilisateur</label>
                    <select name="id_user" class="form-control">
                        <option value="">Tous</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('id_user') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endunless
            <div class="col-md-3">
                <label>Bon</label>
                <select name="id_bon_commande" class="form-control">
                    <option value="">Tous</option>
                    @foreach($bons as $bon)
                        <option value="{{ $bon->id }}" {{ request('id_bon_commande') == $bon->id ? 'selected' : '' }}>{{ $bon->nom_bon_commande }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>Budget</label>
                <select name="id_budget" class="form-control">
                    <option value="">Tous</option>
                    @foreach($budgets as $budget)
                        <option value="{{ $budget->id }}" {{ request('id_budget') == $budget->id ? 'selected' : '' }}>{{ $budget->libelle_ligne_budget }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>Annee academique</label>
                <select name="id_annee_academique" class="form-control">
                    <option value="">Toutes</option>
                    @foreach($annees as $annee)
                        <option value="{{ $annee->id }}" {{ request('id_annee_academique') == $annee->id ? 'selected' : '' }}>{{ $annee->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-12 text-center mt-3">
                <button class="btn btn-primary">Filtrer</button>
                <a href="{{ $currentUserOnly ? route('retour_caisses.mine') : route('retour_caisses.index') }}" class="btn btn-secondary">Reset</a>
                <a href="{{ $currentUserOnly ? route('retour_caisses.mine.pdf', request()->query()) : route('retour_caisses.pdf', request()->query()) }}" class="btn btn-danger">PDF</a>
                <a href="{{ route('retour_caisses.mine') }}" class="btn btn-dark">Mes retours</a>
            </div>
        </form>

        <div class="alert alert-info">
            <strong>Total retourne :</strong> {{ number_format($total, 0, ',', ' ') }} FCFA
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>Numero</th>
                    <th>Bon</th>
                    <th>Motif décaissement</th>
                    <th>Caisse</th>
                    <th>Budget</th>
                    <th>Ligne</th>
                    <th>Element</th>
                    <th>Donnee</th>
                    <th>Annee</th>
                    <th>Utilisateur</th>
                    <th>Motif</th>
                    <th class="text-end">Montant</th>
                </tr>
                </thead>
                <tbody>
                @forelse($retours as $retour)
                    <tr>
                        <td>{{ $retour->date_retour }}</td>
                        <td>{{ $retour->numero_retour }}</td>
                        <td>{{ $retour->bon->nom_bon_commande ?? '-' }}</td>
                        <td>{{ $retour->decaissement->motif ?? '-' }}</td>
   
                        <td>{{ $retour->caisse->nom_caisse ?? '-' }}</td>
                        <td>{{ $retour->budget->libelle_ligne_budget ?? '-' }}</td>
                        <td>{{ $retour->ligne_budgetaire_sortie->libelle_ligne_budgetaire_sortie ?? '-' }}</td>
                        <td>{{ $retour->element_ligne_budgetaire_sortie->libelle_elements_ligne_budgetaire_sortie ?? '-' }}</td>
                        <td>{{ $retour->donnee_ligne_budgetaire_sortie->donnee_ligne_budgetaire_sortie ?? '-' }}</td>
                        <td>{{ $retour->annee_academique->nom ?? '-' }}</td>
                        <td>{{ $retour->user->name ?? '-' }}</td>
                        <td>{{ $retour->motif ?? '-' }}</td>
                        <td class="text-end">{{ number_format($retour->montant, 0, ',', ' ') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center">Aucun retour trouve.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection--}}
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h3>{{ $currentUserOnly ? 'Mes retours en caisse' : 'Liste des retours en caisse' }}</h3>
            <p class="text-muted">Suivi des montants retournés en caisse centrale.</p>
        </div>

        <a href="{{ route('retour_caisses.create') }}" class="btn btn-success">
            Nouveau retour
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-3">
            <label>Date début</label>
            <input type="date" name="date_debut" value="{{ request('date_debut') }}" class="form-control">
        </div>

        <div class="col-md-3">
            <label>Date fin</label>
            <input type="date" name="date_fin" value="{{ request('date_fin') }}" class="form-control">
        </div>

        @unless($currentUserOnly)
            <div class="col-md-3">
                <label>Utilisateur</label>
                <select name="id_user" class="form-control">
                    <option value="">Tous</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('id_user') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endunless

        <div class="col-md-3">
            <label>Bon</label>
            <select name="id_bon_commande" class="form-control">
                <option value="">Tous</option>
                @foreach($bons as $bon)
                    <option value="{{ $bon->id }}" {{ request('id_bon_commande') == $bon->id ? 'selected' : '' }}>
                        {{ $bon->nom_bon_commande }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label>Décaissement</label>
            <select name="id_decaissement" class="form-control">
                <option value="">Tous</option>
                @foreach($decaissements as $decaissement)
                    <option value="{{ $decaissement->id }}" {{ request('id_decaissement') == $decaissement->id ? 'selected' : '' }}>
                        {{ $decaissement->motif ?? 'Décaissement' }}
                        - {{ number_format($decaissement->montant, 0, ',', ' ') }} FCFA
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label>Budget</label>
            <select name="id_budget" class="form-control">
                <option value="">Tous</option>
                @foreach($budgets as $budget)
                    <option value="{{ $budget->id }}" {{ request('id_budget') == $budget->id ? 'selected' : '' }}>
                        {{ $budget->libelle_ligne_budget }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label>Année académique</label>
            <select name="id_annee_academique" class="form-control">
                <option value="">Toutes</option>
                @foreach($annees as $annee)
                    <option value="{{ $annee->id }}" {{ request('id_annee_academique') == $annee->id ? 'selected' : '' }}>
                        {{ $annee->nom }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-12 text-center mt-3">
            <button class="btn btn-primary">
                Filtrer
            </button>

            <a href="{{ $currentUserOnly ? route('retour_caisses.mine') : route('retour_caisses.index') }}" class="btn btn-secondary">
                Reset
            </a>

            <a href="{{ $currentUserOnly ? route('retour_caisses.mine.pdf', request()->query()) : route('retour_caisses.pdf', request()->query()) }}" class="btn btn-danger">
                PDF
            </a>

            @unless($currentUserOnly)
                <a href="{{ route('retour_caisses.mine') }}" class="btn btn-dark">
                    Mes retours
                </a>
            @endunless

            @if($currentUserOnly)
                <a href="{{ route('retour_caisses.index') }}" class="btn btn-dark">
                    Tous les retours
                </a>
            @endif
        </div>
    </form>

    <div class="alert alert-info">
        <strong>Total retourné :</strong> {{ number_format($total, 0, ',', ' ') }} FCFA
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>Numéro retour</th>
                    <th>Bon</th>
                      <th>Motif décaissement</th>
                    <th>Caisse</th>
                    <th>Budget</th>
                    <th>Ligne</th>
                    <th>Élément</th>
                    <th>Donnée</th>
                    <th>Année</th>
                    <th>Utilisateur</th>
                    <th>Motif retour</th>
                    <th class="text-end">Montant retourné</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($retours as $retour)
                    <tr>
                        <td>{{ $retour->date_retour }}</td>

                        <td>{{ $retour->numero_retour }}</td>

                        <td>{{ $retour->bon->nom_bon_commande ?? '-' }}</td>
<!-- 
                        <td>
                            @if($retour->decaissement)
                                {{ $retour->decaissement->motif ?? 'Décaissement' }}
                                <br>
                                <small class="text-muted">
                                    {{ number_format($retour->decaissement->montant, 0, ',', ' ') }} FCFA
                                </small>
                            @else
                                -
                            @endif 
                        </td>-->
                            <td>{{ $retour->decaissement->motif ?? '-' }}</td>

                        <td>{{ $retour->caisse->nom_caisse ?? '-' }}</td>

                        <td>{{ $retour->budget->libelle_ligne_budget ?? '-' }}</td>

                        <td>{{ $retour->ligne_budgetaire_sortie->libelle_ligne_budgetaire_sortie ?? '-' }}</td>

                        <td>{{ $retour->element_ligne_budgetaire_sortie->libelle_elements_ligne_budgetaire_sortie ?? '-' }}</td>

                        <td>{{ $retour->donnee_ligne_budgetaire_sortie->donnee_ligne_budgetaire_sortie ?? '-' }}</td>

                        <td>{{ $retour->annee_academique->nom ?? '-' }}</td>

                        <td>{{ $retour->user->name ?? '-' }}</td>

                        <td>{{ $retour->motif ?? '-' }}</td>

                        <td class="text-end">
                            {{ number_format($retour->montant, 0, ',', ' ') }} FCFA
                        </td>
                        <td>
    <form action="{{ route('retour_caisses.destroy', $retour->id) }}" method="POST" onsubmit="return confirm('Supprimer ce retour ?');">
        @csrf
        @method('DELETE')

        <button class="btn btn-danger btn-sm">
            Supprimer
        </button>
    </form>
</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13" class="text-center">
                            Aucun retour trouvé.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('breadcrumb')
<ol class="breadcrumb" style="background-color: transparent; padding: 4px 10px">
    <li class="breadcrumb-item">
        <a href="{{ route('home') }}"><strong>Accueil</strong></a>
    </li>

    <li class="breadcrumb-item">
        <a href="{{ route('etat_sorties.index') }}"><strong>Etats sorties</strong></a>
    </li>

    <li class="breadcrumb-item active">
        <strong>Retours en caisse</strong>
    </li>
</ol>
@endsection

{{--@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent; padding: 4px 10px">
        <li class="breadcrumb-item"><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li class="breadcrumb-item"><a href="{{ route('etat_sorties.index') }}"><strong>Etats sorties</strong></a></li>
        <li class="breadcrumb-item active"><strong>Retours en caisse</strong></li>
    </ol>
@endsection--}}
