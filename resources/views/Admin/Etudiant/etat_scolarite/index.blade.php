@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-4">
        <h3>Etat des inscriptions et paiements de scolarite</h3>
        <p class="text-muted">Filtrage par specialite, niveau, cycle, filiere, tranche, annee scolaire, entite et budget.</p>
    </div>

    <form id="filterForm" class="row g-3 mb-4">
        <div class="col-md-3">
            <label>Type d'etat</label>
            <select name="type_rapport" class="form-control">
                <option value="inscrits">Etudiants inscrits</option>
                <option value="tranche_paye">Ont paye une tranche</option>
                <option value="tranche_non_paye">N'ont pas paye une tranche</option>
                <option value="scolarite_payee">Ont paye toute la scolarite</option>
                <option value="scolarite_non_payee">N'ont pas paye toute la scolarite</option>
            </select>
        </div>
        <div class="col-md-2">
            <label>Date debut</label>
            <input type="date" name="date_debut" class="form-control">
        </div>
        <div class="col-md-2">
            <label>Date fin</label>
            <input type="date" name="date_fin" class="form-control">
        </div>
        <div class="col-md-2">
            <label>Annee scolaire</label>
            <select name="id_annee_academique" class="form-control">
                <option value="">Toutes</option>
                @foreach($annees as $annee)
                    <option value="{{ $annee->id }}">{{ $annee->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label>Entite</label>
            <select name="id_entite" class="form-control">
                <option value="">Toutes</option>
                @foreach($entites as $entite)
                    <option value="{{ $entite->id }}">{{ $entite->nom_entite }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label>Cycle</label>
            <select name="id_cycle" id="id_cycle" class="form-control">
                <option value="">Tous</option>
                @foreach($cycles as $cycle)
                    <option value="{{ $cycle->id }}">{{ $cycle->nom_cycle }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label>Filiere</label>
            <select name="id_filiere" id="id_filiere" class="form-control">
                <option value="">Toutes</option>
                @foreach($filieres as $filiere)
                    <option value="{{ $filiere->id }}">{{ $filiere->nom_filiere }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label>Niveau</label>
            <select name="id_niveau" id="id_niveau" class="form-control">
                <option value="">Tous</option>
                @foreach($niveaux as $niveau)
                    <option value="{{ $niveau->id }}">{{ $niveau->nom_niveau }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label>Specialite</label>
            <select name="id_specialite" id="id_specialite" class="form-control">
                <option value="">Toutes</option>
                @foreach($specialites as $specialite)
                    <option value="{{ $specialite->id }}">{{ $specialite->nom_specialite }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label>Scolarite</label>
            <select name="id_scolarite" id="id_scolarite" class="form-control">
                <option value="">Toutes</option>
                @foreach($scolarites as $scolarite)
                    <option value="{{ $scolarite->id }}">
                        {{ $scolarite->cycles->nom_cycle ?? '' }} {{ $scolarite->filiere->nom_filiere ?? '' }} {{ $scolarite->niveaux->nom_niveau ?? '' }} {{ $scolarite->specialites->nom_specialite ?? '' }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label>Tranche</label>
            <select name="id_tranche_scolarite" id="id_tranche_scolarite" class="form-control">
                <option value="">Toutes</option>
                @foreach($tranches as $tranche)
                    <option value="{{ $tranche->id }}">{{ $tranche->nom_tranche }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label>Budget</label>
            <select name="id_budget" id="id_budget" class="form-control">
                <option value="">Tous</option>
                @foreach($budgets as $budget)
                    <option value="{{ $budget->id }}">{{ $budget->libelle_ligne_budget }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label>Ligne budgetaire</label>
            <select name="id_ligne_budgetaire_entree" id="id_ligne_budgetaire_entree" class="form-control">
                <option value="">Toutes</option>
            </select>
        </div>

        <div class="col-md-4">
            <label>Element</label>
            <select name="id_element_ligne_budgetaire_entree" id="id_element_ligne_budgetaire_entree" class="form-control">
                <option value="">Tous</option>
            </select>
        </div>
        <div class="col-md-4">
            <label>Donnee budgetaire</label>
            <select name="id_donnee_budgetaire_entree" id="id_donnee_budgetaire_entree" class="form-control">
                <option value="">Toutes</option>
            </select>
        </div>
        <div class="col-md-4">
            <label>Donnee ligne</label>
            <select name="id_donnee_ligne_budgetaire_entree" id="id_donnee_ligne_budgetaire_entree" class="form-control">
                <option value="">Toutes</option>
            </select>
        </div>

        <div class="col-md-12 text-center mt-3">
            <button type="submit" class="btn btn-primary">Afficher</button>
            <button type="button" id="resetBtn" class="btn btn-secondary">Reset</button>
            <a href="#" id="pdfBtn" class="btn btn-danger">PDF</a>
            <a href="#" id="excelBtn" class="btn btn-success">Excel</a>
        </div>
    </form>

    <div id="loading" class="alert alert-info" style="display:none;">Chargement des donnees...</div>
    <div id="resultZone"></div>
</div>
@endsection

@section('breadcrumb')
<ol class="breadcrumb" style="background-color: transparent; padding: 4px 10px">
    <li class="breadcrumb-item"><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
    <li class="breadcrumb-item"><a href="{{ route('etudiant') }}"><strong>Gestion des etudiants</strong></a></li>
    <li class="breadcrumb-item active"><strong>Etat scolarite</strong></li>
</ol>
@endsection

@section('scripts')
<script>
$(function () {
    const form = $('#filterForm');
    const resultZone = $('#resultZone');
    const loading = $('#loading');

    function queryString() {
        return form.serialize();
    }

    function updateExportLinks() {
        $('#pdfBtn').attr('href', "{{ route('etat_etudiants_scolarite.pdf') }}?" + queryString());
        $('#excelBtn').attr('href', "{{ route('etat_etudiants_scolarite.excel') }}?" + queryString());
    }

    function loadData() {
        loading.show();
        updateExportLinks();

        $.get("{{ route('etat_etudiants_scolarite.data') }}", queryString())
            .done(function (html) {
                resultZone.html(html);
            })
            .fail(function () {
                resultZone.html('<div class="alert alert-danger">Impossible de charger les donnees.</div>');
            })
            .always(function () {
                loading.hide();
            });
    }

    function fillSelect(selector, rows, valueKey, labelKey, placeholder) {
        const select = $(selector);
        select.html('<option value="">' + placeholder + '</option>');
        rows.forEach(function (row) {
            select.append('<option value="' + row[valueKey] + '">' + row[labelKey] + '</option>');
        });
    }

    form.on('submit', function (e) {
        e.preventDefault();
        loadData();
    });

    $('#resetBtn').on('click', function () {
        form[0].reset();
        $('#id_ligne_budgetaire_entree, #id_element_ligne_budgetaire_entree, #id_donnee_budgetaire_entree, #id_donnee_ligne_budgetaire_entree').html('<option value="">Toutes</option>');
        loadData();
    });

    $('#id_budget').on('change', function () {
        const id = $(this).val();
        $('#id_ligne_budgetaire_entree, #id_element_ligne_budgetaire_entree, #id_donnee_budgetaire_entree, #id_donnee_ligne_budgetaire_entree').html('<option value="">Toutes</option>');
        if (!id) return;

        $.get("{{ url('etat-etudiants-scolarite/ajax/budget') }}/" + id + "/lignes", function (rows) {
            fillSelect('#id_ligne_budgetaire_entree', rows, 'id', 'libelle_ligne_budgetaire_entree', 'Toutes');
        });
    });

    $('#id_ligne_budgetaire_entree').on('change', function () {
        const id = $(this).val();
        $('#id_element_ligne_budgetaire_entree, #id_donnee_budgetaire_entree, #id_donnee_ligne_budgetaire_entree').html('<option value="">Toutes</option>');
        if (!id) return;

        $.get("{{ url('etat-etudiants-scolarite/ajax/ligne') }}/" + id + "/elements", function (rows) {
            fillSelect('#id_element_ligne_budgetaire_entree', rows, 'id', 'libelle_elements_ligne_budgetaire_entree', 'Tous');
        });

        $.get("{{ url('etat-etudiants-scolarite/ajax/ligne') }}/" + id + "/donnees-budget", function (rows) {
            fillSelect('#id_donnee_budgetaire_entree', rows, 'id', 'donnee_ligne_budgetaire_entree', 'Toutes');
        });
    });

    $('#id_element_ligne_budgetaire_entree').on('change', function () {
        const id = $(this).val();
        $('#id_donnee_ligne_budgetaire_entree').html('<option value="">Toutes</option>');
        if (!id) return;

        $.get("{{ url('etat-etudiants-scolarite/ajax/element') }}/" + id + "/donnees", function (rows) {
            fillSelect('#id_donnee_ligne_budgetaire_entree', rows, 'id', 'donnee_ligne_budgetaire_entree', 'Toutes');
        });
    });

    $('#id_cycle, #id_filiere, #id_niveau, #id_specialite').on('change', function () {
        $.get("{{ route('etat_etudiants_scolarite.ajax.scolarites') }}", {
            id_cycle: $('#id_cycle').val(),
            id_filiere: $('#id_filiere').val(),
            id_niveau: $('#id_niveau').val(),
            id_specialite: $('#id_specialite').val()
        }, function (rows) {
            const select = $('#id_scolarite');
            select.html('<option value="">Toutes</option>');
            rows.forEach(function (row) {
                select.append('<option value="' + row.id + '">Scolarite ' + row.id + ' - ' + Number(row.montant_total).toLocaleString() + ' FCFA</option>');
            });
            $('#id_tranche_scolarite').html('<option value="">Toutes</option>');
        });
    });

    $('#id_scolarite').on('change', function () {
        const id = $(this).val();
        $('#id_tranche_scolarite').html('<option value="">Toutes</option>');
        if (!id) return;

        $.get("{{ url('etat-etudiants-scolarite/ajax/scolarite') }}/" + id + "/tranches", function (rows) {
            fillSelect('#id_tranche_scolarite', rows, 'id', 'nom_tranche', 'Toutes');
        });
    });

    loadData();
});
</script>
@endsection
