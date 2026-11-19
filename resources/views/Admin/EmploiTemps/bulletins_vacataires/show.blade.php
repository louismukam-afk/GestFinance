@extends('layouts.app')
@section('content')
<div class="container">
    <h3 class="text-primary">{{ $title }}</h3>
    <p>{{ $import->libelle }} : {{ $import->date_debut->format('d/m/Y') }} au {{ $import->date_fin->format('d/m/Y') }}</p>
    <a class="btn btn-default" href="{{ route('bulletins_vacataires.index') }}"><i class="fa fa-arrow-left"></i> Imports</a>
    <a class="btn btn-danger" target="_blank" rel="noopener" href="{{ route('bulletins_vacataires.pdf', $import) }}"><i class="fa fa-file-pdf-o"></i> Tous les bulletins PDF</a>
    <div class="table-responsive" style="margin-top:20px"><table class="table table-bordered table-striped"><thead><tr><th>Enseignant</th><th>Entites</th><th>Statut</th><th>Brut fiscal</th><th>Impot</th><th>Frais</th><th>Net FCFA</th><th></th></tr></thead><tbody>
    @foreach($bulletins as $bulletin)<tr><td>{{ $bulletin['nom'] }}</td><td>{{ $bulletin['entites']->pluck('entite_nom')->join(', ') }}</td><td>{{ $bulletin['statut'] }}</td>@foreach(['brut_fiscal','impot','frais_transfert','net_a_payer'] as $champ)<td style="white-space:nowrap">{{ number_format($bulletin[$champ], 2, ',', ' ') }}</td>@endforeach<td><a class="btn btn-default btn-sm" target="_blank" rel="noopener" href="{{ route('bulletins_vacataires.pdf', ['import' => $import->id, 'id_personnel' => $bulletin['id_personnel']]) }}"><i class="fa fa-file-pdf-o"></i> Bulletin</a></td></tr>@endforeach
    </tbody><tfoot><tr><th colspan="3">TOTAL</th>@foreach(['brut_fiscal','impot','frais_transfert','net_a_payer'] as $champ)<th>{{ number_format($bulletins->sum($champ), 2, ',', ' ') }}</th>@endforeach<th></th></tr></tfoot></table></div>
</div>
@endsection
