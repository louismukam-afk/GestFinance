<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Situation Étudiant</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
        }
        th {
            background: #EEE;
        }
        .right {
            text-align: right;
        }
    </style>
</head>
<body>

<h3 style="text-align:center;">
    SITUATION FINANCIÈRE DE L’ÉTUDIANT
</h3>

<table>
    <thead>
    <tr>
        <th>Facture</th>
        <th>Date</th>
        <th>Montant</th>
        <th>Encaissé</th>
        <th>Reste</th>
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
            <td class="right">{{ number_format($r['montant'],0,',',' ') }}</td>
            <td class="right">{{ number_format($r['encaisse'],0,',',' ') }}</td>
            <td class="right">{{ number_format($r['reste'],0,',',' ') }}</td>
        </tr>

        @php
            $total += $r['montant'];
            $encaisse += $r['encaisse'];
            $reste += $r['reste'];
        @endphp
    @endforeach
    </tbody>

    <tfoot>
    <tr style="font-weight:bold;background:#F2F2F2;">
        <td colspan="2">TOTAL</td>
        <td class="right">{{ number_format($total,0,',',' ') }}</td>
        <td class="right">{{ number_format($encaisse,0,',',' ') }}</td>
        <td class="right">{{ number_format($reste,0,',',' ') }}</td>
    </tr>
    </tfoot>
</table>

</body>
</html>
