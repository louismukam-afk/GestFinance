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
            white-space: nowrap;
        }
        .anomalie {
            color: #b91c1c;
            font-weight: bold;
        }
        .page-break { page-break-before: always; }
        tr { page-break-inside: avoid; }
    </style>
</head>
<body>
    <h2>{{ $title }}</h2>

    @if(($tableau ?? 'complet') !== 'totaux')
    @foreach($heures->isEmpty() ? collect([collect()]) : $heures->chunk(18) as $pageHeures)
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
                <th>Montant brut</th>
                <th>Sanction</th>
                <th>Statut</th>
                <th>Observation</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pageHeures as $heure)
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
                    <td class="right">
                        {{ number_format($heure->sanction_annulee ? 0 : $nonComptabilise * $heure->montant_taux, 0, ',', ' ') }}
                        @if($heure->sanction_annulee)
                            <br>Sanction levee
                        @endif
                    </td>
                    <td>{{ $heure->statut }}</td>
                    <td class="{{ $isAnomalie ? 'anomalie' : '' }}">
                        @if($heure->libelle_type_requete)
                            <strong>{{ $heure->libelle_type_requete }}</strong><br>
                        @endif
                        {{ $heure->observation }}
                    </td>
                </tr>
            @empty
                <tr><td colspan="14" style="text-align:center">Aucune heure consolidee.</td></tr>
            @endforelse
        </tbody>
    </table>
    @endforeach

    @endif

    @if(($tableau ?? 'complet') !== 'detail')
    @if(($tableau ?? 'complet') === 'complet')
        <div class="page-break"></div>
    @endif
    <h2>Totaux par enseignant</h2>
    <table>
        <thead>
            <tr>
                <th>Enseignant</th>
                <th>Mois</th>
                <th>Total heures prevues</th>
                <th>Total heures realisees</th>
                <th>Total non comptabilise</th>
                <th>Heures payables</th>
                <th>Montant brut</th>
                <th>Sanction</th>
                <th>Net a payer</th>
            </tr>
        </thead>
        <tbody>
            @forelse($totaux as $total)
                <tr>
                    <td>{{ $total['enseignant'] }}</td>
                    <td>{{ $total['mois'] }}</td>
                    <td class="right">{{ number_format($total['heures_prevues'], 2, ',', ' ') }} h</td>
                    <td class="right">{{ number_format($total['heures_realisees'], 2, ',', ' ') }} h</td>
                    <td class="right {{ ($total['heures_non_comptabilisees'] ?? 0) > 0 ? 'anomalie' : '' }}">{{ number_format($total['heures_non_comptabilisees'] ?? 0, 2, ',', ' ') }} h</td>
                    <td class="right">{{ number_format($total['heures_payables'], 2, ',', ' ') }} h</td>
                    <td class="right">{{ number_format($total['montant_brut'], 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($total['montant_sanction'], 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($total['montant_total'], 0, ',', ' ') }}</td>
                </tr>
            @empty
                <tr><td colspan="9" style="text-align:center">Aucun total disponible.</td></tr>
            @endforelse
        </tbody>
        @if($totaux->isNotEmpty())
            <tfoot>
                <tr>
                    <th colspan="2">TOTAL GENERAL</th>
                    <th class="right">{{ number_format($totaux->sum('heures_prevues'), 2, ',', ' ') }} h</th>
                    <th class="right">{{ number_format($totaux->sum('heures_realisees'), 2, ',', ' ') }} h</th>
                    <th class="right">{{ number_format($totaux->sum('heures_non_comptabilisees'), 2, ',', ' ') }} h</th>
                    <th class="right">{{ number_format($totaux->sum('heures_payables'), 2, ',', ' ') }} h</th>
                    <th class="right">{{ number_format($totaux->sum('montant_brut'), 0, ',', ' ') }}</th>
                    <th class="right">{{ number_format($totaux->sum('montant_sanction'), 0, ',', ' ') }}</th>
                    <th class="right">{{ number_format($totaux->sum('montant_total'), 0, ',', ' ') }}</th>
                </tr>
            </tfoot>
        @endif
    </table>
    @endif
</body>
</html>
