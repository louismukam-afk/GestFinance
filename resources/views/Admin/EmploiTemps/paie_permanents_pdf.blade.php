<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bulletins de paie</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #111;
        }

        .bulletin {
            page-break-after: always;
            padding: 8px 10px;
        }

        .bulletin:last-child {
            page-break-after: auto;
        }

        h1 {
            text-align: center;
            font-size: 18px;
            margin: 0 0 12px;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td, th {
            padding: 5px 6px;
            vertical-align: top;
        }

        .meta td {
            border: 1px solid #111;
        }

        .label {
            font-weight: bold;
            width: 32%;
            background: #f3f4f6;
        }

        .section {
            margin-top: 10px;
        }

        .section-title {
            background: #111;
            color: #fff;
            text-transform: uppercase;
            font-weight: bold;
            text-align: center;
        }

        .lines th,
        .lines td {
            border: 1px solid #111;
        }

        .lines th {
            background: #e5e7eb;
            text-align: left;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .total td {
            font-weight: bold;
            background: #f3f4f6;
        }

        .net td {
            font-weight: bold;
            font-size: 13px;
            background: #dbeafe;
        }

        .small {
            font-size: 10px;
            color: #444;
        }
    </style>
</head>
<body>
@foreach($bulletins as $bulletin)
    @php
        $lignes = $bulletin->lignes;
        $gains = $lignes->where('sens', 'plus')->where('montant', '>', 0);
        $retenues = $lignes->where('sens', 'moins')->where('montant', '>', 0);
        $base = $gains->firstWhere('code', 'salaire_base_consolide');
        $irpp = $retenues->firstWhere('code', 'irpp');
        $cac = $retenues->firstWhere('code', 'cac');
        $ccf = $retenues->firstWhere('code', 'ccf');
        $tdl = $retenues->firstWhere('code', 'tdl');
        $rav = $retenues->firstWhere('code', 'rav');
        $pvid = $retenues->firstWhere('code', 'cnps_salarial');
        $acomptes = $retenues->firstWhere('code', 'acomptes');
        $penaliteBiometrie = $retenues->firstWhere('code', 'penalite_biometrie');
        $tauxLabel = function ($ligne) {
            if (!$ligne || $ligne->mode_calcul === 'fixe' || $ligne->montant <= 0) {
                return '';
            }

            $taux = (float) $ligne->taux;
            if ($taux <= 0 && $ligne->base > 0) {
                $taux = ($ligne->montant / $ligne->base) * 100;
            }

            return $taux > 0 ? ' (' . number_format($taux, 2, ',', ' ') . '%)' : '';
        };
    @endphp

    <div class="bulletin">
        <h1>Bulletin de paie</h1>

        <table class="meta">
            <tr>
                <td class="label">Raison sociale</td>
                <td>INSTITUT SUPERIEUR HINTEL</td>
                <td class="label">N°</td>
                <td>{{ optional($bulletin->periode_debut)->format('m/Y') }}</td>
            </tr>
            <tr>
                <td class="label">N° Contribuable</td>
                <td>{{ $bulletin->personnel->numero_contribuable ?? '-' }}</td>
                <td class="label">CNPS</td>
                <td>{{ $bulletin->personnel->numero_cnps ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Nom de l'employe</td>
                <td colspan="3">{{ $bulletin->personnel->nom ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Emploi</td>
                <td>{{ $bulletin->personnel->type_personnel ?? 'Permanent' }}</td>
                <td class="label">Paie du</td>
                <td>{{ optional($bulletin->periode_debut)->format('d/m/Y') }} au {{ optional($bulletin->periode_fin)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="label">Statut bulletin</td>
                <td>{{ $bulletin->statut }}</td>
                <td class="label">Import biometrie</td>
                <td>{{ $bulletin->import->libelle ?? '-' }}</td>
            </tr>
        </table>

        <table class="lines section">
            <tr><td colspan="3" class="section-title">Taux de remuneration</td></tr>
            <tr>
                <th>Element</th>
                <th class="right">Base / taux</th>
                <th class="right">Montant</th>
            </tr>
            <tr>
                <td>Base mensuelle theorique</td>
                <td class="right">{{ number_format($bulletin->salaire_base, 0, ',', ' ') }}</td>
                <td class="right">{{ number_format($base->montant ?? $bulletin->brut_mensuel, 0, ',', ' ') }}</td>
            </tr>
            {{--<tr>
                <td>Penalite biometrie</td>
                <td class="right">Deduite apres impots</td>
                <td class="right">{{ number_format($bulletin->penalite_biometrie, 0, ',', ' ') }}</td>
            </tr>--}}
            @foreach($gains as $gain)
                @continue($gain->code === 'salaire_base_consolide')
                <tr>
                    <td>{{ $gain->libelle }}{{ $tauxLabel($gain) }}</td>
                    <td class="right">
                        @if($gain->mode_calcul === 'pourcentage')
                            {{ number_format($gain->taux, 2, ',', ' ') }} %
                        @elseif($gain->mode_calcul === 'kilometrage')
                            {{ number_format($gain->quantite, 2, ',', ' ') }} km
                        @else
                            -
                        @endif
                    </td>
                    <td class="right">{{ number_format($gain->montant, 0, ',', ' ') }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td>Brut Mensuel</td>
                <td></td>
                <td class="right">{{ number_format($bulletin->brut_mensuel, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td>Salaire taxable</td>
                <td></td>
                <td class="right">{{ number_format($bulletin->salaire_taxable, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td>Salaire cotisable</td>
                <td></td>
                <td class="right">{{ number_format($bulletin->salaire_cotisable, 0, ',', ' ') }}</td>
            </tr>
        </table>

        <table class="lines section">
            <tr><td colspan="3" class="section-title">Impots et retenues</td></tr>
            <tr>
                <th>Retenue</th>
                <th class="right">Base / taux</th>
                <th class="right">Montant</th>
            </tr>
            @foreach([
                'PVID' => $pvid,
                'IRPP' => $irpp,
                'CAC' => $cac,
                'CCF' => $ccf,
                'TDL' => $tdl,
                'RAV' => $rav,
            ] as $label => $ligne)
                <tr>
                    <td>{{ $label }}{{ $tauxLabel($ligne) }}</td>
                    <td class="right">{{ $ligne ? number_format($ligne->base, 0, ',', ' ') : '-' }}</td>
                    <td class="right">{{ number_format($ligne->montant ?? 0, 0, ',', ' ') }}</td>
                </tr>
            @endforeach
            @foreach($retenues as $retenue)
                @continue(in_array($retenue->code, ['irpp', 'cac', 'ccf', 'tdl', 'rav', 'cnps_salarial', 'acomptes', 'sanctions', 'penalite_biometrie'], true))
                <tr>
                    <td>{{ $retenue->libelle }}{{ $tauxLabel($retenue) }}</td>
                    <td class="right">{{ $retenue->base > 0 ? number_format($retenue->base, 0, ',', ' ') : '-' }}</td>
                    <td class="right">{{ number_format($retenue->montant, 0, ',', ' ') }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td>Total retenues</td>
                <td></td>
                <td class="right">{{ number_format($bulletin->total_retenues, 0, ',', ' ') }}</td>
            </tr>
            <tr class="net">
                <td>NET A PAYER</td>
                <td></td>
                <td class="right">{{ number_format($bulletin->net_a_payer, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td>Avances percues</td>
                <td></td>
                <td class="right">{{ number_format($acomptes->montant ?? $bulletin->total_acomptes, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td>Penalite biometrie / avance</td>
                <td></td>
                <td class="right">{{ number_format($penaliteBiometrie->montant ?? $bulletin->penalite_biometrie, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td>Sanctions salariales</td>
                <td></td>
                <td class="right">{{ number_format($bulletin->total_sanctions, 0, ',', ' ') }}</td>
            </tr>
            <tr class="total">
                <td>Solde du</td>
                <td></td>
                <td class="right">{{ number_format($bulletin->solde_du, 0, ',', ' ') }}</td>
            </tr>
        </table>

        <p class="small">
            Les impots et le PVID sont calcules sur le salaire theorique et les elements imposables/cotisables. La penalite biometrie est deduite apres calcul des impots.
        </p>
    </div>
@endforeach
</body>
</html>
