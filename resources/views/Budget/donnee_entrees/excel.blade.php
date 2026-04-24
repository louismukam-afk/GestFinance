<table>
    <thead>
    <tr>
        <th>Libellé</th>
        <th>Code</th>
        <th>Compte</th>
        <th>Montant</th>
        <th>Budget</th>
        <th>Ligne budgétaire</th>
        <th>Date</th>
    </tr>
    </thead>
    <tbody>
    @foreach($grouped as $budget => $lignes)
        @foreach($lignes as $ligne => $donnees)
            @foreach($donnees as $d)
                <tr>
                    <td>{{ $d->donnee_ligne_budgetaire_entree }}</td>
                    <td>{{ $d->code_donnee_budgetaire_entree }}</td>
                    <td>{{ $d->numero_donnee_budgetaire_entree }}</td>
                    <td>{{ $d->montant }}</td>
                    <td>{{ $budget }}</td>
                    <td>{{ $ligne }}</td>
                    <td>{{ $d->date_creation }}</td>
                </tr>
            @endforeach
        @endforeach
    @endforeach
    </tbody>
</table>
