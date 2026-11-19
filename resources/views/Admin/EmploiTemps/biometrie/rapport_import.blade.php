<!DOCTYPE html>
<html lang="fr"><head>
    <meta charset="utf-8"><title>{{ $title }}</title>
    <style>
        @page { margin: 28px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #171717; }
        h1 { font-size: 17px; margin: 0 0 12px; }
        h2 { font-size: 14px; margin: 20px 0 8px; page-break-after: avoid; }
        h3 { font-size: 11px; margin: 12px 0 6px; page-break-after: avoid; }
        p { margin: 6px 0 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #999; padding: 5px; vertical-align: top; }
        th { background: #ededed; text-align: left; }
        tr { page-break-inside: avoid; }
        .right { text-align: right; white-space: nowrap; }
        .muted { color: #555; }
    </style>
</head><body>
    <h1>{{ $title }}</h1>
    <p><strong>Import #{{ $import->id }} - {{ $import->libelle }}</strong><br>
    Periode : {{ $import->date_debut->format('d/m/Y') }} au {{ $import->date_fin->format('d/m/Y') }}.
    Pointages : {{ $import->total_lignes }} ; non associes : {{ $import->total_non_associees }}.</p>
    @if($rapport === 'discipline')
        <p class="muted">Constats issus des horaires planifies et des heures consolidees de cet import. Une presence non etablie ou un pointage incomplet reste a verifier. Les pointages non associes ne permettent pas d'attribuer une absence a un enseignant.</p>
        @foreach($groupes as $groupe)
            <h2>Specialite : {{ $groupe['specialite'] }}</h2>
            @foreach($groupe['enseignants'] as $enseignant)
                <h3>{{ $enseignant['enseignant'] }} - {{ $enseignant['mois'] }}</h3>
                <p>Prevu : {{ number_format($enseignant['heures_prevues'], 2, ',', ' ') }} h ; realise : {{ number_format($enseignant['heures_realisees'], 2, ',', ' ') }} h ; non realise : {{ number_format($enseignant['heures_non_comptabilisees'], 2, ',', ' ') }} h.</p>
                @forelse($enseignant['evenements']->chunk(12) as $evenements)
                    <table>
                        <thead><tr><th>Date</th><th>Matiere</th><th>Plage prevue</th><th>Constat</th><th>Heures non realisees</th><th>Justification</th><th>Observation</th></tr></thead>
                        <tbody>
                            @foreach($evenements as $evenement)
                                <tr><td>{{ $evenement['date'] }}</td><td>{{ $evenement['matiere'] }}</td><td>{{ $evenement['plage'] }}</td><td>{{ $evenement['nature'] }}</td><td class="right">{{ number_format($evenement['manquantes'], 2, ',', ' ') }} h</td><td>{{ str_replace('_', ' ', $evenement['statuts']) }}</td><td>{{ $evenement['observation'] }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                @empty
                    <p>Aucune anomalie relevee sur les plages consolidees.</p>
                @endforelse
            @endforeach
        @endforeach
    @else
        <p class="muted">Montants en FCFA. La coupe correspond aux heures non realisees au taux de chaque plage. La sanction supplementaire est plafonnee au brut realise pour chaque enseignant et chaque mois. Le net est un montant a payer, et non une preuve de paiement.</p>
        @foreach($finances->chunk(25) as $lignes)
            <table>
                <thead><tr><th>Enseignant</th><th>Mois</th><th>Budget prevu</th><th>Brut realise</th><th>Coupe heures</th><th>Sanction calculee</th><th>Sanction retenue</th><th>Net a payer</th><th>Impact total</th></tr></thead>
                <tbody>
                    @foreach($lignes as $ligne)
                        <tr><td>{{ $ligne['enseignant'] }}</td><td>{{ $ligne['mois'] }}</td>
                            @foreach(['montant_prevu', 'montant_brut', 'coupe_heures', 'montant_sanction', 'sanction_retenue', 'montant_total', 'impact_total'] as $cle)
                                <td class="right">{{ number_format($ligne[$cle], 0, ',', ' ') }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
                @if($loop->last)
                    <tfoot><tr><th colspan="2">TOTAL GENERAL</th>
                        @foreach(['montant_prevu', 'montant_brut', 'coupe_heures', 'montant_sanction', 'sanction_retenue', 'montant_total', 'impact_total'] as $cle)
                            <th class="right">{{ number_format($finances->sum($cle), 0, ',', ' ') }}</th>
                        @endforeach
                    </tr></tfoot>
                @endif
            </table>
        @endforeach
        <p>Sanctions calculees non imputables faute de brut suffisant : <strong>{{ number_format($finances->sum('sanction_non_imputable'), 0, ',', ' ') }} FCFA</strong>.</p>
        <p><strong>Bilan :</strong> budget prevu {{ number_format($finances->sum('montant_prevu'), 0, ',', ' ') }} = net a payer {{ number_format($finances->sum('montant_total'), 0, ',', ' ') }} + coupes horaires {{ number_format($finances->sum('coupe_heures'), 0, ',', ' ') }} + sanctions retenues {{ number_format($finances->sum('sanction_retenue'), 0, ',', ' ') }} FCFA.</p>
    @endif
</body></html>
