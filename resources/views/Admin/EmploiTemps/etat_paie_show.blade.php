@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-primary">{{ $title }} {{ $etat->reference }}</h3>
        <div>
            <a href="{{ route('paie_permanents.etats.pdf', $etat) }}" class="btn btn-danger">PDF</a>
            <a href="{{ route('paie_permanents.index') }}" class="btn btn-secondary">Retour</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3"><strong>Periode</strong><br>{{ optional($etat->periode_debut)->format('d/m/Y') }} - {{ optional($etat->periode_fin)->format('d/m/Y') }}</div>
                <div class="col-md-3"><strong>Annee academique</strong><br>{{ $etat->annee_academique->nom ?? 'Toutes' }}</div>
                <div class="col-md-3"><strong>Entite</strong><br>{{ $etat->entite->nom_entite ?? 'Toutes' }}</div>
                <div class="col-md-3"><strong>Date generation</strong><br>{{ optional($etat->date_generation)->format('d/m/Y H:i') }}</div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-2"><div class="alert alert-info"><strong>Employes</strong><br>{{ $etat->nombre_employes }}</div></div>
        <div class="col-md-2"><div class="alert alert-success"><strong>Gains</strong><br>{{ number_format($etat->total_gains, 0, ',', ' ') }}</div></div>
        <div class="col-md-2"><div class="alert alert-warning"><strong>Retenues</strong><br>{{ number_format($etat->total_retenues, 0, ',', ' ') }}</div></div>
        <div class="col-md-2"><div class="alert alert-danger"><strong>Penalites</strong><br>{{ number_format($etat->total_penalites, 0, ',', ' ') }}</div></div>
        <div class="col-md-2"><div class="alert alert-danger"><strong>Sanctions</strong><br>{{ number_format($etat->total_sanctions, 0, ',', ' ') }}</div></div>
        <div class="col-md-2"><div class="alert alert-primary"><strong>Net</strong><br>{{ number_format($etat->total_net_a_payer, 0, ',', ' ') }}</div></div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Lignes de l'etat de paie</strong></div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Employe permanent</th>
                        <th class="text-end">Gains</th>
                        <th class="text-end">Retenues</th>
                        <th class="text-end">Penalites</th>
                        <th class="text-end">Sanctions</th>
                        <th class="text-end">Acomptes</th>
                        <th class="text-end">Net a payer</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($etat->lignes as $ligne)
                        <tr>
                            <td>{{ $ligne->nom_personnel }}</td>
                            <td class="text-end">{{ number_format($ligne->total_gains, 0, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format($ligne->total_retenues, 0, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format($ligne->penalite_biometrie, 0, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format($ligne->total_sanctions, 0, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format($ligne->total_acomptes, 0, ',', ' ') }}</td>
                            <td class="text-end"><strong>{{ number_format($ligne->net_a_payer, 0, ',', ' ') }}</strong></td>
                            <td>
                                <details>
                                    <summary>Voir</summary>
                                    <strong>Gains</strong>
                                    <ul>
                                        @foreach($ligne->detail_gains ?? [] as $detail)
                                            <li>{{ $detail['libelle'] ?? '-' }} : {{ number_format($detail['montant'] ?? 0, 0, ',', ' ') }}</li>
                                        @endforeach
                                    </ul>
                                    <strong>Retenues</strong>
                                    <ul>
                                        @foreach($ligne->detail_retenues ?? [] as $detail)
                                            <li>{{ $detail['libelle'] ?? '-' }} : {{ number_format($detail['montant'] ?? 0, 0, ',', ' ') }}</li>
                                        @endforeach
                                    </ul>
                                </details>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-secondary fw-bold">
                        <td>Total</td>
                        <td class="text-end">{{ number_format($etat->total_gains, 0, ',', ' ') }}</td>
                        <td class="text-end">{{ number_format($etat->total_retenues, 0, ',', ' ') }}</td>
                        <td class="text-end">{{ number_format($etat->total_penalites, 0, ',', ' ') }}</td>
                        <td class="text-end">{{ number_format($etat->total_sanctions, 0, ',', ' ') }}</td>
                        <td class="text-end">{{ number_format($etat->total_acomptes, 0, ',', ' ') }}</td>
                        <td class="text-end">{{ number_format($etat->total_net_a_payer, 0, ',', ' ') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

@section('breadcrumb')
<ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
    <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
    <li><a href="{{ route('emploi_temps.index') }}"><strong>Ressources humaines</strong></a></li>
    <li><a href="{{ route('paie_permanents.index') }}"><strong>Paie permanents</strong></a></li>
    <li class="active"><strong>Etat de paie</strong></li>
</ol>
@endsection
