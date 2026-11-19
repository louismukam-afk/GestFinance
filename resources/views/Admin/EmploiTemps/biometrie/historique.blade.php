@extends('layouts.app')
@section('content')
<div class="container">
    <h3 class="text-primary">{{ $title }}</h3>
    <div style="margin-bottom: 15px;">
        <a href="{{ route('emploi_temps.index') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Ressources humaines</a>
        <a href="{{ route('biometrie_heures.index') }}" class="btn btn-primary"><i class="fa fa-upload"></i> Importer une biometrie</a>
    </div>
    <form method="GET" action="{{ route('biometrie_heures.historique') }}" class="row" style="margin-bottom: 20px;">
        <div class="col-sm-4 form-group"><label for="historiqueDebut">Date de debut</label><input id="historiqueDebut" type="date" name="date_debut" value="{{ request('date_debut') }}" class="form-control"></div>
        <div class="col-sm-4 form-group"><label for="historiqueFin">Date de fin</label><input id="historiqueFin" type="date" name="date_fin" value="{{ request('date_fin') }}" class="form-control"></div>
        <div class="col-sm-4 form-group" style="padding-top: 25px;">
            <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Filtrer</button>
            <a href="{{ route('biometrie_heures.historique') }}" class="btn btn-default" title="Reinitialiser les filtres" aria-label="Reinitialiser les filtres"><i class="fa fa-times"></i></a>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead><tr><th>Import</th><th>Periode</th><th>Importe le</th><th>Pointages</th><th>Non associes</th><th>Plages consolidees</th><th>Statut</th><th>Rapports</th></tr></thead>
            <tbody>
                @forelse($imports as $import)
                    <tr>
                        <td><a href="{{ route('biometrie_heures.index', ['id_biometrie_import' => $import->id]) }}">#{{ $import->id }} - {{ $import->libelle ?: 'Import biometrie' }}</a></td>
                        <td style="white-space: nowrap;">{{ $import->date_debut->format('d/m/Y') }}<br>{{ $import->date_fin->format('d/m/Y') }}</td>
                        <td>{{ optional($import->created_at)->format('d/m/Y H:i') }}</td>
                        <td>{{ $import->total_lignes }}</td><td>{{ $import->total_non_associees }}</td><td>{{ $import->heures_realisees_count }}</td>
                        <td>{{ $import->statut === 'consolide' ? 'Consolide' : 'A consolider' }}</td>
                        <td style="min-width: 280px;">
                            @if($import->heures_realisees_count > 0)
                                <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                                    <a class="btn btn-default btn-sm" target="_blank" rel="noopener" href="{{ route('biometrie_heures.pdf', ['id_biometrie_import' => $import->id, 'tableau' => 'detail']) }}"><i class="fa fa-file-pdf-o"></i> Detail</a>
                                    <a class="btn btn-default btn-sm" target="_blank" rel="noopener" href="{{ route('biometrie_heures.pdf', ['id_biometrie_import' => $import->id, 'tableau' => 'totaux']) }}"><i class="fa fa-table"></i> Global</a>
                                    <a class="btn btn-default btn-sm" target="_blank" rel="noopener" href="{{ route('biometrie_heures.rapport_import', ['import' => $import->id, 'rapport' => 'discipline']) }}"><i class="fa fa-calendar-times-o"></i> Discipline</a>
                                    <a class="btn btn-default btn-sm" target="_blank" rel="noopener" href="{{ route('biometrie_heures.rapport_import', ['import' => $import->id, 'rapport' => 'financier']) }}"><i class="fa fa-money"></i> Impact financier</a>
                                    <a class="btn btn-default btn-sm" href="{{ route('biometrie_heures.impots', $import->id) }}"><i class="fa fa-percent"></i> Impots</a>
                                    <a class="btn btn-default btn-sm" href="{{ route('virements_vacataires.create', $import->id) }}"><i class="fa fa-exchange"></i> Etat de virement</a>
                                    <a class="btn btn-default btn-sm" href="{{ route('bulletins_vacataires.show', $import->id) }}"><i class="fa fa-file-text-o"></i> Bulletins de paie</a>
                                </div>
                            @else
                                <a class="btn btn-default btn-sm" href="{{ route('biometrie_heures.index', ['id_biometrie_import' => $import->id]) }}"><i class="fa fa-calculator"></i> Consolider</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center">Aucun import pour cette periode.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $imports->links() }}
</div>
@endsection
@section('breadcrumb')
<ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
    <li><a href="{{ route('home') }}">Accueil</a></li>
    <li><a href="{{ route('emploi_temps.index') }}">Ressources humaines</a></li>
    <li class="active">Historique des consolidations</li>
</ol>
@endsection
