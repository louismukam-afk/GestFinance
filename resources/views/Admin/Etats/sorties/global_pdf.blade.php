<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>État global</title>

    <style>
        body {
            font-family: DejaVu Sans;
            font-size: 14px;
        }

        h3, h4 {
            text-align: center;
            margin: 8px 0;
        }

        h3 {
            font-size: 24px;
        }

        h4 {
            font-size: 20px;
        }

        h5 {
            font-size: 17px;
            margin: 10px 0 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px;
            font-size: 13px;
        }

        th {
            background: #EEE;
            font-size: 14px;
        }

        .right {
            text-align: right;
        }

        .total {
            font-weight: bold;
            background: #F0F0F0;
        }

        .section {
            margin-top: 15px;
        }

        .period {
            text-align: center;
            font-size: 17px;
            font-weight: bold;
            margin: 6px 0 12px;
        }

        .summary {
            text-align: center;
            font-size: 17px;
            margin: 8px 0 14px;
        }
    </style>
</head>

<body>


<h3>📊 ÉTAT D’ATTERRISSAGE BUDGÉTAIRE GLOBAL</h3>

<p class="period">
    P&eacute;riode :
    {{ $dateDebut ?? request('date_debut') ?? 'D&eacute;but' }}
    -
    {{ $dateFin ?? request('date_fin') ?? now()->format('Y-m-d') }}
</p>

<p class="summary">
    Disponibilité : <strong>{{ number_format($disponibilite) }} FCFA</strong>
    |
    Déficit : <strong>{{ number_format($deficit) }} FCFA</strong>
</p>

{{-- ================= ENTRÉES ================= --}}
<div class="section">
    <h4>🔵 ENTRÉES</h4>

    @foreach($entreesGrouped as $entite => $lignes)

        <h5>🏢 {{ $entite }}</h5>

        <table>
            <thead>
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

            @php $tp=0;$tf=0;$te=0;$tr=0; @endphp

            @foreach($lignes as $e)

                @php
                    $tp += $e['prevu'];
                    $tf += $e['facture'];
                    $te += $e['encaisse'];
                    $tr += $e['reste'];
                @endphp

                <tr>
                    <td>{{ $e['budget'] }}</td>
                    <td>{{ $e['ligne'] }}</td>
                    <td>{{ $e['element'] ?? '-' }}</td>
                    <td>{{ $e['donnee'] }}</td>
                    <td class="right">{{ number_format($e['prevu']) }}</td>
                    <td class="right">{{ number_format($e['facture']) }}</td>
                    <td class="right">{{ number_format($e['encaisse']) }}</td>
                    <td class="right">{{ number_format($e['reste']) }}</td>
                </tr>

            @endforeach

            <tr class="total">
                <td colspan="4">TOTAL {{ $entite }}</td>
                <td class="right">{{ number_format($tp) }}</td>
                <td class="right">{{ number_format($tf) }}</td>
                <td class="right">{{ number_format($te) }}</td>
                <td class="right">{{ number_format($tr) }}</td>
            </tr>

            </tbody>
        </table>

    @endforeach
</div>

{{-- ================= SORTIES ================= --}}
<div class="section">
    <h4>🔴 SORTIES</h4>

    @foreach($sortiesGrouped as $entite => $lignes)

        <h5>🏢 {{ $entite }}</h5>

        <table>
            <thead>
            <tr>
                <th>Budget</th>
                <th>Ligne</th>
                <th>Élément</th>
                <th>Donnée</th>
                <th>Prévu</th>
                <th>Dépensé</th>
                <th>Reste</th>
            </tr>
            </thead>

            <tbody>

            @php $tp=0;$td=0;$tr=0; @endphp

            @foreach($lignes as $s)

                @php
                    $tp += $s['prevu'];
                    $td += $s['depense'];
                    $tr += $s['reste'];
                @endphp

                <tr>
                    <td>{{ $s['budget'] }}</td>
                    <td>{{ $s['ligne'] }}</td>
                    <td>{{ $s['element'] ?? '-' }}</td>
                    <td>{{ $s['donnee'] }}</td>
                    <td class="right">{{ number_format($s['prevu']) }}</td>
                    <td class="right">{{ number_format($s['depense']) }}</td>
                    <td class="right">{{ number_format($s['reste']) }}</td>
                </tr>

            @endforeach

            <tr class="total">
                <td colspan="4">TOTAL {{ $entite }}</td>
                <td class="right">{{ number_format($tp) }}</td>
                <td class="right">{{ number_format($td) }}</td>
                <td class="right">{{ number_format($tr) }}</td>
            </tr>

            </tbody>
        </table>

    @endforeach
</div>

</body>
</html>
