@php
    $p = $personnel ?? null;
    $disabledAttr = !empty($disabled) ? 'disabled' : '';
    $oldOr = function ($name, $default = '') use ($p) {
        return old($name, $p ? ($p->{$name} ?? $default) : $default);
    };
    $selectedSexe = $oldOr('sexe');
    if ($selectedSexe === 'Féminin') {
        $selectedSexe = 'Feminin';
    }
    $selectedStatut = $oldOr('statut_matrimonial');
    $statutAliases = [
        'Célibataire' => 'Celibataire',
        'Marié(e)' => 'Marie(e)',
        'Divorcé(e)' => 'Divorce(e)',
    ];
    $selectedStatut = $statutAliases[$selectedStatut] ?? $selectedStatut;
@endphp

<div class="row">
    <div class="col-md-4 form-group">
        <label>Nom</label>
        <input type="text" name="nom" class="form-control personnel-field" value="{{ $oldOr('nom') }}" required {{ $disabledAttr }}>
    </div>
    <div class="col-md-4 form-group">
        <label>Nom biometrie</label>
        <input type="text" name="nom_biometrie" class="form-control personnel-field" value="{{ $oldOr('nom_biometrie', $oldOr('nom')) }}" placeholder="Nom affiche par l appareil" {{ $disabledAttr }}>
    </div>
    <div class="col-md-4 form-group">
        <label>Date de naissance</label>
        <input type="date" name="date_naissance" class="form-control personnel-field" value="{{ $oldOr('date_naissance') }}" required {{ $disabledAttr }}>
    </div>
    <div class="col-md-4 form-group">
        <label>Lieu de naissance</label>
        <input type="text" name="lieu_naissance" class="form-control personnel-field" value="{{ $oldOr('lieu_naissance') }}" required {{ $disabledAttr }}>
    </div>

    <div class="col-md-4 form-group">
        <label>Adresse</label>
        <input type="text" name="adresse" class="form-control personnel-field" value="{{ $oldOr('adresse') }}" required {{ $disabledAttr }}>
    </div>
    <div class="col-md-4 form-group">
        <label>Sexe</label>
        <select name="sexe" class="form-control personnel-field" required {{ $disabledAttr }}>
            @foreach(['Masculin' => 'Masculin', 'Feminin' => 'Feminin', 'Autre' => 'Autre'] as $value => $label)
                <option value="{{ $value }}" {{ $selectedSexe === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 form-group">
        <label>Statut matrimonial</label>
        <select name="statut_matrimonial" class="form-control personnel-field" required {{ $disabledAttr }}>
            @foreach(['Celibataire' => 'Celibataire', 'Marie(e)' => 'Marie(e)', 'Divorce(e)' => 'Divorce(e)', 'Veuf(ve)' => 'Veuf(ve)'] as $value => $label)
                <option value="{{ $value }}" {{ $selectedStatut === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4 form-group">
        <label>Email</label>
        <input type="email" name="email" class="form-control personnel-field" value="{{ $oldOr('email') }}" {{ $disabledAttr }}>
    </div>
    <div class="col-md-4 form-group">
        <label>Telephone</label>
        <input type="text" name="telephone" class="form-control personnel-field" value="{{ $oldOr('telephone') }}" required {{ $disabledAttr }}>
    </div>
    <div class="col-md-4 form-group">
        <label>Telephone WhatsApp</label>
        <input type="text" name="telephone_whatsapp" class="form-control personnel-field" value="{{ $oldOr('telephone_whatsapp') }}" {{ $disabledAttr }}>
    </div>

    <div class="col-md-4 form-group">
        <label>Numero CNPS</label>
        <input type="text" name="numero_cnps" class="form-control personnel-field" value="{{ $oldOr('numero_cnps') }}" {{ $disabledAttr }}>
    </div>
    <div class="col-md-4 form-group">
        <label>Numero contribuable</label>
        <input type="text" name="numero_contribuable" class="form-control personnel-field" value="{{ $oldOr('numero_contribuable') }}" {{ $disabledAttr }}>
    </div>
    <div class="col-md-4 form-group">
        <label>Nationalite</label>
        <input type="text" name="nationalite" class="form-control personnel-field" value="{{ $oldOr('nationalite') }}" required {{ $disabledAttr }}>
    </div>

    <div class="col-md-4 form-group">
        <label>Diplome</label>
        <input type="text" name="diplome" class="form-control personnel-field" value="{{ $oldOr('diplome') }}" {{ $disabledAttr }}>
    </div>
    <div class="col-md-4 form-group">
        <label>Niveau d'etude</label>
        <input type="text" name="niveau_etude" class="form-control personnel-field" value="{{ $oldOr('niveau_etude') }}" {{ $disabledAttr }}>
    </div>
    <div class="col-md-4 form-group">
        <label>Domaine de formation</label>
        <input type="text" name="domaine_formation" class="form-control personnel-field" value="{{ $oldOr('domaine_formation') }}" {{ $disabledAttr }}>
    </div>

    <div class="col-md-4 form-group">
        <label>Date de recrutement</label>
        <input type="date" name="date_recrutement" class="form-control personnel-field" value="{{ $oldOr('date_recrutement') }}" required {{ $disabledAttr }}>
    </div>
    <div class="col-md-4 form-group">
        <label>Type personnel</label>
        <select name="type_personnel" class="form-control personnel-field" required {{ $disabledAttr }}>
            <option value="permanent" {{ $oldOr('type_personnel', 'permanent') === 'permanent' ? 'selected' : '' }}>Permanent</option>
            <option value="vacataire" {{ $oldOr('type_personnel') === 'vacataire' ? 'selected' : '' }}>Vacataire</option>
        </select>
    </div>
    <div class="col-md-4 form-group">
        <label>Mode horaire</label>
        <select name="mode_horaire" class="form-control personnel-field" {{ $disabledAttr }}>
            <option value="strict" {{ $oldOr('mode_horaire', 'strict') === 'strict' ? 'selected' : '' }}>Strict</option>
            <option value="souple" {{ $oldOr('mode_horaire') === 'souple' ? 'selected' : '' }}>Souple</option>
        </select>
    </div>

    <div class="col-md-4 form-group">
        <label>Categorie horaire</label>
        <select name="categorie_horaire" class="form-control personnel-field" {{ $disabledAttr }}>
            <option value="standard" {{ $oldOr('categorie_horaire', 'standard') === 'standard' ? 'selected' : '' }}>Standard</option>
            <option value="conseil_administration" {{ $oldOr('categorie_horaire') === 'conseil_administration' ? 'selected' : '' }}>Conseil administration</option>
            <option value="chef_service" {{ $oldOr('categorie_horaire') === 'chef_service' ? 'selected' : '' }}>Chef de service</option>
            <option value="coordination" {{ $oldOr('categorie_horaire') === 'coordination' ? 'selected' : '' }}>Coordination</option>
        </select>
    </div>
    <div class="col-md-4 form-group">
        <label>Horaire travail</label>
        <select name="horaire_travail" class="form-control personnel-field" {{ $disabledAttr }}>
            <option value="">-- Aucun --</option>
            <option value="permanent_jour" {{ $oldOr('horaire_travail') === 'permanent_jour' ? 'selected' : '' }}>Permanent jour</option>
            <option value="permanent_soir" {{ $oldOr('horaire_travail') === 'permanent_soir' ? 'selected' : '' }}>Permanent soir</option>
            <option value="vacataire_jour" {{ $oldOr('horaire_travail') === 'vacataire_jour' ? 'selected' : '' }}>Vacataire jour</option>
            <option value="vacataire_soir" {{ $oldOr('horaire_travail') === 'vacataire_soir' ? 'selected' : '' }}>Vacataire soir</option>
        </select>
    </div>
</div>
