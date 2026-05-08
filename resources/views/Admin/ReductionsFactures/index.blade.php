@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3>Reductions de factures etudiants</h3>
                <p class="text-muted">Gestion et suivi des reductions accordees sur les factures.</p>
            </div>
            <button type="button" onclick="window.print()" class="btn btn-dark no-print">Imprimer</button>
        </div>

        @if(session('success'))
            <div class="alert alert-success no-print">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger no-print">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('reductions_factures.store') }}" class="card p-3 mb-4 no-print">
            @csrf
            <h5>Nouvelle reduction</h5>
            <div class="row">
                @if($factureSelectionnee)
                    @php
                        $resteReduction = $factureSelectionnee->montant_total_facture - $factureSelectionnee->reductions->sum('montant');
                    @endphp
                    <input type="hidden" name="id_facture_etudiant" value="{{ $factureSelectionnee->id }}">
                    <div class="col-md-12 mb-3">
                        <div class="border rounded p-3 bg-light">
                            <div class="row">
                                <div class="col-md-3"><strong>Facture :</strong> {{ $factureSelectionnee->numero_facture }}</div>
                                <div class="col-md-3"><strong>Etudiant :</strong> {{ optional($factureSelectionnee->etudiants)->nom ?? '-' }}</div>
                                <div class="col-md-3"><strong>Entite :</strong> {{ optional($factureSelectionnee->entite)->nom_entite ?? '-' }}</div>
                                <div class="col-md-3"><strong>Annee :</strong> {{ optional($factureSelectionnee->Annee_academique)->nom ?? '-' }}</div>
                                <div class="col-md-3 mt-2"><strong>Cycle :</strong> {{ optional($factureSelectionnee->cycles)->nom_cycle ?? '-' }}</div>
                                <div class="col-md-3 mt-2"><strong>Filiere :</strong> {{ optional($factureSelectionnee->filieres)->nom_filiere ?? '-' }}</div>
                                <div class="col-md-3 mt-2"><strong>Niveau :</strong> {{ optional($factureSelectionnee->niveaux)->nom_niveau ?? '-' }}</div>
                                <div class="col-md-3 mt-2"><strong>Specialite :</strong> {{ optional($factureSelectionnee->specialites)->nom_specialite ?? '-' }}</div>
                                <div class="col-md-4 mt-2"><strong>Budget :</strong> {{ optional($factureSelectionnee->budget)->libelle_ligne_budget ?? '-' }}</div>
                                <div class="col-md-4 mt-2"><strong>Ligne :</strong> {{ optional($factureSelectionnee->ligne_budgetaire_entree)->libelle_ligne_budgetaire_entree ?? '-' }}</div>
                                <div class="col-md-4 mt-2"><strong>Element :</strong> {{ optional($factureSelectionnee->element_ligne_budgetaire_entree)->libelle_elements_ligne_budgetaire_entree ?? '-' }}</div>
                                <div class="col-md-6 mt-2"><strong>Donnee budgetaire :</strong> {{ optional($factureSelectionnee->donnee_budgetaire_entree)->donnee_budgetaire_entree ?? '-' }}</div>
                                <div class="col-md-6 mt-2"><strong>Donnee ligne :</strong> {{ optional($factureSelectionnee->donnee_ligne_budgetaire_entree)->donnee_ligne_budgetaire_entree ?? '-' }}</div>
                                <div class="col-md-4 mt-2"><strong>Montant facture :</strong> {{ number_format($factureSelectionnee->montant_total_facture, 0, ',', ' ') }} FCFA</div>
                                <div class="col-md-4 mt-2"><strong>Reductions deja accordees :</strong> {{ number_format($factureSelectionnee->reductions->sum('montant'), 0, ',', ' ') }} FCFA</div>
                                <div class="col-md-4 mt-2"><strong>Reste reductible :</strong> {{ number_format($resteReduction, 0, ',', ' ') }} FCFA</div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="col-md-5 mb-2">
                        <label>Facture</label>
                        <select name="id_facture_etudiant" class="form-control" required>
                            <option value="">-- Choisir une facture --</option>
                            @foreach($factures as $facture)
                                @php
                                    $resteReduction = $facture->montant_total_facture - $facture->reductions->sum('montant');
                                @endphp
                                <option value="{{ $facture->id }}" {{ old('id_facture_etudiant', request('id_facture_etudiant')) == $facture->id ? 'selected' : '' }}>
                                    {{ $facture->numero_facture }} - {{ optional($facture->etudiants)->nom }} - {{ optional($facture->entite)->nom_entite }} - reste reduc. {{ number_format($resteReduction, 0, ',', ' ') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-2 mb-2">
                    <label>Date</label>
                    <input type="date" name="date_reduction" value="{{ old('date_reduction', now()->format('Y-m-d')) }}" class="form-control" required>
                </div>
                <div class="col-md-2 mb-2">
                    <label>Montant</label>
                    <input type="number" step="0.01" min="1" name="montant" value="{{ old('montant') }}" class="form-control" required>
                </div>
                <div class="col-md-3 mb-2">
                    <label>Motif</label>
                    <input type="text" name="motif" value="{{ old('motif') }}" class="form-control">
                </div>
                <div class="col-md-10 mb-2">
                    <label>Observations</label>
                    <input type="text" name="observations" value="{{ old('observations') }}" class="form-control">
                </div>
                <div class="col-md-2 mb-2 d-flex align-items-end">
                    <button class="btn btn-success w-100">Enregistrer</button>
                </div>
            </div>
        </form>

        <form method="GET" class="card p-3 mb-4 no-print">
            <h5>Filtres</h5>
            @if($factureSelectionnee)
                <input type="hidden" name="id_facture_etudiant" value="{{ $factureSelectionnee->id }}">
            @endif
            <div class="row">
                <div class="col-md-3 mb-2">
                    <label>Specialite</label>
                    <select name="id_specialite" class="form-control">
                        <option value="">Toutes</option>
                        @foreach($specialites as $specialite)
                            <option value="{{ $specialite->id }}" {{ request('id_specialite') == $specialite->id ? 'selected' : '' }}>{{ $specialite->nom_specialite }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label>Entite</label>
                    <select name="id_entite" class="form-control">
                        <option value="">Toutes</option>
                        @foreach($entites as $entite)
                            <option value="{{ $entite->id }}" {{ request('id_entite') == $entite->id ? 'selected' : '' }}>{{ $entite->nom_entite }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label>Annee academique</label>
                    <select name="id_annee_academique" class="form-control">
                        <option value="">Toutes</option>
                        @foreach($annees as $annee)
                            <option value="{{ $annee->id }}" {{ request('id_annee_academique') == $annee->id ? 'selected' : '' }}>{{ $annee->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label>Budget</label>
                    <select name="id_budget" class="form-control">
                        <option value="">Tous</option>
                        @foreach($budgets as $budget)
                            <option value="{{ $budget->id }}" {{ request('id_budget') == $budget->id ? 'selected' : '' }}>{{ $budget->libelle_ligne_budget }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label>Date debut</label>
                    <input type="date" name="date_debut" value="{{ request('date_debut') }}" class="form-control">
                </div>
                <div class="col-md-3 mb-2">
                    <label>Date fin</label>
                    <input type="date" name="date_fin" value="{{ request('date_fin') }}" class="form-control">
                </div>
                <div class="col-md-6 mb-2 d-flex align-items-end gap-2">
                    <button class="btn btn-primary">Filtrer</button>
                    <a href="{{ $factureSelectionnee ? route('reductions_factures.index', ['id_facture_etudiant' => $factureSelectionnee->id]) : route('reductions_factures.index') }}" class="btn btn-secondary">Reinitialiser</a>
                    <a href="{{ route('reductions_factures.excel', request()->query()) }}" class="btn btn-success">Excel</a>
                    <a href="{{ route('reductions_factures.pdf', request()->query()) }}" class="btn btn-danger">PDF</a>
                </div>
            </div>
        </form>

        <div class="alert alert-info">
            Total reductions : <strong>{{ number_format($totalReductions, 0, ',', ' ') }} FCFA</strong>
        </div>

        <div class="table-responsive">
            @include('Admin.ReductionsFactures._table')
        </div>
    </div>
@endsection

@section('styles')
    <style>
        @media print {
            .no-print, form, .btn, .breadcrumb, nav, header, footer {
                display: none !important;
            }
            .container {
                width: 100% !important;
                max-width: 100% !important;
            }
        }
    </style>
@endsection

@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent; padding: 4px 10px">
        <li class="breadcrumb-item"><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li class="breadcrumb-item"><a href="{{ route('etats.index') }}"><strong>Etats</strong></a></li>
        <li class="breadcrumb-item active"><strong>Reductions factures</strong></li>
    </ol>
@endsection
