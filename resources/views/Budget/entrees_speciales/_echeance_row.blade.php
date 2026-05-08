<div class="row echeance-row mb-2">
    <div class="col-md-3">
        <input type="text" name="echeances[{{ $index }}][nom_echeance]" value="{{ $echeance['nom_echeance'] ?? '' }}" class="form-control" placeholder="Nom echeance">
    </div>
    <div class="col-md-3">
        <input type="date" name="echeances[{{ $index }}][date_echeance]" value="{{ $echeance['date_echeance'] ?? '' }}" class="form-control">
    </div>
    <div class="col-md-2">
        <input type="number" name="echeances[{{ $index }}][montant]" value="{{ $echeance['montant'] ?? '' }}" min="0" step="0.01" class="form-control" placeholder="Montant">
    </div>
    <div class="col-md-3">
        <input type="text" name="echeances[{{ $index }}][observations]" value="{{ $echeance['observations'] ?? '' }}" class="form-control" placeholder="Observation">
    </div>
    <div class="col-md-1">
        <button type="button" class="btn btn-danger remove-echeance">X</button>
    </div>
</div>
