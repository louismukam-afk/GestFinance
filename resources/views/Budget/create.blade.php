@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card shadow">
            <div class="card-header bg-success text-white text-center">
                <h3>➕ Nouveau Budget</h3>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('budgets.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="libelle" class="form-label">Libellé du budget</label>
                        <input type="text" name="libelle_ligne_budget" id="libelle" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="code_budget" class="form-label">Code budget</label>
                        <input type="text" name="code_budget" id="code_budget" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="date_debut" class="form-label">Date Début</label>
                            <input type="date" name="date_debut" id="date_debut" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="date_fin" class="form-label">Date Fin</label>
                            <input type="date" name="date_fin" id="date_fin" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="date_creation" class="form-label">Date de Création</label>
                        <input type="date" name="date_creation" id="date_creation" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="montant_global" class="form-label">Montant Global (FCFA)</label>
                        <input type="number" step="0.01" name="montant_global" id="montant_global" class="form-control" required>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('budgets.index') }}" class="btn btn-secondary">⬅ Retour</a>
                        <button type="submit" class="btn btn-success">💾 Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
