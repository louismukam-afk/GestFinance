@extends('layouts.app')

@section('content')
@php
    $isValides = $type === 'valides';
    $title = $isValides ? 'Mes bons valides' : 'Mes bons en attente de validation';
@endphp

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>{{ $title }}</h3>
            <p class="text-muted">Bons de commande crees par {{ auth()->user()->name }}.</p>
        </div>
        <div>
            <a href="{{ route('mes_bons.attente') }}" class="btn {{ !$isValides ? 'btn-primary' : 'btn-default' }}">En attente</a>
            <a href="{{ route('mes_bons.valides') }}" class="btn {{ $isValides ? 'btn-success' : 'btn-default' }}">Valides</a>
        </div>
    </div>

    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-3">
            <label>Date debut</label>
            <input type="date" name="date_debut" value="{{ request('date_debut') }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label>Date fin</label>
            <input type="date" name="date_fin" value="{{ request('date_fin') }}" class="form-control">
        </div>
        <div class="col-md-4">
            <label>Recherche</label>
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nom ou description">
        </div>
        <div class="col-md-2 d-flex align-items-end" style="gap: 6px;">
            <button class="btn btn-primary">Filtrer</button>
            <a href="{{ $isValides ? route('mes_bons.valides') : route('mes_bons.attente') }}" class="btn btn-secondary">Reset</a>
        </div>
        <div class="col-md-12">
            <a href="{{ route('mes_bons.pdf', array_merge(['type' => $type], request()->query())) }}" class="btn btn-danger">
                Export PDF
            </a>
            @unless($isValides)
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#createBonModal">
                    Nouveau bon
                </button>
            @endunless
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Date debut</th>
                    <th>Nom du bon</th>
                    <th>Description</th>
                    <th>Entite</th>
                    <th>Personnel</th>
                    <th class="text-end">Montant</th>
                    <th>Statut</th>
                    <th>PDG/PDF</th>
                    <th>DAF</th>
                    <th>Achats</th>
                    <th>Emetteur</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bons as $bon)
                    <tr>
                        <td>{{ $bon->date_debut }}</td>
                        <td>{{ $bon->nom_bon_commande }}</td>
                        <td>{{ $bon->description_bon_commande }}</td>
                        <td>{{ $bon->entites->nom_entite ?? '-' }}</td>
                        <td>{{ $bon->personnels->nom ?? '-' }}</td>
                        <td class="text-end">{{ number_format($bon->montant_total, 0, ',', ' ') }} FCFA</td>
                        <td>{!! $bon->statut_badge !!}</td>
                        <td>{{ $bon->validation_pdg ? 'Oui' : 'Non' }}</td>
                        <td>{{ $bon->validation_daf ? 'Oui' : 'Non' }}</td>
                        <td>{{ $bon->validation_achats ? 'Oui' : 'Non' }}</td>
                        <td>{{ $bon->validation_emetteur ? 'Oui' : 'Non' }}</td>
                        <td>
                            @unless($bon->validation_emetteur)
                                <form method="POST" action="{{ route('mes_bons.valider_emetteur', $bon) }}" style="display:inline;">
                                    @csrf
                                    <button class="btn btn-xs btn-success" onclick="return confirm('Valider ce bon comme emetteur ?')">
                                        Valider emetteur
                                    </button>
                                </form>
                            @endunless

                            @if($bon->statut_bon_code !== 1)
                                <button type="button" class="btn btn-xs btn-warning" data-toggle="modal" data-target="#editBonModal{{ $bon->id }}">
                                    Modifier
                                </button>
                            @endif
                        </td>
                    </tr>

                    <div class="modal fade" id="editBonModal{{ $bon->id }}" tabindex="-1" role="dialog">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('mes_bons.update', $bon) }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header">
                                        <h4 class="modal-title">Modifier le bon</h4>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        @include('Admin.MesBons.partials.form', ['bon' => $bon])
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
                                        <button class="btn btn-primary">Enregistrer</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="12" class="text-center">Aucun bon trouve.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="createBonModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('mes_bons.store') }}">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title">Nouveau bon de commande</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    @include('Admin.MesBons.partials.form', ['bon' => null])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
                    <button class="btn btn-success">Creer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('breadcrumb')
<ol class="breadcrumb" style="background-color: transparent; padding: 4px 10px">
    <li class="breadcrumb-item"><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
    <li class="breadcrumb-item active"><strong>{{ $title }}</strong></li>
</ol>
@endsection
