@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>{{ $title }}</h3>
        <a href="{{ route('paie_permanents.index') }}" class="btn btn-default">Retour a la paie</a>
    </div>
    <div class="card mb-3">
        <div class="card-header"><strong>Actions sur un mois</strong></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <form method="POST" action="{{ route('paie_permanents.bulletins.regenerer_mois') }}" class="form-inline">
                        @csrf
                        <label class="mr-2">Mois</label>
                        <input type="month" name="mois" value="{{ request('periode_debut') ? substr(request('periode_debut'), 0, 7) : now()->format('Y-m') }}" class="form-control mr-2" required>
                        <button class="btn btn-success" onclick="return confirm('Regenerer et sauvegarder tous les bulletins brouillon de ce mois ?')">Regenerer le mois</button>
                    </form>
                </div>
                <div class="col-md-6 text-right">
                    <form method="POST" action="{{ route('paie_permanents.bulletins.destroy_mois') }}" class="form-inline pull-right">
                        @csrf
                        @method('DELETE')
                        <label class="mr-2">Mois</label>
                        <input type="month" name="mois" value="{{ request('periode_debut') ? substr(request('periode_debut'), 0, 7) : now()->format('Y-m') }}" class="form-control mr-2" required>
                        <button class="btn btn-danger" onclick="return confirm('Supprimer tous les bulletins BROUILLON de ce mois ? Cette action est irreversible.')">Supprimer les brouillons du mois</button>
                    </form>
                </div>
            </div>
            <p class="text-muted mt-2 mb-0">Les bulletins valides et payes sont proteges. Chaque brouillon est regenere avec exactement le meme import biometrie que son bulletin d origine.</p>
        </div>
    </div>
    <div class="card mb-3"><div class="card-body">
        <form method="GET" class="row">
            <div class="col-md-2"><label>Numero</label><input name="reference" value="{{ request('reference') }}" type="number" class="form-control"></div>
            <div class="col-md-3"><label>Personnel</label><select name="id_personnel" class="form-control"><option value="">Tous</option>@foreach($personnels as $personnel)<option value="{{ $personnel->id }}" {{ request('id_personnel') == $personnel->id ? 'selected' : '' }}>{{ $personnel->nom }}</option>@endforeach</select></div>
            <div class="col-md-2"><label>Du</label><input name="periode_debut" value="{{ request('periode_debut') }}" type="date" class="form-control"></div>
            <div class="col-md-2"><label>Au</label><input name="periode_fin" value="{{ request('periode_fin') }}" type="date" class="form-control"></div>
            <div class="col-md-2"><label>Statut</label><select name="statut" class="form-control"><option value="">Tous</option>@foreach(['brouillon','valide','paye'] as $statut)<option value="{{ $statut }}" {{ request('statut') === $statut ? 'selected' : '' }}>{{ ucfirst($statut) }}</option>@endforeach</select></div>
            <div class="col-md-1"><label>&nbsp;</label><button class="btn btn-primary btn-block">Chercher</button></div>
        </form>
    </div></div>
    <div class="card"><div class="table-responsive"><table class="table table-bordered table-striped mb-0">
        <thead><tr><th>No</th><th>Personnel</th><th>Entite</th><th>Periode</th><th>Import</th><th class="text-right">Salaire contrat</th><th class="text-right">Brut</th><th class="text-right">Penalite</th><th class="text-right">Net</th><th>Statut</th><th>Actions</th></tr></thead>
        <tbody>@forelse($bulletins as $bulletin)<tr>
            <td>{{ $bulletin->id }}</td><td>{{ $bulletin->personnel->nom ?? '-' }}</td><td>{{ $bulletin->entite->nom_entite ?? '-' }}</td><td>{{ optional($bulletin->periode_debut)->format('d/m/Y') }} - {{ optional($bulletin->periode_fin)->format('d/m/Y') }}</td><td>{{ $bulletin->import->libelle ?? '-' }}</td>
            <td class="text-right">{{ number_format($bulletin->salaire_permanent->montant_mensuel ?? $bulletin->salaire_base, 0, ',', ' ') }}</td>
            <td class="text-right">{{ number_format($bulletin->total_gains, 0, ',', ' ') }}</td><td class="text-right">{{ number_format($bulletin->penalite_biometrie, 0, ',', ' ') }}</td><td class="text-right"><strong>{{ number_format($bulletin->net_a_payer, 0, ',', ' ') }}</strong></td><td><span class="badge badge-{{ $bulletin->statut === 'paye' ? 'primary' : ($bulletin->statut === 'valide' ? 'success' : 'warning') }}">{{ $bulletin->statut }}</span></td>
            <td><form method="POST" action="{{ route('paie_permanents.bulletins.statut', $bulletin) }}" class="form-inline">@csrf @method('PATCH')<select name="statut" class="form-control input-sm" {{ $bulletin->statut === 'paye' ? 'disabled' : '' }}>@foreach(['brouillon','valide','paye'] as $statut)<option value="{{ $statut }}" {{ $bulletin->statut === $statut ? 'selected' : '' }}>{{ ucfirst($statut) }}</option>@endforeach</select><button class="btn btn-xs btn-primary" {{ $bulletin->statut === 'paye' ? 'disabled' : '' }}>Appliquer</button></form>
            <a class="btn btn-xs btn-danger mt-1" href="{{ route('paie_permanents.bulletins.pdf', ['periode_debut' => $bulletin->periode_debut->format('Y-m-d'), 'periode_fin' => $bulletin->periode_fin->format('Y-m-d'), 'id_personnel' => $bulletin->id_personnel, 'statut' => $bulletin->statut]) }}">PDF</a></td>
        </tr>@empty<tr><td colspan="11" class="text-center text-muted">Aucun bulletin trouve.</td></tr>@endforelse</tbody>
    </table></div><div class="card-footer">{{ $bulletins->links() }}</div></div>
</div>
@endsection
