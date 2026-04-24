@extends('layouts.app')

@section('content')

    <div class="container">

        <h3>💰 Financer : {{ $bon->nom_bon_commande }}</h3>

        <form method="POST" action="{{ route('decaissements.store') }}">
            @csrf

            <input type="hidden" name="id_bon_commande" value="{{ $bon->id }}">
            <input type="hidden" name="id_transfert_caisse" id="id_transfert_caisse">
            <div class="mb-3">
                <label>Type de paiement</label>
                <select id="type_paiement" class="form-control">
                    <option value="caisse">Espèce</option>
                    <option value="banque">Banque</option>
                </select>
            </div>


            <div class="mb-3">
                <label>Caisse</label>
                <select id="caisse" name="id_caisse" class="form-control">
                    <option value="">-- Choisir --</option>
                    @foreach($caissest as $c)
                        <option value="{{ $c->id }}">
                            {{ $c->nom_caisse }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div id="banque_bloc" style="display:none;">
                <label>Banque</label>
                <select name="id_banque" class="form-control">
                    @foreach(\App\Models\banque::all() as $b)
                        <option value="{{ $b->id }}">{{ $b->nom_banque }}</option>
                    @endforeach
                </select>
            </div>

            <div class="alert alert-info mt-2" id="solde_info">
                Solde : 0
            </div>
            <div class="mb-3">
                <label>Motif de decaissement</label>
                <input type="text" name="motif" class="form-control" required>
            </div>
            <!-- MONTANT -->
            <div class="mb-3">
                <label>Montant</label>
                <input type="number" id="montant" name="montant" class="form-control">
            </div>
            <div class="mb-3">
                <label>Date</label>
                <input type="date" name="date_depense" class="form-control" required>
            </div>
            <!-- BUDGET -->
            <div class="mb-3">
                <label>Budget</label>
                <select id="budget" name="id_budget" class="form-control" required>
                    <option value="">-- Choisir --</option>
                    @foreach($budgets as $b)
                        <option value="{{ $b->id }}">{{ $b->libelle_ligne_budget }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label>Année académique</label>
                <select name="id_annee_academique" class="form-control" required>
                    <option value="">-- Choisir --</option>
                    @foreach($annees as $a)
                        <option value="{{ $a->id }}">{{ $a->nom }}</option>
                    @endforeach
                </select>
            </div>
            <!-- LIGNE -->
            <div class="mb-3">
                <label>Ligne budgétaire</label>
                <select id="ligne" name="id_ligne_budgetaire_sortie" class="form-control" required></select>
            </div>

            <!-- ELEMENT -->
            <div class="mb-3">
                <label>Élément</label>
                <select id="element" name="id_elements_ligne_budgetaire_sortie" class="form-control" required></select>
            </div>

            <!-- DONNEE BUDGET -->
            <div class="mb-3">
                <label>Donnée budgétaire</label>
                <select id="donnee_budget" name="id_donnee_budgetaire_sortie" class="form-control" required></select>
            </div>

            <!-- DONNEE LIGNE -->
            <div class="mb-3">
                <label>Donnée ligne</label>
                <select id="donnee_ligne" name="id_donnee_ligne_budgetaire_sortie" class="form-control" required></select>
            </div>

            <!-- CAISSE -->
          {{--  <div class="mb-3">
                <label>Caisse</label>
                <select name="id_transfert_caisse" class="form-control">
                    @foreach($caisses as $c)
                        <option value="{{ $c->id }}">
                            {{ $c->code_transfert }} ({{ $c->sode_caisse }})
                        </option>
                    @endforeach
                </select>
            </div>--}}


            <button type="submit" class="btn btn-primary">
                Valider
            </button>

        </form>

    </div>

@endsection
@section('scripts')

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>

        let baseUrl = "{{ url('decaissements') }}";

        // ==========================
        // TYPE PAIEMENT
        // ==========================
        $('#type_paiement').change(function(){
            if($(this).val() === 'banque'){
                $('#banque_bloc').show();
                $('#caisse').val('');
            } else {
                $('#banque_bloc').hide();
            }
        });

        // ==========================
        // SOLDE CAISSE
        // ==========================
        $('#caisse').change(function(){

            let id = $(this).val();

            if(!id) return;
            // 🔥 TRANSFERT
            $.get(baseUrl + '/ajax/transfert-caisse/' + id, function(data){

                $('#id_transfert_caisse').val(data.id);

            });
            $.get(baseUrl + '/ajax/solde-caisse/' + id, function(data){

                $('#solde_info').html("Solde : " + data.solde);

                $('#montant').data('solde', data.solde);

            });

        });

        // ==========================
        // VALIDATION MONTANT
        // ==========================
        $('#montant').on('keyup', function(){

            let montant = parseFloat($(this).val());
            let solde = parseFloat($(this).data('solde') || 0);

            if(montant > solde){
                alert('❌ Fonds insuffisants');
                $(this).val('');
            }

        });

        // ==========================
        // BUDGET → LIGNE
        // ==========================
        $('#budget').on('change', function(){

            let budget = $(this).val();

            $('#ligne').html('<option value="">-- Choisir --</option>');
            $('#element').html('<option value="">-- Choisir --</option>');
            $('#donnee_budget').html('<option value="">-- Choisir --</option>');
            $('#donnee_ligne').html('<option value="">-- Choisir --</option>');

            if(!budget) return;

            $.get(baseUrl + '/ajax/lignes/' + budget, function(data){

                data.forEach(e => {
                    $('#ligne').append(
                    `<option value="${e.id}">${e.libelle_ligne_budgetaire_sortie}</option>`
                );
            });

            });

        });

        // ==========================
        // LIGNE → ELEMENT + DONNEE
        // ==========================
        $('#ligne').on('change', function(){

            let ligne = $(this).val();

            $('#element').html('<option value="">-- Choisir --</option>');
            $('#donnee_budget').html('<option value="">-- Choisir --</option>');
            $('#donnee_ligne').html('<option value="">-- Choisir --</option>');

            if(!ligne) return;

            $.get(baseUrl + '/ajax/elements/' + ligne, function(data){
                data.forEach(e => {
                    $('#element').append(`<option value="${e.id}">${e.libelle_elements_ligne_budgetaire_sortie}</option>`);
            });
            });

            $.get(baseUrl + '/ajax/donnees-budget/' + ligne, function(data){
                data.forEach(e => {
                    $('#donnee_budget').append(`<option value="${e.id}">${e.donnee_ligne_budgetaire_sortie}</option>`);
            });
            });

        });

        // ==========================
        // ELEMENT → DONNEE LIGNE
        // ==========================
        $('#element').on('change', function(){

            let element = $(this).val();

            $('#donnee_ligne').html('<option value="">-- Choisir --</option>');

            if(!element) return;

            $.get(baseUrl + '/ajax/donnees-ligne/' + element, function(data){

                data.forEach(e => {
                    $('#donnee_ligne').append(
                    `<option value="${e.id}">
                    ${e.donnee_ligne_budgetaire_sortie} (${e.montant})
                </option>`
                );
            });

            });

        });
/*
        let baseUrl = "{{ url('decaissements') }}";

        // ==========================
        // BUDGET → LIGNE
        // ==========================
        $('#budget').on('change', function(){

            let budget = $(this).val();


            // SWITCH caisse / banque
            $('#type_paiement').change(function(){
                if($(this).val() === 'banque'){
                    $('#banque_bloc').show();
                } else {
                    $('#banque_bloc').hide();
                }
            });
// CHARGEMENT SOLDE
            $('#caisse').change(function(){

                let id = $(this).val();

                if(!id) return;

                $.get(baseUrl + '/ajax/solde-caisse/' + id, function(data){
                    console.log(data); // 🔥 DEBUG
                    $('#solde_info').html("Solde : " + data.solde);

                    $('#montant').data('solde', data.solde);

                }).fail(function(){
                    alert('Erreur récupération solde');
                });

            });
            // VALIDATION MONTANT
            $('#montant').on('keyup', function(){

                let montant = parseFloat($(this).val());
                let solde = parseFloat($(this).data('solde') || 0);

                if(montant > solde){
                    alert('❌ Fonds insuffisants');
                    $(this).val('');
                }

            });
            // RESET
            $('#ligne').html('<option value="">-- Choisir --</option>');
            $('#element').html('<option value="">-- Choisir --</option>');
            $('#donnee_budget').html('<option value="">-- Choisir --</option>');
            $('#donnee_ligne').html('<option value="">-- Choisir --</option>');

            if(!budget) return;

            $.get(baseUrl + '/ajax/lignes/' + budget, function(data){

                data.forEach(e => {
                    $('#ligne').append(
                    `<option value="${e.id}">${e.libelle_ligne_budgetaire_sortie}</option>`
                );
            });

            }).fail(function(){
                alert('Erreur chargement lignes');
            });

        });


        // ==========================
        // LIGNE → ELEMENT + DONNEE BUDGET
        // ==========================
        $('#ligne').on('change', function(){

            let ligne = $(this).val();

            $('#element').html('<option value="">-- Choisir --</option>');
            $('#donnee_budget').html('<option value="">-- Choisir --</option>');
            $('#donnee_ligne').html('<option value="">-- Choisir --</option>');

            if(!ligne) return;

            // ELEMENTS
            $.get(baseUrl + '/ajax/elements/' + ligne, function(data){

                data.forEach(e => {
                    $('#element').append(
                    `<option value="${e.id}">${e.libelle_elements_ligne_budgetaire_sortie}</option>`
                );
            });

            });

            // DONNEES BUDGET
            $.get(baseUrl + '/ajax/donnees-budget/' + ligne, function(data){

                data.forEach(e => {
                    $('#donnee_budget').append(
                    `<option value="${e.id}">${e.donnee_ligne_budgetaire_sortie}</option>`
                );
            });

            });

        });


        // ==========================
        // ELEMENT → DONNEE LIGNE
        // ==========================
        $('#element').on('change', function(){

            let element = $(this).val();

            $('#donnee_ligne').html('<option value="">-- Choisir --</option>');

            if(!element) return;

            $.get(baseUrl + '/ajax/donnees-ligne/' + element, function(data){

                data.forEach(e => {
                    $('#donnee_ligne').append(
                    `<option value="${e.id}">
                    ${e.donnee_ligne_budgetaire_sortie} (${e.montant})
                </option>`
                );
            });

            });

        });*/

    </script>

@endsection
@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('budget') }}"><strong>budget</strong></a></li>
        <li><a href="{{ route('decaissements.index') }}"><strong>Decaissements</strong></a></li>
        {{--<li><a href="{{ route('budget') }}"><strong>budget</strong></a></li>--}}

        {{--<li class="active"><strong>{{ $title }}</strong></li>--}}
    </ol>
@endsection
