@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="text-center mb-4">📑 État des Bons de Commande</h2>

        <!-- 🔹 Formulaire de filtre -->
        <form method="GET" action="{{ route('etat_bons.index') }}" class="row g-3 mb-4">
            <div class="col-md-3">
                <label>Date début</label>
                <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}">
            </div>
            <div class="col-md-3">
                <label>Date fin</label>
                <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}">
            </div>
            <div class="col-md-3">
                <label>Personnel</label>
                <select name="id_personnel" class="form-control">
                    <option value="">-- Tous --</option>
                    @foreach($personnels as $perso)
                        <option value="{{ $perso->id }}" {{ request('id_personnel') == $perso->id ? 'selected' : '' }}>
                            {{ $perso->nom }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>Utilisateur</label>
                <select name="id_user" class="form-control">
                    <option value="">-- Tous --</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('id_user') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-12 mt-3 text-center">
                <button type="submit" class="btn btn-primary">🔍 Rechercher</button>
                <a href="{{ route('etat_bons.index') }}" class="btn btn-secondary">♻ Réinitialiser</a>
            </div>
        </form>

        <!-- 🔹 Boutons globaux -->
        <div class="mb-3">
            <a href="{{ route('etat_bons.export_pdf', request()->all()) }}" class="btn btn-danger">
                📄 Exporter PDF
            </a>
            <a href="{{ route('etat_bons.export_excel', request()->all()) }}" class="btn btn-success">
                📊 Exporter Excel
            </a>
        </div>

        <!-- 🔹 Tableau des bons -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Description</th>
                    <th>Date Début</th>
                    <th>Date Fin</th>
                    <th>Montant Total</th>
                    <th>Personnel</th>
                    <th>Utilisateur</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($bons as $i => $bon)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $bon->nom_bon_commande }}</td>
                        <td>{{ $bon->description_bon_commande }}</td>
                        <td>{{ $bon->date_debut }}</td>
                        <td>{{ $bon->date_fin }}</td>
                        <td>{{ number_format($bon->montant_total,0,',',' ') }} FCFA</td>
                        <td>{{ $bon->personnels->nom ?? 'N/A' }}</td>
                        <td>{{ $bon->user->name ?? 'N/A' }}</td>
                        <td>
                            <a href="{{ route('etat_bons.pdf_one', $bon->id) }}" class="btn btn-sm btn-danger">
                                📄 PDF
                            </a>
                            <a href="{{ route('etat_bons.excel_one', $bon->id) }}" class="btn btn-sm btn-success">
                                📊 Excel
                            </a>
                            <a href="{{ route('etat_bons.show', $bon->id) }}" class="btn btn-sm btn-info">
                                👁 Voir détails
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">⚠ Aucun bon trouvé pour cette période</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
