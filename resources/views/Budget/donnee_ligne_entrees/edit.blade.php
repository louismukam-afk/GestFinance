{{--
@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>✏️ Modifier la donnée ligne budgétaire</h3>

        <form method="POST" action="{{ route('donnee_ligne_entrees.update', $donneeLigne->id) }}">
            @csrf
            @method('PUT')

            <div class="row border p-3 mb-3">
                <div class="col-md-6 mb-3">
                    <label>Libellé</label>
                    <input type="text" name="libelle" value="{{ $donneeLigne->donnee_ligne_budgetaire_entree }}" class="form-control" required>
                </div>

                <div class="col-md-3 mb-3">
                    <label>Code</label>
                    <input type="text" name="code" value="{{ $donneeLigne->code_donnee_ligne_budgetaire_entree }}" class="form-control" required>
                </div>

                <div class="col-md-3 mb-3">
                    <label>N° Compte</label>
                    <input type="text" name="compte" value="{{ $donneeLigne->numero_donne_ligne_budgetaire_entree }}" class="form-control" required>
                </div>

                <div class="col-md-12 mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control">{{ $donneeLigne->description }}</textarea>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Date Création</label>
                    <input type="date" name="date_creation" value="{{ $donneeLigne->date_creation }}" class="form-control" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Donnée budgétaire entrée</label>
                    <select name="id_donnee_budgetaire_entree" id="donnee_select" class="form-control select2" required>
                        @foreach($donnees as $d)
                            <option value="{{ $d->id }}" {{ $donneeLigne->id_donnee_budgetaire_entree == $d->id ? 'selected' : '' }}>
                                {{ $d->donnee_ligne_budgetaire_entree }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Élément ligne budgétaire entrée</label>
                    <select name="id_element_ligne_budgetaire_entree" id="element_select" class="form-control select2" required>
                        @foreach($elements as $e)
                            <option value="{{ $e->id }}" {{ $donneeLigne->id_element_ligne_budgetaire_entree == $e->id ? 'selected' : '' }}>
                                {{ $e->libelle_elements_ligne_budgetaire_entree }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Montant</label>
                    <input type="number" step="0.01" name="montant" value="{{ $donneeLigne->montant }}" class="form-control" required>
                </div>
            </div>

            <button type="submit" class="btn btn-success">💾 Enregistrer</button>
            <a href="{{ route('donnee_entrees.index') }}" class="btn btn-secondary">↩️ Retour</a>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('.select2').select2({ width: '100%' });

            $('#donnee_select').on('change', function() {
                let donneeId = $(this).val();
                let elementSelect = $('#element_select');
                elementSelect.empty().append('<option value="">Chargement...</option>');

                if (donneeId) {
                    $.get("{{ url('/get-elements-by-donnee') }}/" + donneeId, function(data) {
                        elementSelect.empty().append('<option value="">-- Sélectionner --</option>');
                        $.each(data, function(key, value) {
                            elementSelect.append('<option value="'+ value.id +'">'+ value.libelle_elements_ligne_budgetaire_entree +'</option>');
                        });
                    });
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
            Modifier une donnée ligne budgétaire :
            <span class="text-primary">{{ $donnee->donnee_ligne_budgetaire_entree }}</span>
        </h3>

        <form method="POST" action="{{ route('donnee_ligne_entrees.update', $ligne->id) }}">
            @csrf
            @method('PUT')

            <div class="row element-row border p-3 mb-3">

                <!-- Libellé -->
                <div class="col-md-6 mb-3">
                    <label>Libellé</label>
                    <input type="text" name="libelle" value="{{ $ligne->donnee_ligne_budgetaire_entree }}" class="form-control" required>
                </div>

                <!-- Code -->
                <div class="col-md-3 mb-3">
                    <label>Code</label>
                    <input type="text" name="code" value="{{ $ligne->code_donnee_ligne_budgetaire_entree }}" class="form-control" required>
                </div>

                <!-- Compte -->
                <div class="col-md-3 mb-3">
                    <label>N° Compte</label>
                    <input type="text" name="compte" value="{{ $ligne->numero_donne_ligne_budgetaire_entree }}" class="form-control" required>
                </div>

                <!-- Description -->
                <div class="col-md-12 mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control">{{ $ligne->description }}</textarea>
                </div>

                <!-- Date -->
                <div class="col-md-4 mb-3">
                    <label>Date Création</label>
                    <input type="date" name="date_creation" value="{{ $ligne->date_creation }}" class="form-control" required>
                </div>

                <!-- Budget -->
                <div class="col-md-4 mb-3">
                    <label>Budget</label>
                    <select name="id_budget" class="form-control select2" required>
                        <option value="">-- Sélectionner --</option>
                        @foreach($budgets as $b)
                            <option value="{{ $b->id }}" {{ $ligne->id_budget == $b->id ? 'selected' : '' }}>
                                {{ $b->libelle_ligne_budget }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Donnée budgétaire entrée -->
                <div class="col-md-4 mb-3">
                    <label>Donnée budgétaire entrée</label>
                    <select name="id_donnee_budgetaire_entree" class="form-control donnee-select select2" required>
                        <option value="">-- Sélectionner --</option>
                        <option value="{{ $donnee->id }}" selected>
                            {{ $donnee->donnee_ligne_budgetaire_entree }}
                        </option>
                    </select>
                </div>

                <!-- Élément ligne budgétaire entrée -->
                <div class="col-md-4 mb-3">
                    <label>Élément ligne budgétaire entrée</label>
                    <select name="id_element_ligne_budgetaire_entree" class="form-control element-select select2" required>
                        <option value="">-- Sélectionner --</option>
                        @foreach($elements as $e)
                            <option value="{{ $e->id }}" {{ $ligne->id_element_ligne_budgetaire_entree == $e->id ? 'selected' : '' }}>
                                {{ $e->libelle_elements_ligne_budgetaire_entree }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Montant -->
                <div class="col-md-4 mb-3">
                    <label>Montant</label>
                    <input type="number" step="0.01" name="montant" value="{{ $ligne->montant }}" class="form-control" required>
                </div>
            </div>

            <button type="submit" class="btn btn-success">💾 Mettre à jour</button>
            <a href="{{ route('donnee_ligne_entrees.index', $donnee->id) }}" class="btn btn-secondary">↩️ Retour</a>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('.select2').select2({ width: '100%' });

            // Charger dynamiquement les éléments si on change la donnée
            $(document).on('change', '.donnee-select', function() {
                let donneeId = $(this).val();
                let elementSelect = $('.element-select');

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
@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>
            ✏️ Modifier la donnée ligne budgétaire :
            <span class="text-primary">{{ $ligne->donnee_ligne_budgetaire_entree ?? 'N/A' }}</span>
        </h3>

        <form method="POST" action="{{ route('donnee_ligne_entrees.update', $ligne->id) }}">
            @csrf
            @method('PUT')

            <div class="row element-row border p-3 mb-3 rounded">
                <div class="col-md-6 mb-3">
                    <label>Libellé</label>
                    <input type="text" name="libelle" class="form-control" value="{{ $ligne->donnee_ligne_budgetaire_entree ?? '' }}" required>
                </div>

                <div class="col-md-3 mb-3">
                    <label>Code</label>
                    <input type="text" name="code" class="form-control" value="{{ $ligne->code_donnee_ligne_budgetaire_entree ?? '' }}" required>
                </div>

                <div class="col-md-3 mb-3">
                    <label>N° Compte</label>
                    <input type="text" name="compte" class="form-control" value="{{ $ligne->numero_donne_ligne_budgetaire_entree ?? '' }}" required>
                </div>

                <div class="col-md-12 mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control">{{ $ligne->description ?? '' }}</textarea>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Date Création</label>
                    <input type="date" name="date_creation" class="form-control"
                           value="{{ $ligne->date_creation ?? now()->toDateString() }}" required>
                </div>

                <!-- Budget (fixe) -->
                <div class="col-md-4 mb-3">
                    <label>Budget</label>
                    <select class="form-control select2" disabled>
                        <option value="{{ $ligne->id_budget }}" selected>
                            {{ $ligne->budget->libelle_ligne_budget ?? 'Budget inconnu' }}
                        </option>
                    </select>
                    <input type="hidden" name="id_budget" value="{{ $ligne->id_budget }}">
                </div>

                <!-- Donnée budgetaire entrée (fixe) -->
                <div class="col-md-4 mb-3">
                    <label>Donnée budgétaire entrée</label>
                    <select class="form-control select2" disabled>
                        <option value="{{ $ligne->id_donnee_budgetaire_entree }}" selected>
                            {{ $ligne->donnee_budgetaire_entrees->donnee_ligne_budgetaire_entree ?? 'Donnée inconnue' }}
                        </option>
                    </select>
                    <input type="hidden" name="id_donnee_budgetaire_entree" value="{{ $ligne->id_donnee_budgetaire_entree }}">
                </div>

                <!-- Élément ligne budgétaire entrée (Ajax) -->
                <div class="col-md-4 mb-3">
                    <label>Élément ligne budgétaire entrée</label>
                    <select name="id_element_ligne_budgetaire_entree" class="form-control element-select select2" required>
                        <option value="">Chargement...</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Montant</label>
                    <input type="number" step="0.01" name="montant" class="form-control"
                           value="{{ $ligne->montant ?? 0 }}" required>
                </div>
            </div>

            <button type="submit" class="btn btn-success">💾 Mettre à jour</button>
            <a href="{{ route('donnee_entrees.index') }}" class="btn btn-secondary">↩️ Retour</a>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        $(function () {
            const ELEMENTS_URL = @json(route('donnee_ligne_entrees.getElements', $ligne->id_donnee_budgetaire_entree));

            function initSelect2(scope) {
                if ($.fn.select2) {
                    (scope ? $(scope) : $(document)).find('.select2').select2({ width: '100%' });
                }
            }

            function loadElements($elementSelect, selectedId = null) {
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
                        if (selectedId) {
                            $elementSelect.val(selectedId).trigger('change');
                        }
                    },
                    error: function (xhr, status, err) {
                        console.error('AJAX error:', status, err, xhr.responseText);
                        $elementSelect.html('<option value="">Erreur de chargement</option>');
                    }
                });
            }

            initSelect2();
            // Charger les éléments avec sélection de celui du $ligne
            loadElements($('.element-select'), "{{ $ligne->id_element_ligne_budgetaire_entree }}");
        });
    </script>
@endsection
@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('budget') }}"><strong>budget</strong></a></li>
        <li><a href="{{ route('donnee_entrees.index',$donnee->id)}}"><strong>Donnée Budgétaires entrées </strong></a></li>
        <li><a href="{{ route('donnee_ligne_entrees.index',$donnee->id)}}"><strong>Donnée ligne budgétaires entrées </strong></a></li>

        <li class="active"><strong>{{ $title }}</strong></li>
    </ol>
@endsection