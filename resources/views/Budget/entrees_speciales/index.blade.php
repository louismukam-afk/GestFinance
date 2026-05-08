@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between mb-3">
            <h3>Entrees speciales hors factures</h3>
            <div>
                <a href="{{ route('entrees_speciales.rappels') }}" class="btn btn-warning">Rappels echeances</a>
                <a href="{{ route('entrees_speciales.remboursements') }}" class="btn btn-success">Remboursements</a>
                <a href="{{ route('entrees_speciales.create') }}" class="btn btn-primary">Nouvelle entree</a>
            </div>
        </div>

        @if($rappels->count())
            <div class="alert alert-warning">
                {{ $rappels->count() }} echeance(s) de dette arrivent a terme dans les 7 prochains jours.
                <a href="{{ route('entrees_speciales.rappels') }}">Voir les rappels</a>
            </div>
        @endif

        <form method="GET" class="row g-3 mb-3">
            <div class="col-md-2">
                <label>Type</label>
                <select name="type_entree" class="form-control">
                    <option value="">Tous</option>
                    @foreach($types as $key => $label)
                        <option value="{{ $key }}" {{ request('type_entree') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label>Date debut</label>
                <input type="date" name="date_debut" value="{{ request('date_debut') }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label>Date fin</label>
                <input type="date" name="date_fin" value="{{ request('date_fin') }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label>Creancier / tiers</label>
                <input type="text" name="creancier" value="{{ request('creancier') }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label>Annee remboursement</label>
                <select name="id_annee_academique" class="form-control">
                    <option value="">Toutes</option>
                    @foreach($annees as $annee)
                        <option value="{{ $annee->id }}" {{ request('id_annee_academique') == $annee->id ? 'selected' : '' }}>{{ $annee->nom }}</option>
                    @endforeach
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
                <label>Budget</label>
                <select name="id_budget" class="form-control">
                    <option value="">Tous</option>
                    @foreach($budgets as $budget)
                        <option value="{{ $budget->id }}" {{ request('id_budget') == $budget->id ? 'selected' : '' }}>{{ $budget->libelle_ligne_budget }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-12 text-center mt-3">
                <button class="btn btn-success">Rechercher</button>
                <a href="{{ route('entrees_speciales.index') }}" class="btn btn-default">Reinitialiser</a>
            </div>
        </form>

        <div class="alert alert-info">
            Total brut filtre : <strong>{{ number_format($totalBrut, 0, ',', ' ') }} FCFA</strong>
            |
            Remboursements dettes : <strong>{{ number_format($totalRembourse, 0, ',', ' ') }} FCFA</strong>
            |
            Encaisse net : <strong>{{ number_format($total, 0, ',', ' ') }} FCFA</strong>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Code</th>
                    <th>Libelle</th>
                    <th>Tiers</th>
                    <th>Caisse</th>
                    <th>Budget</th>
                    <th>Annee utilisation</th>
                    <th>Annee remboursement</th>
                    <th>Montant brut</th>
                    <th>Rembourse</th>
                    <th>Encaisse net</th>
                    <th>Echeances</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($entrees as $entree)
                    <tr>
                        <td>{{ optional($entree->date_entree)->format('d/m/Y') }}</td>
                        <td>{{ $types[$entree->type_entree] ?? $entree->type_entree }}</td>
                        <td>{{ $entree->code_entree }}</td>
                        <td>{{ $entree->libelle }}</td>
                        <td>{{ $entree->nom_tiers }}</td>
                        <td>{{ optional($entree->caisse)->nom_caisse }}</td>
                        <td>{{ optional($entree->budget)->libelle_ligne_budget }}</td>
                        <td>{{ optional($entree->annee_utilisation)->nom }}</td>
                        <td>{{ optional($entree->annee_remboursement)->nom }}</td>
                        <td>{{ number_format($entree->montant, 0, ',', ' ') }}</td>
                        <td>{{ number_format($entree->montant_rembourse, 0, ',', ' ') }}</td>
                        <td>{{ number_format($entree->montant_net_encaisse, 0, ',', ' ') }}</td>
                        <td>{{ $entree->echeances->where('statut', 'en_attente')->count() }} en attente</td>
                        <td>
                            <a href="{{ route('entrees_speciales.show', $entree->id) }}" class="btn btn-info btn-sm">Voir</a>
                            <a href="{{ route('entrees_speciales.edit', $entree->id) }}" class="btn btn-warning btn-sm">Editer</a>
                            <form action="{{ route('entrees_speciales.destroy', $entree->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cette entree speciale ?')">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="14" class="text-center">Aucune entree speciale trouvee.</td>
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
        <li class="active"><strong>Entrees speciales</strong></li>
    </ol>
@endsection
