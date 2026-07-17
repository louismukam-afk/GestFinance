<table>
    <thead>
    <tr>
        <th colspan="7">Elements de la ligne budgetaire entree : {{ $ligne->libelle_ligne_budgetaire_entree }}</th>
    </tr>
    <tr>
        <th>#</th>
        <th>Libelle</th>
        <th>Code</th>
        <th>N compte</th>
        <th>Description</th>
        <th>Date creation</th>
        <th>Utilisateur</th>
    </tr>
    </thead>
    <tbody>
    @foreach($elements as $index => $element)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $element->libelle_elements_ligne_budgetaire_entree }}</td>
            <td>{{ $element->code_elements_ligne_budgetaire_entree }}</td>
            <td>{{ $element->numero_compte_elements_ligne_budgetaire_entree }}</td>
            <td>{{ $element->description }}</td>
            <td>{{ $element->date_creation }}</td>
            <td>{{ $element->user->name ?? 'N/A' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
