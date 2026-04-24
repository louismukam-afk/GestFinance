<table>
    <thead>
    <tr>
        <th>Libellé</th>
        <th>Code</th>
        <th>Compte</th>
        <th>Montant</th>
        <th>Budget</th>
        <th>Ligne budgétaire</th>
        <th>Date création</th>
    </tr>
    </thead>
    <tbody>
    @foreach($donnees as $d)
        <tr>
            <td>{{ $d->donnee_ligne_budgetaire_sortie }}</td>
            <td>{{ $d->code_donnee_budgetaire_sortie }}</td>
            <td>{{ $d->numero_donnee_budgetaire_sortie }}</td>
            <td>{{ number_format($d->montant, 0, ',', ' ') }}</td>
            <td>{{ $d->budget->libelle ?? '-' }}</td>
            <td>{{ $d->ligne_budgetaire_sortie->libelle ?? '-' }}</td>
            <td>{{ $d->date_creation }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
