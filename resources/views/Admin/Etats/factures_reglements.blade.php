@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="mb-4">📄 Factures & Règlements</h3>

        {{-- Filtres --}}
        <form method="GET" class="row g-3 mb-3">
            <div class="col-md-2">
                <label>Année académique</label>
                <select name="annee" class="form-control">
                    <option value="">-- Toutes --</option>
                    @foreach($annees as $a)
                        <option value="{{ $a->id }}" {{ request('annee')==$a->id?'selected':'' }}>
                            {{ $a->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label>Entité</label>
                <select name="entite" class="form-control">
                    <option value="">-- Toutes --</option>
                    @foreach($entites as $e)
                        <option value="{{ $e->id }}" {{ request('entite')==$e->id?'selected':'' }}>
                            {{ $e->nom_entite }}
                        </option>
                    @endforeach
                </select>
            </div>
            {{-- Spécialité --}}
            <div class="col-md-2">
                <label>Spécialité</label>
                <select name="specialite" class="form-control">
                    <option value="">-- Toutes --</option>
                    @foreach($specialites as $s)
                        <option value="{{ $s->id }}"
                                {{ request('specialite')==$s->id?'selected':'' }}>
                            {{ $s->nom_specialite }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Niveau --}}
            <div class="col-md-2">
                <label>Niveau</label>
                <select name="niveau" class="form-control">
                    <option value="">-- Tous --</option>
                    @foreach($niveaux as $n)
                        <option value="{{ $n->id }}"
                                {{ request('niveau')==$n->id?'selected':'' }}>
                            {{ $n->nom_niveau }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Cycle --}}
            <div class="col-md-2">
                <label>Cycle</label>
                <select name="cycle" class="form-control">
                    <option value="">-- Tous --</option>
                    @foreach($cycles as $c)
                        <option value="{{ $c->id }}"
                                {{ request('cycle')==$c->id?'selected':'' }}>
                            {{ $c->nom_cycle }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Filière --}}
            <div class="col-md-2">
                <label>Filière</label>
                <select name="filiere" class="form-control">
                    <option value="">-- Toutes --</option>
                    @foreach($filieres as $f)
                        <option value="{{ $f->id }}"
                                {{ request('filiere')==$f->id?'selected':'' }}>
                            {{ $f->nom_filiere }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label>Caisse</label>
                <select name="caisse" class="form-control">
                    <option value="">-- Toutes --</option>
                    @foreach($caisses as $c)
                        <option value="{{ $c->id }}" {{ request('caisse')==$c->id?'selected':'' }}>
                            {{ $c->nom_caisse }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label>Date début</label>
                <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}">
            </div>

            <div class="col-md-2">
                <label>Date fin</label>
                <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}">
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-primary w-100">🔍 Rechercher</button>
            </div>
        </form>

        {{-- Export --}}
        <div class="mb-3">
            <a href="{{ route('etat_factures_export_excel', request()->all()) }}" class="btn btn-success">⬇️ Excel</a>
            <a href="{{ route('etat_factures_export_pdf', request()->all()) }}" class="btn btn-danger">⬇️ PDF</a>
        </div>

        @forelse($grouped as $specialite => $lignes)
            <h4 class="text-primary mt-4">🎓 Spécialité : {{ $specialite }}</h4>

            @foreach($lignes as $ligne => $users)
                <h5 class="mt-3">📘 Ligne budgétaire : {{ $ligne }}</h5>

                @foreach($users as $user => $factures)
                    <h6 class="text-muted">👤 Utilisateur : {{ $user }}</h6>

                    <table class="table table-bordered table-sm">
                        <thead class="table-dark">
                        <tr>
                            <th>N° Facture</th>
                            <th>Étudiant</th>
                            <th>Montant facturé</th>
                            <th>Montant réglé</th>
                            <th>Reste</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($factures as $f)
                            @php
                                $encaisse = $f->reglement_etudiants->sum('montant_reglement');
                            @endphp
                            <tr>
                                <td>{{ $f->numero_facture }}</td>
                                <td>{{ $f->etudiants->nom }}</td>
                                <td>{{ number_format($f->montant_total_facture,0,',',' ') }}</td>
                                <td>{{ number_format($encaisse,0,',',' ') }}</td>
                                <td>{{ number_format($f->montant_total_facture - $encaisse,0,',',' ') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endforeach
            @endforeach
        @empty
            <p class="text-center text-muted">⚠️ Aucune donnée trouvée</p>
        @endforelse
    </div>
@endsection
@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent; padding: 4px 10px">
        <li class="breadcrumb-item">
            <a href="{{ route('home') }}"><strong>Accueil</strong></a>
        </li>
        <li class="breadcrumb-item active">
            <a href="{{ route('etats.index') }}"><strong>États comptables</strong></a>

        </li>

        <li class="breadcrumb-item">
            <a href="{{ route('etat_factures_reglements') }}"><strong>Nouvelle Etat facture</strong></a>
        </li>
    </ol>
@endsection

