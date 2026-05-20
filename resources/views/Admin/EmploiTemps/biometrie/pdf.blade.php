<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1cm;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #111;
        }
        h2 {
            text-align: center;
            text-transform: uppercase;
            margin: 0 0 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        th, td {
            border: 1px solid #333;
            padding: 4px;
            vertical-align: top;
        }
        th {
            background: #e5e5e5;
            text-align: center;
        }
        .right {
            text-align: right;
        }
        .anomalie {
            color: #b91c1c;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h2>{{ $title }}</h2>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Enseignant</th>
                <th>Matiere</th>
                <th>Plage prevue</th>
                <th>Debut realise</th>
                <th>Fin realise</th>
                <th>Prevu</th>
                <th>Realise</th>
                <th>Non comptabilise</th>
                <th>Taux</th>
                <th>Montant</th>
                <th>Statut</th>
                <th>Observation</th>
            </tr>
        </thead>
        <tbody>
            @forelse($heures as $heure)
                @php
                    $nonComptabilise = max(($heure->duree_prevue ?? 0) - ($heure->duree_realisee ?? 0), 0);
                    $isAnomalie = $nonComptabilise > 0 || !empty($heure->observation);
                @endphp
                <tr>
                    <td>{{ optional($heure->date_seance)->format('d/m/Y') }}</td>
                    <td>{{ $heure->personnel->nom ?? '-' }}</td>
                    <td>{{ $heure->cours->programme->matiere->nom_matiere ?? '-' }}</td>
                    <td>{{ substr($heure->heure_debut_prevue, 0, 5) }} - {{ substr($heure->heure_fin_prevue, 0, 5) }}</td>
                    <td>{{ $heure->heure_debut_reelle ? substr($heure->heure_debut_reelle, 0, 5) : '-' }}</td>
                    <td>{{ $heure->heure_fin_reelle ? substr($heure->heure_fin_reelle, 0, 5) : '-' }}</td>
                    <td class="right">{{ number_format($heure->duree_prevue, 2, ',', ' ') }} h</td>
                    <td class="right">{{ number_format($heure->duree_realisee, 2, ',', ' ') }} h</td>
                    <td class="right {{ $nonComptabilise > 0 ? 'anomalie' : '' }}">{{ number_format($nonComptabilise, 2, ',', ' ') }} h</td>
                    <td class="right">{{ number_format($heure->montant_taux, 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($heure->montant_total, 0, ',', ' ') }}</td>
                    <td>{{ $heure->statut }}</td>
                    <td class="{{ $isAnomalie ? 'anomalie' : '' }}">{{ $heure->observation }}</td>
                </tr>
            @empty
                <tr><td colspan="13" style="text-align:center">Aucune heure consolidee.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Totaux par enseignant</h2>
    <table>
        <thead>
            <tr>
                <th>Enseignant</th>
                <th>Total heures prevues</th>
                <th>Total heures realisees</th>
                <th>Total non comptabilise</th>
                <th>Montant total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($totaux as $total)
                <tr>
                    <td>{{ $total['enseignant'] }}</td>
                    <td class="right">{{ number_format($total['heures_prevues'], 2, ',', ' ') }} h</td>
                    <td class="right">{{ number_format($total['heures_realisees'], 2, ',', ' ') }} h</td>
                    <td class="right {{ ($total['heures_non_comptabilisees'] ?? 0) > 0 ? 'anomalie' : '' }}">{{ number_format($total['heures_non_comptabilisees'] ?? 0, 2, ',', ' ') }} h</td>
                    <td class="right">{{ number_format($total['montant_total'], 0, ',', ' ') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center">Aucun total disponible.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
