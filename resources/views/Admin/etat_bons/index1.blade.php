@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="mb-3 text-primary">📊 État des Bons de Commande</h3>

        <form method="GET" action="{{ route('etat_bons.index') }}" class="row g-2 mb-3">
            <div class="col-md-3">
                <label>Date début</label>
                <input type="date" name="date_debut" value="{{ request('date_debut') }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label>Date fin</label>
                <input type="date" name="date_fin" value="{{ request('date_fin') }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label>Personnel</label>
                <select name="id_personnel" class="form-control">
                    <option value="">-- Tous --</option>
                    @foreach($personnels as $p)
                        <option value="{{ $p->id }}" {{ request('id_personnel')==$p->id ? 'selected' : '' }}>
                            {{ $p->nom }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>Utilisateur</label>
                <select name="id_user" class="form-control">
                    <option value="">-- Tous --</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('id_user')==$u->id ? 'selected' : '' }}>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-12 mt-3">
                <button class="btn btn-primary">Filtrer</button>
                <a href="{{ route('etat_bons.exportExcel', request()->all()) }}" class="btn btn-success">Exporter Excel</a>
                <a href="{{ route('etat_bons.exportPdf', request()->all()) }}" class="btn btn-danger">Exporter PDF</a>
            </div>
        </form>

        <table class="table table-bordered table-striped mt-3">
            <thead>
            <tr>
                <th>#</th>
                <th>Nom</th>
                <th>Période</th>
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
                    <td>{{ $bon->date_debut }} - {{ $bon->date_fin }}</td>
                    <td>{{ number_format($bon->montant_total, 0, ',', ' ') }} FCFA</td>
                    <td>{{ $bon->personnels->nom ?? 'N/A' }}</td>
                    <td>{{ $bon->user->name ?? 'N/A' }}</td>
                    <td>
                        <a href="{{ route('etat_bons.show', $bon->id) }}" class="btn btn-sm btn-info">Détails</a>
                        <a href="{{ route('etat_bons.exportBonPdf', $bon->id) }}" class="btn btn-sm btn-danger">PDF</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Aucun bon trouvé</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
