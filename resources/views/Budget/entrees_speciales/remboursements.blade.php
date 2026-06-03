@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between mb-3">
            <h3>Remboursements des dettes</h3>
            <a href="{{ route('entrees_speciales.index') }}" class="btn btn-default">Retour</a>
        </div>

        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-2">
                <label>Date echeance debut</label>
                <input type="date" name="date_debut" value="{{ request('date_debut') }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label>Date echeance fin</label>
                <input type="date" name="date_fin" value="{{ request('date_fin') }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label>Statut</label>
                <select name="statut" class="form-control">
                    <option value="">Tous</option>
                    <option value="en_attente" {{ request('statut') == 'en_attente' ? 'selected' : '' }}>En attente</option>
                    <option value="payee" {{ request('statut') == 'payee' ? 'selected' : '' }}>Payee</option>
                </select>
            </div>
            <div class="col-md-2">
                <label>Annee utilisation</label>
                <select name="id_annee_academique_utilisation" class="form-control">
                    <option value="">Toutes</option>
                    @foreach($annees as $annee)
                        <option value="{{ $annee->id }}" {{ request('id_annee_academique_utilisation') == $annee->id ? 'selected' : '' }}>{{ $annee->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label>Annee remboursement prevue</label>
                <select name="id_annee_academique_remboursement" class="form-control">
                    <option value="">Toutes</option>
                    @foreach($annees as $annee)
                        <option value="{{ $annee->id }}" {{ request('id_annee_academique_remboursement') == $annee->id ? 'selected' : '' }}>{{ $annee->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label>Annee paiement</label>
                <select name="id_annee_academique_paiement" class="form-control">
                    <option value="">Toutes</option>
                    @foreach($annees as $annee)
                        <option value="{{ $annee->id }}" {{ request('id_annee_academique_paiement') == $annee->id ? 'selected' : '' }}>{{ $annee->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mt-3">
                <label>Caisse de remboursement</label>
                <select name="id_caisse_paiement" class="form-control">
                    <option value="">Toutes</option>
                    @foreach($caisses as $caisse)
                        <option value="{{ $caisse->id }}" {{ request('id_caisse_paiement') == $caisse->id ? 'selected' : '' }}>{{ $caisse->nom_caisse }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mt-3">
                <label>Budget</label>
                <select name="id_budget" class="form-control">
                    <option value="">Tous</option>
                    @foreach($budgets as $budget)
                        <option value="{{ $budget->id }}" {{ request('id_budget') == $budget->id ? 'selected' : '' }}>{{ $budget->libelle_ligne_budget }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mt-3">
                <label>Creancier</label>
                <input type="text" name="creancier" value="{{ request('creancier') }}" class="form-control">
            </div>
            <div class="col-md-12 text-center mt-3">
                <button class="btn btn-primary">Filtrer</button>
                <a href="{{ route('entrees_speciales.remboursements') }}" class="btn btn-default">Reinitialiser</a>
            </div>
        </form>

        <div class="alert alert-info">
            Total a rembourser : <strong>{{ number_format($echeances->sum('montant'), 0, ',', ' ') }} FCFA</strong>
            |
            Total rembourse : <strong>{{ number_format($echeances->sum('montant_paye'), 0, ',', ' ') }} FCFA</strong>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>Date prevue</th>
                    <th>Dette</th>
                    <th>Creancier</th>
                    <th>Budget</th>
                    <th>Compte entree</th>
                    <th>Annee utilisation</th>
                    <th>Annee remboursement</th>
                    <th>Echeance</th>
                    <th>Montant prevu</th>
                    <th>Statut</th>
                    <th>Caisse paiement</th>
                    <th>Paiement</th>
                </tr>
                </thead>
                <tbody>
                @forelse($echeances as $echeance)
                    @php $dette = $echeance->entree_speciale; @endphp
                    <tr>
                        <td>{{ optional($echeance->date_echeance)->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('entrees_speciales.show', $dette->id) }}">
                                {{ $dette->libelle }}
                            </a>
                            <br>
                            <small>Statut dette : {{ $dette->statut }}</small>
                        </td>
                        <td>{{ $dette->nom_tiers }}</td>
                        <td>{{ optional($dette->budget)->libelle_ligne_budget }}</td>
                        <td>
                            @if($dette->id_banque)
                                Banque - {{ optional($dette->banque)->nom_banque }}
                            @else
                                Caisse - {{ optional($dette->caisse)->nom_caisse }}
                            @endif
                        </td>
                        <td>{{ optional($dette->annee_utilisation)->nom }}</td>
                        <td>{{ optional($dette->annee_remboursement)->nom }}</td>
                        <td>{{ $echeance->nom_echeance }}</td>
                        <td>{{ number_format($echeance->montant, 0, ',', ' ') }}</td>
                        <td>{{ $echeance->statut }}</td>
                        <td>{{ optional($echeance->caisse_paiement)->nom_caisse ?: '-' }}</td>
                        <td>
                            @if($echeance->statut === 'payee')
                                <div>
                                    Payee le {{ optional($echeance->date_paiement)->format('d/m/Y') }}
                                    <br>
                                    Montant : {{ number_format($echeance->montant_paye, 0, ',', ' ') }}
                                    <br>
                                    Annee : {{ optional($echeance->annee_paiement)->nom }}
                                    <br>
                                    Caisse : {{ optional($echeance->caisse_paiement)->nom_caisse }}
                                </div>
                                <form action="{{ route('entrees_speciales.echeances.annuler_paiement', $echeance->id) }}" method="POST" class="mt-2">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-warning btn-sm" onclick="return confirm('Annuler ce paiement ?')">Annuler</button>
                                </form>
                            @else
                                <form action="{{ route('entrees_speciales.echeances.payer', $echeance->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <div class="mb-1">
                                        <input type="date" name="date_paiement" value="{{ now()->format('Y-m-d') }}" class="form-control" required>
                                    </div>
                                    <div class="mb-1">
                                        <input type="number" name="montant_paye" value="{{ $echeance->montant }}" class="form-control" min="0" step="0.01" required>
                                    </div>
                                    <div class="mb-1">
                                        <select name="id_caisse_paiement" class="form-control" required>
                                            <option value="">Caisse de sortie</option>
                                            @foreach($caisses as $caisse)
                                                <option value="{{ $caisse->id }}">{{ $caisse->nom_caisse }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-1">
                                        <select name="id_annee_academique_paiement" class="form-control" required>
                                            <option value="">Annee paiement</option>
                                            @foreach($annees as $annee)
                                                <option value="{{ $annee->id }}" {{ ($dette->id_annee_academique_remboursement ?: $dette->id_annee_academique) == $annee->id ? 'selected' : '' }}>{{ $annee->nom }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-1">
                                        <input type="text" name="observations" class="form-control" placeholder="Observation">
                                    </div>
                                    <button class="btn btn-success btn-sm">Enregistrer remboursement</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center">Aucune echeance trouvee.</td>
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
        <li><a href="{{ route('budget') }}"><strong>Budget</strong></a></li>
        <li><a href="{{ route('entrees_speciales.index') }}"><strong>Entrees speciales</strong></a></li>
        <li class="active"><strong>Remboursements</strong></li>
    </ol>
@endsection
