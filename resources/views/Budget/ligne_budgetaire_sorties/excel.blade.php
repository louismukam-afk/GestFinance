<table>
    <thead>
    <tr>
        <th>#</th>
        <th>Libellé</th>
        <th>Code</th>
        <th>N° Compte</th>
        <th>Description</th>
        <th>Date Création</th>
    </tr>
    </thead>
    <tbody>
    @foreach($lignes as $ligne)
        <tr>
            <td>{{ $ligne->id }}</td>
            <td>{{ $ligne->libelle_ligne_budgetaire_sortie }}</td>
            <td>{{ $ligne->code_ligne_budgetaire_sortie }}</td>
            <td>{{ $ligne->numero_compte_ligne_budgetaire_sortie }}</td>
            <td>{{ $ligne->description }}</td>
            <td>{{ $ligne->date_creation }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
