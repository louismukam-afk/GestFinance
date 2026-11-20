@extends('layouts.app')
@section('content')
<div class="container">
    <h3 class="text-primary">{{ $title }}</h3>
    <p>{{ $import->libelle }} - Du {{ $import->date_debut->format('d/m/Y') }} au {{ $import->date_fin->format('d/m/Y') }}</p>
    @if($import->estRequete())<p><strong>Complement apres requete - import initial #{{ $import->id_import_parent }}</strong>. Droits revises moins les montants deja couverts par les ordres valides. Taux initial conserve.</p>@endif
    <div style="margin-bottom: 15px;">
        <a href="{{ route('biometrie_heures.historique') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Historique</a>
        <a href="{{ route('taux_impots.index') }}" class="btn btn-default"><i class="fa fa-percent"></i> Taux d'impots</a>
        <a href="{{ route('biometrie_heures.impots.pdf', $import->id) }}" target="_blank" rel="noopener" class="btn btn-danger"><i class="fa fa-file-pdf-o"></i> PDF des impots</a>
    </div>
    <form method="POST" action="{{ route('biometrie_heures.impots.store', $import->id) }}">
        @csrf
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead><tr><th>Personnel</th><th>Taux d'impot</th><th>Mois</th><th>Brut fiscal apres sanctions</th><th>Impot</th><th>Net a payer</th></tr></thead>
                <tbody>
                    @foreach($lignes->groupBy('id_personnel') as $personnelId => $items)
                        @foreach($items as $ligne)
                            <tr>
                                @if($loop->first)
                                    <td rowspan="{{ $items->count() }}">{{ $ligne['nom'] }}</td>
                                    <td rowspan="{{ $items->count() }}" style="min-width: 230px;">
                                        @if($import->estRequete()){{ number_format((float)$ligne['taux'], 2, ',', ' ') }} % (reference)
                                        @else
                                        <select name="taux_personnels[{{ $personnelId }}]" class="form-control" aria-label="Taux d impot de {{ $ligne['nom'] }}">
                                            <option value="">Par defaut : {{ $defaut->nom }} ({{ number_format((float)$defaut->taux, 2, ',', ' ') }} %)</option>
                                            @foreach($taux as $item)
                                                <option value="{{ $item->id }}" {{ (string)old('taux_personnels.'.$personnelId, $ligne['id_taux_impot']) === (string)$item->id ? 'selected' : '' }}>{{ $item->nom }} ({{ number_format((float)$item->taux, 2, ',', ' ') }} %)</option>
                                            @endforeach
                                        </select>
                                        @endif
                                    </td>
                                @endif
                                <td>{{ $ligne['mois'] }}</td><td>{{ number_format($ligne['brut'], 2, ',', ' ') }}</td><td>{{ number_format($ligne['impot'], 2, ',', ' ') }}</td><td>{{ number_format($ligne['net'], 2, ',', ' ') }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
                <tfoot><tr><th colspan="3">TOTAL GENERAL (FCFA)</th><th>{{ number_format($lignes->sum('brut'), 2, ',', ' ') }}</th><th>{{ number_format($lignes->sum('impot'), 2, ',', ' ') }}</th><th>{{ number_format($lignes->sum('net'), 2, ',', ' ') }}</th></tr></tfoot>
            </table>
        </div>
        @unless($import->estRequete())<button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Enregistrer les taux du personnel</button>@endunless
    </form>
</div>
@endsection
@section('breadcrumb')
<ol class="breadcrumb"><li><a href="{{ route('biometrie_heures.historique') }}">Historique des consolidations</a></li><li class="active">Impots de l'import #{{ $import->id }}</li></ol>
@endsection
