@extends('layouts.app')
@section('content')
<div class="container">
    <h3 class="text-primary">{{ $title }}</h3>
    <a class="btn btn-default" href="{{ route('emploi_temps.index') }}"><i class="fa fa-arrow-left"></i> Ressources humaines</a>
    <div class="table-responsive" style="margin-top:20px"><table class="table table-bordered table-striped"><thead><tr><th>Import</th><th>Periode</th><th></th></tr></thead><tbody>
    @forelse($imports as $import)<tr><td>#{{ $import->id }} - {{ $import->libelle }}</td><td>{{ $import->date_debut->format('d/m/Y') }} au {{ $import->date_fin->format('d/m/Y') }}</td><td><a class="btn btn-primary btn-sm" href="{{ route('bulletins_vacataires.show', $import) }}"><i class="fa fa-file-text-o"></i> Generer les bulletins</a></td></tr>@empty<tr><td colspan="3">Aucun import consolide.</td></tr>@endforelse
    </tbody></table></div>{{ $imports->links() }}
</div>
@endsection
