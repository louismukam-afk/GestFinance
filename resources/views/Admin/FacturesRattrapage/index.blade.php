@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-primary">{{ $title }}</h3>
        <div>
            <a href="{{ route('matieres.index') }}" class="btn btn-secondary">Matieres</a>
            <a href="{{ route('etudiant_management') }}" class="btn btn-success">Creer depuis une facture etudiant</a>
        </div>
    </div>

    <form method="GET" class="card card-body mb-3">
        <div class="row">
            <div class="col-md-3">
                <label>Etudiant</label>
                <select name="id_etudiant" class="form-control">
                    <option value="">Tous</option>
                    @foreach($etudiants as $etudiant)
                        <option value="{{ $etudiant->id }}" {{ ($filters['id_etudiant'] ?? '') == $etudiant->id ? 'selected' : '' }}>
                            {{ $etudiant->nom }} {{ $etudiant->matricule ? ' - '.$etudiant->matricule : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label>Annee</label>
                <select name="id_annee_academique" class="form-control">
                    <option value="">Toutes</option>
                    @foreach($annees as $annee)
                        <option value="{{ $annee->id }}" {{ ($filters['id_annee_academique'] ?? '') == $annee->id ? 'selected' : '' }}>{{ $annee->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label>Budget</label>
                <select name="id_budget" class="form-control">
                    <option value="">Tous</option>
                    @foreach($budgets as $budget)
                        <option value="{{ $budget->id }}" {{ ($filters['id_budget'] ?? '') == $budget->id ? 'selected' : '' }}>{{ $budget->libelle_ligne_budget }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label>Date debut</label>
                <input type="date" name="date_debut" class="form-control" value="{{ $filters['date_debut'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <label>Date fin</label>
                <input type="date" name="date_fin" class="form-control" value="{{ $filters['date_fin'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <label>Statut paiement</label>
                <select name="statut_paiement" class="form-control">
                    <option value="">Tous</option>
                    <option value="paye" {{ ($filters['statut_paiement'] ?? '') === 'paye' ? 'selected' : '' }}>Payes</option>
                    <option value="partiel" {{ ($filters['statut_paiement'] ?? '') === 'partiel' ? 'selected' : '' }}>Partiels</option>
                    <option value="non_paye" {{ ($filters['statut_paiement'] ?? '') === 'non_paye' ? 'selected' : '' }}>Non payes</option>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button class="btn btn-primary w-100">Filtrer</button>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Etudiant</th>
                    <th>N facture</th>
                    <th>Date</th>
                    <th>Annee</th>
                    <th>Budget</th>
                    <th>Montant</th>
                    <th>Encaisse</th>
                    <th>Reste</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($factures as $i => $facture)
                    @php
                        $encaisse = $facture->reglement_etudiants->sum('montant_reglement');
                        $reste = max($facture->montant_total_facture - $encaisse, 0);
                        $statut = $encaisse <= 0 ? 'Non paye' : ($reste > 0 ? 'Partiel' : 'Paye');
                    @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $facture->etudiants->nom ?? '-' }}</td>
                        <td>{{ $facture->numero_facture }}</td>
                        <td>{{ \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') }}</td>
                        <td>{{ $facture->Annee_academique->nom ?? '-' }}</td>
                        <td>{{ $facture->budget->libelle_ligne_budget ?? '-' }}</td>
                        <td>{{ number_format($facture->montant_total_facture, 0, ',', ' ') }}</td>
                        <td>{{ number_format($encaisse, 0, ',', ' ') }}</td>
                        <td>{{ number_format($reste, 0, ',', ' ') }}</td>
                        <td>{{ $statut }}</td>
                        <td>
                            <a href="{{ route('factures_rattrapage.show', $facture->id) }}" class="btn btn-xs btn-info">Detail</a>
                            <a href="{{ route('reglement_by_facture', $facture->id) }}" class="btn btn-xs btn-success">Regler</a>
                            <form method="POST" action="{{ route('factures_rattrapage.destroy', $facture->id) }}" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-xs btn-danger" onclick="return confirm('Supprimer cette facture ?')">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="text-center text-muted">Aucune facture de rattrapage trouvee.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('breadcrumb')
<ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
    <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
    <li class="active"><strong>{{ $title }}</strong></li>
</ol>
@endsection
