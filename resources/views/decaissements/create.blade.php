@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Financer : {{ $bon->nom_bon_commande }}</h3>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="alert alert-info">
        <strong>Imputation DAF :</strong>
        {{ $bon->budget->libelle_ligne_budget ?? '-' }} /
        {{ $bon->ligne_budgetaire_sortie->libelle_ligne_budgetaire_sortie ?? '-' }} /
        {{ $bon->elements_ligne_budgetaire_sortie->libelle_elements_ligne_budgetaire_sortie ?? '-' }} /
        {{ $bon->annee_academique->nom ?? '-' }}.
        <br>
        Montant du bon : {{ number_format($bon->montant_total, 0, ',', ' ') }} FCFA.
        Reste a financer : {{ number_format($reste, 0, ',', ' ') }} FCFA.
    </div>

    <form method="POST" action="{{ route('decaissements.store') }}">
        @csrf

        <input type="hidden" name="id_bon_commande" value="{{ $bon->id }}">
        <input type="hidden" name="id_transfert_caisse" id="id_transfert_caisse">

        <div class="mb-3">
            <label>Type de paiement</label>
            <select id="type_paiement" class="form-control">
                <option value="caisse">Espece</option>
                <option value="banque">Banque</option>
            </select>
        </div>

        <div class="mb-3" id="caisse_bloc">
            <label>Caisse</label>
            <select id="caisse" name="id_caisse" class="form-control">
                <option value="">-- Choisir --</option>
                @foreach($caissest as $c)
                    <option value="{{ $c->id }}">{{ $c->nom_caisse }}</option>
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

        <div class="alert alert-info mt-2" id="solde_info">Solde : 0</div>

        <div class="mb-3">
            <label>Motif de decaissement</label>
            <input type="text" name="motif" class="form-control" value="{{ old('motif', $bon->nom_bon_commande) }}" required>
        </div>

        <div class="mb-3">
            <label>Montant</label>
            <input type="number" id="montant" name="montant" class="form-control" max="{{ $reste }}" value="{{ old('montant', $reste) }}" required>
        </div>

        <div class="mb-3">
            <label>Date</label>
            <input type="date" name="date_depense" class="form-control" value="{{ old('date_depense', now()->toDateString()) }}" required>
        </div>

        <button type="submit" class="btn btn-primary">Valider la sortie</button>
        <a href="{{ route('decaissements.index') }}" class="btn btn-secondary">Retour</a>
    </form>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let baseUrl = "{{ url('decaissements') }}";

    $('#type_paiement').change(function () {
        if ($(this).val() === 'banque') {
            $('#banque_bloc').show();
            $('#caisse_bloc').hide();
            $('#caisse').val('');
            $('#id_transfert_caisse').val('');
            $('#solde_info').html('Solde : paiement banque');
        } else {
            $('#banque_bloc').hide();
            $('#caisse_bloc').show();
        }
    });

    $('#caisse').change(function () {
        let id = $(this).val();
        if (!id) return;

        $.get(baseUrl + '/ajax/transfert-caisse/' + id, function (data) {
            $('#id_transfert_caisse').val(data.id);
        });

        $.get(baseUrl + '/ajax/solde-caisse/' + id, function (data) {
            $('#solde_info').html('Solde : ' + data.solde);
            $('#montant').data('solde', data.solde);
        });
    });

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
            alert('Montant superieur au reste a financer');
            $(this).val('');
        }
    });
</script>
@endsection

@section('breadcrumb')
<ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
    <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
    <li><a href="{{ route('budget') }}"><strong>Budget</strong></a></li>
    <li><a href="{{ route('decaissements.index') }}"><strong>Decaissements</strong></a></li>
</ol>
@endsection
