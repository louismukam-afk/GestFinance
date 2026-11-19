@extends('layouts.app')

@section('content')
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Erreur :</strong> {{ $errors->first() }}
            </div>
        @endif

        @if(session('import_errors') && count(session('import_errors')))
            <div class="alert alert-info">
                <strong>Details des lignes ignorees</strong>
                <ul class="mb-0">
                    @foreach(session('import_errors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <h3 class="text-primary"><i class="fa fa-users"></i> {{ $title }}</h3>

        <div class="btn-toolbar" style="margin-bottom: 15px;">
            <a class="btn btn-default" href="{{ route('personnels_banques.index') }}"><i class="fa fa-university"></i> Affecter a une banque</a>
            <a class="btn btn-primary" href="{{ route('personnel_management', ['ajouter' => 1]) }}">
                <i class="fa fa-plus"></i> Nouveau Personnel
            </a>

            <a href="{{ route('personnels.import') }}" class="btn btn-success">
                <i class="fa fa-upload"></i> Importation Excel
            </a>

            <a href="{{ route('personnels.export.excel') }}" class="btn btn-info">
                <i class="fa fa-file-excel-o"></i> Exporter Excel
            </a>

            <a href="{{ route('personnels.import.template') }}" class="btn btn-default">
                <i class="fa fa-download"></i> Template Excel
            </a>
        </div>

        <div class="form-group" style="max-width: 420px;">
            <label for="personnelSearch">Recherche instantanee</label>
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-search"></i></span>
                <input type="search"
                       id="personnelSearch"
                       class="form-control"
                       placeholder="Rechercher par nom, type, date ou telephone">
            </div>
        </div>

        <div class="table-responsive">
            <table id="personnelsTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>Nom</th>
                    <th>Nom biometrie</th>
                    <th>Type de personnel</th>
                    <th>Date de naissance</th>
                    <th>Numero de telephone</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($personnels as $p)
                    <tr>
                        <td>{{ $p->nom }}</td>
                        <td>{{ $p->nom_biometrie }}</td>
                        <td>{{ ucfirst($p->type_personnel ?? 'permanent') }}</td>
                        <td>{{ $p->date_naissance ? \Carbon\Carbon::parse($p->date_naissance)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $p->telephone }}</td>
                        <td class="text-nowrap">
                            <a href="{{ route('personnels.show', $p->id) }}" class="btn btn-xs btn-info">
                                <i class="fa fa-id-card-o"></i> Details
                            </a>

                            <a href="{{ route('personnels.edit', $p->id) }}" class="btn btn-xs btn-warning">
                                <i class="fa fa-pencil"></i> Modifier
                            </a>

                            <form action="{{ route('delete_personnel', $p->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-xs btn-danger" onclick="return confirm('Supprimer ce personnel ?')">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        (function () {
            $(function () {
                var searchInput = document.getElementById('personnelSearch');
                var table = document.getElementById('personnelsTable');

                if (window.jQuery && $.fn && $.fn.DataTable) {
                    var dataTable = $('#personnelsTable').DataTable({
                        responsive: true,
                        dom: 'rtip',
                        pageLength: 25,
                        language: { url: "{{ asset('js/datatables/fr-FR.json') }}" }
                    });

                    if (searchInput) {
                        searchInput.addEventListener('input', function () {
                            dataTable.search(this.value).draw();
                        });
                    }

                    return;
                }

                if (searchInput && table) {
                    searchInput.addEventListener('input', function () {
                        var query = this.value.toLowerCase().trim();
                        var rows = table.querySelectorAll('tbody tr');

                        rows.forEach(function (row) {
                            var text = row.textContent.toLowerCase();
                            row.style.display = text.indexOf(query) !== -1 ? '' : 'none';
                        });
                    });
                }
            });
        })();
    </script>
@endsection

@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('personnel') }}"><strong>Gestion du personnel</strong></a></li>
        <li class="active"><strong>{{ $title }}</strong></li>
    </ol>
@endsection
