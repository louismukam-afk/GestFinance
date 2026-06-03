@php
    $isEdit = $entree->exists;
    $oldType = old('type_entree', $entree->type_entree ?? 'dette');
    $oldCompteType = old('compte_recepteur_type', ($entree->id_banque ?? 0) > 0 ? 'banque' : 'caisse');
    $oldMultiple = old('remboursement_multiple', $entree->remboursement_multiple ? 1 : 0);
    $dateEntree = old('date_entree', $entree->date_entree ? $entree->date_entree->format('Y-m-d') : now()->format('Y-m-d'));
    $echeances = old('echeances', $entree->echeances ? $entree->echeances->map(function ($e) {
        return [
            'nom_echeance' => $e->nom_echeance,
            'date_echeance' => optional($e->date_echeance)->format('Y-m-d'),
            'montant' => $e->montant,
            'observations' => $e->observations,
        ];
    })->toArray() : []);
@endphp

<form method="POST" action="{{ $isEdit ? route('entrees_speciales.update', $entree->id) : route('entrees_speciales.store') }}">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-md-3">
            <label>Type d'entree</label>
            <select name="type_entree" id="type_entree" class="form-control" required>
                @foreach($types as $key => $label)
                    <option value="{{ $key }}" {{ $oldType == $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label>Code</label>
            <input type="text" name="code_entree" value="{{ old('code_entree', $entree->code_entree) }}" class="form-control" placeholder="Auto si vide">
        </div>

        <div class="col-md-6">
            <label>Libelle</label>
            <input type="text" name="libelle" value="{{ old('libelle', $entree->libelle) }}" class="form-control" required>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-4">
            <label>Nom du creancier / donateur / promoteur</label>
            <input type="text" name="nom_tiers" value="{{ old('nom_tiers', $entree->nom_tiers) }}" class="form-control" required>
        </div>

        <div class="col-md-4">
            <label>Telephone</label>
            <input type="text" name="telephone_tiers" value="{{ old('telephone_tiers', $entree->telephone_tiers) }}" class="form-control">
        </div>

        <div class="col-md-4">
            <label>Adresse</label>
            <input type="text" name="adresse_tiers" value="{{ old('adresse_tiers', $entree->adresse_tiers) }}" class="form-control">
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-3">
            <label>Date d'entree</label>
            <input type="date" name="date_entree" value="{{ $dateEntree }}" class="form-control" required>
        </div>

        <div class="col-md-3 dette-field">
            <label>Date contraction dette</label>
            <input type="date" name="date_contraction_dette" value="{{ old('date_contraction_dette', optional($entree->date_contraction_dette)->format('Y-m-d')) }}" class="form-control">
        </div>

        <div class="col-md-3 dette-field">
            <label>Date remboursement final</label>
            <input type="date" name="date_remboursement" value="{{ old('date_remboursement', optional($entree->date_remboursement)->format('Y-m-d')) }}" class="form-control">
        </div>

        <div class="col-md-3">
            <label>Montant</label>
            <input type="number" name="montant" value="{{ old('montant', $entree->montant) }}" min="0" step="0.01" class="form-control" required>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-4">
            <label>Type de compte recepteur</label>
            <select name="compte_recepteur_type" id="compte_recepteur_type" class="form-control" required>
                <option value="caisse" {{ $oldCompteType === 'caisse' ? 'selected' : '' }}>Caisse</option>
                <option value="banque" {{ $oldCompteType === 'banque' ? 'selected' : '' }}>Banque</option>
            </select>
        </div>

        <div class="col-md-4 compte-caisse-field">
            <label>Caisse receptrice</label>
            <select name="id_caisse" id="id_caisse" class="form-control">
                <option value="">-- Choisir --</option>
                @foreach($caisses as $caisse)
                    <option value="{{ $caisse->id }}" {{ old('id_caisse', $entree->id_caisse) == $caisse->id ? 'selected' : '' }}>
                        {{ $caisse->nom_caisse }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4 compte-banque-field">
            <label>Banque receptrice</label>
            <select name="id_banque" id="id_banque" class="form-control">
                <option value="">-- Choisir --</option>
                @foreach($banques as $banque)
                    <option value="{{ $banque->id }}" {{ old('id_banque', $entree->id_banque) == $banque->id ? 'selected' : '' }}>
                        {{ $banque->nom_banque }}
                    </option>
                @endforeach
            </select>
        </div>

    </div>

    <div class="row mt-3">
        <div class="col-md-4">
            <label>Budget d'utilisation</label>
            <select name="id_budget" class="form-control" required>
                <option value="">-- Choisir --</option>
                @foreach($budgets as $budget)
                    <option value="{{ $budget->id }}" {{ old('id_budget', $entree->id_budget) == $budget->id ? 'selected' : '' }}>
                        {{ $budget->libelle_ligne_budget }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label>Annee academique d'utilisation</label>
            <select name="id_annee_academique_utilisation" class="form-control" required>
                <option value="">-- Choisir --</option>
                @foreach($annees as $annee)
                    <option value="{{ $annee->id }}" {{ old('id_annee_academique_utilisation', $entree->id_annee_academique_utilisation ?: $entree->id_annee_academique) == $annee->id ? 'selected' : '' }}>
                        {{ $annee->nom }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row mt-3 dette-field">
        <div class="col-md-4">
            <label>Annee academique de remboursement</label>
            <select name="id_annee_academique_remboursement" class="form-control">
                <option value="">-- Choisir --</option>
                @foreach($annees as $annee)
                    <option value="{{ $annee->id }}" {{ old('id_annee_academique_remboursement', $entree->id_annee_academique_remboursement ?: $entree->id_annee_academique) == $annee->id ? 'selected' : '' }}>
                        {{ $annee->nom }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-3">
            <label>Statut</label>
            <select name="statut" class="form-control">
                <option value="actif" {{ old('statut', $entree->statut ?? 'actif') == 'actif' ? 'selected' : '' }}>Actif</option>
                <option value="solde" {{ old('statut', $entree->statut) == 'solde' ? 'selected' : '' }}>Solde</option>
                <option value="annule" {{ old('statut', $entree->statut) == 'annule' ? 'selected' : '' }}>Annule</option>
            </select>
        </div>

        <div class="col-md-3 dette-field">
            <label>Remboursement</label>
            <select name="remboursement_multiple" id="remboursement_multiple" class="form-control">
                <option value="0" {{ !$oldMultiple ? 'selected' : '' }}>Une seule echeance</option>
                <option value="1" {{ $oldMultiple ? 'selected' : '' }}>Plusieurs echeances</option>
            </select>
        </div>

        <div class="col-md-3 dette-field">
            <label>Nombre d'echeances</label>
            <input type="number" name="nombre_echeances" id="nombre_echeances" value="{{ old('nombre_echeances', $entree->nombre_echeances ?: max(count($echeances), 1)) }}" min="0" class="form-control">
        </div>
    </div>

    <div class="mt-3">
        <label>Observations</label>
        <textarea name="observations" class="form-control" rows="3">{{ old('observations', $entree->observations) }}</textarea>
    </div>

    <div class="panel panel-default mt-4 dette-field">
        <div class="panel-heading">
            <strong>Echeances de remboursement</strong>
            <button type="button" class="btn btn-default btn-sm pull-right" id="add-echeance">Ajouter une echeance</button>
        </div>
        <div class="panel-body" id="echeances-wrapper">
            @forelse($echeances as $index => $echeance)
                @include('Budget.entrees_speciales._echeance_row', ['index' => $index, 'echeance' => $echeance])
            @empty
                @include('Budget.entrees_speciales._echeance_row', ['index' => 0, 'echeance' => []])
            @endforelse
        </div>
    </div>

    <div class="mt-4">
        <button class="btn btn-primary">{{ $isEdit ? 'Modifier' : 'Enregistrer' }}</button>
        <a href="{{ route('entrees_speciales.index') }}" class="btn btn-default">Retour</a>
    </div>
</form>

<template id="echeance-template">
    @include('Budget.entrees_speciales._echeance_row', ['index' => '__INDEX__', 'echeance' => []])
</template>

@section('scripts')
    <script>
        (function () {
            var typeSelect = document.getElementById('type_entree');
            var compteTypeSelect = document.getElementById('compte_recepteur_type');
            var caisseSelect = document.getElementById('id_caisse');
            var banqueSelect = document.getElementById('id_banque');
            var multipleSelect = document.getElementById('remboursement_multiple');
            var wrapper = document.getElementById('echeances-wrapper');
            var template = document.getElementById('echeance-template').innerHTML;
            var addButton = document.getElementById('add-echeance');

            function toggleDetteFields() {
                var isDette = typeSelect.value === 'dette';
                document.querySelectorAll('.dette-field').forEach(function (el) {
                    el.style.display = isDette ? '' : 'none';
                });
            }

            function toggleCompteFields() {
                var isBanque = compteTypeSelect.value === 'banque';
                document.querySelectorAll('.compte-caisse-field').forEach(function (el) {
                    el.style.display = isBanque ? 'none' : '';
                });
                document.querySelectorAll('.compte-banque-field').forEach(function (el) {
                    el.style.display = isBanque ? '' : 'none';
                });
                if (isBanque) {
                    caisseSelect.value = '';
                } else {
                    banqueSelect.value = '';
                }
            }

            function refreshIndexes() {
                document.querySelectorAll('.echeance-row').forEach(function (row, index) {
                    row.querySelectorAll('[name]').forEach(function (input) {
                        input.name = input.name.replace(/echeances\[[^\]]+\]/, 'echeances[' + index + ']');
                    });
                });
            }

            function ensureEcheanceCount() {
                var countInput = document.getElementById('nombre_echeances');
                var expected = parseInt(countInput.value || '0', 10);

                if (multipleSelect.value !== '1' || expected < 1) {
                    expected = 1;
                    countInput.value = 1;
                }

                while (wrapper.querySelectorAll('.echeance-row').length < expected) {
                    var index = wrapper.querySelectorAll('.echeance-row').length;
                    wrapper.insertAdjacentHTML('beforeend', template.replaceAll('__INDEX__', index));
                }

                while (wrapper.querySelectorAll('.echeance-row').length > expected) {
                    wrapper.querySelector('.echeance-row:last-child').remove();
                }

                refreshIndexes();
            }

            addButton.addEventListener('click', function () {
                var index = wrapper.querySelectorAll('.echeance-row').length;
                wrapper.insertAdjacentHTML('beforeend', template.replaceAll('__INDEX__', index));
                document.getElementById('nombre_echeances').value = wrapper.querySelectorAll('.echeance-row').length;
            });

            wrapper.addEventListener('click', function (event) {
                if (event.target.classList.contains('remove-echeance')) {
                    event.target.closest('.echeance-row').remove();
                    refreshIndexes();
                }
            });

            typeSelect.addEventListener('change', toggleDetteFields);
            compteTypeSelect.addEventListener('change', toggleCompteFields);
            if (multipleSelect) {
                multipleSelect.addEventListener('change', function () {
                    document.getElementById('nombre_echeances').value = multipleSelect.value === '1'
                        ? Math.max(wrapper.querySelectorAll('.echeance-row').length, 2)
                        : 1;
                    ensureEcheanceCount();
                });
            }
            document.getElementById('nombre_echeances').addEventListener('change', ensureEcheanceCount);
            document.getElementById('nombre_echeances').addEventListener('input', ensureEcheanceCount);
            toggleDetteFields();
            toggleCompteFields();
        })();
    </script>
@endsection
