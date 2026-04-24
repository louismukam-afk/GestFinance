@extends('layouts.app')

@section('content')
    <div class="container" style=" font-family: 'Times New Roman'; ">
        <div class="card shadow">
            <div class="card-header text-center bg-primary text-white">
                <h3>Bon de Commande : {{ $bon->nom_bon_commande }}</h3>
            </div>

            <div class="card-body">
                <h5 class="text-info">📌 Informations générales</h5>
                <ul class="list-group mb-3">
                    <li class="list-group-item"><strong>Description :</strong> {{ $bon->description_bon_commande }}</li>
                    <li class="list-group-item"><strong>Période :</strong> {{ $bon->date_debut }} → {{ $bon->date_fin }}</li>
                    <li class="list-group-item"><strong>Montant Total :</strong> {{ number_format($bon->montant_total,0,',',' ') }} FCFA</li>
                    <li class="list-group-item"><strong>Montant Réalisé :</strong> {{ number_format($bon->montant_realise,0,',',' ') }} FCFA</li>
                    <li class="list-group-item"><strong>Reste :</strong> {{ number_format($bon->reste,0,',',' ') }} FCFA</li>
                    <li class="list-group-item"><strong>Montant en lettres :</strong> {{ $bon->montant_lettre }}</li>
                    <li class="list-group-item"><strong>Personnel :</strong> {{ $bon->personnels->nom ?? 'N/A' }}</li>
                    <li class="list-group-item"><strong>Entite :</strong> {{ $bon->entites->nom_entite ?? 'N/A' }}</li>
                    <li class="list-group-item"><strong>Utilisateur :</strong> {{ $bon->user->name ?? 'N/A' }}</li>
                   {{-- <li class="list-group-item" style="text-align: left;">
                        <strong>Statut :</strong> {!! $bon->statut_badge !!}
                    </li>--}}
                   <li class="list-group-item"><strong>Statut :</strong>
                        @if($bon->statuts == 0)
                            <span class="badge bg-warning">En attente</span>
                        @elseif($bon->statuts == 1)
                            <span class="badge bg-success">Validé</span>
                        @elseif($bon->statuts == 2) <span class="badge bg-danger">Rejeté</span> @else
                        <span class="badge bg-secondary">Inconnu</span>
                        @endif
                    </li>
                  {{--  <li class="list-group-item">
                        <strong>Statut :</strong>
                        @switch((int) $bon->statuts)
                        @case(0)
                        <span class="badge bg-warning">En attente</span>
                        @break
                        @case(1)
                        <span class="badge bg-success">Validé</span>
                        @break
                        @case(2)
                        <span class="badge bg-danger">Rejeté</span>
                        @break
                        @default
                        <span class="badge bg-secondary">Inconnu</span>
                        @endswitch
                    </li>--}}

                </ul>

                <h5 class="text-info">📋 Éléments du Bon</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm" style="max-width: 80%;">
                        <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Nom</th>
                            <th>Description</th>
                            <th>Quantité</th>
                            <th>Prix Unitaire</th>
                            <th>Montant Total</th>
                            <th>Date Réalisation</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($bon->element_bon_commandes as $i => $el)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ $el->nom_element_bon_commande }}</td>
                                <td>{{ $el->description_elements_bon_commande }}</td>
                                <td>{{ $el->quantite_element_bon_commande }}</td>
                                <td>{{ number_format($el->prix_unitaire_element_bon_commande,0,',',' ') }} FCFA</td>
                                <td>{{ number_format($el->montant_total_element_bon_commande,0,',',' ') }} FCFA</td>
                                <td>{{ $el->date_realisation }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Aucun élément enregistré</td>
                            </tr>
                        @endforelse

                        </tbody>
                    </table>
                </div>

                <div class="mt-3 d-flex justify-content-between">
                    <a href="{{ route('etat_bons.index') }}" class="btn btn-secondary">⬅ Retour</a>
                    <div>
                        <a href="{{ route('etat_bons.exportPdfOne', $bon->id) }}" class="btn btn-danger">🖨 Exporter PDF</a>
                        <a href="{{ route('etat_bons.exportExcelOne', $bon->id) }}" class="btn btn-success">📊 Exporter Excel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
