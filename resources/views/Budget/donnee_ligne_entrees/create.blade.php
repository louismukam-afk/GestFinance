
@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>
            Ajouter des données ligne budgétaire pour :
            <span class="text-primary">{{ $donnee->donnee_ligne_budgetaire_entree }}</span>
        </h3>

        @php($montantDejaAffecte = \App\Models\donnee_ligne_budgetaire_entree::where('id_donnee_budgetaire_entree', $donnee->id)->sum('montant'))
        @php($montantDisponible = max(0, (float) $donnee->montant - (float) $montantDejaAffecte))
        <div class="alert alert-info">
            Montant de la donnee : <strong>{{ number_format($donnee->montant, 0, ',', ' ') }} FCFA</strong>.
            Deja affecte : <strong>{{ number_format($montantDejaAffecte, 0, ',', ' ') }} FCFA</strong>.
            Disponible : <strong>{{ number_format($montantDisponible, 0, ',', ' ') }} FCFA</strong>.
        </div>
        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('donnee_ligne_entrees.store', $donnee->id) }}">
            @csrf

            <div id="elements-container"></div>

            <div class="mt-3">
                <button type="button" id="addRow" class="btn btn-info">➕ Ajouter une ligne</button>
                <button type="submit" class="btn btn-success">💾 Enregistrer</button>
                <a href="{{ route('donnee_entrees.index') }}" class="btn btn-secondary">↩️ Retour</a>
            </div>
        </form>

        <template id="row-template">
            <div class="row element-row border p-3 mb-3 rounded">
                <div class="col-md-6 mb-3">
                    <label>Libellé</label>
                    <input type="text" name="libelle[]" class="form-control" required>
                </div>

                <div class="col-md-3 mb-3">
                    <label>Code</label>
                    <input type="text" name="code[]" class="form-control" required>
                </div>

                <div class="col-md-3 mb-3">
                    <label>N° Compte</label>
                    <input type="text" name="compte[]" class="form-control" required>
                </div>

                <div class="col-md-12 mb-3">
                    <label>Description</label>
                    <textarea name="description[]" class="form-control"></textarea>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Date Création</label>
                    <input type="date" name="date_creation[]" class="form-control" required>
                </div>

                <!-- Budget (fixe) -->
                <div class="col-md-4 mb-3">
                    <label>Budget</label>
                    <select class="form-control select2" disabled>
                        <option value="{{ $donnee->id_budget }}" selected>
                            {{ $donnee->budgets->libelle_ligne_budget ?? 'Budget inconnu' }}
                        </option>
                    </select>
                    <input type="hidden" name="id_budget[]" value="{{ $donnee->id_budget }}">
                </div>

                <!-- Donnée d’entrée (fixe) -->
                <div class="col-md-4 mb-3">
                    <label>Donnée budgétaire entrée</label>
                    <select class="form-control select2" disabled>
                        <option value="{{ $donnee->id }}" selected>
                            {{ $donnee->donnee_ligne_budgetaire_entree }}
                        </option>
                    </select>
                    <input type="hidden" name="id_donnee_budgetaire_entree[]" value="{{ $donnee->id }}">
                </div>

                <!-- Élément (AJAX) -->
                <div class="col-md-4 mb-3">
                    <label>Élément ligne budgétaire entrée</label>
                    <select name="id_element_ligne_budgetaire_entree[]" class="form-control element-select select2" required>
                        <option value="">Chargement...</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Montant</label>
                    <input type="number" step="0.01" name="montant[]" class="form-control" required>
                </div>
            </div>
        </template>
    </div>
@endsection

@section('scripts')
    <script>
        $(function () {
            // URL AJAX générée côté serveur (plus de concaténation fragile)
            const ELEMENTS_URL = @json(route('donnee_ligne_entrees.getElements', $donnee->id));

            function initSelect2(scope) {
                if ($.fn.select2) {
                    (scope ? $(scope) : $(document)).find('.select2').select2({ width: '100%' });
                }
            }

            function loadElements($elementSelect) {
                $elementSelect.html('<option value="">Chargement...</option>');
                $.ajax({
                    url: ELEMENTS_URL,
                    method: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        $elementSelect.empty().append('<option value="">-- Sélectionner --</option>');
                        if (!data || data.length === 0) {
                            $elementSelect.append('<option value="">(Aucun élément trouvé)</option>');
                            return;
                        }
                        data.forEach(function (el) {
                            $elementSelect.append(
                                $('<option>', { value: el.id, text: el.libelle_elements_ligne_budgetaire_entree })
                            );
                        });
                    },
                    error: function (xhr, status, err) {
                        console.error('AJAX error:', status, err, xhr.responseText);
                        $elementSelect.html('<option value="">Erreur de chargement</option>');
                    }
                });
            }

            function addRow() {
                const tpl = document.getElementById('row-template');
                const $row = $(tpl.content.cloneNode(true));
                $('#elements-container').append($row);

                const $lastRow = $('#elements-container .element-row').last();
                initSelect2($lastRow);
                loadElements($lastRow.find('.element-select'));
            }

            // Première ligne
            addRow();

            // Bouton ➕
            $('#addRow').on('click', addRow);
        });
    </script>
@endsection
