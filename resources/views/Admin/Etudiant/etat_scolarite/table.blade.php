<div class="row mb-3">
    <div class="col-md-4">
        <div class="alert alert-info"><strong>Total facturé :</strong> {{ number_format($totalFacture, 0, ',', ' ') }} FCFA</div>
    </div>
    <div class="col-md-4">
        <div class="alert alert-success"><strong>Total payé :</strong> {{ number_format($totalPaye, 0, ',', ' ') }} FCFA</div>
    </div>
    <div class="col-md-4">
        <div class="alert alert-warning"><strong>Total reste :</strong> {{ number_format($totalReste, 0, ',', ' ') }} FCFA</div>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>Matricule</th>
                <th>Etudiant</th>
                <th>Cycle</th>
                <th>Filiere</th>
                <th>Niveau</th>
                <th>Specialite</th>
                <th>Annee</th>
                <th>Entite</th>
                <th>Budget</th>
                <th>Ligne</th>
                <th>Element</th>
                <th>Donnee budgetaire</th>
                <th>Donnee ligne</th>
                <th>Tranche</th>
                <th class="text-end">Facture</th>
                <th class="text-end">Paye</th>
                <th class="text-end">Reste</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row['matricule'] ?: '-' }}</td>
                    <td>{{ $row['etudiant']->nom ?? '-' }}</td>
                    <td>{{ $row['cycle'] ?: '-' }}</td>
                    <td>{{ $row['filiere'] ?: '-' }}</td>
                    <td>{{ $row['niveau'] ?: '-' }}</td>
                    <td>{{ $row['specialite'] ?: '-' }}</td>
                    <td>{{ $row['annee'] ?: '-' }}</td>
                    <td>{{ $row['entite'] ?: '-' }}</td>
                    <td>{{ $row['budget'] ?: '-' }}</td>
                    <td>{{ $row['ligne'] ?: '-' }}</td>
                    <td>{{ $row['element'] ?: '-' }}</td>
                    <td>{{ $row['donnee_budgetaire'] ?: '-' }}</td>
                    <td>{{ $row['donnee_ligne'] ?: '-' }}</td>
                    <td>{{ $row['tranche'] ?: '-' }}</td>
                    <td class="text-end">{{ number_format($row['facture'], 0, ',', ' ') }}</td>
                    <td class="text-end">{{ number_format($row['paye'], 0, ',', ' ') }}</td>
                    <td class="text-end">{{ number_format($row['reste'], 0, ',', ' ') }}</td>
                    <td>
                        <span class="badge {{ $row['statut_paiement'] === 'Payé' ? 'bg-success' : 'bg-warning' }}">
                            {{ $row['statut_paiement'] }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="18" class="text-center">Aucune donnée trouvée.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
