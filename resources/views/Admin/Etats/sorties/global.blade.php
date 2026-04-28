
@extends('layouts.app')

@section('content')
    <div class="scroll-container">
    <div class="container">

        {{-- ================= HEADER IMPRESSION ================= --}}
        <div class="d-none d-print-block text-center mb-3">
            <h3>📊 ÉTAT D’ATTERRISSAGE BUDGÉTAIRE GLOBAL</h3>
            <p>
                Période :
                {{ request('date_debut') ?? 'Début' }}
                -
                {{ request('date_fin') ?? 'Aujourd\'hui' }}
            </p>
        </div>

        {{-- ================= TITRE ================= --}}
        <div class="mb-3 no-print">
            <h3>📊 Atterrissage budgétaire global</h3>
            <p class="text-muted">Analyse consolidée des entrées et sorties par entité</p>
        </div>

        {{-- ================= FILTRES ================= --}}
        <form method="GET" class="row g-3 mb-4 no-print">

            <div class="col-md-3">
                <label>Année académique</label>
                <select name="id_annee_academique" class="form-control">
                    <option value="">-- Toutes --</option>
                    @foreach($annees as $a)
                        <option value="{{ $a->id }}"
                                {{ request('id_annee_academique') == $a->id ? 'selected' : '' }}>
                            {{ $a->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>Entité</label>
                <select name="id_entite" class="form-control">
                    <option value="">-- Toutes --</option>
                    @foreach($entites as $e)
                        <option value="{{ $e->id }}"
                                {{ request('id_entite') == $e->id ? 'selected' : '' }}>
                            {{ $e->nom_entite }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>Budget</label>
                <select name="id_budget" class="form-control">
                    <option value="">-- Tous --</option>
                    @foreach($budgets as $b)
                        <option value="{{ $b->id }}"
                                {{ request('id_budget') == $b->id ? 'selected' : '' }}>
                            {{ $b->libelle_ligne_budget }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>Date début</label>
                <input type="date" name="date_debut"
                       value="{{ request('date_debut') }}"
                       class="form-control">
            </div>

            <div class="col-md-3">
                <label>Date fin</label>
                <input type="date" name="date_fin"
                       value="{{ request('date_fin') }}"
                       class="form-control">
            </div>

            <div class="col-md-12 mt-3 text-center">
                <button class="btn btn-primary">🔍 Rechercher</button>

                <a href="{{ url()->current() }}" class="btn btn-secondary">
                    ♻ Reset
                </a>

                <button type="button" onclick="window.print()" class="btn btn-dark">
                    🖨 Imprimer
                </button>
                <a href="{{ route('etat_sorties.global.pdf', request()->query()) }}" class="btn btn-danger">
                    PDF
                </a>

                <a href="{{ route('etat_sorties.global.excel', request()->query()) }}" class="btn btn-success">
                    Excel
                </a>
            </div>
        </form>

        {{-- ================= KPI ================= --}}
        <div class="alert alert-info text-center no-print">
            💰 Disponibilité : <strong>{{ number_format($disponibilite) }} FCFA</strong>
            |
            ⚠️ Déficit :
            <strong class="{{ $deficit < 0 ? 'text-danger' : 'text-success' }}">
                {{ number_format($deficit) }} FCFA
            </strong>
        </div>

        {{-- ================= ENTRÉES ================= --}}
        <h4 class="bg-primary text-white p-2">🔵 ENTRÉES</h4>

        @foreach($entreesGrouped as $entite => $lignes)

            <h5 class="mt-3">🏢 {{ $entite }}</h5>

            <table class="table table-bordered table-sm">
                <thead class="table-dark">
                <tr>
                    <th>Budget</th>
                    <th>Ligne</th>
                    <th>Élément</th>
                    <th>Donnée</th>
                    <th>Prévu</th>
                    <th>Facturé</th>
                    <th>Encaissé</th>
                    <th>Reste</th>
                </tr>
                </thead>

                <tbody>
                @php $tPrevu=0;$tFacture=0;$tEncaisse=0;$tReste=0; @endphp

                @foreach($lignes as $e)
                    @php
                        $tPrevu += $e['prevu'];
                        $tFacture += $e['facture'];
                        $tEncaisse += $e['encaisse'];
                        $tReste += $e['reste'];
                    @endphp

                    <tr>
                        <td>{{ $e['budget'] }}</td>
                        <td>{{ $e['ligne'] }}</td>
                        <td>{{ $e['element'] ?? '—' }}</td>
                        <td>{{ $e['donnee'] }}</td>
                        <td>{{ number_format($e['prevu']) }}</td>
                        <td>{{ number_format($e['facture']) }}</td>
                        <td>{{ number_format($e['encaisse']) }}</td>
                        <td>{{ number_format($e['reste']) }}</td>
                    </tr>
                @endforeach

                <tr class="table-success fw-bold">
                    <td colspan="4">TOTAL {{ $entite }}</td>
                    <td>{{ number_format($tPrevu) }}</td>
                    <td>{{ number_format($tFacture) }}</td>
                    <td>{{ number_format($tEncaisse) }}</td>
                    <td>{{ number_format($tReste) }}</td>
                </tr>

                </tbody>
            </table>

        @endforeach

        {{-- 🔥 SAUT DE PAGE --}}
        <div style="page-break-before: always;"></div>

        {{-- ================= SORTIES ================= --}}
        <h4 class="bg-danger text-white p-2">🔴 SORTIES</h4>

        @foreach($sortiesGrouped as $entite => $lignes)

            <h5 class="mt-3">🏢 {{ $entite }}</h5>

            <table class="table table-bordered table-sm">
                <thead class="table-dark">
                <tr>
                    <th>Budget</th>
                    <th>Ligne</th>
                    <th>Élément</th>
                    <th>Donnée</th>
                    <th>Prévu</th>
                    <th>Dépensé</th>
                    <th>Reste</th>
                    <th>Décision</th>
                </tr>
                </thead>

                <tbody>
                @php $tPrevu=0;$tDepense=0;$tReste=0; @endphp

                @foreach($lignes as $s)
                    @php
                        $tPrevu += $s['prevu'];
                        $tDepense += $s['depense'];
                        $tReste += $s['reste'];
                    @endphp

                    <tr>
                        <td>{{ $s['budget'] }}</td>
                        <td>{{ $s['ligne'] }}</td>
                        <td>{{ $s['element'] ?? '—' }}</td>
                        <td>{{ $s['donnee'] }}</td>
                        <td>{{ number_format($s['prevu']) }}</td>
                        <td>{{ number_format($s['depense']) }}</td>
                        <td>{{ number_format($s['reste']) }}</td>
                        <td>
                            @if($s['reste'] <= 0)
                                ✔ OK
                            @elseif($s['depense'] > $s['prevu'])
                                ⚠ Dépassement
                            @else
                                Suivi
                            @endif
                        </td>
                    </tr>
                @endforeach

                <tr class="table-success fw-bold">
                    <td colspan="4">TOTAL {{ $entite }}</td>
                    <td>{{ number_format($tPrevu) }}</td>
                    <td>{{ number_format($tDepense) }}</td>
                    <td>{{ number_format($tReste) }}</td>
                    <td></td>
                </tr>

                </tbody>
            </table>

        @endforeach

    </div>
    </div>

    {{-- ================= CSS IMPRESSION ================= --}}
    <style>
    /* ================================
    📄 IMPRESSION PRO A3
    ================================ */
    @page  {
        margin-top: 2%;
        margin-bottom: 2%;
        margin-left: 2%;
        margin-right: 2%;
        size: A4,A3 portrait;
    }

  /*  .scroll-container {
        max-height: 600px;
        overflow-y: auto;
    }*/
    /* ================================
    GLOBAL
    ================================ */
    body {
    margin: 0;
    padding: 0;
    font-size: 15px;
    -webkit-print-color-adjust: exact;
    }

    /* ================================
    ❌ MASQUER INTERFACE
    ================================ */
    @media print {

        @page  {
            margin-top: 2%;
            margin-bottom: 2%;
            margin-left: 2%;
            margin-right: 2%;
            size: A4,A3 portrait;
        }
       /* table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }
        .scroll-container {
            max-height: 600px;
            overflow-y: auto;
        }
        .page-break {
            page-break-before: always;
        }

        table {
            page-break-inside: auto;
        }

        tr {
            page-break-inside: avoid;
        }*/
        .wrapper,
    .navbar,
    .sidebar,
    .breadcrumb,
    .btn,
    form,
    select,
    input,
    .alert,
    nav,
    footer,
    header {
    display: none !important;
    }

    /* ================================
    CONTENU
    ================================= */
    .container {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0;
    padding: 0;
    }

    /* ================================
    TITRE
    ================================= */
    .titre {
    text-align: center;
    margin: 5mm 0;
    font-weight: bold;
    font-size: 16px;
    }

    /* ================================
    TABLE
    ================================= */
    table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11px;
    margin-bottom: 10mm;
    }

    th, td {
    border: 1px solid black;
    padding: 4px;
    word-break: break-word;
    }

    th {
    background: #EEE;
    text-align: center;
    }

    thead {
    display: table-header-group;
    }

    tr {
    page-break-inside: avoid;
    }

    /* ================================
    TOTAUX
    ================================= */
    .total {
    font-weight: bold;
    background: #DDD;
    }

    /* ================================
    BADGES PRINT
    ================================= */
    .badge {
    border: 1px solid black;
    background: none !important;
    color: black !important;
    }

    /* ================================
    PAGINATION
    ================================= */
   /* .page-break {
    page-break-before: always;
    }*/

    }
    </style>
@endsection
@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent; padding: 4px 10px">
        <li class="breadcrumb-item">
            <a href="{{ route('home') }}"><strong>Accueil</strong></a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('etat_sorties.index') }}"><strong>Etats budgetaires sorties</strong></a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('etat_sorties.pilotage') }}"><strong>Pilotage</strong></a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('etat_sorties.atterrissage') }}"><strong>Atterrissage</strong></a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('etat_sorties.decaissements') }}"><strong>Decaissements</strong></a>
        </li>
        <li class="breadcrumb-item active">
            <strong>Global</strong>
        </li>
    </ol>
@endsection

{{--@extends('layouts.app')

@section('content')

    <div class="container">
        <style>
            /* =========================
               🖨 MODE IMPRESSION A4
            ========================= */
            @media print {

                /* Format A4 */
                @page {
                    size: A4 portrait;
                    margin: 10mm;
                }

                body {
                    font-size: 11px;
                    color: #000;
                    background: #fff;
                }

                /* ❌ Masquer éléments inutiles */
                form,
                .btn,
                .breadcrumb,
                nav,
                .alert-info {
                    display: none !important;
                }

                /* Conteneur plein écran */
                .container {
                    width: 100%;
                    max-width: 100%;
                }

                /* Titres */
                h3, h4, h5 {
                    color: #000 !important;
                    margin-top: 10px;
                    margin-bottom: 5px;
                }

                /* Table */
                table {
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 10px;
                }

                th, td {
                    border: 1px solid #000 !important;
                    padding: 4px;
                    word-break: break-word;
                }

                th {
                    background: #eee !important;
                    color: #000 !important;
                }

                /* Répéter entête tableau */
                thead {
                    display: table-header-group;
                }

                /* Eviter coupure lignes */
                tr {
                    page-break-inside: avoid;
                }

                /* Saut de page par entité */
                h5 {
                    page-break-before: auto;
                }

                /* Totaux */
                .table-success {
                    background: #ddd !important;
                    font-weight: bold;
                }

                /* Badges en noir et blanc */
                .badge {
                    color: #000 !important;
                    border: 1px solid #000;
                    background: none !important;
                }
            }
        </style>
        <div class="d-none d-print-block text-center mb-3">
            <h3>ÉTAT D’ATTERRISSAGE BUDGÉTAIRE</h3>
            <p>
                Période :
                {{ request('date_debut') ?? 'Début' }}
                -
                {{ request('date_fin') ?? 'Aujourd\'hui' }}
            </p>
        </div>
        --}}{{-- ================= TITRE ================= --}}{{--
        <div class="mb-3">
            <h3>📊 Atterrissage budgétaire global</h3>
            <p class="text-muted">Analyse consolidée des entrées et sorties par entité</p>
        </div>

        --}}{{-- ================= FILTRES ================= --}}{{--
        <form method="GET" class="row g-3 mb-4">

            <div class="col-md-3">
                <label>Année académique</label>
                <select name="id_annee_academique" class="form-control">
                    <option value="">-- Toutes --</option>
                    @foreach($annees ?? [] as $a)
                        <option value="{{ $a->id }}"
                                {{ request('id_annee_academique') == $a->id ? 'selected' : '' }}>
                            {{ $a->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>Entité</label>
                <select name="id_entite" class="form-control">
                    <option value="">-- Toutes --</option>
                    @foreach($entites ?? [] as $e)
                        <option value="{{ $e->id }}"
                                {{ request('id_entite') == $e->id ? 'selected' : '' }}>
                            {{ $e->nom_entite }}
                        </option>
                    @endforeach
                </select>
            </div>

            --}}{{-- 🔥 BUDGET (corrigé) --}}{{--
            <div class="col-md-3">
                <label>Budget</label>
                <select name="id_budget" class="form-control">
                    <option value="">-- Tous --</option>
                    @foreach($budgets as $b)
                        <option value="{{ $b->id }}"
                                {{ request('id_budget') == $b->id ? 'selected' : '' }}>
                            {{ $b->libelle_ligne_budget }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>Date début</label>
                <input type="date" name="date_debut"
                       value="{{ request('date_debut') }}"
                       class="form-control">
            </div>

            <div class="col-md-3">
                <label>Date fin</label>
                <input type="date" name="date_fin"
                       value="{{ request('date_fin') }}"
                       class="form-control">
            </div>

            <div class="col-md-12 mt-3 text-center">
                <button class="btn btn-primary">🔍 Rechercher</button>

                <a href="{{ url()->current() }}" class="btn btn-secondary">
                    ♻ Reset
                </a>

                <button type="button" onclick="printPage()" class="btn btn-dark">
                    🖨 Imprimer
                </button>

                <script>
                    function printPage() {
                        window.print();
                    }
                </script>
            </div>

        </form>

        --}}{{-- ================= KPI ================= --}}{{--
        <div class="alert alert-info text-center">
            💰 Disponibilité : <strong>{{ number_format($disponibilite) }} FCFA</strong>
            |
            ⚠️ Déficit :
            <strong class="{{ $deficit < 0 ? 'text-danger' : 'text-success' }}">
                {{ number_format($deficit) }} FCFA
            </strong>
        </div>

        --}}{{-- ================= ENTRÉES ================= --}}{{--
        <h4 class="bg-primary text-white p-2">🔵 ENTRÉES (Prévision / Recouvrement)</h4>

        @foreach($entreesGrouped as $entite => $lignes)

            <h5 class="mt-3">🏢 {{ $entite }}</h5>

            <table class="table table-bordered table-sm">
                <thead class="table-dark">
                <tr>
                    <th>Budget</th>
                    <th>Ligne</th>
                    <th>Élément</th>
                    <th>Donnée</th>
                    <th>Prévu</th>
                    <th>Facturé</th>
                    <th>Encaissé</th>
                    <th>Reste</th>
                </tr>
                </thead>

                <tbody>

                @php
                    $tPrevu = $tFacture = $tEncaisse = $tReste = 0;
                @endphp

                @foreach($lignes as $e)

                    @php
                        $tPrevu += $e['prevu'];
                        $tFacture += $e['facture'];
                        $tEncaisse += $e['encaisse'];
                        $tReste += $e['reste'];
                    @endphp

                    <tr>
                        <td>{{ $e['budget'] }}</td>
                        <td>{{ $e['ligne'] }}</td>
                        <td>{{ $e['element'] ?? '—' }}</td>
                        <td>{{ $e['donnee'] }}</td>
                        <td>{{ number_format($e['prevu']) }}</td>
                        <td>{{ number_format($e['facture']) }}</td>
                        <td>{{ number_format($e['encaisse']) }}</td>
                        <td>{{ number_format($e['reste']) }}</td>
                    </tr>

                @endforeach

                <tr class="table-success fw-bold">
                    <td colspan="4">TOTAL {{ $entite }}</td>
                    <td>{{ number_format($tPrevu) }}</td>
                    <td>{{ number_format($tFacture) }}</td>
                    <td>{{ number_format($tEncaisse) }}</td>
                    <td>{{ number_format($tReste) }}</td>
                </tr>

                </tbody>
            </table>

        @endforeach

        <div style="page-break-before: always;"></div>
        --}}{{-- ================= SORTIES ================= --}}{{--
        <h4 class="bg-danger text-white p-2 mt-4">🔴 SORTIES (Prévu vs Réalisé)</h4>

        @foreach($sortiesGrouped as $entite => $lignes)

            <h5 class="mt-3">🏢 {{ $entite }}</h5>

            <table class="table table-bordered table-sm">
                <thead class="table-dark">
                <tr>
                    <th>Budget</th>
                    <th>Ligne</th>
                    <th>Élément</th>
                    <th>Donnée</th>
                    <th>Prévu</th>
                    <th>Dépensé</th>
                    <th>Reste</th>
                    <th>Décision</th>
                </tr>
                </thead>

                <tbody>

                @php
                    $tPrevu = $tDepense = $tReste = 0;
                @endphp

                @foreach($lignes as $s)

                    @php
                        $tPrevu += $s['prevu'];
                        $tDepense += $s['depense'];
                        $tReste += $s['reste'];
                    @endphp

                    <tr>
                        <td>{{ $s['budget'] }}</td>
                        <td>{{ $s['ligne'] }}</td>
                        <td>{{ $s['element'] ?? '—' }}</td>
                        <td>{{ $s['donnee'] }}</td>
                        <td>{{ number_format($s['prevu']) }}</td>
                        <td>{{ number_format($s['depense']) }}</td>
                        <td>{{ number_format($s['reste']) }}</td>
                        <td>
                            @if($s['reste'] <= 0)
                                <span class="badge bg-success">✔ OK</span>
                            @elseif($s['depense'] > $s['prevu'])
                                <span class="badge bg-danger">⚠ Dépassement</span>
                            @else
                                <span class="badge bg-warning">Suivi</span>
                            @endif
                        </td>
                    </tr>

                @endforeach

                <tr class="table-success fw-bold">
                    <td colspan="4">TOTAL {{ $entite }}</td>
                    <td>{{ number_format($tPrevu) }}</td>
                    <td>{{ number_format($tDepense) }}</td>
                    <td>{{ number_format($tReste) }}</td>
                    <td></td>
                </tr>

                </tbody>
            </table>

        @endforeach

    </div>
@endsection--}}

{{--eends('layouts.app')

@section('content')
    <div class="container">

        --}}{{-- HEADER --}}{{--
        <div class="mb-4">
            <h3>📊 État global Entrées / Sorties</h3>
            <p class="text-muted">
                Analyse consolidée des prévisions, réalisations et soldes par entité.
            </p>
        </div>

        --}}{{-- FILTRES --}}{{--
        <form method="GET" action="{{ route('etat_sorties.global') }}" class="card p-3 mb-4 shadow-sm">
            <div class="row">

                <div class="col-md-3">
                    <label>Budget</label>
                    <select name="id_budget" class="form-control">
                        <option value="">-- Tous --</option>
                        @foreach($budgets as $b)
                            <option value="{{ $b->id }}" {{ request('id_budget') == $b->id ? 'selected' : '' }}>
                                {{ $b->libelle_ligne_budget }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Année académique</label>
                    <select name="id_annee_academique" class="form-control">
                        <option value="">-- Toutes --</option>
                        @foreach($annees as $a)
                            <option value="{{ $a->id }}" {{ request('id_annee_academique') == $a->id ? 'selected' : '' }}>
                                {{ $a->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Date début</label>
                    <input type="date" name="date_debut" value="{{ request('date_debut') }}" class="form-control">
                </div>

                <div class="col-md-3">
                    <label>Date fin</label>
                    <input type="date" name="date_fin" value="{{ request('date_fin') }}" class="form-control">
                </div>

            </div>

            <div class="text-center mt-3">
                <button class="btn btn-primary">🔍 Filtrer</button>
                <a href="{{ route('etat_sorties.global') }}" class="btn btn-secondary">♻ Reset</a>
                <button type="button" onclick="window.print()" class="btn btn-dark">🖨 Imprimer</button>
            </div>
        </form>

        --}}{{-- SOLDE GLOBAL --}}{{--
        <div class="alert alert-info text-center">
            💰 Disponibilité globale en caisse :
            <strong>{{ number_format($soldeGlobal,0,',',' ') }} FCFA</strong>
        </div>

        --}}{{-- ========================= --}}{{--
        --}}{{-- 🔵 ENTRÉES --}}{{--
        --}}{{-- ========================= --}}{{--
        @php
            $totalPrevu = 0;
            $totalEncaisse = 0;
            $totalReste = 0;
        @endphp

        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">
                📥 Entrées prévisionnelles / recouvrement
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                    <tr>
                        <th>Entité</th>
                        <th>Budget</th>
                        <th>Ligne</th>
                        <th>Prévu</th>
                        <th>Encaisse</th>
                        <th>Reste</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($entrees as $entite => $lignes)

                        @foreach($lignes as $e)

                            @php
                                $totalPrevu += $e['prevu'];
                                $totalEncaisse += $e['encaisse'];
                                $totalReste += $e['reste'];
                            @endphp

                            <tr>
                                <td>{{ $entite }}</td>
                                <td>{{ $e['budget'] }}</td>
                                <td>{{ $e['ligne'] }}</td>
                                <td>{{ number_format($e['prevu'],0,',',' ') }}</td>
                                <td>{{ number_format($e['encaisse'],0,',',' ') }}</td>
                                <td>{{ number_format($e['reste'],0,',',' ') }}</td>
                            </tr>

                        @endforeach

                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Aucune donnée</td>
                        </tr>
                    @endforelse
                    </tbody>

                    <tfoot>
                    <tr style="font-weight:bold; background:#f0f0f0;">
                        <td colspan="3">TOTAL</td>
                        <td>{{ number_format($totalPrevu,0,',',' ') }}</td>
                        <td>{{ number_format($totalEncaisse,0,',',' ') }}</td>
                        <td>{{ number_format($totalReste,0,',',' ') }}</td>
                    </tr>
                    </tfoot>

                </table>
            </div>
        </div>

        --}}{{-- ========================= --}}{{--
        --}}{{-- 🔴 SORTIES --}}{{--
        --}}{{-- ========================= --}}{{--
        @php
            $totalPrevuS = 0;
            $totalDepense = 0;
            $totalResteS = 0;
        @endphp

        <div class="card shadow-sm">
            <div class="card-header bg-danger text-white">
                📤 Sorties (prévu vs réalisé)
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                    <tr>
                        <th>Entité</th>
                        <th>Budget</th>
                        <th>Ligne</th>
                        <th>Prévu</th>
                        <th>Dépensé</th>
                        <th>Reste</th>
                        <th>Décision</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($sorties as $entite => $lignes)

                        @foreach($lignes as $s)

                            @php
                                $totalPrevuS += $s['prevu'] ?? 0;
                                $totalDepense += $s['depense'];
                                $totalResteS += $s['reste'] ?? 0;
                            @endphp

                            <tr>
                                <td>{{ $entite }}</td>
                                <td>{{ $s['budget'] }}</td>
                                <td>{{ $s['ligne'] }}</td>
                                <td>{{ number_format($s['prevu'] ?? 0,0,',',' ') }}</td>
                                <td>{{ number_format($s['depense'],0,',',' ') }}</td>
                                <td>{{ number_format($s['reste'] ?? 0,0,',',' ') }}</td>

                                <td>
                                    @if(($s['reste'] ?? 0) <= 0)
                                        <span class="badge bg-success">✔ OK</span>
                                    @elseif($s['depense'] > ($s['prevu'] ?? 0))
                                        <span class="badge bg-danger">⚠ Dépassement</span>
                                    @else
                                        <span class="badge bg-warning">Suivi</span>
                                    @endif
                                </td>
                            </tr>

                        @endforeach

                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Aucune donnée</td>
                        </tr>
                    @endforelse
                    </tbody>

                    <tfoot>
                    <tr style="font-weight:bold; background:#f0f0f0;">
                        <td colspan="3">TOTAL</td>
                        <td>{{ number_format($totalPrevuS,0,',',' ') }}</td>
                        <td>{{ number_format($totalDepense,0,',',' ') }}</td>
                        <td>{{ number_format($totalResteS,0,',',' ') }}</td>
                        <td></td>
                    </tr>
                    </tfoot>

                </table>
            </div>
        </div>

    </div>
@endsection
@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent; padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><strong>État global</strong></li>
    </ol>
    @endsection--}}{{--xt--}}

{{--@extends('layouts.app')

@section('content')
    <div class="container">



        <div class="mb-4">
            <h3>📊 État global Entrées / Sorties</h3>
            <p class="text-muted">
                Analyse consolidée des prévisions, réalisations et soldes par entité.
            </p>
        </div>




        <form method="GET" action="{{ route('etat_sorties.global') }}" class="card p-3 mb-4 shadow-sm">

            <div class="row">

                <div class="col-md-3">
                    <label>Budget *</label>
                    <select name="id_budget" class="form-control" required>
                        <option value="">-- Choisir --</option>
                        @foreach($budgets as $b)
                            <option value="{{ $b->id }}"
                                    {{ request('id_budget') == $b->id ? 'selected' : '' }}>
                                {{ $b->libelle_ligne_budget }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Année académique *</label>
                    <select name="id_annee_academique" class="form-control" required>
                        <option value="">-- Choisir --</option>
                        @foreach($annees as $a)
                            <option value="{{ $a->id }}"
                                    {{ request('id_annee_academique') == $a->id ? 'selected' : '' }}>
                                {{ $a->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Date début *</label>
                    <input type="date" name="date_debut"
                           value="{{ request('date_debut') }}"
                           class="form-control" required>
                </div>

                <div class="col-md-3">
                    <label>Date fin *</label>
                    <input type="date" name="date_fin"
                           value="{{ request('date_fin') }}"
                           class="form-control" required>
                </div>

            </div>

            <div class="text-center mt-3">
                <button class="btn btn-primary">🔍 Rechercher</button>

                <a href="{{ route('etat_sorties.global') }}" class="btn btn-secondary">
                    ♻ Réinitialiser
                </a>

                <a href="{{ route('etat_sorties.global.pdf', request()->all()) }}"
                   class="btn btn-danger">
                    📄 PDF
                </a>

                <a href="{{ route('etat_sorties.global.excel', request()->all()) }}"
                   class="btn btn-success">
                    📊 Excel
                </a>

                <button type="button" onclick="window.print()" class="btn btn-dark">
                    🖨 Imprimer
                </button>
            </div>

        </form>




        <div class="alert alert-info text-center">
            💰 Disponibilité globale en caisse :
            <strong>{{ number_format($soldeGlobal,0,',',' ') }} FCFA</strong>
        </div>



        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">
                📥 Entrées prévisionnelles / recouvrement
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                    <tr>
                        <th>Entité</th>
                        <th>Budget</th>
                        <th>Ligne</th>
                        <th>Prévu</th>
                        <th>Encaisse</th>
                        <th>Reste</th>
                    </tr>
                    </thead>

                    <tbody>
                   --}}{{-- @forelse($entreesGrouped as $entite => $lignes)

                        @foreach($lignes as $e)
                            <tr>
                                <td>{{ $entite }}</td>
                                <td>{{ $e['budget'] }}</td>
                                <td>{{ $e['ligne'] }}</td>
                                <td>{{ number_format($e['prevu']) }}</td>
                                <td>{{ number_format($e['encaisse']) }}</td>
                                <td>{{ number_format($e['reste']) }}</td>
                            </tr>
                        @endforeach

                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Aucune donnée</td>
                        </tr>
                    @endforelse--}}{{--
                   @forelse($entreesGrouped as $entite => $lignes)

                       <tr class="table-primary">
                           <td colspan="6"><strong>🏢 {{ $entite }}</strong></td>
                       </tr>

                       @foreach($lignes as $e)
                           <tr>
                               <td></td> --}}{{-- vide car entité déjà affichée --}}{{--
                               <td>{{ $e['budget'] }}</td>
                               <td>{{ $e['ligne'] }}</td>
                               <td>{{ number_format($e['prevu'],0,',',' ') }}</td>
                               <td>{{ number_format($e['encaisse'],0,',',' ') }}</td>
                               <td>{{ number_format($e['reste'],0,',',' ') }}</td>
                           </tr>
                       @endforeach
                   @empty
                       <tr>
                           <td colspan="6" class="text-center">Aucune donnée</td>
                       </tr>
                   @endforelse
                    </tbody>
                    <tfoot>
                    <tr style="font-weight:bold; background:#f0f0f0;">
                        <td colspan="3">TOTAL</td>
                        <td>{{ number_format(collect($entreesGrouped)->flatten(1)->sum('prevu')) }}</td>
                        <td>{{ number_format(collect($entreesGrouped)->flatten(1)->sum('encaisse')) }}</td>
                        <td>{{ number_format(collect($entreesGrouped)->flatten(1)->sum('reste')) }}</td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>



        <div class="card shadow-sm">
            <div class="card-header bg-danger text-white">
                📤 Sorties (prévu vs réalisé)
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                    <tr>
                        <th>Entité</th>
                        <th>Budget</th>
                        <th>Ligne</th>
                        <th>Prévu</th>
                        <th>Dépensé</th>
                        <th>Reste</th>
                        <th>Décision</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($sortiesGrouped as $entite => $lignes)

                        <tr class="table-danger">
                            <td colspan="7"><strong>🏢 {{ $entite }}</strong></td>
                        </tr>

                        @foreach($lignes as $s)
                            <tr>
                                <td></td>
                                <td>{{ $s['budget'] }}</td>
                                <td>{{ $s['ligne'] }}</td>
                                <td>{{ number_format($s['prevu']) }}</td>
                                <td>{{ number_format($s['depense']) }}</td>
                                <td>{{ number_format($s['reste']) }}</td>
                                <td>
                                    @if($s['reste'] <= 0)
                                        <span class="badge bg-success">✔ OK</span>
                                    @elseif($s['depense'] > $s['prevu'])
                                        <span class="badge bg-danger">⚠ Dépassement</span>
                                    @else
                                        <span class="badge bg-warning">Suivi</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Aucune donnée</td>
                        </tr>
                    @endforelse
                   --}}{{-- @forelse($sortiesGrouped as $entite => $lignes)

                        @foreach($lignes as $s)

                            <tr>
                                <td>{{ $entite }}</td>
                                <td>{{ $s['budget'] }}</td>
                                <td>{{ $s['ligne'] }}</td>
                                <td>{{ number_format($s['prevu']) }}</td>
                                <td>{{ number_format($s['depense']) }}</td>
                                <td>{{ number_format($s['reste']) }}</td>

                                <td>
                                    @if($s['reste'] <= 0)
                                        <span class="badge bg-success">✔ OK</span>
                                    @elseif($s['depense'] > $s['prevu'])
                                        <span class="badge bg-danger">⚠ Dépassement</span>
                                    @else
                                        <span class="badge bg-warning">Suivi</span>
                                    @endif
                                </td>

                            </tr>

                        @endforeach

                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Aucune donnée</td>
                        </tr>
                    @endforelse--}}{{--
                    </tbody>
                    <tfoot>
                    <tr style="font-weight:bold; background:#f0f0f0;">
                        <td colspan="3">TOTAL</td>
                        <td>{{ number_format(collect($sortiesGrouped)->flatten(1)->sum('prevu')) }}</td>
                        <td>{{ number_format(collect($sortiesGrouped)->flatten(1)->sum('depense')) }}</td>
                        <td>{{ number_format(collect($sortiesGrouped)->flatten(1)->sum('reste')) }}</td>
                        <td></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>
@endsection

@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent; padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><strong>État global</strong></li>
    </ol>
@endsection--}}
{{--
@extends('layouts.app')

@section('content')
    <div class="container">

        <div class="alert alert-info text-center">
            💰 Disponibilité globale :
            <strong>{{ number_format($soldeGlobal,0,',',' ') }} FCFA</strong>
        </div>

        --}}
{{-- 🔵 ENTREES --}}{{--

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">Entrées</div>

            <table class="table table-bordered">
                <thead class="table-dark">
                <tr>
                    <th>Entité</th>
                    <th>Budget</th>
                    <th>Ligne</th>
                    <th>Élément</th>
                    <th>Prévu</th>
                    <th>Encaisse</th>
                    <th>Reste</th>
                </tr>
                </thead>

                <tbody>

                @foreach($entreesGrouped as $entite => $lignes)

                    <tr class="table-primary">
                        <td colspan="7"><strong>{{ $entite }}</strong></td>
                    </tr>

                    @foreach($lignes as $ligne => $elements)

                        <tr class="table-secondary">
                            <td colspan="7"><strong>{{ $ligne }}</strong></td>
                        </tr>

                        @foreach($elements as $e)

                            <tr>
                                <td></td>
                                <td>{{ $e['budget'] }}</td>
                                <td></td>
                                <td>{{ $e['element'] }}</td>
                                <td>{{ number_format($e['prevu'],0,',',' ') }}</td>
                                <td>{{ number_format($e['encaisse'],0,',',' ') }}</td>
                                <td>{{ number_format($e['reste'],0,',',' ') }}</td>
                            </tr>

                        @endforeach
                    @endforeach
                @endforeach

                </tbody>
            </table>
        </div>

        --}}
{{-- 🔴 SORTIES --}}{{--

        <div class="card">
            <div class="card-header bg-danger text-white">Sorties</div>

            <table class="table table-bordered">
                <thead class="table-dark">
                <tr>
                    <th>Entité</th>
                    <th>Budget</th>
                    <th>Ligne</th>
                    <th>Élément</th>
                    <th>Prévu</th>
                    <th>Dépensé</th>
                    <th>Reste</th>
                    <th>Décision</th>
                </tr>
                </thead>

                <tbody>

                @foreach($sortiesGrouped as $entite => $lignes)

                    <tr class="table-danger">
                        <td colspan="8"><strong>{{ $entite }}</strong></td>
                    </tr>

                    @foreach($lignes as $ligne => $elements)

                        <tr class="table-secondary">
                            <td colspan="8"><strong>{{ $ligne }}</strong></td>
                        </tr>

                        @foreach($elements as $s)

                            <tr>
                                <td></td>
                                <td>{{ $s['budget'] }}</td>
                                <td></td>
                                <td>{{ $s['element'] }}</td>
                                <td>{{ number_format($s['prevu']) }}</td>
                                <td>{{ number_format($s['depense']) }}</td>
                                <td>{{ number_format($s['reste']) }}</td>
                                <td>
                                    @if($s['reste'] <= 0)
                                        <span class="badge bg-success">OK</span>
                                    @elseif($s['depense'] > $s['prevu'])
                                        <span class="badge bg-danger">Dépassement</span>
                                    @else
                                        <span class="badge bg-warning">Suivi</span>
                                    @endif
                                </td>
                            </tr>

                        @endforeach
                    @endforeach
                @endforeach

                </tbody>
            </table>
        </div>

    </div>
@endsection--}}
{{--
@extends('layouts.app')

@section('content')
<div class="container">



    <div class="mb-4">
        <h3>📊 État global Entrées / Sorties</h3>
        <p class="text-muted">
            Analyse consolidée des prévisions, réalisations et soldes par entité.
        </p>
    </div>




    <form method="GET" action="{{ route('etat_sorties.global') }}" class="card p-3 mb-4 shadow-sm">

        <div class="row">

            <div class="col-md-3">
                <label>Budget *</label>
                <select name="id_budget" class="form-control" required>
                    <option value="">-- Choisir --</option>
                    @foreach($budgets as $b)
                        <option value="{{ $b->id }}"
                                {{ request('id_budget') == $b->id ? 'selected' : '' }}>
                            {{ $b->libelle_ligne_budget }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>Année académique *</label>
                <select name="id_annee_academique" class="form-control" required>
                    <option value="">-- Choisir --</option>
                    @foreach($annees as $a)
                        <option value="{{ $a->id }}"
                                {{ request('id_annee_academique') == $a->id ? 'selected' : '' }}>
                            {{ $a->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>Date début *</label>
                <input type="date" name="date_debut"
                       value="{{ request('date_debut') }}"
                       class="form-control" required>
            </div>

            <div class="col-md-3">
                <label>Date fin *</label>
                <input type="date" name="date_fin"
                       value="{{ request('date_fin') }}"
                       class="form-control" required>
            </div>

        </div>

        <div class="text-center mt-3">
            <button class="btn btn-primary">🔍 Rechercher</button>

            <a href="{{ route('etat_sorties.global') }}" class="btn btn-secondary">
                ♻ Réinitialiser
            </a>

            <a href="{{ route('etat_sorties.global.pdf', request()->all()) }}"
               class="btn btn-danger">
                📄 PDF
            </a>

            <a href="{{ route('etat_sorties.global.excel', request()->all()) }}"
               class="btn btn-success">
                📊 Excel
            </a>

            <button type="button" onclick="window.print()" class="btn btn-dark">
                🖨 Imprimer
            </button>
        </div>

    </form>

<div class="alert alert-info text-center">
    💰 Disponibilité : {{ number_format($disponibilite) }} FCFA |
    ⚠️ Déficit : {{ number_format($deficit) }} FCFA
</div>


--}}{{-- 🔵 ENTREES --}}{{--

@foreach($entrees as $entite => $lignes)

    <h5 class="bg-primary text-white p-2">{{ $entite }}</h5>

    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Budget</th>
            <th>Ligne</th>
            <th>Élément</th>
            <th>Prévu</th>
            <th>Réalisé</th>
            <th>Reste</th>
        </tr>
        </thead>

        <tbody>

        @foreach($lignes as $ligne => $elements)
            <tr class="table-secondary">
                <td colspan="6">{{ $ligne }}</td>
            </tr>

            @foreach($elements as $e)
                <tr>
                    <td>{{ $e['budget'] }}</td>
                    <td></td>
                    <td>{{ $e['element'] }}</td>
                    <td>{{ number_format($e['prevu']) }}</td>
                    <td>{{ number_format($e['realise']) }}</td>
                    <td>{{ number_format($e['reste']) }}</td>
                </tr>
            @endforeach
        @endforeach

        </tbody>
    </table>

@endforeach



--}}{{-- 🔴 SORTIES --}}{{--

@foreach($sorties as $entite => $lignes)

    <h5 class="bg-danger text-white p-2">{{ $entite }}</h5>

    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Budget</th>
            <th>Ligne</th>
            <th>Élément</th>
            <th>Prévu</th>
            <th>Réalisé</th>
            <th>Reste</th>
            <th>Décision</th>
        </tr>
        </thead>

        <tbody>

        @foreach($lignes as $ligne => $elements)
            <tr class="table-secondary">
                <td colspan="7">{{ $ligne }}</td>
            </tr>

            @foreach($elements as $s)
                <tr>
                    <td>{{ $s['budget'] }}</td>
                    <td></td>
                    <td>{{ $s['element'] }}</td>
                    <td>{{ number_format($s['prevu']) }}</td>
                    <td>{{ number_format($s['realise']) }}</td>
                    <td>{{ number_format($s['reste']) }}</td>
                    <td>
                        @if($s['reste'] <= 0)
                            <span class="badge bg-success">OK</span>
                        @else
                            <span class="badge bg-warning">Suivi</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        @endforeach

        </tbody>
    </table>

@endforeach
</div>
    @endsection--}}
{{--
@extends('layouts.app')

@section('content')
    <div class="container">

        --}}
{{-- HEADER --}}{{--

        <div class="mb-4">
            <h3>📊 État global Entrées / Sorties</h3>
            <p class="text-muted">
                Analyse consolidée des prévisions, réalisations et soldes par budget.
            </p>
        </div>

        --}}
{{-- FILTRES --}}{{--

        <form method="GET" action="{{ route('etat_sorties.global') }}" class="card p-3 mb-4 shadow-sm">

            <div class="row">

                <div class="col-md-3">
                    <label>Budget</label>
                    <select name="id_budget" class="form-control">
                        <option value="">-- Tous --</option>
                        @foreach($budgets ?? [] as $b)
                            <option value="{{ $b->id }}"
                                    {{ request('id_budget') == $b->id ? 'selected' : '' }}>
                                {{ $b->libelle_ligne_budget }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Année académique</label>
                    <select name="id_annee_academique" class="form-control">
                        <option value="">-- Toutes --</option>
                        @foreach($annees ?? [] as $a)
                            <option value="{{ $a->id }}"
                                    {{ request('id_annee_academique') == $a->id ? 'selected' : '' }}>
                                {{ $a->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Date début</label>
                    <input type="date" name="date_debut"
                           value="{{ request('date_debut') }}"
                           class="form-control">
                </div>

                <div class="col-md-3">
                    <label>Date fin</label>
                    <input type="date" name="date_fin"
                           value="{{ request('date_fin') }}"
                           class="form-control">
                </div>

            </div>

            <div class="text-center mt-3">
                <button class="btn btn-primary">🔍 Rechercher</button>
                <a href="{{ route('etat_sorties.global') }}" class="btn btn-secondary">♻ Reset</a>
                <button type="button" onclick="window.print()" class="btn btn-dark">🖨 Imprimer</button>
            </div>

        </form>

        --}}
{{-- KPI --}}{{--

        <div class="alert alert-info text-center">
            💰 Disponibilité : <strong>{{ number_format($disponibilite ?? 0) }}</strong> FCFA |
            ⚠️ Déficit : <strong>{{ number_format($deficit ?? 0) }}</strong> FCFA
        </div>

        --}}
{{-- ============================= --}}{{--

        --}}
{{-- 🔵 ENTREES --}}{{--

        --}}
{{-- ============================= --}}{{--

        <h4 class="bg-primary text-white p-2">ENTRÉES</h4>

        @forelse($entrees as $budget => $lignes)

            <h5 class="mt-3 text-primary">📁 Budget : {{ $budget }}</h5>

            <table class="table table-bordered table-sm">
                <thead class="table-dark">
                <tr>
                    <th>Ligne</th>
                    <th>Élément</th>
                    <th>Prévu</th>
                    <th>Réalisé</th>
                    <th>Reste</th>
                </tr>
                </thead>

                <tbody>

                @php
                    $totalPrevu = 0;
                    $totalRealise = 0;
                    $totalReste = 0;
                @endphp

                @foreach($lignes as $ligne => $elements)

                    <tr class="table-secondary">
                        <td colspan="5"><strong>{{ $ligne }}</strong></td>
                    </tr>

                    @foreach($elements as $e)

                        @php
                            $prevu = $e['prevu'] ?? 0;
                            $realise = $e['realise'] ?? 0;
                            $reste = $e['reste'] ?? 0;

                            $totalPrevu += $prevu;
                            $totalRealise += $realise;
                            $totalReste += $reste;
                        @endphp

                        <tr>
                            <td></td>
                            <td>{{ $e['element'] ?? 'N/A' }}</td>
                            <td>{{ number_format($prevu) }}</td>
                            <td>{{ number_format($realise) }}</td>
                            <td>{{ number_format($reste) }}</td>
                        </tr>

                    @endforeach

                @endforeach

                <tr class="table-success fw-bold">
                    <td colspan="2">TOTAL</td>
                    <td>{{ number_format($totalPrevu) }}</td>
                    <td>{{ number_format($totalRealise) }}</td>
                    <td>{{ number_format($totalReste) }}</td>
                </tr>

                </tbody>
            </table>

        @empty
            <div class="alert alert-warning">Aucune donnée d'entrée disponible</div>
        @endforelse


        --}}
{{-- ============================= --}}{{--

        --}}
{{-- 🔴 SORTIES --}}{{--

        --}}
{{-- ============================= --}}{{--

        <h4 class="bg-danger text-white p-2 mt-5">SORTIES</h4>

        @forelse($sorties as $budget => $lignes)

            <h5 class="mt-3 text-danger">📁 Budget : {{ $budget }}</h5>

            <table class="table table-bordered table-sm">
                <thead class="table-dark">
                <tr>
                    <th>Ligne</th>
                    <th>Élément</th>
                    <th>Prévu</th>
                    <th>Réalisé</th>
                    <th>Reste</th>
                    <th>Décision</th>
                </tr>
                </thead>

                <tbody>

                @php
                    $totalPrevu = 0;
                    $totalRealise = 0;
                    $totalReste = 0;
                @endphp

                @foreach($lignes as $ligne => $elements)

                    <tr class="table-secondary">
                        <td colspan="6"><strong>{{ $ligne }}</strong></td>
                    </tr>

                    @foreach($elements as $s)

                        @php
                            $prevu = $s['prevu'] ?? 0;
                            $realise = $s['realise'] ?? 0;
                            $reste = $s['reste'] ?? 0;

                            $totalPrevu += $prevu;
                            $totalRealise += $realise;
                            $totalReste += $reste;
                        @endphp

                        <tr>
                            <td></td>
                            <td>{{ $s['element'] ?? 'N/A' }}</td>
                            <td>{{ number_format($prevu) }}</td>
                            <td>{{ number_format($realise) }}</td>
                            <td>{{ number_format($reste) }}</td>
                            <td>
                                @if($reste <= 0)
                                    <span class="badge bg-success">OK</span>
                                @else
                                    <span class="badge bg-warning">Suivi</span>
                                @endif
                            </td>
                        </tr>

                    @endforeach

                @endforeach

                <tr class="table-success fw-bold">
                    <td colspan="2">TOTAL</td>
                    <td>{{ number_format($totalPrevu) }}</td>
                    <td>{{ number_format($totalRealise) }}</td>
                    <td>{{ number_format($totalReste) }}</td>
                    <td></td>
                </tr>

                </tbody>
            </table>

        @empty
            <div class="alert alert-warning">Aucune donnée de sortie disponible</div>
        @endforelse

    </div>
@endsection--}}
