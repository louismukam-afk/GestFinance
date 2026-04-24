@extends('layouts.app')
<style>
    @media print {

        /* 🔁 Forcer le mode paysage */
        @page {
            size: A4 landscape;
            margin: 15mm 12mm;
        }

        /* ========================= */
        /* BASE DOCUMENT             */
        /* ========================= */
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
            color: #000;
        }

        /* ========================= */
        /* TITRES                    */
        /* ========================= */
        h3 {
            text-align: center;
            margin-bottom: 8px;
        }

        .context {
            text-align: center;
            font-size: 9px;
            margin-bottom: 10px;
        }

        /* ========================= */
        /* TABLEAU                   */
        /* ========================= */
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;            /* 🔑 stabilité multi-pages */
            page-break-inside: auto;        /* autoriser la pagination */
        }

        /* Répéter les entêtes */
        thead {
            display: table-header-group;
        }

        tfoot {
            display: table-footer-group;
        }

        /* Éviter la coupure des lignes */
        tr {
            page-break-inside: avoid;
        }

        /* ========================= */
        /* CELLULES                  */
        /* ========================= */
        th, td {
            border: 1px solid #000;
            padding: 4px 5px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        /* Entêtes */
        th {
            background-color: #EDEDED;
            text-align: center;
            font-weight: bold;
            font-size: 10px;
        }

        /* Alignements */
        .right {
            text-align: right;
        }

        /* ========================= */
        /* LARGEURS COLONNES A4      */
        /* ========================= */
        .col-budget   { width: 18%; }
        .col-ligne    { width: 22%; }
        .col-donnee   { width: 22%; }
        .col-num      { width: 8%; }

        /* ========================= */
        /* ENTITÉ / SOUS-TITRE       */
        /* ========================= */
        .entite-row {
            background: #D9D9D9;
            font-weight: bold;
            text-align: left;
        }

        /* ========================= */
        /* TOTAUX                    */
        /* ========================= */
        .total {
            font-weight: bold;
            background: #F2F2F2;
        }

        /* ========================= */
        /* SAUT DE PAGE MANUEL       */
        /* ========================= */
        .page-break {
            page-break-before: always;
        }
        }
        /** 📄 Format paysage **/

</style>
@section('content')
    <div class="container">


        {{-- ============================= --}}
        {{-- TITRE --}}
        {{-- ============================= --}}
        <h3 class="mb-2">
            📊 Atterrissage budgétaire – Entrées
        </h3>

        {{-- ============================= --}}
        {{-- CONTEXTE (ANNÉE / ENTITÉ / CAISSE) --}}
        {{-- ============================= --}}
        <div class="mb-4">

            @if($anneeNom)
                <span class="badge bg-secondary me-2">
                🎓 Année académique : {{ $anneeNom }}
            </span>
            @else
                <span class="badge bg-secondary me-2">
                🎓 Toutes les années
            </span>
            @endif

            @if($entiteNom)
                <span class="badge bg-primary me-2">
                🏢 {{ $entiteNom }}
            </span>
            @else
                <span class="badge bg-primary me-2">
                🏢 Toutes les entités
            </span>
            @endif

            @if($caisseNom)
                <span class="badge bg-success">
                💰 Caisse : {{ $caisseNom }}
            </span>
            @else
                <span class="badge bg-success">
                💰 Toutes les caisses
            </span>
            @endif

        </div>

        {{-- ============================= --}}
        {{-- ACTIONS --}}
        {{-- ============================= --}}
        <div class="mb-3 d-flex justify-content-between">
            <div>
                <a href="{{ route('etat_budget_export_excel', request()->all()) }}"
                   class="btn btn-success btn-sm me-2">
                    ⬇️ Export Excel
                </a>

                <a href="{{ route('etat_budget_export_pdf', request()->all()) }}"
                   class="btn btn-danger btn-sm">
                    ⬇️ Export PDF
                </a>
                {{-- 🔥 BOUTON IMPRIMER --}}
                <button type="button"
                        onclick="window.print()"
                        class="btn btn-primary btn-sm">
                    🖨️ Imprimer
                </button>
            </div>

            <a href="{{ route('etat_budget') }}"
               class="btn btn-secondary btn-sm">
                ↩️ Nouvelle recherche
            </a>
        </div>

        {{-- ============================= --}}
        {{-- TABLEAUX PAR ENTITÉ --}}
        {{-- ============================= --}}
        @forelse($etatGrouped as $nomEntite => $lignes)

            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light">
                    <strong>🏢 Entité : {{ $nomEntite }}</strong>
                </div>

                <div class="card-body p-0">
                    <table class="table table-bordered table-sm mb-0">
                        <thead class="table-dark">
                        <tr>
                            <th class="col-budget">Budget</th>
                            <th class="col-ligne">Ligne budgétaire</th>
                            <th class="col-donnee">Donnée</th>
                            <th class="col-num">Prévu</th>
                            <th class="col-num">Facturé</th>
                            <th class="col-num">Encaissé</th>
                            <th class="col-num">Reste</th>
                        </tr>
                        </thead>
                        <tbody>

                        @php
                            $totalPrevu = 0;
                            $totalFacture = 0;
                            $totalEncaisse = 0;
                            $totalReste = 0;
                        @endphp

                        @foreach($lignes as $e)

                            @if(!is_array($e))
                                @continue
                            @endif

                            @php
                                $prevu    = $e['prevu'] ?? 0;
                                $facture  = $e['facture'] ?? 0;
                                $encaisse = $e['encaisse'] ?? 0;
                                $reste    = $e['reste'] ?? 0;

                                $totalPrevu    += $prevu;
                                $totalFacture  += $facture;
                                $totalEncaisse += $encaisse;
                                $totalReste    += $reste;
                            @endphp

                            <tr>
                                <td>{{ $e['budget'] ?? '—' }}</td>
                                <td>{{ $e['ligne'] ?? '—' }}</td>
                                <td>{{ $e['donnee'] ?? '—' }}</td>

                                <td class="text-end">
                                    {{ number_format($prevu, 0, ',', ' ') }}
                                </td>

                                <td class="text-end">
                                    {{ number_format($facture, 0, ',', ' ') }}
                                </td>

                                <td class="text-end">
                                    {{ number_format($encaisse, 0, ',', ' ') }}
                                </td>

                                <td class="text-end fw-bold">
                                    {{ number_format($reste, 0, ',', ' ') }}
                                </td>
                            </tr>

                        @endforeach

                        {{-- TOTAL PAR ENTITÉ --}}
                        <tr class="table-secondary fw-bold">
                            <td colspan="3">
                                TOTAL {{ $nomEntite }}
                            </td>
                            <td class="text-end">
                                {{ number_format($totalPrevu, 0, ',', ' ') }}
                            </td>
                            <td class="text-end">
                                {{ number_format($totalFacture, 0, ',', ' ') }}
                            </td>
                            <td class="text-end">
                                {{ number_format($totalEncaisse, 0, ',', ' ') }}
                            </td>
                            <td class="text-end">
                                {{ number_format($totalReste, 0, ',', ' ') }}
                            </td>
                        </tr>

                        </tbody>
                    </table>
                </div>
            </div>

        @empty

            <div class="alert alert-warning text-center">
                ⚠️ Aucune donnée budgétaire trouvée pour les critères sélectionnés.
            </div>

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
            <a href="{{ route('etat_budget') }}"><strong>Nouvelle attérissage</strong></a>
        </li>
    </ol>
@endsection
