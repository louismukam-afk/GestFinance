<table>
    <thead>
    <tr>
        <th>#</th>
        <th>Libellé</th>
        <th>Période</th>
        <th>Montant Global</th>
        <th>Code Budget</th>
        <th>Date Création</th>
        <th>Utilisateur</th>
    </tr>
    </thead>
    <tbody>
    @foreach($budgets as $i => $budget)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $budget->libelle_ligne_budget }}</td>
            <td>{{ $budget->date_debut }} → {{ $budget->date_fin }}</td>
            <td>{{ number_format($budget->montant_global,0,',',' ') }} FCFA</td>
            <td>{{ $budget->code_budget }}</td>
            <td>{{ $budget->date_creation }}</td>
            <td>{{ $budget->user->name ?? 'N/A' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
