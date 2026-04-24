<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>État budgétaire – Entrées</title>
    <style>
        body {
            font-family: DejaVu Sans;
            font-size: 10px;
            margin: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: auto;
        }

        thead {
            display: table-header-group; /* 🔥 RÉPÈTE L’ENTÊTE */
        }

        tfoot {
            display: table-footer-group;
        }

        tr {
            page-break-inside: avoid; /* 🔥 NE COUPE PAS UNE LIGNE */
            page-break-after: auto;
        }

        th, td {
            border: 1px solid #000;
            padding: 4px;
            word-wrap: break-word;
        }

        th {
            background-color: #EEE;
        }

        .right {
            text-align: right;
        }

        .page-break {
            page-break-before: always; /* 🔥 SAUT DE PAGE MANUEL */
        }

        h3, h4 {
            margin: 6px 0;
        }
    </style>

    <style>
        body { font-family: DejaVu Sans; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 5px; }
        th { background: #EEE; }
        .right { text-align: right; }
        .center { text-align: center; }
        h3, h4 { margin: 5px 0; }
    </style>
</head>
<body>

<h3 class="center">
    ÉTAT BUDGÉTAIRE – RECETTES
</h3>

{{-- CONTEXTE --}}
<p class="center">
    @if($anneeNom) Année académique : <strong>{{ $anneeNom }}</strong> @else Toutes les années @endif
    |
    @if($caisseNom) Caisse : <strong>{{ $caisseNom }}</strong> @else Toutes les caisses @endif
</p>

{{-- TABLEAUX PAR ENTITÉ --}}
@php $first = true; @endphp

@foreach($etatGrouped as $nomEntite => $lignes)

    @if(!$first)
        <div class="page-break"></div>
    @endif

    @php $first = false; @endphp

    <h4>🏢 Entité : {{ $nomEntite }}</h4>

    <table>
        <thead>
        <tr>
            <th>Budget</th>
            <th>Ligne</th>
            <th>Donnée</th>
            <th class="right">Prévu</th>
            <th class="right">Facturé</th>
            <th class="right">Encaissé</th>
            <th class="right">Reste</th>
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
            @php
                $totalPrevu    += $e['prevu'];
                $totalFacture  += $e['facture'];
                $totalEncaisse += $e['encaisse'];
                $totalReste    += $e['reste'];
            @endphp

            <tr>
                <td>{{ $e['budget'] }}</td>
                <td>{{ $e['ligne'] }}</td>
                <td>{{ $e['donnee'] }}</td>
                <td class="right">{{ number_format($e['prevu'],0,',',' ') }}</td>
                <td class="right">{{ number_format($e['facture'],0,',',' ') }}</td>
                <td class="right">{{ number_format($e['encaisse'],0,',',' ') }}</td>
                <td class="right">{{ number_format($e['reste'],0,',',' ') }}</td>
            </tr>
        @endforeach

        {{-- TOTAL ENTITÉ --}}
        <tr style="font-weight:bold; background:#F0F0F0;">
            <td colspan="3">TOTAL {{ $nomEntite }}</td>
            <td class="right">{{ number_format($totalPrevu,0,',',' ') }}</td>
            <td class="right">{{ number_format($totalFacture,0,',',' ') }}</td>
            <td class="right">{{ number_format($totalEncaisse,0,',',' ') }}</td>
            <td class="right">{{ number_format($totalReste,0,',',' ') }}</td>
        </tr>

        </tbody>
    </table>

@endforeach

</body>
</html>
