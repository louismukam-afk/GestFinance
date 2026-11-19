@extends('layouts.app')
@section('content')
<div class="container">
    <h3 class="text-primary">{{ $title }}</h3>
    <a class="btn btn-default" href="{{ route('emploi_temps.index') }}"><i class="fa fa-arrow-left"></i> Ressources humaines</a>
    <a class="btn btn-primary" href="{{ route('virements_vacataires.index') }}"><i class="fa fa-plus"></i> Nouvel ordre</a>
    @if($errors->any())<div class="alert alert-danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="GET" class="row" style="margin-top:20px">
        <div class="col-sm-3 form-group"><label for="debut">Date de l'ordre du</label><input id="debut" name="date_debut" type="date" class="form-control" value="{{ request('date_debut') }}"></div>
        <div class="col-sm-3 form-group"><label for="fin">Au</label><input id="fin" name="date_fin" type="date" class="form-control" value="{{ request('date_fin') }}"></div>
        <div class="col-sm-3 form-group"><label for="entite">Entite</label><select id="entite" name="id_entite" class="form-control"><option value="">Toutes</option>@foreach($entites as $e)<option value="{{ $e->id_entite }}" @selected(request('id_entite') == $e->id_entite)>{{ $e->entite_nom }}</option>@endforeach</select></div>
        <div class="col-sm-3 form-group"><label for="banque">Banque</label><select id="banque" name="id_banque" class="form-control"><option value="">Toutes</option>@foreach($banques as $b)<option value="{{ $b->id_banque }}" @selected(request('id_banque') == $b->id_banque)>{{ $b->banque_nom }}</option>@endforeach</select></div>
        <div class="col-sm-3 form-group"><label for="import">Numero d'import</label><input id="import" type="number" min="1" name="id_import" class="form-control" value="{{ request('id_import') }}"></div>
        <div class="col-sm-6 form-group"><label for="recherche">Enseignant ou motif</label><input id="recherche" name="recherche" class="form-control" maxlength="255" value="{{ request('recherche') }}"></div>
        <div class="col-sm-3 form-group" style="padding-top:25px"><button class="btn btn-primary"><i class="fa fa-filter"></i> Filtrer</button> <a class="btn btn-default" href="{{ route('virements_vacataires.historique') }}" title="Reinitialiser" aria-label="Reinitialiser"><i class="fa fa-times"></i></a></div>
    </form>
    <p>{{ $etats->total() }} ordre(s)</p>
    <div class="table-responsive"><table class="table table-bordered table-striped">
        <thead><tr><th>Reference</th><th>Date de l'ordre</th><th>Periode</th><th>Entite / banque</th><th>Motif</th><th>Beneficiaires</th><th>Net FCFA</th><th></th></tr></thead>
        <tbody>@forelse($etats as $etat)
        <tr><td>OV-{{ $etat->id }}<br>Import #{{ $etat->id_biometrie_import }}</td><td>{{ $etat->date_ordre->format('d/m/Y') }}</td><td>{{ $etat->date_debut->format('d/m/Y') }} au {{ $etat->date_fin->format('d/m/Y') }}</td><td>{{ $etat->entite_nom }}<br>{{ $etat->banque_nom }}</td><td style="min-width:160px;overflow-wrap:anywhere">{{ $etat->motif }}</td><td>{{ $etat->lignes_count }}</td><td style="white-space:nowrap">{{ number_format($etat->lignes_sum_net_a_payer, 2, ',', ' ') }}</td><td style="min-width:120px"><a class="btn btn-default btn-sm" href="{{ route('virements_vacataires.show', $etat) }}"><i class="fa fa-eye"></i> Consulter</a> <a class="btn btn-default btn-sm" target="_blank" rel="noopener" href="{{ route('virements_vacataires.pdf', $etat) }}"><i class="fa fa-file-pdf-o"></i> PDF</a></td></tr>
        @empty<tr><td colspan="8">Aucun ordre pour ces criteres.</td></tr>@endforelse</tbody>
    </table></div>
    {{ $etats->links() }}
</div>
@endsection
