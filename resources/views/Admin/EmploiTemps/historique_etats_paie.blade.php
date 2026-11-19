@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3"><h3>{{ $title }}</h3><a href="{{ route('paie_permanents.index') }}" class="btn btn-default">Retour a la paie</a></div>
    <div class="card mb-3"><div class="card-body"><form method="GET" class="row">
        <div class="col-md-2"><label>Reference</label><input name="reference" value="{{ request('reference') }}" class="form-control"></div>
        <div class="col-md-2"><label>Du</label><input name="periode_debut" value="{{ request('periode_debut') }}" type="date" class="form-control"></div>
        <div class="col-md-2"><label>Au</label><input name="periode_fin" value="{{ request('periode_fin') }}" type="date" class="form-control"></div>
        <div class="col-md-3"><label>Entite</label><select name="id_entite" class="form-control"><option value="">Toutes</option>@foreach($entites as $entite)<option value="{{ $entite->id }}" {{ request('id_entite') == $entite->id ? 'selected' : '' }}>{{ $entite->nom_entite }}</option>@endforeach</select></div>
        <div class="col-md-2"><label>Statut</label><select name="statut" class="form-control"><option value="">Tous</option>@foreach(['genere','valide','paye'] as $statut)<option value="{{ $statut }}" {{ request('statut') === $statut ? 'selected' : '' }}>{{ ucfirst($statut) }}</option>@endforeach</select></div>
        <div class="col-md-1"><label>&nbsp;</label><button class="btn btn-primary btn-block">Chercher</button></div>
    </form></div></div>
    <div class="card"><div class="table-responsive"><table class="table table-bordered table-striped mb-0"><thead><tr><th>Reference</th><th>Periode</th><th>Entite</th><th>Employes</th><th class="text-right">Net total</th><th>Statut</th><th>Actions</th></tr></thead><tbody>
    @forelse($etats as $etat)<tr><td>{{ $etat->reference }}</td><td>{{ $etat->periode_debut->format('d/m/Y') }} - {{ $etat->periode_fin->format('d/m/Y') }}</td><td>{{ $etat->entite->nom_entite ?? 'Toutes' }}</td><td>{{ $etat->nombre_employes }}</td><td class="text-right"><strong>{{ number_format($etat->total_net_a_payer, 0, ',', ' ') }}</strong></td><td><span class="badge badge-{{ $etat->statut === 'paye' ? 'primary' : ($etat->statut === 'valide' ? 'success' : 'warning') }}">{{ $etat->statut }}</span></td><td>
        <a href="{{ route('paie_permanents.etats.show', $etat) }}" class="btn btn-xs btn-default">Voir</a><a href="{{ route('paie_permanents.etats.pdf', $etat) }}" class="btn btn-xs btn-danger">PDF</a>
        <form method="POST" action="{{ route('paie_permanents.etats.statut', $etat) }}" class="form-inline mt-1">@csrf @method('PATCH')<select name="statut" class="form-control input-sm" {{ $etat->statut === 'paye' ? 'disabled' : '' }}>@foreach(['genere','valide','paye'] as $statut)<option value="{{ $statut }}" {{ $etat->statut === $statut ? 'selected' : '' }}>{{ ucfirst($statut) }}</option>@endforeach</select><button class="btn btn-xs btn-primary" {{ $etat->statut === 'paye' ? 'disabled' : '' }}>Appliquer</button></form>
    </td></tr>@empty<tr><td colspan="7" class="text-center text-muted">Aucun etat trouve.</td></tr>@endforelse</tbody></table></div><div class="card-footer">{{ $etats->links() }}</div></div>
</div>
@endsection
