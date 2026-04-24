@extends('layouts.app')

@section('content')

    <div class="container">

        <h2 class="text-center mb-4">
            💰 Détails des décaissements du bon : {{ $bon->nom_bon_commande }} Numero : BC{{$bon->id}}
        </h2>

        <!-- INFOS BON -->
        <div class="alert alert-info">
            <strong>Montant total :</strong> {{ number_format($bon->montant_total,0,',',' ') }} FCFA <br>
            <strong>Total décaissé :</strong> {{ number_format($total,0,',',' ') }} FCFA <br>
            <strong>Reste :</strong>
            <span class="badge {{ $reste > 0 ? 'bg-warning' : 'bg-success' }}">
            {{ number_format($reste,0,',',' ') }}
        </span>
        </div>

        <!-- 🔍 FILTRES -->
        <form method="GET" class="row g-3 mb-4">

            <div class="col-md-3">
                <label>Date début</label>
                <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}">
            </div>

            <div class="col-md-3">
                <label>Date fin</label>
                <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}">
            </div>

            <div class="col-md-2">
                <label>Personnel</label>
                <select name="id_personnel" class="form-control">
                    <option value="">-- Tous --</option>
                    @foreach($personnels as $p)
                        <option value="{{ $p->id }}" {{ request('id_personnel') == $p->id ? 'selected' : '' }}>
                            {{ $p->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label>Utilisateur</label>
                <select name="id_user" class="form-control">
                    <option value="">-- Tous --</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('id_user') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label>Caisse</label>
                <select name="id_caisse" class="form-control">
                    <option value="">-- Toutes --</option>
                    @foreach($caisses as $c)
                        <option value="{{ $c->id }}" {{ request('id_caisse') == $c->id ? 'selected' : '' }}>
                            {{ $c->nom_caisse }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>Entité</label>
                <select name="id_entite" class="form-control">
                    <option value="">-- Toutes --</option>
                    @foreach($entites as $e)
                        <option value="{{ $e->id }}" {{ request('id_entite') == $e->id ? 'selected' : '' }}>
                            {{ $e->nom_entite }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-12 text-center mt-3">
                <button class="btn btn-primary">🔍 Filtrer</button>
            </div>

        </form>

        <!-- TABLE -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover">

                <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Montant</th>
                    <th>Caisse</th>
                    <th>Personnel</th>
                    <th>Utilisateur</th>
                    <th>Motif</th>
                    <th>Entité</th>
                </tr>
                </thead>

                <tbody>
                @forelse($decaissements as $i => $d)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $d->date_depense }}</td>
                        <td>{{ number_format($d->montant,0,',',' ') }} FCFA</td>
                        <td>{{ $d->caisses->nom_caisse ?? 'N/A' }}</td>
                        <td>{{ $d->personnels->nom ?? 'N/A' }}</td>
                        <td>{{ $d->user->name ?? 'N/A' }}</td>
                        <td>{{ $d->motif }}</td>
                        <td>{{ $d->bon->entites->nom_entite ?? 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">⚠ Aucun décaissement</td>
                    </tr>
                @endforelse
                </tbody>

                <!-- TOTAL -->
                <tfoot>
                <tr class="table-dark">
                    <th colspan="2">TOTAL</th>
                    <th>{{ number_format($total,0,',',' ') }} FCFA</th>
                    <th colspan="4"></th>
                </tr>
                </tfoot>

            </table>
        </div>

    </div>
    <a href="{{ route('decaissements.pdf', ['bon_id'=>$bon->id] + request()->all()) }}" class="btn btn-danger">
        📄 Export PDF
    </a>
@endsection
@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('budget') }}"><strong>budget</strong></a></li>
        <li><a href="{{ route('decaissements.index') }}"><strong>Decaissements</strong></a></li>
        {{--<li><a href="{{ route('budget') }}"><strong>budget</strong></a></li>--}}

        {{--<li class="active"><strong>{{ $title }}</strong></li>--}}
    </ol>
@endsection