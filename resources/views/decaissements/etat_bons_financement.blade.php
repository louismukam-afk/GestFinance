@extends('layouts.app')

@section('content')
<div class="container">
    @include('decaissements.partials.navigation')

    <h3>{{ $title }}</h3>

    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-3">
            <label>Date debut validation</label>
            <input type="date" name="date_debut" value="{{ request('date_debut') }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label>Date fin validation</label>
            <input type="date" name="date_fin" value="{{ request('date_fin') }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label>Entite</label>
            <select name="id_entite" class="form-control">
                <option value="">Toutes</option>
                @foreach($entites as $entite)
                    <option value="{{ $entite->id }}" {{ request('id_entite') == $entite->id ? 'selected' : '' }}>{{ $entite->nom_entite }}</option>
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
        <div class="col-md-12 mt-2">
            <button class="btn btn-primary">Filtrer</button>
            <a href="{{ $type === 'realises' ? route('decaissements.etat_realises') : route('decaissements.etat_non_finances') }}" class="btn btn-secondary">Reset</a>
            <a href="{{ $type === 'realises' ? route('decaissements.etat_realises.pdf', request()->query()) : route('decaissements.etat_non_finances.pdf', request()->query()) }}" class="btn btn-danger">Imprimer PDF</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Date validation</th>
                    <th>Bon</th>
                    <th>Entite</th>
                    <th>Annee</th>
                    <th>Budget</th>
                    <th class="text-end">Montant</th>
                    <th class="text-end">Finance</th>
                    <th class="text-end">Reste</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bons as $bon)
                    <tr>
                        <td>{{ $bon->date_validation ? \Carbon\Carbon::parse($bon->date_validation)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $bon->nom_bon_commande }}</td>
                        <td>{{ $bon->entites->nom_entite ?? '-' }}</td>
                        <td>{{ $bon->annee_academique->nom ?? '-' }}</td>
                        <td>{{ $bon->budget->libelle_ligne_budget ?? '-' }}</td>
                        <td class="text-end">{{ number_format($bon->montant_total, 0, ',', ' ') }}</td>
                        <td class="text-end">{{ number_format($bon->total_decaisse, 0, ',', ' ') }}</td>
                        <td class="text-end">{{ number_format($bon->reste_financement, 0, ',', ' ') }}</td>
                        <td class="text-nowrap">
                            <a href="{{ route('etat_bons.show', $bon->id) }}" class="btn btn-info btn-sm">
                                Details
                            </a>
                        <a href="{{ route('decaissements.create', $bon->id) }}"
                               class="btn btn-primary btn-sm">
                                💸 Financer
                            </a>
                            <a href="{{ route('etat_bons.show', $bon->id) }}?print=1" class="btn btn-secondary btn-sm" target="_blank">
                                Imprimer
                            </a>
                            <a href="{{ route('etat_bons.exportPdfOne', $bon->id) }}" class="btn btn-danger btn-sm">
                                PDF
                            </a>
                            <a href="{{ route('etat_bons.exportExcelOne', $bon->id) }}" class="btn btn-success btn-sm">
                                Excel
                            </a>
                             <a href="{{ route('mes_bons.document1', $bon) }}" class="btn btn-xs btn-info">Document</a>
                               </a>
                             <a href="{{ route('mes_bons.document_pdf1', $bon) }}" class="btn btn-xs btn-info">pdf</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center">Aucun bon trouve.</td></tr>
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
    <li class="active"><strong>{{ $title }}</strong></li>
</ol>
@endsection
