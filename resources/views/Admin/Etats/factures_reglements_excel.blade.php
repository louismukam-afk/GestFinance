{{-- resources/views/Admin/Etats/factures_reglements_excel.blade.php --}}

<table>
    <tr>
        <td colspan="5"><strong>ETAT FACTURES & REGLEMENTS</strong></td>
    </tr>
    <tr>
        <td colspan="5">
            Période :
            {{ request('date_debut') }} au {{ request('date_fin') }}
        </td>
    </tr>
</table>

@php
    $totalFacture = 0;
    $totalEncaisse = 0;
    $totalReste = 0;
@endphp

@forelse($grouped as $specialite => $lignes)

    <table>
        <tr>
            <td colspan="5"><strong>SPECIALITE : {{ $specialite }}</strong></td>
        </tr>
    </table>

    @foreach($lignes as $ligne => $users)

        <table>
            <tr>
                <td colspan="5"><strong>LIGNE BUDGETAIRE : {{ $ligne }}</strong></td>
            </tr>
        </table>

        @foreach($users as $user => $factures)

            <table border="1">
                <tr>
                    <td colspan="5"><strong>UTILISATEUR : {{ $user }}</strong></td>
                </tr>

                <tr>
                    <th>N° Facture</th>
                    <th>Étudiant</th>
                    <th>Montant facturé</th>
                    <th>Montant réglé</th>
                    <th>Reste</th>
                </tr>

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
                        <td>{{ $f->numero_facture }}</td>
                        <td>{{ optional($f->etudiants)->nom }}</td>
                        <td>{{ $f->montant_total_facture }}</td>
                        <td>{{ $encaisse }}</td>
                        <td>{{ $reste }}</td>
                    </tr>

                @endforeach

                <tr>
                    <td colspan="2"><strong>SOUS TOTAL</strong></td>
                    <td><strong>{{ $sousFacture }}</strong></td>
                    <td><strong>{{ $sousEncaisse }}</strong></td>
                    <td><strong>{{ $sousReste }}</strong></td>
                </tr>

            </table>
            <br>

        @endforeach
    @endforeach

@empty

    <table>
        <tr>
            <td>Aucune donnée trouvée</td>
        </tr>
    </table>

@endforelse

<table border="1">
    <tr>
        <td colspan="2"><strong>TOTAL GENERAL</strong></td>
        <td><strong>{{ $totalFacture }}</strong></td>
        <td><strong>{{ $totalEncaisse }}</strong></td>
        <td><strong>{{ $totalReste }}</strong></td>
    </tr>
</table>