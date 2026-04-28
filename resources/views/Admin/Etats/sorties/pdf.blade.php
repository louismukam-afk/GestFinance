<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Etat atterrissage sorties</title>

    <style>
        body {
            font-family: DejaVu Sans;
            font-size: 13px;
        }

        h3, h4 {
            text-align: center;
            margin: 8px 0;
        }

        h3 {
            font-size: 23px;
        }

        h4 {
            font-size: 19px;
        }

        h5 {
            font-size: 16px;
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
            font-size: 12px;
        }

        th {
            background: #EEE;
            font-size: 13px;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .period {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 6px 0 12px;
        }

        .summary {
            text-align: center;
            font-size: 16px;
            margin: 8px 0 14px;
        }

        .total {
            font-weight: bold;
            background: #F0F0F0;
        }

        .section {
            margin-top: 15px;
        }
    </style>
</head>

<body>

<h3>ETAT D'ATTERRISSAGE BUDGETAIRE - SORTIES</h3>

<p class="period">
    Periode :
    {{ $dateDebut ?? request('date_debut') ?? 'Debut' }}
    -
    {{ $dateFin ?? request('date_fin') ?? now()->format('Y-m-d') }}
</p>

<p class="summary">
    Disponibilite globale :
    <strong>{{ number_format($soldeGlobal ?? 0, 0, ',', ' ') }} FCFA</strong>
</p>

<div class="section">
    <h4>SORTIES</h4>

    @foreach($etatGrouped as $entite => $lignes)
        <h5>{{ $entite }}</h5>

        <table>
            <thead>
            <tr>
                <th>Budget</th>
                <th>Ligne</th>
                <th>Donnee</th>
                <th class="right">Prevu</th>
                <th class="right">Depense</th>
                <th class="right">Reste</th>
                <th class="right">Dispo caisse</th>
                <th class="center">Decision</th>
            </tr>
            </thead>

            <tbody>
            @php
                $totalPrevu = 0;
                $totalDepense = 0;
                $totalReste = 0;
            @endphp

            @foreach($lignes as $e)
                @php
                    $totalPrevu += $e['prevu'];
                    $totalDepense += $e['depense'];
                    $totalReste += $e['reste'];

                    if ($e['reste'] <= 0) {
                        $decision = 'Budget epuise';
                    } elseif ($e['solde'] <= 0) {
                        $decision = 'Bloque';
                    } elseif ($e['solde'] < $e['reste']) {
                        $decision = 'Financement partiel';
                    } else {
                        $decision = 'Financable';
                    }
                @endphp

                <tr>
                    <td>{{ $e['budget'] }}</td>
                    <td>{{ $e['ligne'] }}</td>
                    <td>{{ $e['donnee'] }}</td>
                    <td class="right">{{ number_format($e['prevu'], 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($e['depense'], 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($e['reste'], 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($e['solde'], 0, ',', ' ') }}</td>
                    <td class="center">{{ $decision }}</td>
                </tr>
            @endforeach

            <tr class="total">
                <td colspan="3">TOTAL {{ $entite }}</td>
                <td class="right">{{ number_format($totalPrevu, 0, ',', ' ') }}</td>
                <td class="right">{{ number_format($totalDepense, 0, ',', ' ') }}</td>
                <td class="right">{{ number_format($totalReste, 0, ',', ' ') }}</td>
                <td colspan="2"></td>
            </tr>
            </tbody>
        </table>
    @endforeach
</div>

</body>
</html>
