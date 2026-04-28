@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h3>Disponibilite des caisses</h3>
                <p class="text-muted">
                    Solde disponible dans chaque caisse avant et apres les transferts.
                </p>
            </div>
            <a href="{{ route('etat_sorties.etat_caisse') }}" class="btn btn-dark">
                Etat des caisses
            </a>
        </div>

        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
                <label>Date de situation</label>
                <input type="date" name="date_fin" value="{{ request('date_fin', $dateFin) }}" class="form-control">
            </div>
            <div class="col-md-12 text-center mt-3">
                <button class="btn btn-primary">Afficher</button>
                <a href="{{ route('etat_sorties.disponibilite_caisses') }}" class="btn btn-secondary">
                    Reset
                </a>
                <a href="{{ route('etat_sorties.disponibilite_caisses.pdf', request()->query()) }}" class="btn btn-danger">
                    PDF
                </a>
                <button type="button" onclick="window.print()" class="btn btn-dark">
                    Imprimer
                </button>
            </div>
        </form>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="alert alert-warning text-center">
                    <h5>Total avant transfert</h5>
                    <h3>{{ number_format($totalAvantTransfert, 0, ',', ' ') }} FCFA</h3>
                </div>
            </div>
            <div class="col-md-6">
                <div class="alert alert-success text-center">
                    <h5>Total apres transfert</h5>
                    <h3>{{ number_format($totalApresTransfert, 0, ',', ' ') }} FCFA</h3>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            @foreach($caisses as $ligne)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-dark text-white">
                            <strong>{{ $ligne['caisse']->nom_caisse }}</strong>
                            <span class="float-end">{{ $ligne['caisse']->type_caisse ?? '' }}</span>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <small class="text-muted">Solde avant transfert</small>
                                <h4 class="text-warning">
                                    {{ number_format($ligne['solde_avant_transfert'], 0, ',', ' ') }} FCFA
                                </h4>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted">Solde apres transfert</small>
                                <h4 class="{{ $ligne['solde_apres_transfert'] < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($ligne['solde_apres_transfert'], 0, ',', ' ') }} FCFA
                                </h4>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span>Entrees</span>
                                <strong>{{ number_format($ligne['entrees_reglements'], 0, ',', ' ') }}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Retours en caisse</span>
                                <strong>{{ number_format($ligne['entrees_retours'], 0, ',', ' ') }}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Decaissements</span>
                                <strong>{{ number_format($ligne['sorties_decaissements'], 0, ',', ' ') }}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Transferts entrants</span>
                                <strong>{{ number_format($ligne['transferts_entrants'], 0, ',', ' ') }}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Transferts sortants</span>
                                <strong>{{ number_format($ligne['transferts_sortants'], 0, ',', ' ') }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                <tr>
                    <th>Caisse</th>
                    <th>Type</th>
                    <th class="text-end">Entrees reglements</th>
                    <th class="text-end">Retours caisse</th>
                    <th class="text-end">Decaissements</th>
                    <th class="text-end">Solde avant transfert</th>
                    <th class="text-end">Transferts entrants</th>
                    <th class="text-end">Transferts sortants</th>
                    <th class="text-end">Solde apres transfert</th>
                </tr>
                </thead>
                <tbody>
                @foreach($caisses as $ligne)
                    <tr>
                        <td>{{ $ligne['caisse']->nom_caisse }}</td>
                        <td>{{ $ligne['caisse']->type_caisse ?? '-' }}</td>
                        <td class="text-end">{{ number_format($ligne['entrees_reglements'], 0, ',', ' ') }}</td>
                        <td class="text-end">{{ number_format($ligne['entrees_retours'], 0, ',', ' ') }}</td>
                        <td class="text-end">{{ number_format($ligne['sorties_decaissements'], 0, ',', ' ') }}</td>
                        <td class="text-end">{{ number_format($ligne['solde_avant_transfert'], 0, ',', ' ') }}</td>
                        <td class="text-end">{{ number_format($ligne['transferts_entrants'], 0, ',', ' ') }}</td>
                        <td class="text-end">{{ number_format($ligne['transferts_sortants'], 0, ',', ' ') }}</td>
                        <td class="text-end fw-bold">{{ number_format($ligne['solde_apres_transfert'], 0, ',', ' ') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        @media print {
            form,
            .btn,
            .breadcrumb,
            nav,
            footer,
            header {
                display: none !important;
            }

            .container {
                width: 100% !important;
                max-width: 100% !important;
            }

            .card {
                page-break-inside: avoid;
            }
        }
    </style>
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
            <strong>Disponibilite des caisses</strong>
        </li>
    </ol>
@endsection
