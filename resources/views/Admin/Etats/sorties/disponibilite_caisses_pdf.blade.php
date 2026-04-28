<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Disponibilite des caisses</title>
    <style>
        body {
            font-family: DejaVu Sans;
            font-size: 13px;
        }

        h3 {
            text-align: center;
            font-size: 26px;
            margin: 8px 0;
        }

        .summary {
            text-align: center;
            font-size: 17px;
            margin: 10px 0 18px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
        }

        th, td {
            border: 1px solid #000;
            padding: 7px;
            font-size: 13px;
        }

        th {
            background: #EEE;
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .total {
            font-weight: bold;
            background: #F0F0F0;
        }
    </style>
</head>
<body>

<h3>DISPONIBILITE DES CAISSES</h3>

<p class="summary">
    Date de situation : <strong>{{ $dateFin }}</strong>
    |
    Total avant transfert : <strong>{{ number_format($totalAvantTransfert, 0, ',', ' ') }} FCFA</strong>
    |
    Total apres transfert : <strong>{{ number_format($totalApresTransfert, 0, ',', ' ') }} FCFA</strong>
</p>

<table>
    <thead>
    <tr>
        <th>Caisse</th>
        <th>Type</th>
        <th class="right">Entrees reglements</th>
        <th class="right">Retours caisse</th>
        <th class="right">Decaissements</th>
        <th class="right">Solde avant transfert</th>
        <th class="right">Transferts entrants</th>
        <th class="right">Transferts sortants</th>
        <th class="right">Solde apres transfert</th>
    </tr>
    </thead>
    <tbody>
    @foreach($caisses as $ligne)
        <tr>
            <td>{{ $ligne['caisse']->nom_caisse }}</td>
            <td>{{ $ligne['caisse']->type_caisse ?? '-' }}</td>
            <td class="right">{{ number_format($ligne['entrees_reglements'], 0, ',', ' ') }}</td>
            <td class="right">{{ number_format($ligne['entrees_retours'], 0, ',', ' ') }}</td>
            <td class="right">{{ number_format($ligne['sorties_decaissements'], 0, ',', ' ') }}</td>
            <td class="right">{{ number_format($ligne['solde_avant_transfert'], 0, ',', ' ') }}</td>
            <td class="right">{{ number_format($ligne['transferts_entrants'], 0, ',', ' ') }}</td>
            <td class="right">{{ number_format($ligne['transferts_sortants'], 0, ',', ' ') }}</td>
            <td class="right">{{ number_format($ligne['solde_apres_transfert'], 0, ',', ' ') }}</td>
        </tr>
    @endforeach
    <tr class="total">
        <td colspan="5">TOTAL</td>
        <td class="right">{{ number_format($totalAvantTransfert, 0, ',', ' ') }}</td>
        <td colspan="2"></td>
        <td class="right">{{ number_format($totalApresTransfert, 0, ',', ' ') }}</td>
    </tr>
    </tbody>
</table>

</body>
</html>
