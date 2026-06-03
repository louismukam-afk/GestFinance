@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>Rappels des echeances de remboursement</h3>
        <p class="text-muted">Echeances non payees dont la date arrive dans les 7 prochains jours.</p>

        <div class="table-responsive">
            <div class="mb-3">
                <a href="{{ route('entrees_speciales.remboursements') }}" class="btn btn-success">
                    Interface de remboursement
                </a>
            </div>

            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>Date echeance</th>
                    <th>Dette</th>
                    <th>Creancier</th>
                    <th>Budget</th>
                    <th>Compte entree</th>
                    <th>Montant</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @forelse($rappels as $rappel)
                    <tr>
                        <td>{{ optional($rappel->date_echeance)->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('entrees_speciales.show', $rappel->id_entree_speciale) }}">
                                {{ optional($rappel->entree_speciale)->libelle }}
                            </a>
                        </td>
                        <td>{{ optional($rappel->entree_speciale)->nom_tiers }}</td>
                        <td>{{ optional(optional($rappel->entree_speciale)->budget)->libelle_ligne_budget }}</td>
                        <td>
                            @if(optional($rappel->entree_speciale)->id_banque)
                                Banque - {{ optional(optional($rappel->entree_speciale)->banque)->nom_banque }}
                            @else
                                Caisse - {{ optional(optional($rappel->entree_speciale)->caisse)->nom_caisse }}
                            @endif
                        </td>
                        <td>{{ number_format($rappel->montant, 0, ',', ' ') }}</td>
                        <td>
                            <form action="{{ route('entrees_speciales.echeances.payer', $rappel->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="date_paiement" value="{{ now()->format('Y-m-d') }}">
                                <input type="hidden" name="montant_paye" value="{{ $rappel->montant }}">
                                <input type="hidden" name="id_annee_academique_paiement" value="{{ optional($rappel->entree_speciale)->id_annee_academique_remboursement ?: optional($rappel->entree_speciale)->id_annee_academique }}">
                                <select name="id_caisse_paiement" class="form-control input-sm" required>
                                    <option value="">Caisse de remboursement</option>
                                    @foreach($caisses as $caisse)
                                        <option value="{{ $caisse->id }}">{{ $caisse->nom_caisse }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-success btn-sm">Marquer payee aujourd'hui</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Aucune echeance a rappeler.</td>
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
        <li class="active"><strong>Rappels</strong></li>
    </ol>
@endsection
