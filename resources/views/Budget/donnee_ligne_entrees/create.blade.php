{{--
@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>
            Ajouter des données ligne budgétaire pour :
            <span class="text-primary">{{ $donnee->donnee_ligne_budgetaire_entree }}</span>
        </h3>

        <form method="POST" action="{{ route('donnee_ligne_entrees.store', $donnee->id) }}">
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

                    <!-- Budget -->
                    <div class="col-md-4 mb-3">
                        <label>Budget</label>
                        <input type="text" class="form-control" value="{{ $donnee->budget->libelle_ligne_budget ?? '-' }}" readonly>
                        <input type="hidden" name="id_budget[]" value="{{ $donnee->id_budget }}">
                    </div>

                    <!-- Ligne budgétaire entrée -->
                    <div class="col-md-4 mb-3">
                        <label>Ligne budgétaire entrée</label>
                        <input type="text" class="form-control" value="{{ $ligne->libelle_ligne_budgetaire_entree ?? '-' }}" readonly>
                        <input type="hidden" name="id_ligne_budgetaire_entree[]" value="{{ $donnee->id_ligne_budgetaire_entree }}">
                    </div>

                    <!-- Élément ligne budgétaire -->
                    <div class="col-md-4 mb-3">
                        <label>Élément ligne budgétaire entrée</label>
                        <select name="id_element_ligne_budgetaire_entree[]" class="form-control select2" required>
                            <option value="">-- Sélectionner --</option>
                            @foreach($elements as $e)
                                <option value="{{ $e->id }}">{{ $e->libelle_elements_ligne_budgetaire_entree }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Montant</label>
                        <input type="number" step="0.01" name="montant[]" class="form-control" required>
                    </div>
                </div>
            </div>

            <button type="button" id="addRow" class="btn btn-info">➕ Ajouter une ligne</button>
            <button type="submit" class="btn btn-success">💾 Enregistrer</button>
            <a href="{{ route('donnee_entrees.index') }}" class="btn btn-secondary">↩️ Retour</a>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('.select2').select2({ width: '100%' });

            $('#addRow').on('click', function () {
                let container = $('#elements-container');
                let newRow = container.find('.element-row').first().clone();

                newRow.find('input[type=text], input[type=number], textarea').val('');
                newRow.find('select').val('').trigger('change');

                container.append(newRow);
            });
        });

            // Dynamique : recharger éléments par donnée
            $(document).on('change', '#donnee_select', function() {
                let donneeId = $(this).val();
                let elementSelect = $(this).closest('.element-row').find('#element_select');

                elementSelect.empty().append('<option value="">Chargement...</option>');

                if (donneeId) {
                    $.get("{{ url('/get-elements-by-donnee') }}/" + donneeId, function(data) {
                        elementSelect.empty().append('<option value="">-- Sélectionner --</option>');
                        $.each(data, function(key, value) {
                            elementSelect.append('<option value="'+ value.id +'">'+ value.libelle_elements_ligne_budgetaire_entree +'</option>');
                        });
                    });
                } else {
                    elementSelect.empty().append('<option value="">-- Sélectionner une donnée d\'abord --</option>');
                }
            });
        });
    </script>
@endsection
--}}
{{--
@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>
            Ajouter des données ligne budgétaire pour :
            <span class="text-primary">{{ $donnee->donnee_ligne_budgetaire_entree }}</span>
        </h3>

        <form method="POST" action="{{ route('donnee_ligne_entrees.store', $donnee->id) }}">
            @csrf

            <div id="elements-container">
                <div class="row element-row border p-3 mb-3">

                    <!-- Libellé -->
                    <div class="col-md-6 mb-3">
                        <label>Libellé</label>
                        <input type="text" name="libelle[]" class="form-control" required>
                    </div>

                    <!-- Code -->
                    <div class="col-md-3 mb-3">
                        <label>Code</label>
                        <input type="text" name="code[]" class="form-control" required>
                    </div>

                    <!-- Compte -->
                    <div class="col-md-3 mb-3">
                        <label>N° Compte</label>
                        <input type="text" name="compte[]" class="form-control" required>
                    </div>

                    <!-- Description -->
                    <div class="col-md-12 mb-3">
                        <label>Description</label>
                        <textarea name="description[]" class="form-control"></textarea>
                    </div>

                    <!-- Date -->
                    <div class="col-md-4 mb-3">
                        <label>Date Création</label>
                        <input type="date" name="date_creation[]" class="form-control" required>
                    </div>

                    <!-- Budget -->
                    <div class="col-md-4 mb-3">
                        <label>Budget</label>
                        <select name="id_budget[]" class="form-control select2" required>
                            <option value="">-- Sélectionner --</option>
                            @foreach($budgets as $b)
                                <option value="{{ $b->id }}">{{ $b->libelle_ligne_budget }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Donnée budgetaire entrée -->
                    <div class="col-md-4 mb-3">
                        <label>Donnée budgétaire entrée</label>
                        <select name="id_donnee_budgetaire_entree[]" class="form-control donnee-select select2" required>
                            <option value="{{ $donnee->id }}" selected>
                                {{ $donnee->donnee_ligne_budgetaire_entree }}
                            </option>
                        </select>
                    </div>

                    <!-- Élément ligne (chargé dynamiquement) -->
                    <div class="col-md-4 mb-3">
                        <label>Élément ligne budgétaire entrée</label>
                        <select name="id_element_ligne_budgetaire_entree[]" class="form-control element-select select2" required>
                            <option value="">-- Sélectionner une donnée d'abord --</option>
                        </select>
                    </div>

                    <!-- Montant -->
                    <div class="col-md-4 mb-3">
                        <label>Montant</label>
                        <input type="number" step="0.01" name="montant[]" class="form-control" required>
                    </div>

                </div>
            </div>

            <button type="button" id="addRow" class="btn btn-info">➕ Ajouter une ligne</button>
            <button type="submit" class="btn btn-success">💾 Enregistrer</button>
            <a href="{{ route('donnee_entrees.index') }}" class="btn btn-secondary">↩️ Retour</a>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('.select2').select2({ width: '100%' });

            // Ajouter une nouvelle ligne
            $('#addRow').on('click', function () {
                let container = $('#elements-container');
                let newRow = container.find('.element-row').first().clone();

                newRow.find('input, textarea').val('');
                newRow.find('select').val('').trigger('change');

                container.append(newRow);
                newRow.find('.select2').select2({ width: '100%' });
            });

            // Charger dynamiquement les éléments selon la donnée choisie
            $(document).on('change', '.donnee-select', function() {
                let donneeId = $(this).val();
                let elementSelect = $(this).closest('.element-row').find('.element-select');

                elementSelect.empty().append('<option value="">Chargement...</option>');

                if (donneeId) {
                    $.get("{{ url('/get-elements-by-donnee') }}/" + donneeId, function(data) {
                        elementSelect.empty().append('<option value="">-- Sélectionner --</option>');
                        $.each(data, function(key, value) {
                            elementSelect.append('<option value="'+ value.id +'">'+ value.libelle_elements_ligne_budgetaire_entree +'</option>');
                        });
                    });
                } else {
                    elementSelect.empty().append('<option value="">-- Sélectionner une donnée d\'abord --</option>');
                }
            });
        });
    </script>
@endsection
--}}
{{--
@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>
            Ajouter des données ligne budgétaire pour :
            <span class="text-primary">{{ $donnee->donnee_ligne_budgetaire_entree }}</span>
        </h3>

        <form method="POST" action="{{ route('donnee_ligne_entrees.store', $donnee->id) }}">
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

                    <!-- Budget -->
                    <div class="col-md-4 mb-3">
                        <label>Budget</label>
                        <select name="id_budget[]" class="form-control select2" required>
                            <option value="{{ $donnee->id_budget }}" selected>
                                {{ $donnee->budgets->libelle_ligne_budget ?? 'Budget inconnu' }}
                            </option>
                        </select>
                    </div>

                    <!-- Donnée budgetaire entrée -->
                    <div class="col-md-4 mb-3">
                        <label>Donnée budgétaire entrée</label>
                        <select name="id_donnee_budgetaire_entree[]" class="form-control donnee-select select2" required>
                            <option value="{{ $donnee->id }}" selected>
                                {{ $donnee->donnee_ligne_budgetaire_entree }}
                            </option>
                        </select>
                    </div>

                    <!-- Élément ligne budgétaire entrée -->
                    <div class="col-md-4 mb-3">
                        <label>Élément ligne budgétaire entrée</label>
                        <select name="id_element_ligne_budgetaire_entree[]" class="form-control element-select select2" required>
                            <option value="">-- Sélectionner --</option>
                            @foreach($elements as $el)
                                <option value="{{ $el->id }}">{{ $el->libelle_elements_ligne_budgetaire_entree }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Montant</label>
                        <input type="number" step="0.01" name="montant[]" class="form-control" required>
                    </div>
                </div>
            </div>
            <button type="button" id="addRow" class="btn btn-info">➕ Ajouter une ligne</button>
            <button type="submit" class="btn btn-success">💾 Enregistrer</button>
            <a href="{{ route('donnee_entrees.index') }}" class="btn btn-secondary">↩️ Retour</a>
        </form>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function () {
            $('.select2').select2({width: '100%'});

            // Fonction générique de chargement AJAX
            function loadElements(donneeId, elementSelect) {
                elementSelect.empty().append('<option value="">Chargement...</option>');
                if (donneeId) {
                    $.get("{{ url('/get-elements-by-donnee') }}/" + donneeId, function (data) {
                        elementSelect.empty().append('<option value="">-- Sélectionner --</option>');
                        $.each(data, function (key, value) {
                            elementSelect.append('<option value="' + value.id + '">' + value.libelle_elements_ligne_budgetaire_entree + '</option>');
                        });
                    });
                } else {
                    elementSelect.empty().append('<option value="">-- Sélectionner une donnée d\'abord --</option>');
                }
            }

            // Changement dynamique de donnée
            $(document).on('change', '.donnee-select', function () {
                let donneeId = $(this).val();
                let elementSelect = $(this).closest('.element-row').find('.element-select');
                loadElements(donneeId, elementSelect);
            });

            // Bouton ajouter une nouvelle ligne
            $('#addRow').on('click', function () {
                let container = $('#elements-container');
                let newRow = container.find('.element-row').first().clone();

                newRow.find('input, textarea').val('');
                newRow.find('select').val('').trigger('change');

                // enlever les id doublons
                newRow.find('[id]').removeAttr('id');

                container.append(newRow);
                newRow.find('.select2').select2({width: '100%'});
            });
        });
    </script>
@endsection


@section('scripts')
    <script>
        $(document).ready(function () {
            $('.select2').select2({width: '100%'});

            // Fonction générique de chargement AJAX
            function loadElements(donneeId, elementSelect) {
                elementSelect.empty().append('<option value="">Chargement...</option>');
                if (donneeId) {
                    $.get("{{ url('/get-elements-by-donnee') }}/" + donneeId, function (data) {
                        elementSelect.empty().append('<option value="">-- Sélectionner --</option>');
                        $.each(data, function (key, value) {
                            elementSelect.append('<option value="' + value.id + '">' + value.libelle_elements_ligne_budgetaire_entree + '</option>');
                        });
                    });
                } else {
                    elementSelect.empty().append('<option value="">-- Sélectionner une donnée d\'abord --</option>');
                }
            }

            // Changement dynamique de donnée
            $(document).on('change', '.donnee-select', function () {
                let donneeId = $(this).val();
                let elementSelect = $(this).closest('.element-row').find('.element-select');
                loadElements(donneeId, elementSelect);
            });

            // Bouton ajouter une nouvelle ligne
            $('#addRow').on('click', function () {
                let container = $('#elements-container');
                let newRow = container.find('.element-row').first().clone();

                newRow.find('input, textarea').val('');
                newRow.find('select').val('').trigger('change');

                container.append(newRow);
                newRow.find('.select2').select2({width: '100%'}); // Reinit select2
            });
        });
    </script>
@endsection

@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>
            Ajouter des données ligne budgétaire pour :
            <span class="text-primary">{{ $donnee->donnee_ligne_budgetaire_entree }}</span>
        </h3>

        <form method="POST" action="{{ route('donnee_ligne_entrees.store', $donnee->id) }}">
            @csrf

            <div id="elements-container"></div>

            <div class="mt-3">
                <button type="button" id="addRow" class="btn btn-info">➕ Ajouter une ligne</button>
                <button type="submit" class="btn btn-success">💾 Enregistrer</button>
                <a href="{{ route('donnee_entrees.index') }}" class="btn btn-secondary">↩️ Retour</a>
            </div>
        </form>



 Template invisible réutilisé pour chaque ligne

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

                <!-- Budget (fixe, hérité de la donnée parente) -->
                <div class="col-md-4 mb-3">
                    <label>Budget</label>
                    <select class="form-control select2" disabled>
                        <option value="{{ $donnee->id_budget }}" selected>
                            {{ $donnee->budgets->libelle_ligne_budget ?? 'Budget inconnu' }}
                        </option>
                    </select>
                    <input type="hidden" name="id_budget[]" value="{{ $donnee->id_budget }}">
                </div>

                <!-- Donnée budgétaire entrée (fixe) -->
                <div class="col-md-4 mb-3">
                    <label>Donnée budgétaire entrée</label>
                    <select class="form-control select2" disabled>
                        <option value="{{ $donnee->id }}" selected>
                            {{ $donnee->donnee_ligne_budgetaire_entree }}
                        </option>
                    </select>
                    <input type="hidden" name="id_donnee_budgetaire_entree[]" value="{{ $donnee->id }}">
                </div>

                <!-- Élément ligne budgétaire entrée (Ajax) -->
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
        $(document).ready(function () {
            function initSelect2(scope) {
                if ($.fn.select2) {
                    (scope ? $(scope) : $(document)).find('.select2').select2({ width: '100%' });
                }
            }

            function loadElements(donneeId, $elementSelect) {
                $elementSelect.empty().append('<option value="">Chargement...</option>');
                $.get("{{ url('/get-elements-by-donnee') }}/" + donneeId, function (data) {
                    $elementSelect.empty().append('<option value="">-- Sélectionner --</option>');
                    $.each(data, function (key, value) {
                        $elementSelect.append('<option value="'+ value.id +'">'+ value.libelle_elements_ligne_budgetaire_entree +'</option>');
                    });
                });
            }

            function addRow() {
                let tpl = document.getElementById('row-template');
                let $row = $(tpl.content.cloneNode(true));
                $('#elements-container').append($row);

                // Init select2
                let $lastRow = $('#elements-container .element-row').last();
                initSelect2($lastRow);

                // Charger les éléments par Ajax
                loadElements("{{ $donnee->id }}", $lastRow.find('.element-select'));
            }

            // Première ligne
            addRow();

            // Bouton ➕ Ajouter une ligne
            $('#addRow').on('click', function () {
                addRow();
            });
        });
    </script>
@endsection
--}}
@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>
            Ajouter des données ligne budgétaire pour :
            <span class="text-primary">{{ $donnee->donnee_ligne_budgetaire_entree }}</span>
        </h3>

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
