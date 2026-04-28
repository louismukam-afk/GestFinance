<div class="row">
    <div class="col-md-6">
        <label>Nom du bon</label>
        <input type="text" name="nom_bon_commande" class="form-control" value="{{ old('nom_bon_commande', $bon->nom_bon_commande ?? '') }}" required>
    </div>
    <div class="col-md-6">
        <label>Montant total</label>
        <input type="number" step="0.01" name="montant_total" class="form-control" value="{{ old('montant_total', $bon->montant_total ?? '') }}" required>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-4">
        <label>Date debut</label>
        <input type="date" name="date_debut" class="form-control" value="{{ old('date_debut', $bon->date_debut ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label>Date fin</label>
        <input type="date" name="date_fin" class="form-control" value="{{ old('date_fin', $bon->date_fin ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label>Date entree signature</label>
        <input type="date" name="date_entree_signature" class="form-control" value="{{ old('date_entree_signature', $bon->date_entree_signature ?? '') }}" required>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-6">
        <label>Personnel concerne</label>
        <select name="id_personnel" class="form-control" required>
            <option value="">Selectionner</option>
            @foreach($personnels as $personnel)
                <option value="{{ $personnel->id }}" {{ old('id_personnel', $bon->id_personnel ?? '') == $personnel->id ? 'selected' : '' }}>
                    {{ $personnel->nom }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label>Entite</label>
        <select name="id_entite" class="form-control" required>
            <option value="">Selectionner</option>
            @foreach($entites as $entite)
                <option value="{{ $entite->id }}" {{ old('id_entite', $bon->id_entite ?? '') == $entite->id ? 'selected' : '' }}>
                    {{ $entite->nom_entite }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-12">
        <label>Montant en lettres</label>
        <input type="text" name="montant_lettre" class="form-control" value="{{ old('montant_lettre', $bon->montant_lettre ?? '') }}" required>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-12">
        <label>Description</label>
        <textarea name="description_bon_commande" class="form-control" rows="4" required>{{ old('description_bon_commande', $bon->description_bon_commande ?? '') }}</textarea>
    </div>
</div>
