@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-4">
        <h3>Retour en caisse</h3>
        <p class="text-muted">
            Enregistrer le retour d'argent non utilisé sur un bon financé.
        </p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('retour_caisses.store') }}" class="card p-4 shadow-sm">
        @csrf

        <div class="row g-3">

            <div class="col-md-4">
                <label>Bon existant</label>
                <select name="id_bon_commande" id="id_bon_commande" class="form-control" required>
                    <option value="">Choisir</option>
                    @foreach($bons as $bon)
                        <option value="{{ $bon->id }}" {{ old('id_bon_commande') == $bon->id ? 'selected' : '' }}>
                            {{ $bon->nom_bon_commande }}
                            - {{ number_format($bon->montant_total, 0, ',', ' ') }} FCFA
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label>Décaissement</label>
                <select name="id_decaissement" id="id_decaissement" class="form-control" required>
                    <option value="">Choisir d'abord un bon</option>
                </select>
            </div>

            <div class="col-md-4">
                <label>Caisse centrale</label>
                <select name="id_caisse" class="form-control" required>
                    <option value="">Choisir</option>
                    @foreach($caisses as $caisse)
                        <option value="{{ $caisse->id }}" {{ old('id_caisse') == $caisse->id ? 'selected' : '' }}>
                            {{ $caisse->nom_caisse }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Champs cachés envoyés au controller --}}
            <input type="hidden" name="id_budget" id="id_budget" value="{{ old('id_budget') }}">
            <input type="hidden" name="id_ligne_budgetaire_sortie" id="id_ligne_budgetaire_sortie" value="{{ old('id_ligne_budgetaire_sortie') }}">
            <input type="hidden" name="id_elements_ligne_budgetaire_sortie" id="id_elements_ligne_budgetaire_sortie" value="{{ old('id_elements_ligne_budgetaire_sortie') }}">
            <input type="hidden" name="id_donnee_ligne_budgetaire_sortie" id="id_donnee_ligne_budgetaire_sortie" value="{{ old('id_donnee_ligne_budgetaire_sortie') }}">
            <input type="hidden" name="id_donnee_budgetaire_sortie" id="id_donnee_budgetaire_sortie" value="{{ old('id_donnee_budgetaire_sortie') }}">
            <input type="hidden" name="id_annee_academique" id="id_annee_academique" value="{{ old('id_annee_academique') }}">

            {{-- Affichage hiérarchique --}}
            <div class="col-md-4">
                <label>Budget</label>
                <input type="text" id="budget_libelle" class="form-control" readonly>
            </div>

            <div class="col-md-4">
                <label>Ligne budgétaire sortie</label>
                <input type="text" id="ligne_libelle" class="form-control" readonly>
            </div>

            <div class="col-md-4">
                <label>Élément</label>
                <input type="text" id="element_libelle" class="form-control" readonly>
            </div>

            <div class="col-md-4">
                <label>Donnée ligne budgétaire</label>
                <input type="text" id="donnee_ligne_libelle" class="form-control" readonly>
            </div>

            <div class="col-md-4">
                <label>Donnée budgétaire</label>
                <input type="text" id="donnee_budgetaire_libelle" class="form-control" readonly>
            </div>

            <div class="col-md-4">
                <label>Année académique</label>
                <input type="text" id="annee_libelle" class="form-control" readonly>
            </div>

            {{-- Montants --}}
            <div class="col-md-4">
                <label>Montant décaissé</label>
                <input type="text" id="montant_decaisse" class="form-control" readonly>
            </div>

            <div class="col-md-4">
                <label>Déjà retourné</label>
                <input type="text" id="montant_retourne" class="form-control" readonly>
            </div>

            <div class="col-md-4">
                <label>Reste à retourner</label>
                <input type="text" id="reste" class="form-control" readonly>
            </div>

            <div class="col-md-4">
                <label>Date retour</label>
                <input 
                    type="date" 
                    name="date_retour" 
                    value="{{ old('date_retour', now()->format('Y-m-d')) }}" 
                    class="form-control" 
                    required
                >
            </div>

            <div class="col-md-4">
                <label>Montant retourné</label>
                <input 
                    type="number" 
                    name="montant" 
                    id="montant" 
                    value="{{ old('montant') }}" 
                    class="form-control" 
                    min="1" 
                    required
                >
            </div>

            <div class="col-md-8">
                <label>Motif du retour</label>
                <input 
                    type="text" 
                    name="motif" 
                    value="{{ old('motif') }}" 
                    class="form-control" 
                    placeholder="Motif du retour en caisse"
                >
            </div>

            <div class="col-md-12 text-center mt-4">
                <button type="submit" class="btn btn-success">
                    Enregistrer
                </button>

                <a href="{{ route('retour_caisses.index') }}" class="btn btn-secondary">
                    Liste des retours
                </a>
            </div>
        </div>
    </form>
</div>
@endsection

@section('breadcrumb')
<ol class="breadcrumb" style="background-color: transparent; padding: 4px 10px">
    <li class="breadcrumb-item">
        <a href="{{ route('home') }}"><strong>Accueil</strong></a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('etat_sorties.index') }}"><strong>Etats sorties</strong></a>
    </li>
    <li class="breadcrumb-item active">
        <strong>Retour en caisse</strong>
    </li>
</ol>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const bonSelect = document.getElementById('id_bon_commande');
    const decaissementSelect = document.getElementById('id_decaissement');
    const montantInput = document.getElementById('montant');

    function formatMontant(value) {
        value = Number(value || 0);
        return value.toLocaleString('fr-FR') + ' FCFA';
    }

    function resetDetails() {
        document.getElementById('id_budget').value = '';
        document.getElementById('id_ligne_budgetaire_sortie').value = '';
        document.getElementById('id_elements_ligne_budgetaire_sortie').value = '';
        document.getElementById('id_donnee_ligne_budgetaire_sortie').value = '';
        document.getElementById('id_donnee_budgetaire_sortie').value = '';
        document.getElementById('id_annee_academique').value = '';

        document.getElementById('budget_libelle').value = '';
        document.getElementById('ligne_libelle').value = '';
        document.getElementById('element_libelle').value = '';
        document.getElementById('donnee_ligne_libelle').value = '';
        document.getElementById('donnee_budgetaire_libelle').value = '';
        document.getElementById('annee_libelle').value = '';

        document.getElementById('montant_decaisse').value = '';
        document.getElementById('montant_retourne').value = '';
        document.getElementById('reste').value = '';

        montantInput.removeAttribute('max');
    }

    bonSelect.addEventListener('change', function () {
        const bonId = this.value;

        resetDetails();
        decaissementSelect.innerHTML = '<option value="">Chargement...</option>';

        if (!bonId) {
            decaissementSelect.innerHTML = '<option value="">Choisir d’abord un bon</option>';
            return;
        }

        // fetch(`/retour-caisses/decaissements/${bonId}`)
        let url = "{{ route('retour_caisses.decaissements', ':id') }}";
        url = url.replace(':id', bonId);

         fetch(url)
            .then(response => response.json())
            .then(data => {
                decaissementSelect.innerHTML = '<option value="">Choisir un décaissement</option>';

                if (data.length === 0) {
                    decaissementSelect.innerHTML = '<option value="">Aucun décaissement trouvé</option>';
                    return;
                }

                data.forEach(item => {
                    decaissementSelect.innerHTML += `
                        <option value="${item.id}">
                            ${(item.motif ?? 'Décaissement')} - ${formatMontant(item.montant)}
                        </option>
                    `;
                });
            })
            .catch(() => {
                decaissementSelect.innerHTML = '<option value="">Erreur de chargement</option>';
            });
    });

    decaissementSelect.addEventListener('change', function () {
        const decaissementId = this.value;

        resetDetails();

        if (!decaissementId) {
            return;
        }
let url = "{{ route('retour_caisses.decaissement_details', ':id') }}";
url = url.replace(':id', decaissementId);

fetch(url)
        // fetch(`/retour-caisses/decaissement-details/${decaissementId}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('id_budget').value = data.id_budget ?? '';
                document.getElementById('id_ligne_budgetaire_sortie').value = data.id_ligne_budgetaire_sortie ?? '';
                document.getElementById('id_elements_ligne_budgetaire_sortie').value = data.id_elements_ligne_budgetaire_sortie ?? '';
                document.getElementById('id_donnee_ligne_budgetaire_sortie').value = data.id_donnee_ligne_budgetaire_sortie ?? '';
                document.getElementById('id_donnee_budgetaire_sortie').value = data.id_donnee_budgetaire_sortie ?? '';
                document.getElementById('id_annee_academique').value = data.id_annee_academique ?? '';

                document.getElementById('budget_libelle').value = data.budget_libelle ?? '';
                document.getElementById('ligne_libelle').value = data.ligne_libelle ?? '';
                document.getElementById('element_libelle').value = data.element_libelle ?? 'Aucun';
                document.getElementById('donnee_ligne_libelle').value = data.donnee_ligne_libelle ?? '';
                document.getElementById('donnee_budgetaire_libelle').value = data.donnee_budgetaire_libelle ?? 'Aucune';
                document.getElementById('annee_libelle').value = data.annee_libelle ?? '';

                document.getElementById('montant_decaisse').value = formatMontant(data.montant_decaisse);
                document.getElementById('montant_retourne').value = formatMontant(data.montant_retourne);
                document.getElementById('reste').value = formatMontant(data.reste);

                montantInput.setAttribute('max', data.reste);
            })
            .catch(() => {
                alert('Impossible de charger les détails du décaissement.');
            });
    });
});
</script>
@endsection


@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent; padding: 4px 10px">
        <li class="breadcrumb-item"><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li class="breadcrumb-item"><a href="{{ route('etat_sorties.index') }}"><strong>Etats sorties</strong></a></li>
        <li class="breadcrumb-item active"><strong>Retour en caisse</strong></li>
    </ol>
@endsection
 -->