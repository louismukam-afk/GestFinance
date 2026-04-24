@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card shadow">
            <div class="card-header text-center bg-info text-white">
                <h3>📑 Détails du Budget : {{ $budget->libelle_ligne_budget }}</h3>
            </div>

            <div class="card-body">
                <h5 class="text-info">📌 Informations générales</h5>
                <ul class="list-group mb-3">
                    <li class="list-group-item"><strong>Code Budget :</strong> {{ $budget->code_budget }}</li>
                    <li class="list-group-item"><strong>Description :</strong> {{ $budget->description }}</li>
                    <li class="list-group-item"><strong>Période :</strong> {{ $budget->date_debut }} → {{ $budget->date_fin }}</li>
                    <li class="list-group-item"><strong>Date de création :</strong> {{ $budget->date_creation }}</li>
                    <li class="list-group-item"><strong>Montant Global :</strong> {{ number_format($budget->montant_global, 0, ',', ' ') }} FCFA</li>
                    <li class="list-group-item"><strong>Utilisateur :</strong> {{ $budget->user->name ?? 'N/A' }}</li>
                </ul>

                <div class="mt-3 d-flex justify-content-between">
                    <a href="{{ route('budgets.index') }}" class="btn btn-secondary">⬅ Retour</a>
                    <div>
                        <a href="{{ route('budgets.exportPdfOne', $budget->id) }}" class="btn btn-danger">🖨 Exporter PDF</a>
                        <a href="{{ route('budgets.exportExcelOne', $budget->id) }}" class="btn btn-success">📊 Exporter Excel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
