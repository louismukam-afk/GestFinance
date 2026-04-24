{{--
@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>➕ Ajouter des données ligne budgétaire sortie pour :
            <span class="text-primary">{{ $donnee->donnee_ligne_budgetaire_sortie }}</span>
        </h3>

        <form method="POST" action="{{ route('donnee_ligne_sorties.store', $donnee->id) }}">
            @csrf

            <div id="elements-container">
                <div class="row element-row border p-3 mb-3">
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

                    <div class="col-md-4 mb-3">
                        <label>Élément ligne budgétaire sortie</label>
                        <select name="id_element_ligne_budgetaire_sortie[]" class="form-control select2" required>
                            <option value="">-- Sélectionner --</option>
                            @foreach($elements as $el)
                                <option value="{{ $el->id }}">{{ $el->libelle_elements_ligne_budgetaire_sortie }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <button type="button" id="addRow" class="btn btn-info">➕ Ajouter une ligne</button>
            <button type="submit" class="btn btn-success">💾 Enregistrer</button>
            <a href="{{ route('donnee_ligne_sorties.index', $donnee->id) }}" class="btn btn-secondary">↩️ Retour</a>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('.select2').select2({width: '100%'});

            $('#addRow').on('click', function () {
                let container = $('#elements-container');
                let newRow = container.find('.element-row').first().clone();

                newRow.find('input, textarea').val('');
                newRow.find('select').val('').trigger('change');

                container.append(newRow);
                newRow.find('.select2').select2({width: '100%'});
            });
        });
    </script>
@endsection
--}}
@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>➕ Ajouter des données ligne budgétaire sortie pour :
            <span class="text-primary">{{ $donnee->donnee_ligne_budgetaire_sortie }}</span>
        </h3>

        <form method="POST" action="{{ route('donnee_ligne_sorties.store', $donnee->id) }}">
            @csrf

            <div id="elements-container"></div>

            <div class="mt-3">
                <button type="button" id="addRow" class="btn btn-info">➕ Ajouter une ligne</button>
                <button type="submit" class="btn btn-success">💾 Enregistrer</button>
                <a href="{{ route('donnee_ligne_sorties.index', $donnee->id) }}" class="btn btn-secondary">↩️ Retour</a>
            </div>
        </form>

        <!-- Template invisible -->
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

                <!-- Budget (hérité, fixe) -->
                <div class="col-md-4 mb-3">
                    <label>Budget</label>
                    <select class="form-control select2" disabled>
                        <option value="{{ $donnee->id_budget }}" selected>
                            {{ $donnee->budgets->libelle_ligne_budget ?? 'Budget inconnu' }}
                        </option>
                    </select>
                    <input type="hidden" name="id_budget[]" value="{{ $donnee->id_budget }}">
                </div>

                <!-- Donnée budgétaire sortie (héritée, fixe) -->
                <div class="col-md-4 mb-3">
                    <label>Donnée budgétaire sortie</label>
                    <select class="form-control select2" disabled>
                        <option value="{{ $donnee->id }}" selected>
                            {{ $donnee->donnee_ligne_budgetaire_sortie }}
                        </option>
                    </select>
                    <input type="hidden" name="id_donnee_budgetaire_sortie[]" value="{{ $donnee->id }}">
                </div>

                <!-- Élément (chargé dynamiquement via AJAX) -->
                <div class="col-md-4 mb-3">
                    <label>Élément ligne budgétaire sortie</label>
                    <select name="id_element_ligne_budgetaire_sortie[]" class="form-control element-select select2" required>
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
            // URL AJAX générée côté serveur (via une route spécifique)
            const ELEMENTS_URL = @json(route('donnee_ligne_sorties.getElements', $donnee->id));

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
                                $('<option>', { value: el.id, text: el.libelle_elements_ligne_budgetaire_sortie })
                            );
                        });
                    },
                    error: function () {
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

            // Ajouter la première ligne automatiquement
            addRow();

            // Bouton ➕
            $('#addRow').on('click', addRow);
        });
    </script>
@endsection
@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('budget') }}"><strong>budget</strong></a></li>
        <li><a href="{{ route('donnee_ligne_sorties.index',$donnee->id)}}"><strong>Donnée </strong></a></li>

        <li class="active"><strong>{{ $title }}</strong></li>
    </ol>
@endsection