@extends('layouts.app')
@section('content')
<div class="container">
    <h3 class="text-primary">{{ $title }}</h3>
    <a href="{{ route('personnels_banques.index') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Banques du personnel</a>
    <div class="form-group" style="margin-top: 15px;"><label for="recherchePersonnelBanque">Rechercher un personnel ou un compte</label><input id="recherchePersonnelBanque" type="search" class="form-control"></div>
    <form method="POST" action="{{ route('personnels_banques.store', $banque->id) }}">
        @csrf
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="selectionPersonnelBanque">
                <thead><tr><th><input type="checkbox" id="selectionTous" aria-label="Selectionner tous les personnels visibles"></th><th>Personnel</th><th>Numero de compte (niveau d'etude)</th><th>Banque actuelle</th></tr></thead>
                <tbody>
                    @forelse($personnels as $personnel)
                        <tr>
                            <td><input type="checkbox" name="personnels[]" value="{{ $personnel->id }}" id="personnelBanque{{ $personnel->id }}" {{ in_array($personnel->id, old('personnels', [])) ? 'checked' : '' }}></td>
                            <td><label for="personnelBanque{{ $personnel->id }}">{{ $personnel->nom }}</label></td>
                            <td>{{ $personnel->niveau_etude ?: '-' }}</td><td>{{ $personnel->banque->nom_banque ?? 'Sans banque' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">Aucun personnel.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <button type="submit" class="btn btn-success" {{ $personnels->isEmpty() ? 'disabled' : '' }}><i class="fa fa-check"></i> Affecter la selection a {{ $banque->nom_banque }}</button>
    </form>
</div>
@endsection
@section('scripts')
<script>
    (function () {
        var search = document.getElementById('recherchePersonnelBanque');
        var master = document.getElementById('selectionTous');
        var rows = document.querySelectorAll('#selectionPersonnelBanque tbody tr');
        search.addEventListener('input', function () {
            var term = this.value.toLocaleLowerCase().trim();
            rows.forEach(function (row) { row.hidden = row.textContent.toLocaleLowerCase().indexOf(term) === -1; });
            master.checked = false;
        });
        master.addEventListener('change', function () {
            rows.forEach(function (row) {
                var checkbox = row.querySelector('input[type="checkbox"]');
                if (!row.hidden && checkbox) { checkbox.checked = master.checked; }
            });
        });
    })();
</script>
@endsection
@section('breadcrumb')
<ol class="breadcrumb"><li><a href="{{ route('personnels_banques.index') }}">Banques du personnel</a></li><li class="active">{{ $banque->nom_banque }}</li></ol>
@endsection
