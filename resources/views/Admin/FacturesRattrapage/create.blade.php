@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-primary">{{ $title }}</h3>
        <a href="{{ route('factures_by_etudiant', $sourceFacture->id_etudiant) }}" class="btn btn-secondary">Retour factures</a>
    </div>

    @if($programmes->isEmpty())
        <div class="alert alert-warning">
            Aucune matiere n'est encore affectee au programme de cette specialite. Cree d'abord le programme de specialite.
        </div>
    @endif

    <form method="POST" action="{{ route('factures_rattrapage.store') }}">
        @csrf
        <input type="hidden" name="id_facture_source" value="{{ $sourceFacture->id }}">

        <div class="card mb-3">
            <div class="card-header"><strong>Facture source</strong></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4"><strong>Etudiant :</strong> {{ $sourceFacture->etudiants->nom ?? '-' }}</div>
                    <div class="col-md-2"><strong>Facture :</strong> {{ $sourceFacture->numero_facture }}</div>
                    <div class="col-md-3"><strong>Annee :</strong> {{ $sourceFacture->Annee_academique->nom ?? '-' }}</div>
                    <div class="col-md-3"><strong>Entite :</strong> {{ $sourceFacture->entite->nom_entite ?? '-' }}</div>
                    <div class="col-md-4 mt-2"><strong>Specialite :</strong> {{ $sourceFacture->specialites->nom_specialite ?? '-' }}</div>
                    <div class="col-md-4 mt-2"><strong>Filiere :</strong> {{ $sourceFacture->filieres->nom_filiere ?? '-' }}</div>
                    <div class="col-md-4 mt-2"><strong>Niveau :</strong> {{ $sourceFacture->niveaux->nom_niveau ?? '-' }}</div>
                    <div class="col-md-12 mt-2">
                        <strong>Budget repris :</strong>
                        {{ $sourceFacture->budget->libelle_ligne_budget ?? '-' }} /
                        {{ $sourceFacture->ligne_budgetaire_entree->libelle_ligne_budgetaire_entree ?? '-' }} /
                        {{ $sourceFacture->element_ligne_budgetaire_entree->libelle_elements_ligne_budgetaire_entree ?? '-' }} /
                        {{ $sourceFacture->donnee_budgetaire_entree->donnee_ligne_budgetaire_entree ?? '-' }} /
                        {{ $sourceFacture->donnee_ligne_budgetaire_entree->donnee_ligne_budgetaire_entree ?? '-' }}
                    </div>
                    <div class="col-md-3 mt-3">
                        <label>Date facture rattrapage</label>
                        <input type="date" name="date_facture" class="form-control" value="{{ old('date_facture', date('Y-m-d')) }}" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Matieres du programme de specialite</strong>
                <button class="btn btn-success" {{ $programmes->isEmpty() ? 'disabled' : '' }}>Enregistrer la facture</button>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Rechercher une matiere</label>
                        <input type="text" id="matiere-search" class="form-control" placeholder="Nom, code, semestre, type">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Choisir</th>
                                <th>Matiere</th>
                                <th>Code specialite</th>
                                <th>Type</th>
                                <th>Semestre</th>
                                <th>Prix unitaire</th>
                                <th>Quantite</th>
                                <th>Montant</th>
                                <th>Observation</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($programmes as $i => $programme)
                                <tr class="matiere-row">
                                    <td>
                                        <input type="checkbox" name="matieres[{{ $i }}][selected]" value="1">
                                        <input type="hidden" name="matieres[{{ $i }}][id_programme_specialite]" value="{{ $programme->id }}">
                                    </td>
                                    <td>{{ $programme->matiere->nom_matiere ?? '-' }}</td>
                                    <td>{{ $programme->code_matiere_specialite }}</td>
                                    <td>{{ ucfirst($programme->type_matiere) }}</td>
                                    <td>{{ $programme->semestre }}</td>
                                    <td><input type="number" step="0.01" min="0" name="matieres[{{ $i }}][prix_unitaire]" class="form-control line-price"></td>
                                    <td><input type="number" min="1" name="matieres[{{ $i }}][quantite]" class="form-control line-qty" value="1"></td>
                                    <td><input type="text" class="form-control line-total" readonly value="0"></td>
                                    <td><input type="text" name="matieres[{{ $i }}][observation]" class="form-control"></td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="7" class="text-end">Total</th>
                                <th><input type="text" id="total" class="form-control" readonly value="0"></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="text-end">
            <button class="btn btn-success" {{ $programmes->isEmpty() ? 'disabled' : '' }}>Enregistrer la facture</button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
(function () {
    function recalc() {
        let total = 0;

        document.querySelectorAll('.matiere-row').forEach(function (row) {
            const price = parseFloat(row.querySelector('.line-price').value || 0);
            const qty = parseInt(row.querySelector('.line-qty').value || 0, 10);
            const lineTotal = price * qty;
            total += lineTotal;
            row.querySelector('.line-total').value = lineTotal.toLocaleString('fr-FR');
        });

        document.getElementById('total').value = total.toLocaleString('fr-FR');
    }

    document.querySelectorAll('.line-price, .line-qty').forEach(function (input) {
        input.addEventListener('input', recalc);
    });

    document.getElementById('matiere-search').addEventListener('input', function () {
        const term = this.value.toLowerCase();
        document.querySelectorAll('.matiere-row').forEach(function (row) {
            row.style.display = row.innerText.toLowerCase().includes(term) ? '' : 'none';
        });
    });
})();
</script>
@endsection

@section('breadcrumb')
<ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
    <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
    <li><a href="{{ route('factures_by_etudiant', $sourceFacture->id_etudiant) }}"><strong>Factures etudiant</strong></a></li>
    <li class="active"><strong>{{ $title }}</strong></li>
</ol>
@endsection
