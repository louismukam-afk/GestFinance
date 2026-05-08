@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-primary">{{ $title }}</h3>
        <a href="{{ route('factures_rattrapage.index') }}" class="btn btn-info">Factures rattrapage</a>
    </div>

    <div class="card mb-4">
        <div class="card-header"><strong>Nouvelle matiere</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('matieres.store') }}" class="row g-3">
                @csrf
                <div class="col-md-4">
                    <label>Nom de la matiere</label>
                    <input type="text" name="nom_matiere" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label>Code</label>
                    <input type="text" name="code_matiere" class="form-control">
                </div>
                <div class="col-md-3">
                    <label>Description</label>
                    <input type="text" name="description" class="form-control">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-success w-100">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Liste des matieres</strong></div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Matiere</th>
                        <th>Code</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($matieres as $i => $matiere)
                        <tr>
                            <form method="POST" action="{{ route('matieres.update', $matiere) }}">
                                @csrf
                                @method('PUT')
                                <td>{{ $i + 1 }}</td>
                                <td><input type="text" name="nom_matiere" class="form-control" value="{{ $matiere->nom_matiere }}" required></td>
                                <td><input type="text" name="code_matiere" class="form-control" value="{{ $matiere->code_matiere }}"></td>
                                <td><input type="text" name="description" class="form-control" value="{{ $matiere->description }}"></td>
                                <td>
                                    <button class="btn btn-sm btn-primary">Modifier</button>
                            </form>
                                    <form method="POST" action="{{ route('matieres.destroy', $matiere) }}" style="display:inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette matiere ?')">Supprimer</button>
                                    </form>
                                </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Aucune matiere enregistree.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('breadcrumb')
<ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
    <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
    <li class="active"><strong>{{ $title }}</strong></li>
</ol>
@endsection
