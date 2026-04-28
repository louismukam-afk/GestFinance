<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Etat caisse</title>
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
            font-size: 26px;
        }

        h4 {
            font-size: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
            table-layout: fixed;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px;
            font-size: 12px;
            word-wrap: break-word;
        }

        th {
            background: #EEE;
            font-size: 12px;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
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
    </style>
</head>
<body>

<h3>
    @if($currentUserOnly)
        Etat de caisse de {{ $userName ?? '' }} de la periode du {{ $dateDebut ?? 'Debut' }} au {{ $dateFin }}
    @else
        Etat de caisse global de la periode du {{ $dateDebut ?? 'Debut' }} au {{ $dateFin }}
    @endif
</h3>

<p class="summary">
    Total entrees : <strong>{{ number_format($totalEntrees, 0, ',', ' ') }} FCFA</strong>
    |
    Total sorties : <strong>{{ number_format($totalSorties, 0, ',', ' ') }} FCFA</strong>
    |
    Solde : <strong>{{ number_format($solde, 0, ',', ' ') }} FCFA</strong>
</p>

@foreach($operationsGrouped as $caisse => $lignes)
    <h4>{{ $caisse }}</h4>

    <table>
        <thead>
        <tr>
            <th>Date</th>
            <th>Operation</th>
            <th>Numero</th>
            <th>Motif</th>
            <th>Budget</th>
            <th>Ligne budgetaire</th>
            <th>Element</th>
            <th>Donnee</th>
            <th>Entite</th>
            <th>Annee</th>
            <th>Utilisateur</th>
            <th class="right">Entree</th>
            <th class="right">Sortie</th>
        </tr>
        </thead>
        <tbody>
        @php $tEntree = 0; $tSortie = 0; @endphp
        @foreach($lignes as $op)
            @php
                $tEntree += $op['entree'];
                $tSortie += $op['sortie'];
            @endphp
            <tr>
                <td>{{ $op['date'] }}</td>
                <td>{{ $op['operation'] }}</td>
                <td>{{ $op['numero'] }}</td>
                <td>{{ $op['motif'] ?: '-' }}</td>
                <td>{{ $op['budget'] ?: '-' }}</td>
                <td>{{ $op['ligne'] ?: '-' }}</td>
                <td>{{ $op['element'] ?: '-' }}</td>
                <td>{{ $op['donnee'] ?: '-' }}</td>
                <td>{{ $op['entite'] ?: '-' }}</td>
                <td>{{ $op['annee'] ?: '-' }}</td>
                <td>{{ $op['utilisateur'] ?: '-' }}</td>
                <td class="right">{{ number_format($op['entree'], 0, ',', ' ') }}</td>
                <td class="right">{{ number_format($op['sortie'], 0, ',', ' ') }}</td>
            </tr>
        @endforeach
        <tr class="total">
            <td colspan="11">TOTAL {{ $caisse }}</td>
            <td class="right">{{ number_format($tEntree, 0, ',', ' ') }}</td>
            <td class="right">{{ number_format($tSortie, 0, ',', ' ') }}</td>
        </tr>
        <tr class="total">
            <td colspan="11">SOLDE {{ $caisse }}</td>
            <td colspan="2" class="right">{{ number_format($tEntree - $tSortie, 0, ',', ' ') }} FCFA</td>
        </tr>
        </tbody>
    </table>
@endforeach

</body>
</html>
