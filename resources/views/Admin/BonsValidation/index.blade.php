@extends('layouts.app')

@php
    if (!function_exists('validation_badge')) {
        function validation_badge(string $state): string {
            return match ($state) {
                'valide' => '<span class="label label-success">Valide</span>',
                'refuse' => '<span class="label label-danger">Refuse</span>',
                default => '<span class="label label-warning">En attente</span>',
            };
        }
    }
@endphp

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Validation des bons - {{ $niveauLabel }}</h3>
            <p class="text-muted">Bons en attente, valides ou refuses au niveau {{ $niveauLabel }}.</p>
        </div>
        <div>
            @if(auth()->user()?->canAccessRoute('validation_bons.pdg'))
                <a href="{{ route('validation_bons.pdg') }}" class="btn {{ $niveau === 'pdg' ? 'btn-primary' : 'btn-default' }}">PDG</a>
            @endif
            @if(auth()->user()?->canAccessRoute('validation_bons.daf'))
                <a href="{{ route('validation_bons.daf') }}" class="btn {{ $niveau === 'daf' ? 'btn-primary' : 'btn-default' }}">DAF</a>
            @endif
            @if(auth()->user()?->canAccessRoute('validation_bons.achats'))
                <a href="{{ route('validation_bons.achats') }}" class="btn {{ $niveau === 'achats' ? 'btn-primary' : 'btn-default' }}">Achats</a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-2">
            <label>Date debut</label>
            <input type="date" name="date_debut" value="{{ request('date_debut') }}" class="form-control">
        </div>
        <div class="col-md-2">
            <label>Date fin</label>
            <input type="date" name="date_fin" value="{{ request('date_fin') }}" class="form-control">
        </div>
        <div class="col-md-2">
            <label>Statut niveau</label>
            <select name="statut" class="form-control">
                <option value="">Tous</option>
                <option value="attente" {{ request('statut') === 'attente' ? 'selected' : '' }}>En attente</option>
                <option value="valide" {{ request('statut') === 'valide' ? 'selected' : '' }}>Valide</option>
                <option value="refuse" {{ request('statut') === 'refuse' ? 'selected' : '' }}>Refuse</option>
            </select>
        </div>
        <div class="col-md-4">
            <label>Recherche</label>
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nom ou description">
        </div>
        <div class="col-md-2 d-flex align-items-end" style="gap:6px;">
            <button class="btn btn-primary">Filtrer</button>
            <a href="{{ route('validation_bons.'.$niveau) }}" class="btn btn-secondary">Reset</a>
        </div>
        <div class="col-md-12" style="margin-top:10px;">
            <a href="{{ route('validation_bons.pdf', array_merge(['niveau' => $niveau], request()->query())) }}" class="btn btn-danger">Export PDF</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Bon</th>
                    <th>Description</th>
                    <th>Emetteur</th>
                    <th>Entite</th>
                    <th>Personnel</th>
                    <th class="text-end">Montant</th>
                    <th>Statut global</th>
                    <th>Actions</th>
                    <th>PDG</th>
                    <th>DAF</th>
                    <th>Achats</th>
                    <th>Emetteur</th>
                    <th>Motif refus</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bons as $bon)
                    @php
                        $state = $bon->validationState($niveau);
                        $validationField = 'validation_'.$niveau;
                        $refusField = 'refus_'.$niveau;
                        $motifField = 'motif_refus_'.$niveau;
                    @endphp
                    <tr>
                        <td>{{ $bon->date_debut }}</td>
                        <td>{{ $bon->nom_bon_commande }}</td>
                        <td>{{ $bon->description_bon_commande }}</td>
                        <td>{{ $bon->user->name ?? '-' }}</td>
                        <td>{{ $bon->entites->nom_entite ?? '-' }}</td>
                        <td>{{ $bon->personnels->nom ?? '-' }}</td>
                        <td class="text-end">{{ number_format($bon->montant_total, 0, ',', ' ') }} FCFA</td>
                        <td>{!! $bon->statut_badge !!}</td>
                        <td style="min-width:260px;">
                            <a href="{{ route('element_bon.index', $bon->id) }}" class="btn btn-xs btn-default">Voir elements</a>
                            <a href="{{ route('element_bon.exportPdf', $bon->id) }}" class="btn btn-xs btn-danger">PDF</a>
                            @if($niveau === 'daf' && $bon->{$validationField} && !$bon->{$refusField})
                                <button type="button" class="btn btn-xs btn-info" data-toggle="modal" data-target="#imputationDaf{{ $bon->id }}">
                                    Imputer / modifier
                                </button>
                            @endif
                            @if(!$bon->{$validationField} && !$bon->{$refusField})
                                @if($niveau === 'daf')
                                    <button type="button" class="btn btn-xs btn-success" data-toggle="modal" data-target="#validationDaf{{ $bon->id }}">
                                        Valider / imputer
                                    </button>
                                @else
                                    <form method="POST" action="{{ route('validation_bons.valider', [$niveau, $bon]) }}" style="display:inline;">
                                        @csrf
                                        <button class="btn btn-xs btn-success" onclick="return confirm('Valider ce bon ?')">Valider</button>
                                    </form>
                                @endif
                                <button type="button" class="btn btn-xs btn-warning" data-toggle="modal" data-target="#refusBon{{ $bon->id }}">
                                    Refuser
                                </button>
                            @endif
                        </td>
                        <td>{!! validation_badge($bon->validationState('pdg')) !!}</td>
                        <td>{!! validation_badge($bon->validationState('daf')) !!}</td>
                        <td>{!! validation_badge($bon->validationState('achats')) !!}</td>
                        <td>{!! validation_badge($bon->validationState('emetteur')) !!}</td>
                        <td>{{ $bon->{$motifField} ?: $bon->motif_refus ?: '-' }}</td>
                    </tr>

                    @if($niveau === 'daf' && !$bon->{$validationField} && !$bon->{$refusField})
                        <div class="modal fade" id="validationDaf{{ $bon->id }}" tabindex="-1" role="dialog">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('validation_bons.valider', [$niveau, $bon]) }}">
                                        @csrf
                                        <div class="modal-header">
                                            <h4 class="modal-title">Validation DAF et imputation budgetaire</h4>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>{{ $bon->nom_bon_commande }}</strong> - {{ number_format($bon->montant_total, 0, ',', ' ') }} FCFA</p>

                                            @include('Admin.BonsValidation.partials.imputation_daf_form', ['bon' => $bon])
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
                                            <button class="btn btn-success">Valider DAF</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($niveau === 'daf' && $bon->{$validationField} && !$bon->{$refusField})
                        <div class="modal fade" id="imputationDaf{{ $bon->id }}" tabindex="-1" role="dialog">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('validation_bons.daf.imputer', $bon) }}">
                                        @csrf
                                        <div class="modal-header">
                                            <h4 class="modal-title">Imputation budgetaire DAF</h4>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>{{ $bon->nom_bon_commande }}</strong> - {{ number_format($bon->montant_total, 0, ',', ' ') }} FCFA</p>
                                            @include('Admin.BonsValidation.partials.imputation_daf_form', ['bon' => $bon])
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
                                            <button class="btn btn-success">Enregistrer imputation</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="modal fade" id="refusBon{{ $bon->id }}" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('validation_bons.refuser', [$niveau, $bon]) }}">
                                    @csrf
                                    <div class="modal-header">
                                        <h4 class="modal-title">Motif du refus</h4>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <p><strong>{{ $bon->nom_bon_commande }}</strong></p>
                                        <textarea name="motif_refus" class="form-control" rows="4" required placeholder="Saisir le motif du refus"></textarea>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
                                        <button class="btn btn-warning">Confirmer le refus</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="14" class="text-center">Aucun bon trouve.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
@if($niveau === 'daf')
    <script>
        const dafBudgetBaseUrl = "{{ url('decaissements') }}";

        function resetSelect($select) {
            $select.html('<option value="">-- Choisir --</option>');
        }

        function appendOption($select, value, label, selectedValue) {
            const selected = String(selectedValue || '') === String(value) ? ' selected' : '';
            $select.append('<option value="' + value + '"' + selected + '>' + label + '</option>');
        }

        $('.js-budget-daf').on('change', function () {
            const bon = $(this).data('bon');
            const budget = $(this).val();
            const $ligne = $('.js-ligne-daf[data-bon="' + bon + '"]');
            const $element = $('.js-element-daf[data-bon="' + bon + '"]');
            const $donneeBudget = $('.js-donnee-budget-daf[data-bon="' + bon + '"]');
            const $donneeLigne = $('.js-donnee-ligne-daf[data-bon="' + bon + '"]');

            resetSelect($ligne);
            resetSelect($element);
            resetSelect($donneeBudget);
            resetSelect($donneeLigne);

            if (!budget) return;

            $.get(dafBudgetBaseUrl + '/ajax/lignes/' + budget, function (data) {
                const selected = $ligne.data('selected');
                data.forEach(function (item) {
                    appendOption($ligne, item.id, item.libelle_ligne_budgetaire_sortie, selected);
                });
                if (selected) {
                    $ligne.trigger('change');
                }
            });
        });

        $('.js-ligne-daf').on('change', function () {
            const bon = $(this).data('bon');
            const ligne = $(this).val();
            const $element = $('.js-element-daf[data-bon="' + bon + '"]');
            const $donneeBudget = $('.js-donnee-budget-daf[data-bon="' + bon + '"]');
            const $donneeLigne = $('.js-donnee-ligne-daf[data-bon="' + bon + '"]');

            resetSelect($element);
            resetSelect($donneeBudget);
            resetSelect($donneeLigne);

            if (!ligne) return;

            $.get(dafBudgetBaseUrl + '/ajax/elements/' + ligne, function (data) {
                const selected = $element.data('selected');
                data.forEach(function (item) {
                    appendOption($element, item.id, item.libelle_elements_ligne_budgetaire_sortie, selected);
                });
                if (selected) {
                    $element.trigger('change');
                }
            });

            $.get(dafBudgetBaseUrl + '/ajax/donnees-budget/' + ligne, function (data) {
                const selected = $donneeBudget.data('selected');
                data.forEach(function (item) {
                    appendOption($donneeBudget, item.id, item.donnee_ligne_budgetaire_sortie, selected);
                });
            });
        });

        $('.js-element-daf').on('change', function () {
            const bon = $(this).data('bon');
            const element = $(this).val();
            const $donneeLigne = $('.js-donnee-ligne-daf[data-bon="' + bon + '"]');

            resetSelect($donneeLigne);

            if (!element) return;

            $.get(dafBudgetBaseUrl + '/ajax/donnees-ligne/' + element, function (data) {
                const selected = $donneeLigne.data('selected');
                data.forEach(function (item) {
                    appendOption($donneeLigne, item.id, item.donnee_ligne_budgetaire_sortie + ' (' + item.montant + ')', selected);
                });
            });
        });

        $('.js-budget-daf').each(function () {
            if ($(this).val()) {
                $(this).trigger('change');
            }
        });
    </script>
@endif
@endsection

@section('breadcrumb')
<ol class="breadcrumb" style="background-color: transparent; padding: 4px 10px">
    <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
    <li class="active"><strong>Validation bons {{ $niveauLabel }}</strong></li>
</ol>
@endsection
