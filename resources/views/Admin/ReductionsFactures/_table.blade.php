<table class="table table-bordered table-sm">
    <thead class="table-dark">
    <tr>
        <th>Date</th>
        <th>Etudiant</th>
        <th>Facture</th>
        <th>Entite</th>
        <th>Specialite</th>
        <th>Annee</th>
        <th>Budget</th>
        <th>Motif</th>
        <th class="text-end">Montant facture</th>
        <th class="text-end">Reduction</th>
        <th class="text-end">Facture nette</th>
        @if(!($print ?? false))
            <th class="text-center no-print">Actions</th>
        @endif
    </tr>
    </thead>
    <tbody>
    @forelse($reductions as $reduction)
        @php
            $facture = $reduction->facture;
            $montantFacture = optional($facture)->montant_total_facture ?? 0;
            $totalReductionFacture = optional($facture)->montant_reduction ?? $reduction->montant;
            $netFacture = max($montantFacture - $totalReductionFacture, 0);
        @endphp
        <tr>
            <td>{{ optional(\Carbon\Carbon::parse($reduction->date_reduction))->format('d/m/Y') }}</td>
            <td>{{ optional($reduction->etudiant)->nom ?? '-' }}</td>
            <td>{{ optional($facture)->numero_facture ?? '-' }}</td>
            <td>{{ optional($reduction->entite)->nom_entite ?? '-' }}</td>
            <td>{{ optional($reduction->specialite)->nom_specialite ?? '-' }}</td>
            <td>{{ optional($reduction->annee_academique)->nom ?? '-' }}</td>
            <td>{{ optional($reduction->budget)->libelle_ligne_budget ?? '-' }}</td>
            <td>
                {{ $reduction->motif ?? '-' }}
                @if($reduction->observations)
                    <br><small>{{ $reduction->observations }}</small>
                @endif
            </td>
            <td class="text-end">{{ number_format($montantFacture, 0, ',', ' ') }}</td>
            <td class="text-end">{{ number_format($reduction->montant, 0, ',', ' ') }}</td>
            <td class="text-end">{{ number_format($netFacture, 0, ',', ' ') }}</td>
            @if(!($print ?? false))
                <td class="text-center no-print">
                    <button class="btn btn-sm btn-warning" type="button" data-toggle="collapse" data-target="#edit-reduction-{{ $reduction->id }}">
                        Modifier
                    </button>
                    <form action="{{ route('reductions_factures.destroy', $reduction->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette reduction ?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Supprimer</button>
                    </form>
                </td>
            @endif
        </tr>
        @if(!($print ?? false))
            <tr class="collapse no-print" id="edit-reduction-{{ $reduction->id }}">
                <td colspan="12">
                    <form action="{{ route('reductions_factures.update', $reduction->id) }}" method="POST" class="row g-2">
                        @csrf
                        @method('PUT')
                        <div class="col-md-3">
                            <label>Date</label>
                            <input type="date" name="date_reduction" value="{{ $reduction->date_reduction }}" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label>Montant</label>
                            <input type="number" step="0.01" min="1" name="montant" value="{{ $reduction->montant }}" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label>Motif</label>
                            <input type="text" name="motif" value="{{ $reduction->motif }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label>Observations</label>
                            <input type="text" name="observations" value="{{ $reduction->observations }}" class="form-control">
                        </div>
                        <div class="col-md-12 mt-2">
                            <button class="btn btn-primary">Enregistrer</button>
                        </div>
                    </form>
                </td>
            </tr>
        @endif
    @empty
        <tr>
            <td colspan="{{ ($print ?? false) ? 11 : 12 }}" class="text-center">Aucune reduction trouvee.</td>
        </tr>
    @endforelse
    </tbody>
    <tfoot>
    <tr class="fw-bold table-secondary">
        <td colspan="9">TOTAL</td>
        <td class="text-end">{{ number_format($totalReductions ?? $reductions->sum('montant'), 0, ',', ' ') }}</td>
        <td colspan="{{ ($print ?? false) ? 1 : 2 }}"></td>
    </tr>
    </tfoot>
</table>
