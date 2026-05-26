<div class="row">
    <div class="col-md-6">
        <label>Budget</label>
        <select name="id_budget" class="form-control js-budget-daf" data-bon="{{ $bon->id }}" required>
            <option value="">-- Choisir --</option>
            @foreach($budgets as $budget)
                <option value="{{ $budget->id }}" {{ $bon->id_budget == $budget->id ? 'selected' : '' }}>{{ $budget->libelle_ligne_budget }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label>Annee academique</label>
        <select name="id_annee_academique" class="form-control" required>
            <option value="">-- Choisir --</option>
            @foreach($annees as $annee)
                <option value="{{ $annee->id }}" {{ $bon->id_annee_academique == $annee->id ? 'selected' : '' }}>{{ $annee->nom }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mt-2">
        <label>Ligne budgetaire</label>
        <select name="id_ligne_budgetaire_sortie" class="form-control js-ligne-daf" data-bon="{{ $bon->id }}" data-selected="{{ $bon->id_ligne_budgetaire_sortie }}" required>
            <option value="">-- Choisir --</option>
        </select>
    </div>
    <div class="col-md-6 mt-2">
        <label>Element</label>
        <select name="id_elements_ligne_budgetaire_sortie" class="form-control js-element-daf" data-bon="{{ $bon->id }}" data-selected="{{ $bon->id_elements_ligne_budgetaire_sortie }}" required>
            <option value="">-- Choisir --</option>
        </select>
    </div>
    <div class="col-md-6 mt-2">
        <label>Donnee budgetaire</label>
        <select name="id_donnee_budgetaire_sortie" class="form-control js-donnee-budget-daf" data-bon="{{ $bon->id }}" data-selected="{{ $bon->id_donnee_budgetaire_sortie }}" required>
            <option value="">-- Choisir --</option>
        </select>
    </div>
    <div class="col-md-6 mt-2">
        <label>Donnee ligne</label>
        <select name="id_donnee_ligne_budgetaire_sortie" class="form-control js-donnee-ligne-daf" data-bon="{{ $bon->id }}" data-selected="{{ $bon->id_donnee_ligne_budgetaire_sortie }}" required>
            <option value="">-- Choisir --</option>
        </select>
    </div>
    <div class="col-md-12 mt-2">
        <label>Entree speciale utilisee (optionnel)</label>
        <select name="id_entree_speciale" class="form-control">
            <option value="">-- Aucune --</option>
            @foreach($entreesSpeciales as $entreeSpeciale)
                <option value="{{ $entreeSpeciale->id }}" {{ $bon->id_entree_speciale == $entreeSpeciale->id ? 'selected' : '' }}>
                    {{ ucfirst($entreeSpeciale->type_entree) }} - {{ $entreeSpeciale->libelle }}
                </option>
            @endforeach
        </select>
    </div>
</div>
