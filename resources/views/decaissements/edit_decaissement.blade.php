@extends('layouts.app')

@section('content')
<div class="container">
    @include('decaissements.partials.navigation')

    <h3>Modifier le decaissement : {{ $decaissement->numero_depense }}</h3>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="alert alert-info">
        <strong>Bon :</strong> {{ $bon->nom_bon_commande }} - BC{{ $bon->id }}<br>
        <strong>Imputation DAF :</strong>
        {{ $bon->budget->libelle_ligne_budget ?? '-' }} /
        {{ $bon->ligne_budgetaire_sortie->libelle_ligne_budgetaire_sortie ?? '-' }} /
        {{ $bon->elements_ligne_budgetaire_sortie->libelle_elements_ligne_budgetaire_sortie ?? '-' }} /
        {{ $bon->annee_academique->nom ?? '-' }}.
        <br>
        Montant du bon : {{ number_format($bon->montant_total, 0, ',', ' ') }} FCFA.
        Maximum autorise pour cette correction : {{ number_format($resteDisponible, 0, ',', ' ') }} FCFA.
    </div>

    <form method="POST" action="{{ route('decaissements.update_decaissement', ['bon' => $bon->id, 'decaissement' => $decaissement->id]) }}">
        @csrf

        <input type="hidden" name="id_transfert_caisse" id="id_transfert_caisse" value="{{ old('id_transfert_caisse', $decaissement->id_transfert_caisse) }}">

        <div class="mb-3">
            <label>Type de paiement</label>
            <select id="type_paiement" class="form-control">
                <option value="caisse" {{ old('id_banque', $decaissement->id_banque) ? '' : 'selected' }}>Espece</option>
                <option value="banque" {{ old('id_banque', $decaissement->id_banque) ? 'selected' : '' }}>Banque</option>
            </select>
        </div>

        <div class="mb-3" id="caisse_bloc">
            <label>Caisse</label>
            <select id="caisse" name="id_caisse" class="form-control">
                <option value="">-- Choisir --</option>
                @foreach($caissest as $c)
                    <option value="{{ $c->id }}" {{ (string) old('id_caisse', $decaissement->id_caisse) === (string) $c->id ? 'selected' : '' }}>
                        {{ $c->nom_caisse }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3" id="banque_bloc">
            <label>Banque</label>
            <select name="id_banque" class="form-control">
                <option value="">-- Choisir --</option>
                @foreach(\App\Models\banque::orderBy('nom_banque')->get() as $b)
                    <option value="{{ $b->id }}" {{ (string) old('id_banque', $decaissement->id_banque) === (string) $b->id ? 'selected' : '' }}>
                        {{ $b->nom_banque }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="alert alert-info mt-2" id="solde_info">Solde : selectionner une caisse</div>

        <div class="mb-3">
            <label>Motif de decaissement</label>
            <input type="text" name="motif" class="form-control" value="{{ old('motif', $decaissement->motif) }}" required>
        </div>

        <div class="mb-3">
            <label>Montant</label>
            <input type="number" id="montant" name="montant" class="form-control" max="{{ $resteDisponible }}" value="{{ old('montant', $decaissement->montant) }}" required>
        </div>

        <div class="mb-3">
            <label>Date</label>
            <input type="date" name="date_depense" class="form-control" value="{{ old('date_depense', \Carbon\Carbon::parse($decaissement->date_depense)->format('Y-m-d')) }}" required>
        </div>

        <button type="submit" class="btn btn-primary">Enregistrer la modification</button>
        <a href="{{ route('decaissements.detailBon', $bon->id) }}" class="btn btn-secondary">Retour</a>
    </form>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let baseUrl = "{{ url('decaissements') }}";
    let ancienMontant = parseFloat("{{ (float) $decaissement->montant }}");
    let ancienneCaisse = "{{ (int) $decaissement->id_caisse }}";

    function syncPaiement() {
        if ($('#type_paiement').val() === 'banque') {
            $('#banque_bloc').show();
            $('#caisse_bloc').hide();
            $('#caisse').val('');
            $('#id_transfert_caisse').val('');
            $('#solde_info').html('Solde : paiement banque');
        } else {
            $('#banque_bloc').hide();
            $('#caisse_bloc').show();
        }
    }

    function loadCaisseData() {
        let id = $('#caisse').val();
        if (!id) return;

        $.get(baseUrl + '/ajax/transfert-caisse/' + id, function (data) {
            $('#id_transfert_caisse').val(data.id);
        });

        $.get(baseUrl + '/ajax/solde-caisse/' + id, function (data) {
            let solde = parseFloat(data.solde || 0);
            if (String(id) === String(ancienneCaisse)) {
                solde += ancienMontant;
            }
            $('#solde_info').html('Solde disponible : ' + solde);
            $('#montant').data('solde', solde);
        });
    }

    $('#type_paiement').change(syncPaiement);
    $('#caisse').change(loadCaisseData);

    $('#montant').on('keyup change', function () {
        let montant = parseFloat($(this).val());
        let solde = parseFloat($(this).data('solde') || 0);
        let reste = parseFloat($(this).attr('max') || 0);

        if ($('#type_paiement').val() === 'caisse' && solde > 0 && montant > solde) {
            alert('Fonds insuffisants');
            $(this).val('');
            return;
        }

        if (reste > 0 && montant > reste) {
            alert('Montant superieur au maximum autorise');
            $(this).val('');
        }
    });

    syncPaiement();
    if ($('#type_paiement').val() === 'caisse') {
        loadCaisseData();
    }
</script>
@endsection

@section('breadcrumb')
<ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
    <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
    <li><a href="{{ route('budget') }}"><strong>Budget</strong></a></li>
    <li><a href="{{ route('decaissements.index') }}"><strong>Decaissements</strong></a></li>
    <li class="active"><strong>Modifier decaissement</strong></li>
</ol>
@endsection
