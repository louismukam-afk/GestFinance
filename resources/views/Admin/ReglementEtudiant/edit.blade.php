{{--
@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="text-primary">✏️ Modifier le règlement N° {{ $reglement->numero_reglement }}</h3>

        --}}
{{-- Infos facture / étudiant --}}{{--

        @isset($facture)
            <div class="alert alert-info">
                <strong>Facture :</strong> {{ $facture->numero_facture ?? '—' }} —
                <strong>Étudiant :</strong> {{ $reglement->etudiants->nom ?? '—' }}
            </div>
        @endisset

        <form method="POST" action="{{ route('update_reglement') }}">
            @csrf
            <input type="hidden" name="id" value="{{ $reglement->id }}">
            <input type="hidden" name="id_facture_etudiant" value="{{ $reglement->id_facture_etudiant }}">
            <input type="hidden" name="id_etudiant" value="{{ $reglement->id_etudiant }}">
            <input type="hidden" name="id_annee_academique" value="{{ $reglement->id_annee_academique }}">

            --}}
{{-- Bloc Pédagogie (cycle, filière, niveau, spécialité, scolarité) --}}{{--

            <div class="card p-3 mb-3">
                <h5>Informations pédagogiques</h5>
                <div class="row">
                    <div class="col-md-3">
                        <label>Cycle</label>
                        <select id="cycle" name="id_cycle" class="form-control">
                            <option value="">—</option>
                            @foreach($cycles as $c)
                                <option value="{{ $c->id }}" @selected($reglement->id_cycle == $c->id)>{{ $c->nom_cycle }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Filière</label>
                        <select id="filiere" name="id_filiere" class="form-control">
                            <option value="">—</option>
                            @foreach($filieres as $f)
                                <option value="{{ $f->id }}" @selected($reglement->id_filiere == $f->id)>{{ $f->nom_filiere }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Niveau</label>
                        <select id="niveau" name="id_niveau" class="form-control">
                            <option value="">—</option>
                            @foreach($niveaux ?? [] as $n)
                                <option value="{{ $n->id }}" @selected($reglement->id_niveau == $n->id)>{{ $n->nom_niveau }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Spécialité</label>
                        <select id="specialite" name="id_specialite" class="form-control">
                            <option value="">—</option>
                            @foreach($specialites ?? [] as $s)
                                <option value="{{ $s->id }}" @selected($reglement->id_specialite == $s->id)>{{ $s->nom_specialite }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mt-3">
                        <label>Scolarité</label>
                        <select id="scolarite" name="id_scolarite" class="form-control">
                            <option value="">—</option>
                            @foreach($scolarites ?? [] as $sco)
                                <option value="{{ $sco->id }}" @selected($reglement->id_scolarite == $sco->id)>{{ $sco->libelle ?? ('Scolarité #'.$sco->id) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mt-3">
                        <label>Tranche de scolarité</label>
                        <select id="tranche" name="id_tranche_scolarite" class="form-control">
                            <option value="">—</option>
                            @foreach(($tranches ?? []) as $t)
                                <option value="{{ $t->id }}" @selected($reglement->id_tranche_scolarite == $t->id)>{{ $t->nom_tranche }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Laisser vide pour un règlement global (toutes tranches).</small>
                    </div>
                </div>
            </div>

            --}}
{{-- Mode de versement --}}{{--

            <div class="card p-3 mb-3">
                <h5>Mode de versement</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div>
                            @php $tv = $reglement->type_versement; @endphp
                            <label class="mr-2"><input type="radio" name="type_versement" value="espece"   {{ $tv==='espece'?'checked':'' }}> Espèce</label>
                            <label class="mr-2"><input type="radio" name="type_versement" value="bancaire" {{ $tv==='bancaire'?'checked':'' }}> Bancaire</label>
                            <label class="mr-2"><input type="radio" name="type_versement" value="om"       {{ $tv==='om'?'checked':'' }}> Orange Money</label>
                            <label><input type="radio" name="type_versement" value="mtn"     {{ $tv==='mtn'?'checked':'' }}> MTN Money</label>
                        </div>
                    </div>

                    <div class="col-md-4" id="bloc-caisse" style="{{ $tv==='espece'?'':'display:none' }}">
                        <label>Caisse</label>
                        <select name="id_caisse" class="form-control">
                            <option value="">—</option>
                            @foreach($caisses as $c)
                                <option value="{{ $c->id }}" @selected($reglement->id_caisse == $c->id)>{{ $c->nom_caisse }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4" id="bloc-banque" style="{{ $tv==='bancaire'?'':'display:none' }}">
                        <label>Banque</label>
                        <select name="id_banque" class="form-control">
                            <option value="">—</option>
                            @foreach($banques as $b)
                                <option value="{{ $b->id }}" @selected($reglement->id_banque == $b->id)>{{ $b->nom_banque }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            --}}
{{-- Affectation budgétaire --}}{{--

            <div class="card p-3 mb-3">
                <h5>Affectation budgétaire</h5>
                <div class="row">
                    <div class="col-md-3">
                        <label>Budget <span class="text-danger">*</span></label>
                        <select id="budget" name="id_budget" class="form-control" required>
                            <option value="">—</option>
                            @foreach($budgets as $bud)
                                <option value="{{ $bud->id }}" @selected($reglement->id_budget == $bud->id)>{{ $bud->libelle_ligne_budget ?? ('Budget #'.$bud->id) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Ligne <span class="text-danger">*</span></label>
                        <select id="ligne" name="id_ligne_budgetaire_entree" class="form-control" required>
                            <option value="">—</option>
                            @foreach($lignes ?? [] as $lg)
                                <option value="{{ $lg->id }}" @selected($reglement->id_ligne_budgetaire_entree == $lg->id)>{{ $lg->libelle_ligne_budgetaire_entree }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Élément <span class="text-danger">*</span></label>
                        <select id="element" name="id_element_ligne_budgetaire_entree" class="form-control" required>
                            <option value="">—</option>
                            @foreach($elements ?? [] as $el)
                                <option value="{{ $el->id }}" @selected($reglement->id_element_ligne_budgetaire_entree == $el->id)>{{ $el->libelle_elements_ligne_budgetaire_entree }}</option>
                            @endforeach
                        </select>
                    </div>

                    --}}
{{-- Donnée budgétaire d’entrée --}}{{--

                    <div class="col-md-3">
                        <label>Donnée budgétaire <span class="text-danger">*</span></label>
                        <select id="donneeBudget" name="id_donnee_budgetaire_entree" class="form-control" required>
                            <option value="">—</option>
                            @foreach($donneesBudget ?? [] as $db)
                                <option value="{{ $db->id }}" @selected($reglement->id_donnee_budgetaire_entree == $db->id)>{{ $db->label ?? $db->donnee_budgetaire_entree ?? ('#'.$db->id) }}</option>
                            @endforeach
                        </select>
                    </div>

                    --}}
{{-- Donnée de ligne budgétaire d’entrée --}}{{--

                    <div class="col-md-3 mt-3">
                        <label>Donnée de ligne <span class="text-danger">*</span></label>
                        <select id="donneeLigne" name="id_donnee_ligne_budgetaire_entree" class="form-control" required>
                            <option value="">—</option>
                            @foreach($donneesLigne ?? [] as $dl)
                                <option value="{{ $dl->id }}" @selected($reglement->id_donnee_ligne_budgetaire_entree == $dl->id)>{{ $dl->label ?? $dl->donnee_ligne_budgetaire_entree ?? ('#'.$dl->id) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            --}}
{{-- Montant & Motif --}}{{--

            <div class="card p-3 mb-3">
                <div class="row">
                    <div class="col-md-3">
                        <label>Montant du règlement <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" class="form-control" name="montant_reglement" value="{{ old('montant_reglement', $reglement->montant_reglement) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label>date du règlement <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="date_reglement" value="{{ old('date_reglement', $reglement->date_reglement) }}" required>
                    </div>
                    <div class="col-md-9">
                        <label>Motif</label>
                        <input type="text" class="form-control" name="motif_reglement" value="{{ old('motif_reglement', $reglement->motif_reglement) }}" placeholder="Ex: acompte, tranche 1, frais...">
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <button class="btn btn-success">💾 Enregistrer</button>
                <a class="btn btn-default" href="{{ route('reglement_by_facture', $reglement->id_facture_etudiant) }}">Annuler</a>
                <a class="btn btn-default" target="_blank" href="{{ route('reglement_pdf', $reglement->id) }}">🧾 PDF</a>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        (function(){
            // Toggle caisse / banque
            function refreshVersement(){
                const val = document.querySelector('input[name="type_versement"]:checked')?.value;
                document.getElementById('bloc-caisse').style.display = (val === 'espece') ? '' : 'none';
                document.getElementById('bloc-banque').style.display = (val === 'bancaire') ? '' : 'none';
            }
            document.querySelectorAll('input[name="type_versement"]').forEach(r=>r.addEventListener('change', refreshVersement));
            refreshVersement();

            // Small helper
            function fill(select, items, labelKey, valueKey){
                const current = select.getAttribute('data-current') || '';
                const curVal  = select.value || current;
                select.innerHTML = '<option value="">—</option>';
                (items||[]).forEach(it=>{
                    const o = document.createElement('option');
                o.value = it[valueKey];
                o.textContent = it[labelKey];
                if (String(curVal) === String(it[valueKey])) o.selected = true;
                select.appendChild(o);
            });
            }

            // ---------- Pédagogie (cycle + filière ⇒ niveaux, spécialités, scolarités) ----------
            const $cycle = document.getElementById('cycle');
            const $filiere = document.getElementById('filiere');
            const $niveau = document.getElementById('niveau');
            const $spec = document.getElementById('specialite');
            const $sco = document.getElementById('scolarite');

            function refreshPedago(){
                const id_cycle = $cycle.value, id_filiere = $filiere.value;
                if(!id_cycle || !id_filiere){
                    fill($niveau, [], 'nom_niveau','id');
                    fill($spec,   [], 'nom_specialite','id');
                    fill($sco,    [], 'label','id');
                    return;
                }
                fetch("{{ route('ajax_regl_filters') }}?"+new URLSearchParams({id_cycle, id_filiere}))
                    .then(r=>r.json())
            .then(res=>{
                    fill($niveau, res.niveaux || [], 'nom_niveau','id');
                fill($spec,   res.specialites || [], 'nom_specialite','id');
                fill($sco,    res.scolarites || [], 'label','id');
            });
            }
            $cycle.addEventListener('change', refreshPedago);
            $filiere.addEventListener('change', refreshPedago);

            // ---------- Budget cascade ----------
            const $budget = document.getElementById('budget');
            const $ligne  = document.getElementById('ligne');
            const $elt    = document.getElementById('element');
            const $donB   = document.getElementById('donneeBudget');
            const $donL   = document.getElementById('donneeLigne');

            $budget.addEventListener('change', function(){
                fill($ligne, [], 'libelle_ligne_budgetaire_entree','id');
                fill($elt,   [], 'libelle_elements_ligne_budgetaire_entree','id');
                fill($donB,  [], 'label','id');
                fill($donL,  [], 'label','id');
                if (!this.value) return;
                fetch("{{ route('ajax_regl_lignes', ':id') }}".replace(':id', this.value))
                    .then(r=>r.json())
                .then(items=>fill($ligne, items, 'libelle_ligne_budgetaire_entree','id'));
            });

            $ligne.addEventListener('change', function(){
                fill($elt,  [], 'libelle_elements_ligne_budgetaire_entree','id');
                fill($donB, [], 'label','id');
                fill($donL, [], 'label','id');
                if(!this.value || !$budget.value) return;

                fetch("{{ route('ajax_regl_elements', ':id') }}".replace(':id', this.value))
                    .then(r=>r.json())
                .then(items=>fill($elt, items, 'libelle_elements_ligne_budgetaire_entree','id'));

                fetch("{{ route('ajax_regl_donnees_budget', ':id') }}".replace(':id', this.value) + "?"+new URLSearchParams({id_budget:$budget.value}))
                    .then(r=>r.json())
                .then(items=>fill($donB, items, 'label','id'));
            });

            function refreshDonneesLigne(){
                fill($donL, [], 'label','id');
                if(!$elt.value || !$budget.value || !$donB.value) return;
                const params = new URLSearchParams({ id_budget:$budget.value, id_donnee_budgetaire_entree:$donB.value });
                fetch("{{ route('ajax_regl_donnees_ligne', ':id') }}".replace(':id', $elt.value) + "?"+params.toString())
                    .then(r=>r.json())
            .then(items=>fill($donL, items, 'label','id'));
            }
            $elt.addEventListener('change', refreshDonneesLigne);
            $donB.addEventListener('change', refreshDonneesLigne);
        })();
    </script>
@endsection
--}}
@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="text-primary">✏️ Modifier le règlement N° {{ $reglement->numero_reglement }}</h3>

        <div class="alert alert-info">
            <strong>Facture :</strong> {{ $facture->numero_facture ?? '—' }} —
            <strong>Étudiant :</strong> {{ $reglement->etudiants->nom ?? '—' }}<br>
            <strong>Type :</strong> {{ $facture->type_facture === 1 ? 'Scolarité' : 'Frais' }}
        </div>

        <form method="POST" action="{{ route('update_reglement') }}">
            @csrf
            <input type="hidden" name="id" value="{{ $reglement->id }}">
            <input type="hidden" name="id_facture_etudiant" value="{{ $reglement->id_facture_etudiant }}">
            <input type="hidden" name="id_etudiant" value="{{ $reglement->id_etudiant }}">
            {{--<input type="hidden" name="id_annee_academique" value="{{ $reglement->id_annee_academique }}">--}}

            {{-- INFOS PÉDAGOGIQUES (verrouillées) --}}
            <div class="card p-3 mb-3">
                <h5>Informations pédagogiques</h5>
                <div class="row">
                    <div class="col-md-3">
                        <label>Cycle</label>
                        <input type="text" class="form-control" value="{{ $facture->cycles->nom_cycle ?? '—' }}" disabled>
                        <input type="hidden" name="id_cycle" value="{{ $facture->id_cycle }}">
                    </div>
                    <div class="col-md-3">
                        <label>Filière</label>
                        <input type="text" class="form-control" value="{{ $facture->filieres->nom_filiere ?? '—' }}" disabled>
                        <input type="hidden" name="id_filiere" value="{{ $facture->id_filiere }}">
                    </div>
                    <div class="col-md-3">
                        <label>Niveau</label>
                        <input type="text" class="form-control" value="{{ $facture->niveaux->nom_niveau ?? '—' }}" disabled>
                        <input type="hidden" name="id_niveau" value="{{ $facture->id_niveau }}">
                    </div>
                    <div class="col-md-3">
                        <label>Spécialité</label>
                        <input type="text" class="form-control" value="{{ $facture->specialites->nom_specialite ?? '—' }}" disabled>
                        <input type="hidden" name="id_specialite" value="{{ $facture->id_specialite }}">
                    </div>
                    <div class="col-md-4 mt-2">
                        <label>Scolarité</label>
                        <input type="text" class="form-control"
                               value="{{ $facture->scolarites->montant_total ?? $facture->scolarites->montant_total ?? '—' }}"
                               disabled>
                        <input type="hidden" name="id_scolarite" value="{{ $facture->id_scolarite }}">
                    </div>
                    <div class="col-md-4 mt-2">
                        <label>Entite</label>
                        <input type="text" class="form-control"
                               value="{{ $facture->entite->non_entite ?? $facture->entite->nom_entite ?? '—' }}"
                               disabled>
                        <input type="hidden" name="id_entite" value="{{ $facture->id_entite }}">
                    </div>
                    <div class="col-md-4 mt-2">
                        <label>Année académique</label>
                        <input type="text" class="form-control"
                               value="{{ $facture->Annee_academique->nom ?? $facture->Annee_academique->nom ?? '—' }}"
                               disabled>
                        <input type="hidden" name="id_annee_academique" value="{{ $facture->id_annee_academique }}">
                    </div>



                @if($facture->type_facture == 1)
                        {{-- on laisse changer seulement la tranche --}}
                        <div class="col-md-4 mt-2">
                            <label>Tranche de scolarité</label>
                            <select name="id_tranche_scolarite" class="form-control">
                                <option value="">— (aucune / avance) —</option>
                                <option value="0" {{ $reglement->id_tranche_scolarite == 0 ? 'selected' : '' }}>Toutes les tranches</option>
                                @php
                                    $trs = \App\Models\tranche_scolarite::where('id_scolarite', $facture->id_scolarite)->orderBy('date_limite')->get();
                                @endphp
                                @foreach($trs as $t)
                                    <option value="{{ $t->id }}" {{ $reglement->id_tranche_scolarite == $t->id ? 'selected' : '' }}>
                                        {{ $t->nom_tranche }} — {{ number_format($t->montant_tranche,0,',',' ') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        {{-- frais --}}
                        <input type="hidden" name="id_frais" value="{{ $facture->id_frais ?? 0 }}">
                        <div class="col-md-4 mt-2">
                            <label>Frais</label>
                            <input type="text" class="form-control" value="{{ $facture->frais->nom_frais ?? '—' }}" disabled>
                        </div>
                    @endif
                </div>
            </div>

            {{-- BUDGET (verrouillé) --}}
            <div class="card p-3 mb-3">
                <h5>Affectation budgétaire</h5>
                <div class="row">
                    <div class="col-md-3">
                        <label>Budget</label>
                        <input type="text" class="form-control" value="{{ $facture->budget->libelle_ligne_budget ?? '—' }}" disabled>
                        <input type="hidden" name="id_budget" value="{{ $facture->id_budget }}">
                    </div>
                    <div class="col-md-3">
                        <label>Ligne budgétaire</label>
                        <input type="text" class="form-control"
                               value="{{ $facture->ligne_budgetaire_entree->libelle_ligne_budgetaire_entree ?? '—' }}" disabled>
                        <input type="hidden" name="id_ligne_budgetaire_entree" value="{{ $facture->id_ligne_budgetaire_entree }}">
                    </div>
                    <div class="col-md-3">
                        <label>Élément</label>
                        <input type="text" class="form-control"
                               value="{{ $facture->element_ligne_budgetaire_entree->libelle_elements_ligne_budgetaire_entree ?? '—' }}" disabled>
                        <input type="hidden" name="id_element_ligne_budgetaire_entree" value="{{ $facture->id_element_ligne_budgetaire_entree }}">
                    </div>
                    <div class="col-md-3">
                        <label>Donnée budgétaire</label>
                        <input type="text" class="form-control"
                               value="{{ $facture->donnee_budgetaire_entree->donnee_ligne_budgetaire_entree ?? '—' }}" disabled>
                        <input type="hidden" name="id_donnee_budgetaire_entree" value="{{ $facture->id_donnee_budgetaire_entree }}">
                    </div>
                    <div class="col-md-3 mt-2">
                        <label>Donnée de ligne</label>
                        <input type="text" class="form-control"
                               value="{{ $facture->donnee_ligne_budgetaire_entree->donnee_ligne_budgetaire_entree ?? '—' }}" disabled>
                        <input type="hidden" name="id_donnee_ligne_budgetaire_entree" value="{{ $facture->id_donnee_ligne_budgetaire_entree }}">
                    </div>
                </div>
            </div>

            {{-- MODE DE VERSEMENT --}}
            <div class="card p-3 mb-3">
                <h5>Mode de versement</h5>
                @php
                    // on doit afficher le mode dans le même style que dans l’index
                    $tv = $reglement->type_versement;
                    // si c’est un INT, on convertit en string pour les radios
                    if (is_numeric($tv)) {
                        $mapBack = [0 => 'espece', 1 => 'bancaire', 2 => 'om', 3 => 'mtn'];
                        $tv = $mapBack[(int)$tv] ?? 'espece';
                    }
                @endphp
                <div class="row">
                    <div class="col-md-4">
                        <label class="mr-2">
                            <input type="radio" name="type_versement" value="espece" {{ $tv==='espece'?'checked':'' }}>
                            Espèce
                        </label>
                        <label class="mr-2">
                            <input type="radio" name="type_versement" value="bancaire" {{ $tv==='bancaire'?'checked':'' }}>
                            Bancaire
                        </label>
                        <label class="mr-2">
                            <input type="radio" name="type_versement" value="om" {{ $tv==='om'?'checked':'' }}>
                            Orange Money
                        </label>
                        <label>
                            <input type="radio" name="type_versement" value="mtn" {{ $tv==='mtn'?'checked':'' }}>
                            MTN Money
                        </label>
                    </div>
                    <div class="col-md-4" id="bloc-caisse" style="{{ $tv==='espece' ? '' : 'display:none' }}">
                        <label>Caisse (si espèce)</label>
                        <select name="id_caisse" class="form-control">
                            <option value="">—</option>
                            @foreach($caisses as $c)
                                <option value="{{ $c->id }}" {{ $reglement->id_caisse == $c->id ? 'selected' : '' }}>
                                    {{ $c->nom_caisse }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4" id="bloc-banque" style="{{ $tv==='bancaire' ? '' : 'display:none' }}">
                        <label>Banque (si bancaire)</label>
                        <select name="id_banque" class="form-control">
                            <option value="">—</option>
                            @foreach($banques as $b)
                                <option value="{{ $b->id }}" {{ $reglement->id_banque == $b->id ? 'selected' : '' }}>
                                    {{ $b->nom_banque }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- MONTANT / MOTIF / DATE --}}
            <div class="card p-3 mb-3">
                <div class="row">
                    <div class="col-md-3">
                        <label>Montant du règlement <span class="text-danger">*</span></label>
                        <input type="number" name="montant_reglement" min="0" step="0.01"
                               value="{{ old('montant_reglement', $reglement->montant_reglement) }}"
                               class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label>Date du règlement <span class="text-danger">*</span></label>
                        <input type="date" name="date_reglement"
                               value="{{ old('date_reglement', \Carbon\Carbon::parse($reglement->date_reglement)->toDateString()) }}"
                               class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label>Motif</label>
                        <input type="text" name="motif_reglement"
                               value="{{ old('motif_reglement', $reglement->motif_reglement) }}"
                               class="form-control" placeholder="Ex: acompte, tranche 1 ...">
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <button class="btn btn-success">💾 Enregistrer</button>
                <a href="{{ route('reglement_by_facture', $reglement->id_facture_etudiant) }}" class="btn btn-secondary">↩ Retour</a>
                <a class="btn btn-default" target="_blank" href="{{ route('reglement_pdf', $reglement->id) }}">🧾 PDF</a>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            function toggleVB() {
                const val = document.querySelector('input[name="type_versement"]:checked').value;
                document.getElementById('bloc-caisse').style.display = (val === 'espece') ? '' : 'none';
                document.getElementById('bloc-banque').style.display = (val === 'bancaire') ? '' : 'none';
            }
            document.querySelectorAll('input[name="type_versement"]').forEach(r => {
                r.addEventListener('change', toggleVB);
        });
            toggleVB();
        })();
    </script>
@endsection
