@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-primary">{{ $title }}</h3>
        <div>
            <a href="{{ route('biometrie_heures.index') }}" class="btn btn-secondary">Retour</a>
            <a href="{{ route('biometrie_heures.requetes.index', ['id_import_parent' => $selectedImport?->id_import_parent ?: $selectedImport?->id]) }}" class="btn btn-info">Suivi des requetes</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif
    @if($imports->isEmpty())
        <div class="alert alert-info">
            Aucun import enseignant disponible.
        </div>
    @endif
    <h4>Nouvelle requete sur les heures et sanctions</h4>
    <form method="POST" action="{{ route('biometrie_heures.requetes.store') }}" style="margin-bottom:25px">
        @csrf
        <div class="row">
            <div class="col-sm-6 form-group"><label for="parentRequete">Import initial</label><select id="parentRequete" name="id_import_parent" class="form-control" required><option value="">Choisir</option>@foreach($parentsRequetes as $parent)<option value="{{ $parent->id }}" @selected(old('id_import_parent', $selectedImport?->id_import_parent ?: $selectedImport?->id) == $parent->id)>#{{ $parent->id }} - {{ $parent->libelle }}</option>@endforeach</select></div>
            <div class="col-sm-3 form-group"><label for="moisRequete">Mois concerne</label><input id="moisRequete" type="month" name="mois_requete" class="form-control" value="{{ old('mois_requete', $selectedImport?->mois_requete?->format('Y-m')) }}" required></div>
            <div class="col-sm-12 form-group"><label for="motifRequete">Motif et decision sur la requete</label><textarea id="motifRequete" name="motif_requete" rows="2" maxlength="3000" class="form-control" required>{{ old('motif_requete') }}</textarea></div>
        </div>
        <button class="btn btn-primary" @disabled($parentsRequetes->isEmpty())><i class="fa fa-plus"></i> Creer le dossier apres requete</button>
    </form>
    @if($selectedImport)
        <h4>#{{ $selectedImport->id }} - {{ $selectedImport->libelle }}</h4>
        @if($selectedImport->estRequete())<p>Apres requete : import initial #{{ $selectedImport->id_import_parent }}, mois {{ $selectedImport->mois_requete->format('m/Y') }}. {{ $selectedImport->motif_requete }}</p>@endif
        @if($selectedImport->total_consolidees > 0)
        <div style="margin-bottom:15px;display:flex;flex-wrap:wrap;gap:6px">
            <a class="btn btn-default" href="{{ route('biometrie_heures.index', ['id_biometrie_import' => $selectedImport->id]) }}"><i class="fa fa-table"></i> Consolidation des heures</a>
            <a class="btn btn-default" href="{{ route('biometrie_heures.impots', $selectedImport) }}"><i class="fa fa-percent"></i> Impots</a>
            <a class="btn btn-default" href="{{ route('virements_vacataires.create', $selectedImport) }}"><i class="fa fa-exchange"></i> Etat de virement</a>
            <a class="btn btn-default" href="{{ route('bulletins_vacataires.show', $selectedImport) }}"><i class="fa fa-file-text-o"></i> Bulletins</a>
        </div>
        @endif
    @endif
    @if($cours->isEmpty())
        <div class="alert alert-warning">
            Aucun cours enseignant programme n'est disponible. Cree d'abord les cours enseignants et leurs seances avant de saisir les heures realisees.
        </div>
    @else
        <div class="alert alert-secondary">
            {{ number_format($cours->count(), 0, ',', ' ') }} cours enseignant(s) disponible(s) pour la saisie manuelle.
        </div>
    @endif

    <form method="POST" action="{{ route('biometrie_heures.manual.store') }}" class="card p-3">
        @csrf
        @if($correction)<input type="hidden" name="id_heure_correction" value="{{ $correction->id }}"><div class="alert alert-info">Modification de la correction #{{ $correction->id }}. Le cours, la seance et la date doivent rester identiques.</div>@endif

        <fieldset class="border p-3 mb-4">
            <legend class="w-auto px-2">Dossier de requete</legend>
            <div class="row">
                <div class="col-md-4">
                    <label>Reutiliser un import / dossier</label>
                    <select name="id_biometrie_import" id="id_biometrie_import" class="form-control">
                        <option value="">-- Choisir un dossier --</option>
                        @foreach($imports as $import)
                            <option value="{{ $import->id }}" data-debut="{{ optional($import->date_debut)->format('Y-m-d') }}" data-fin="{{ optional($import->date_fin)->format('Y-m-d') }}" @selected($selectedImport?->id == $import->id)>
                                {{ $import->libelle }} ({{ optional($import->date_debut)->format('d/m/Y') }} - {{ optional($import->date_fin)->format('d/m/Y') }})
                            </option>
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-default btn-sm" id="chargerImport"><i class="fa fa-refresh"></i> Charger cet import</button>
                </div>
                <div class="col-md-2">
                    <label>Date debut</label>
                    <input type="date" id="date_debut_import" class="form-control" readonly>
                </div>
                <div class="col-md-2">
                    <label>Date fin</label>
                    <input type="date" id="date_fin_import" class="form-control" readonly>
                </div>
            </div>
        </fieldset>

        <fieldset class="border p-3 mb-4">
            <legend class="w-auto px-2">Cours et seance</legend>
            <div class="row">
                <div class="col-md-8">
                    <label>Cours enseignant</label>
                    <input type="text" id="cours_search" class="form-control mb-2" placeholder="Rechercher enseignant, matiere ou specialite">
                    <select name="id_cours_enseignant" id="id_cours_enseignant" class="form-control" required>
                        <option value="">-- Choisir le cours --</option>
                        @foreach($cours as $item)
                            <option value="{{ $item->id }}" {{ old('id_cours_enseignant', $correction?->id_cours_enseignant) == $item->id ? 'selected' : '' }}>
                                {{ $item->personnel->nom ?? '-' }} | {{ $item->programme->matiere->nom_matiere ?? '-' }} | {{ $item->programme->specialite->nom_specialite ?? '-' }} | {{ optional($item->date_debut)->format('d/m/Y') }} - {{ optional($item->date_fin)->format('d/m/Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Seance / plage</label>
                    <select name="id_seance_cours" id="id_seance_cours" class="form-control" required>
                        <option value="">-- Choisir la seance --</option>
                    </select>
                </div>
            </div>
        </fieldset>

        <fieldset class="border p-3 mb-4">
            <legend class="w-auto px-2">Heures realisees</legend>
            @if($selectedImport?->estRequete())
                <div class="form-group">
                    <label>Pointages associés à l'enseignant dans l'import initial pour ce mois</label><br>
                    <label><input type="radio" name="presence_import" value="present" @checked(old('presence_import') === 'present') required> Présent dans l'import</label>
                    &nbsp;&nbsp;
                    <label><input type="radio" name="presence_import" value="absent" @checked(old('presence_import') === 'absent') required> Non présent dans l'import</label>
                    <small class="form-text text-muted">Le système vérifie les pointages associés au personnel sur le mois. En cas d'absence de pointages, une séance consolidée existante reste prise en compte.</small>
                </div>
                <div class="form-group">
                    <label for="typeRequete">Nature de la requete</label>
                    <select id="typeRequete" name="type_requete" class="form-control" required>
                        <option value="absence_justifiee" @selected(old('type_requete', $correction?->type_requete) === 'absence_justifiee')>Absence justifiee - restitution de sanction uniquement</option>
                        <option value="presence_manuelle" @selected(old('type_requete', $correction?->type_requete) === 'presence_manuelle')>Presence attestee par releve administratif - appareil hors service</option>
                        <option value="pointage_incomplet" @selected(old('type_requete', $correction?->type_requete) === 'pointage_incomplet')>Pointage incomplet - entree ou sortie manquante</option>
                    </select>
                    <small id="explicationAbsence" class="form-text text-muted">Une absence justifiee conserve les heures deja comptabilisees et leve uniquement la sanction de la seance. Le complement depend de la sanction effectivement retenue sur le mois et des droits deja couverts ; il est calcule avant impot et frais eventuels.</small>
                </div>
            @endif
            <div class="row">
                <div class="col-md-3">
                    <label>Date seance</label>
                    <input type="date" name="date_seance" class="form-control" value="{{ old('date_seance', $correction?->date_seance?->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-2">
                    <label>Debut atteste</label>
                    <input type="time" name="heure_debut_reelle" id="heure_debut_reelle" class="form-control" value="{{ old('heure_debut_reelle', $correction?->heure_debut_reelle ? substr($correction->heure_debut_reelle, 0, 5) : '') }}" required>
                </div>
                <div class="col-md-2">
                    <label>Fin attestee</label>
                    <input type="time" name="heure_fin_reelle" id="heure_fin_reelle" class="form-control" value="{{ old('heure_fin_reelle', $correction?->heure_fin_reelle ? substr($correction->heure_fin_reelle, 0, 5) : '') }}" required>
                </div>
                <div class="col-md-2">
                    <label>Duree reconnue</label>
                    <input type="text" id="duree_calculee" class="form-control" value="0 h" readonly>
                </div>
                <div class="col-md-12 form-group">
                    <label for="justificationRequete">Justification / reference du releve administratif</label>
                    <textarea id="justificationRequete" name="observation" rows="3" maxlength="1000" class="form-control" required>{{ old('observation', $correction?->observation) }}</textarea>
                </div>
            </div>
            <div id="referenceSeance" class="alert alert-info" role="status" hidden></div>
        </fieldset>

        <button id="enregistrerRequete" class="btn btn-success" @disabled(!$selectedImport?->estRequete())><i class="fa fa-check"></i> Valider la correction dans le dossier</button>
    </form>

    <div class="card mt-4">
        <div class="card-header"><strong>Imports et dossiers disponibles</strong></div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Libelle</th>
                        <th>Periode</th>
                        <th>Heures saisies</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($imports as $import)
                        <tr>
                            <td>{{ $import->libelle }}</td>
                            <td>{{ optional($import->date_debut)->format('d/m/Y') }} - {{ optional($import->date_fin)->format('d/m/Y') }}</td>
                            <td>{{ number_format($import->total_consolidees, 0, ',', ' ') }}</td>
                            <td>
                                <a href="{{ route('biometrie_heures.manual.create', ['id_biometrie_import' => $import->id]) }}" class="btn btn-xs btn-primary">Utiliser</a>
                                <a href="{{ route('biometrie_heures.index', ['id_biometrie_import' => $import->id]) }}" class="btn btn-xs btn-info">Voir decompte</a>
                                @if($import->total_consolidees > 0)<a href="{{ route('virements_vacataires.create', $import) }}" class="btn btn-xs btn-default">Virement</a>@endif
                                @if($import->estRequete())
                                    @if($requetesAvecOrdre->contains($import->id))
                                        <button type="button" class="btn btn-xs btn-danger" disabled title="Un ordre de virement est deja valide pour ce dossier"><i class="fa fa-trash"></i> Supprimer le dossier</button>
                                    @else
                                        <form method="POST" action="{{ route('biometrie_heures.destroy', $import) }}" style="display:inline" onsubmit="return confirm('Supprimer ce dossier de requete et ses heures corrigees ? L import initial et les autres dossiers seront conserves.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i> Supprimer le dossier</button>
                                        </form>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">Aucun import manuel pour le moment.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header"><strong>Seances du dossier et decisions</strong></div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Import</th>
                        <th>Date</th>
                        <th>Enseignant</th>
                        <th>Matiere</th>
                        <th>Plage prevue</th>
                        <th>Heures realisees</th>
                        <th>Duree</th>
                        <th>Montant</th>
                        <th>Requete</th>
                        <th>Heures avant / apres</th>
                        <th>Sanction avant / apres</th>
                        <th>Justification</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($heuresManuelles as $heure)
                        <tr>
                            <td>{{ $heure->import->libelle ?? '-' }}</td>
                            <td>{{ optional($heure->date_seance)->format('d/m/Y') }}</td>
                            <td>{{ $heure->personnel->nom ?? '-' }}</td>
                            <td>{{ $heure->cours->programme->matiere->nom_matiere ?? '-' }}</td>
                            <td>{{ substr($heure->heure_debut_prevue, 0, 5) }} - {{ substr($heure->heure_fin_prevue, 0, 5) }}</td>
                            <td>{{ substr($heure->heure_debut_reelle, 0, 5) }} - {{ substr($heure->heure_fin_reelle, 0, 5) }}</td>
                            <td>{{ number_format($heure->duree_realisee, 2, ',', ' ') }} h</td>
                            <td>{{ number_format($heure->montant_total, 0, ',', ' ') }}</td>
                            <td>{{ $heure->libelle_type_requete ?? '-' }}</td>
                            <td>{{ $heure->duree_avant_requete === null ? '-' : number_format($heure->duree_avant_requete, 2, ',', ' ') }} / {{ number_format($heure->duree_realisee, 2, ',', ' ') }} h</td>
                            <td>{{ $heure->sanction_avant_requete === null ? '-' : number_format($heure->sanction_avant_requete, 0, ',', ' ') }} / {{ number_format($heure->sanction_annulee ? 0 : max($heure->duree_prevue - $heure->duree_realisee, 0) * $heure->montant_taux, 0, ',', ' ') }} FCFA</td>
                            <td>{{ $heure->observation }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="12" class="text-center text-muted">Aucune seance dans ce dossier.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('breadcrumb')
<ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
    <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
    <li><a href="{{ route('emploi_temps.index') }}"><strong>Ressources humaines</strong></a></li>
    <li><a href="{{ route('biometrie_heures.index') }}"><strong>Decompte heures</strong></a></li>
    <li class="active"><strong>{{ $title }}</strong></li>
</ol>
@endsection

@section('scripts')
<script>
const coursData = @json($coursData);
const coursSelect = document.getElementById('id_cours_enseignant');
const seanceSelect = document.getElementById('id_seance_cours');
const debutInput = document.getElementById('heure_debut_reelle');
const finInput = document.getElementById('heure_fin_reelle');
const dureeInput = document.getElementById('duree_calculee');
const typeRequete = document.getElementById('typeRequete');
const referenceUrl = @json($selectedImport?->estRequete() ? route('biometrie_heures.requetes.reference', $selectedImport) : null);
let reference = null;
let referenceRequest = null;

function absenceJustifiee() {
    return typeRequete?.value === 'absence_justifiee';
}

function refreshDecision() {
    debutInput.disabled = finInput.disabled = absenceJustifiee();
    debutInput.required = finInput.required = !absenceJustifiee();
    const explication = document.getElementById('explicationAbsence');
    if (explication) explication.hidden = !absenceJustifiee();
    calculerDuree();
}

async function chargerReference() {
    referenceRequest?.abort();
    reference = null;
    const target = document.getElementById('referenceSeance');
    target.hidden = true;
    calculerDuree();
    const date = document.querySelector('[name="date_seance"]').value;
    if (!referenceUrl || !coursSelect.value || !seanceSelect.value || !date) return;
    referenceRequest = new AbortController();
    const params = new URLSearchParams({ id_cours_enseignant: coursSelect.value, id_seance_cours: seanceSelect.value, date_seance: date, presence_import: document.querySelector('[name="presence_import"]:checked')?.value || '' });
    try {
        const response = await fetch(referenceUrl + '?' + params, { headers: { Accept: 'application/json' }, signal: referenceRequest.signal });
        const data = await response.json();
        target.hidden = false;
        if (!response.ok) {
            target.textContent = data.message || 'Reference de la seance indisponible.';
            return;
        }
        const historique = data.historique_initial;
        const montant = value => Number(value).toLocaleString('fr-FR', { maximumFractionDigits: 2 });
        const autres = historique.autres_sanctions.map(s => `${s.date}, ${s.matiere} (${s.debut}-${s.fin}) : ${montant(s.montant)} FCFA`).join(' ; ') || 'aucune';
        const archive = historique.ordre_initial ? (historique.sanction_archivee === null ? 'ordre initial existant, détail de sanction indisponible' : `sanction totale archivée : ${montant(historique.sanction_archivee)} FCFA`) : 'aucun ordre initial archivé pour ce mois';
        const verification = `Consolidation initiale : ${historique.seance_trouvee ? 'séance trouvée, sanction calculée ' + montant(historique.sanction_seance) + ' FCFA' : 'aucune ligne pour cette séance'}. Sanctions calculées du mois : ${montant(historique.sanction_mois_calculee)} FCFA ; plafond calculé sur la rémunération : ${montant(historique.sanction_mois_plafonnee)} FCFA ; ${archive}. Autres séances sanctionnées : ${autres}. Les sanctions sont globales et ne sont pas automatiquement attribuées à cette séance. `;
        if (data.sans_consolidation) {
            target.textContent = verification + data.message;
            return;
        }
        reference = data;
        target.textContent = verification + `Référence actuelle : ${data.duree_prevue} h prévues ; ${data.duree_realisee} h comptabilisées ; sanction ${montant(data.sanction)} FCFA ; montant réalisé ${montant(data.montant_realise)} FCFA. Pointages de l'import initial : ${data.pointages.join(', ') || 'aucun'}. ${data.observation || ''}`;
        calculerDuree();
    } catch (error) {
        if (error.name === 'AbortError') return;
        target.hidden = false;
        target.textContent = 'Reference de la seance indisponible.';
    }
}

function minutes(value) {
    if (!value || !value.includes(':')) return null;
    const parts = value.split(':');
    return parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
}

function selectedSeance() {
    const data = coursData[coursSelect.value];
    if (!data) return null;
    return data.seances.find(item => String(item.id) === String(seanceSelect.value));
}

function refreshSeances() {
    const oldValue = @json(old('id_seance_cours', $correction?->id_seance_cours));
    seanceSelect.innerHTML = '<option value="">-- Choisir la seance --</option>';
    const data = coursData[coursSelect.value];
    if (!data) {
        calculerDuree();
        return;
    }
    data.seances.forEach(item => {
        const option = document.createElement('option');
        option.value = item.id;
        option.textContent = (item.jour_label || '-') + ' - ' + item.libelle;
        option.dataset.debut = item.debut;
        option.dataset.fin = item.fin;
        if (String(oldValue) === String(item.id)) option.selected = true;
        seanceSelect.appendChild(option);
    });
    calculerDuree();
    chargerReference();
}

function calculerDuree() {
    if (absenceJustifiee()) {
        dureeInput.value = reference ? Number(reference.duree_realisee).toFixed(2).replace('.', ',') + ' h' : '-';
        return;
    }
    const seance = selectedSeance();
    const realStart = minutes(debutInput.value);
    const realEnd = minutes(finInput.value);
    if (!seance || realStart === null || realEnd === null || realEnd <= realStart) {
        dureeInput.value = '0 h';
        return;
    }
    const plannedStart = minutes(seance.debut);
    const plannedEnd = minutes(seance.fin);
    const clippedStart = Math.max(realStart, plannedStart);
    const clippedEnd = Math.min(realEnd, plannedEnd);
    const duration = clippedEnd > clippedStart ? ((clippedEnd - clippedStart) / 60) : 0;
    dureeInput.value = duration.toFixed(2).replace('.', ',') + ' h';
}

document.getElementById('id_biometrie_import').addEventListener('change', function () {
    const option = this.selectedOptions[0];
    document.getElementById('date_debut_import').value = option?.dataset.debut || '';
    document.getElementById('date_fin_import').value = option?.dataset.fin || '';
    const dossierCharge = this.value === @json((string) $selectedImport?->id) && @json((bool) $selectedImport?->estRequete());
    document.getElementById('enregistrerRequete').disabled = !dossierCharge;
    if (typeRequete) typeRequete.disabled = !dossierCharge;
});
document.getElementById('cours_search').addEventListener('input', function () {
    const needle = this.value.toLowerCase();
    Array.from(coursSelect.options).forEach(option => {
        if (!option.value) return;
        option.hidden = !option.textContent.toLowerCase().includes(needle);
    });
});
coursSelect.addEventListener('change', refreshSeances);
seanceSelect.addEventListener('change', chargerReference);
document.querySelector('[name="date_seance"]').addEventListener('change', chargerReference);
typeRequete?.addEventListener('change', refreshDecision);
document.querySelectorAll('[name="presence_import"]').forEach(input => input.addEventListener('change', chargerReference));
debutInput.addEventListener('input', calculerDuree);
finInput.addEventListener('input', calculerDuree);
document.getElementById('id_biometrie_import').dispatchEvent(new Event('change'));
document.getElementById('chargerImport').addEventListener('click', function () {
    const id = document.getElementById('id_biometrie_import').value;
    window.location.href = @json(route('biometrie_heures.manual.create')) + (id ? '?id_biometrie_import=' + encodeURIComponent(id) : '');
});
refreshSeances();
refreshDecision();
</script>
@endsection
