@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="mb-4">🎓 Situation financière d’un étudiant</h3>

        {{-- FILTRES --}}
        <form method="GET" class="row g-3 mb-4">

            <div class="col-md-3">
                <label>Étudiant *</label>
                <select name="etudiant" class="form-control" required>
                    <option value="">-- Sélectionner --</option>
                    @foreach($etudiants as $e)
                        <option value="{{ $e->id }}" {{ request('etudiant')==$e->id?'selected':'' }}>
                            {{ $e->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label>Année</label>
                <select name="annee" class="form-control">
                    <option value="">Toutes</option>
                    @foreach($annees as $a)
                        <option value="{{ $a->id }}" {{ request('annee')==$a->id?'selected':'' }}>
                            {{ $a->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label>Spécialité</label>
                <select name="specialite" class="form-control">
                    <option value="">Toutes</option>
                    @foreach($specialites as $s)
                        <option value="{{ $s->id }}" {{ request('specialite')==$s->id?'selected':'' }}>
                            {{ $s->nom_specialite }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label>Niveau</label>
                <select name="niveau" class="form-control">
                    <option value="">Tous</option>
                    @foreach($niveaux as $n)
                        <option value="{{ $n->id }}" {{ request('niveau')==$n->id?'selected':'' }}>
                            {{ $n->nom_niveau }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-1 d-flex align-items-end">
                <button class="btn btn-primary w-100">🔍</button>
            </div>
        </form>

        {{-- TABLEAU --}}
        @if($result->count())
            <div class="mb-3">
                <a href="{{ route('etat_situation_etudiant_pdf', request()->all()) }}"
                   class="btn btn-danger btn-sm">
                    🖨️ Imprimer / PDF
                </a>
            </div>

            <table class="table table-bordered table-sm">
                <thead class="table-dark">
                <tr>
                    <th>Facture</th>
                    <th>Date</th>
                    <th class="text-end">Montant</th>
                    <th class="text-end">Encaissé</th>
                    <th class="text-end">Reste</th>
                </tr>
                </thead>

                <tbody>

                @php
                    $total = 0;
                    $encaisse = 0;
                    $reste = 0;
                @endphp

                @foreach($result as $r)
                    <tr>
                        <td>{{ $r['facture'] }}</td>
                        <td>{{ $r['date'] }}</td>
                        <td class="text-end">{{ number_format($r['montant'],0,',',' ') }}</td>
                        <td class="text-end">{{ number_format($r['encaisse'],0,',',' ') }}</td>
                        <td class="text-end">{{ number_format($r['reste'],0,',',' ') }}</td>
                    </tr>

                    @php
                        $total += $r['montant'];
                        $encaisse += $r['encaisse'];
                        $reste += $r['reste'];
                    @endphp
                @endforeach

                </tbody>

                <tfoot class="table-secondary fw-bold">
                <tr>
                    <td colspan="2">TOTAL</td>
                    <td class="text-end">{{ number_format($total,0,',',' ') }}</td>
                    <td class="text-end">{{ number_format($encaisse,0,',',' ') }}</td>
                    <td class="text-end">{{ number_format($reste,0,',',' ') }}</td>
                </tr>
                </tfoot>
            </table>

        @endif

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
            <a href="{{ route('etat_situation_etudiant', ['etudiant' => 1]) }}"><strong>Nouvelle Etat facture</strong></a>
        </li>
    </ol>
@endsection
