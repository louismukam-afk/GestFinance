<h3>Liste des décaissements</h3>

<table border="1" width="100%">
    <tr>
        <th>Bon</th>
        <th>Total</th>
        <th>Financé</th>
        <th>Reste</th>
    </tr>

    @foreach($bons as $b)

        @php
            $fin = $b->decaissements->sum('montant');
            $reste = $b->montant_total - $fin;
        @endphp

        <tr>
            <td>{{ $b->nom_bon_commande }}</td>
            <td>{{ $b->montant_total }}</td>
            <td>{{ $fin }}</td>
            <td>{{ $reste }}</td>
        </tr>

    @endforeach

</table>
