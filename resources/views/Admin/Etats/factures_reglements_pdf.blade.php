{{-- resources/views/Admin/Etats/factures_reglements_pdf.blade.php --}}

        <!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">

    <style>
        body{
            font-family: DejaVu Sans, sans-serif;
            font-size:10px;
            color:#000;
        }

        h2,h4{
            text-align:center;
            margin:0;
            padding:0;
        }

        table{
            width:100%;
            border-collapse:collapse;
            margin-top:8px;
        }

        th,td{
            border:1px solid #000;
            padding:4px;
        }

        th{
            background:#333;
            color:#fff;
        }

        .group1{
            background:#d9edf7;
            font-weight:bold;
        }

        .group2{
            background:#fcf8e3;
            font-weight:bold;
        }

        .group3{
            background:#f5f5f5;
            font-weight:bold;
        }

        .right{
            text-align:right;
        }

        .center{
            text-align:center;
        }

        .total{
            background:#dff0d8;
            font-weight:bold;
        }

        .small{
            font-size:9px;
        }
    </style>

</head>
<body>

<h2>ETAT FACTURES & REGLEMENTS</h2>
<h4>Rapport Comptable</h4>

<table>
    <tr>
        <td><strong>Date début :</strong> {{ request('date_debut') }}</td>
        <td><strong>Date fin :</strong> {{ request('date_fin') }}</td>
        <td><strong>Imprimé le :</strong> {{ date('d/m/Y H:i') }}</td>
    </tr>
</table>

@php
    $totalFacture = 0;
    $totalEncaisse = 0;
    $totalReste = 0;
@endphp

@forelse($grouped as $specialite => $lignes)

    <table>
        <tr class="group1">
            <td colspan="5">🎓 SPECIALITE : {{ $specialite }}</td>
        </tr>
    </table>

    @foreach($lignes as $ligne => $users)

        <table>
            <tr class="group2">
                <td colspan="5">📘 LIGNE BUDGETAIRE : {{ $ligne }}</td>
            </tr>
        </table>

        @foreach($users as $user => $factures)

            <table>
                <tr class="group3">
                    <td colspan="5">👤 UTILISATEUR : {{ $user }}</td>
                </tr>

                <thead>
                <tr>
                    <th width="15%">N° Facture</th>
                    <th>Étudiant</th>
                    <th width="18%">Montant facturé</th>
                    <th width="18%">Montant réglé</th>
                    <th width="18%">Reste</th>
                </tr>
                </thead>

                <tbody>

                @php
                    $sousFacture = 0;
                    $sousEncaisse = 0;
                    $sousReste = 0;
                @endphp

                @foreach($factures as $f)

                    @php
                        $encaisse = $f->reglement_etudiants->sum('montant_reglement');

                        $reste = $f->montant_total_facture - $encaisse;

                        $sousFacture += $f->montant_total_facture;
                        $sousEncaisse += $encaisse;
                        $sousReste += $reste;

                        $totalFacture += $f->montant_total_facture;
                        $totalEncaisse += $encaisse;
                        $totalReste += $reste;
                    @endphp

                    <tr>
                        <td class="center">{{ $f->numero_facture }}</td>
                        <td>{{ optional($f->etudiants)->nom }}</td>
                        <td class="right">{{ number_format($f->montant_total_facture,0,',',' ') }}</td>
                        <td class="right">{{ number_format($encaisse,0,',',' ') }}</td>
                        <td class="right">{{ number_format($reste,0,',',' ') }}</td>
                    </tr>

                @endforeach

                <tr class="total">
                    <td colspan="2">SOUS TOTAL {{ $user }}</td>
                    <td class="right">{{ number_format($sousFacture,0,',',' ') }}</td>
                    <td class="right">{{ number_format($sousEncaisse,0,',',' ') }}</td>
                    <td class="right">{{ number_format($sousReste,0,',',' ') }}</td>
                </tr>

                </tbody>
            </table>

        @endforeach
    @endforeach

@empty

    <table>
        <tr>
            <td class="center">Aucune donnée trouvée</td>
        </tr>
    </table>

@endforelse

<table>
    <tr class="total">
        <td width="40%"><strong>TOTAL GENERAL</strong></td>
        <td width="20%" class="right">{{ number_format($totalFacture,0,',',' ') }}</td>
        <td width="20%" class="right">{{ number_format($totalEncaisse,0,',',' ') }}</td>
        <td width="20%" class="right">{{ number_format($totalReste,0,',',' ') }}</td>
    </tr>
</table>

</body>
</html>