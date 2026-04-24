{{--
@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>✏️ Modifier donnée ligne budgétaire sortie</h3>

        <form method="POST" action="{{ route('donnee_ligne_sorties.update', $ligne->id) }}">
            @csrf @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Libellé</label>
                    <input type="text" name="libelle" class="form-control" value="{{ $ligne->donnee_ligne_budgetaire_sortie }}" required>
                </div>

                <div class="col-md-3 mb-3">
                    <label>Code</label>
                    <input type="text" name="code" class="form-control" value="{{ $ligne->code_donnee_ligne_budgetaire_sortie }}" required>
                </div>

                <div class="col-md-3 mb-3">
                    <label>N° Compte</label>
                    <input type="text" name="compte" class="form-control" value="{{ $ligne->numero_donne_ligne_budgetaire_sortie }}" required>
                </div>

                <div class="col-md-12 mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control">{{ $ligne->description }}</textarea>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Date Création</label>
                    <input type="date" name="date_creation" class="form-control" value="{{ $ligne->date_creation }}" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Élément ligne budgétaire sortie</label>
                    <select name="id_element_ligne_budgetaire_sortie" class="form-control select2" required>
                        @foreach($elements as $el)
                            <option value="{{ $el->id }}" @if($ligne->id_element_ligne_budgetaire_sortie == $el->id) selected @endif>
                                {{ $el->libelle_elements_ligne_budgetaire_sortie }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-success">💾 Mettre à jour</button>
            <a href="{{ route('donnee_ligne_sorties.index', $ligne->id_donnee_budgetaire_sortie) }}" class="btn btn-secondary">↩️ Retour</a>
        </form>
    </div>
@endsection
--}}
@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>✏️ Modifier une donnée ligne budgétaire sortie</h3>

        <form method="POST" action="{{ route('donnee_ligne_sorties.update', $ligne->id) }}">
            @csrf
            @method('PUT')

            <div class="row border p-3 mb-3 rounded">

                <div class="col-md-6 mb-3">
                    <label>Libellé</label>
                    <input type="text" name="libelle" class="form-control" value="{{ $ligne->donnee_ligne_budgetaire_sortie }}" required>
                </div>

                <div class="col-md-3 mb-3">
                    <label>Code</label>
                    <input type="text" name="code" class="form-control" value="{{ $ligne->code_donnee_ligne_budgetaire_sortie }}" required>
                </div>

                <div class="col-md-3 mb-3">
                    <label>N° Compte</label>
                    <input type="text" name="compte" class="form-control" value="{{ $ligne->numero_donnee_ligne_budgetaire_sortie }}" required>
                </div>

                <div class="col-md-12 mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control">{{ $ligne->description }}</textarea>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Date Création</label>
                    <input type="date" name="date_creation" class="form-control" value="{{ $ligne->date_creation }}" required>
                </div>

                <!-- Budget -->
                <div class="col-md-4 mb-3">
                    <label>Budget</label>
                    <select name="id_budget" class="form-control select2" required>
                        <option value="">-- Sélectionner --</option>
                        @foreach($budgets as $b)
                            <option value="{{ $b->id }}" {{ $b->id == $ligne->id_budget ? 'selected' : '' }}>
                                {{ $b->libelle_ligne_budget }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Élément -->
                <div class="col-md-4 mb-3">
                    <label>Élément ligne budgétaire sortie</label>
                    <select name="id_element_ligne_budgetaire_sortie" id="element_select" class="form-control select2" required>
                        <option value="">-- Sélectionner --</option>
                        @foreach($elements as $el)
                            <option value="{{ $el->id }}" {{ $el->id == $ligne->id_element_ligne_budgetaire_sortie ? 'selected' : '' }}>
                                {{ $el->libelle_elements_ligne_budgetaire_sortie }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Montant</label>
                    <input type="number" step="0.01" name="montant" class="form-control" value="{{ $ligne->montant }}" required>
                </div>

            </div>

            <button type="submit" class="btn btn-success">💾 Mettre à jour</button>
            <a href="{{ route('donnee_ligne_sorties.index', $ligne->id_donnee_budgetaire_sortie) }}" class="btn btn-secondary">↩️ Retour</a>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('.select2').select2({width: '100%'});

            // Recharge dynamique des éléments si nécessaire
            $('select[name="id_budget"]').on('change', function () {
                let budgetId = $(this).val();
                let $elementSelect = $('#element_select');

                $elementSelect.html('<option value="">Chargement...</option>');
                if (budgetId) {
                    $.get("{{ url('/donnee_ligne_sorties/get-elements') }}/" + budgetId, function (data) {
                        $elementSelect.empty().append('<option value="">-- Sélectionner --</option>');
                        $.each(data, function (key, value) {
                            $elementSelect.append('<option value="'+ value.id +'">'+ value.libelle_elements_ligne_budgetaire_sortie +'</option>');
                        });
                    });
                }
            });
        });
    </script>
@endsection
@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('budget') }}"><strong>budget</strong></a></li>
        <li><a href="{{ route('donnee_sorties.index',$donnee->id)}}"><strong>Donnée Budgétaires sorties </strong></a></li>
        <li><a href="{{ route('donnee_ligne_sorties.index',$donnee->id)}}"><strong>Donnée ligne budgétaires sorties </strong></a></li>

        <li class="active"><strong>{{ $title }}</strong></li>
    </ol>
@endsection