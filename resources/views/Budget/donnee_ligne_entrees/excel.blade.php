<table>
    <thead>
    <tr>
        <th>Libellé</th>
        <th>Code</th>
        <th>Compte</th>
        <th>Description</th>
        <th>Date</th>
        <th>Élément</th>
    </tr>
    </thead>
    <tbody>
    @foreach($lignes as $l)
        <tr>
            <td>{{ $l->donnee_ligne_budgetaire_entree }}</td>
            <td>{{ $l->code_donnee_ligne_budgetaire_entree }}</td>
            <td>{{ $l->numero_donne_ligne_budgetaire_entree }}</td>
            <td>{{ $l->description }}</td>
            <td>{{ $l->date_creation }}</td>
            <td>{{ $l->element_ligne_budgetaire_entrees->libelle_elements_ligne_budgetaire_entree ?? '-' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
