{{--
@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="text-primary">💵 Nouveau règlement — Facture N° {{ $facture->numero_facture }}</h3>

        <div class="alert alert-info">
            <strong>Étudiant :</strong> {{ $facture->etudiants->nom }}<br>
            <strong>Type :</strong> {{ $facture->type_facture === 1 ? 'Scolarité' : 'Frais' }}<br>
            <strong>Total facture :</strong> {{ number_format($totalFacture,0,',',' ') }} —
            <strong>Déjà payé :</strong> {{ number_format($totalPaye,0,',',' ') }} —
            <strong>Reste :</strong> {{ number_format($reste,0,',',' ') }}
        </div>

        <form method="POST" action="{{ route('store_reglement') }}">
            @csrf
            <input type="hidden" name="id_facture_etudiant" value="{{ $facture->id }}">
            <input type="hidden" name="id_etudiant" value="{{ $facture->id_etudiant }}">
            <input type="hidden" name="id_annee_academique" value="{{ $facture->id_annee_academique }}">
            <input type="hidden" name="type_reglement" value="{{ $facture->type_facture }}"> --}}
{{-- 1 = scolarité, 0 = frais --}}{{--


            <div class="row">
                @if($facture->type_facture === 1)
                    --}}
{{-- Règlement de scolarité : choix de la tranche (ou "Toutes les tranches") --}}{{--

                    <div class="col-md-6">
                        <label>Tranche à régler</label>
                        <select name="id_tranche_scolarite" id="tranche" class="form-control">
                            <option value="">— Toutes les tranches —</option>
                            @php
                                $tranches = \App\Models\tranche_scolarite::where('id_scolarite',$facture->id_scolarite)
                                    ->orderBy('date_limite')->get();
                            @endphp
                            @foreach($tranches as $t)
                                <option value="{{ $t->id }}">
                                    {{ $t->nom_tranche }} — {{ number_format($t->montant_tranche,0,',',' ') }} —
                                    {{ \Carbon\Carbon::parse($t->date_limite)->format('d/m/Y') }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Laisser vide pour un règlement global (toutes tranches).</small>
                    </div>
                @else
                    --}}
{{-- Règlement de frais : rappel du frais et montant --}}{{--

                    <div class="col-md-6">
                        <label>Frais</label>
                        <input type="text" class="form-control" value="{{ $facture->frais->nom_frais ?? '—' }}" disabled>
                        <input type="hidden" name="id_frais" value="{{ $facture->id_frais }}">
                    </div>
                @endif

                <div class="col-md-3">
                    <label>Montant du règlement <span class="text-danger">*</span></label>
                    <input type="number" min="0" step="0.01" class="form-control" name="montant_reglement" required>
                </div>

                <div class="col-md-3">
                    <label>Motif (optionnel)</label>
                    <input type="text" class="form-control" name="motif_reglement" placeholder="Ex: avance tranche 1">
                </div>

                --}}
{{-- Mode d’encaissement --}}{{--

                <div class="col-md-12 mt-3">
                    <label>Mode de versement <span class="text-danger">*</span></label>
                    <div>
                        <label class="mr-3"><input type="radio" name="type_versement" value="espece" checked> Espèce</label>
                        <label class="mr-3"><input type="radio" name="type_versement" value="bancaire"> Bancaire</label>
                        <label class="mr-3"><input type="radio" name="type_versement" value="om"> Orange Money</label>
                        <label class="mr-3"><input type="radio" name="type_versement" value="mtn"> MTN Money</label>
                    </div>
                </div>

                <div class="col-md-6 mt-2" id="bloc-caisse">
                    <label>Caisse (si espèce)</label>
                    <select name="id_caisse" class="form-control">
                        <option value="">—</option>
                        @foreach($caisses as $c)
                            <option value="{{ $c->id }}">{{ $c->nom_caisse }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 mt-2" id="bloc-banque" style="display:none">
                    <label>Banque (si bancaire)</label>
                    <select name="id_banque" class="form-control">
                        <option value="">—</option>
                        @foreach($banques as $b)
                            <option value="{{ $b->id }}">{{ $b->nom_banque }}</option>
                        @endforeach
                    </select>
                </div>

                --}}
{{-- Budget (obligatoire) --}}{{--

                <div class="col-md-3 mt-3">
                    <label>Budget <span class="text-danger">*</span></label>
                    <select id="budget" name="id_budget" class="form-control" required>
                        <option value="">—</option>
                        @foreach($budgets as $bud)
                            <option value="{{ $bud->id }}">{{ $bud->libelle_ligne_budget ?? ('Budget #'.$bud->id) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mt-3">
                    <label>Ligne <span class="text-danger">*</span></label>
                    <select id="ligne" name="id_ligne_budgetaire_entree" class="form-control" required></select>
                </div>
                <div class="col-md-3 mt-3">
                    <label>Élément <span class="text-danger">*</span></label>
                    <select id="element" name="id_element_ligne_budgetaire_entree" class="form-control" required></select>
                </div>
                <div class="col-md-3 mt-3">
                    <label>Donnée <span class="text-danger">*</span></label>
                    <select id="donnee" name="id_donnee_ligne_budgetaire_entree" class="form-control" required></select>
                </div>
            </div>

            <div class="mt-4">
                <button class="btn btn-success">✅ Enregistrer le règlement</button>
                <a href="{{ url()->previous() }}" class="btn btn-default">↩ Retour</a>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        (function(){
            // Toggle caisse / banque selon le mode
            function refreshVersement() {
                const val = document.querySelector('input[name="type_versement"]:checked')?.value;
                document.getElementById('bloc-caisse').style.display = (val === 'espece') ? '' : 'none';
                document.getElementById('bloc-banque').style.display = (val === 'bancaire') ? '' : 'none';
            }
            document.querySelectorAll('input[name="type_versement"]').forEach(r=>{
                r.addEventListener('change', refreshVersement);
        });
            refreshVersement();

            // Cascade Budget -> Ligne -> Élément -> Donnée
            function fill($select, items, labelKey, valueKey){
                $select.innerHTML = '<option value="">—</option>';
                (items||[]).forEach(it=>{
                    const opt = document.createElement('option');
                opt.value = it[valueKey]; opt.textContent = it[labelKey];
                $select.appendChild(opt);
            });
            }

            const $budget = document.getElementById('budget');
            const $ligne  = document.getElementById('ligne');
            const $elt    = document.getElementById('element');
            const $donnee = document.getElementById('donnee');

            $budget.addEventListener('change', function(){
                fill($ligne, [], 'libelle_ligne_budgetaire_entree', 'id');
                fill($elt,   [], 'libelle_elements_ligne_budgetaire_entree', 'id');
                fill($donnee,[], 'donnee_ligne_budgetaire_entree', 'id');
                if(!this.value) return;
                fetch("{{ url('ajax/budget') }}/"+this.value+"/lignes")
                    .then(r=>r.json()).then(items=>fill($ligne, items, 'libelle_ligne_budgetaire_entree', 'id'));
            });

            $ligne.addEventListener('change', function(){
                fill($elt,   [], 'libelle_elements_ligne_budgetaire_entree', 'id');
                fill($donnee,[], 'donnee_ligne_budgetaire_entree', 'id');
                if(!this.value) return;
                fetch("{{ url('ajax/ligne') }}/"+this.value+"/elements")
                    .then(r=>r.json()).then(items=>fill($elt, items, 'libelle_elements_ligne_budgetaire_entree', 'id'));
            });

            $elt.addEventListener('change', function(){
                fill($donnee,[], 'donnee_ligne_budgetaire_entree', 'id');
                if(!this.value) return;
                fetch("{{ url('ajax/element') }}/"+this.value+"/donnees")
                    .then(r=>r.json()).then(items=>fill($donnee, items, 'donnee_ligne_budgetaire_entree', 'id'));
            });
        })();
    </script>
@endsection
--}}
{{--
@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="text-primary">➕ Nouveau règlement — Facture N° {{ $facture->numero_facture }}</h3>
        <p>
            <strong>Étudiant :</strong> {{ $facture->etudiants->nom }} —
            <strong>Reste à payer :</strong> {{ number_format($reste,0,',',' ') }}
        </p>

        <form method="POST" action="{{ route('store_reglement') }}">
            @csrf
            <input type="hidden" name="id_facture_etudiant" value="{{ $facture->id }}">
            <input type="hidden" name="id_etudiant" value="{{ $facture->id_etudiant }}">
            <input type="hidden" name="id_annee_academique" value="{{ $facture->id_annee_academique }}">

            <div class="card p-3 mb-3">
                <div class="row">
                    <div class="col-md-3">
                        <label>Type de versement</label>
                        <div>
                            <label class="mr-2"><input type="radio" name="type_versement" value="espece" checked> Espèce</label>
                            <label class="mr-2"><input type="radio" name="type_versement" value="bancaire"> Bancaire</label>
                            <label class="mr-2"><input type="radio" name="type_versement" value="om"> Orange Money</label>
                            <label><input type="radio" name="type_versement" value="mtn"> MTN Money</label>
                        </div>
                    </div>

                    <div class="col-md-3" id="bloc-caisse">
                        <label>Caisse</label>
                        <select name="id_caisse" class="form-control">
                            <option value="">—</option>
                            @foreach($caisses as $c)
                                <option value="{{ $c->id }}">{{ $c->nom_caisse }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3" id="bloc-banque" style="display:none">
                        <label>Banque</label>
                        <select name="id_banque" class="form-control">
                            <option value="">—</option>
                            @foreach($banques as $b)
                                <option value="{{ $b->id }}">{{ $b->nom_banque }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>Montant du règlement</label>
                        <input type="number" step="0.01" min="0" name="montant_reglement" class="form-control" required>
                    </div>
                </div>
            </div>

            @if($facture->type_facture === 1)
                <div class="card p-3 mb-3">
                    <h5>Tranche de scolarité</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <label>Choisir la tranche</label>
                            <select id="tranche" name="id_tranche_scolarite" class="form-control">
                                <option value="">— (aucune / avance libre)</option>
                                <option value="0">Toutes les tranches</option>
                                @php
                                    $trs = \App\Models\tranche_scolarite::where('id_scolarite', $facture->id_scolarite)
                                           ->orderBy('date_limite')->get();
                                @endphp
                                @foreach($trs as $t)
                                    <option value="{{ $t->id }}">{{ $t->nom_tranche }} — {{ number_format($t->montant_tranche,0,',',' ') }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card p-3 mb-3">
                <h5>Affectation budgétaire</h5>
                <div class="row">
                    <div class="col-md-3">
                        <label>Budget <span class="text-danger">*</span></label>
                        <select id="budget" name="id_budget" class="form-control" required>
                            <option value="">—</option>
                            @foreach($budgets as $bud)
                                <option value="{{ $bud->id }}">{{ $bud->libelle_ligne_budget ?? ('Budget #'.$bud->id) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Ligne <span class="text-danger">*</span></label>
                        <select id="ligne" name="id_ligne_budgetaire_entree" class="form-control" required></select>
                    </div>
                    <div class="col-md-3">
                        <label>Élément <span class="text-danger">*</span></label>
                        <select id="element" name="id_element_ligne_budgetaire_entree" class="form-control" required></select>
                    </div>

                    --}}
{{-- Donnée budgétaire d’entrée --}}{{--

                    <div class="col-md-3">
                        <label>Donnée budgétaire <span class="text-danger">*</span></label>
                        <select id="donneeBudget" name="id_donnee_budgetaire_entree" class="form-control" required></select>
                    </div>

                    --}}
{{-- Donnée de ligne budgétaire d’entrée --}}{{--

                    <div class="col-md-3 mt-3">
                        <label>Donnée de ligne <span class="text-danger">*</span></label>
                        <select id="donneeLigne" name="id_donnee_ligne_budgetaire_entree" class="form-control" required></select>
                    </div>
                </div>
            </div>

            <div class="card p-3 mb-3">
                <label>Motif (optionnel)</label>
                <input type="text" name="motif_reglement" class="form-control" placeholder="Ex: acompte, tranche 1, frais...">
            </div>

            <div class="mb-3">
                <button class="btn btn-success">💾 Enregistrer</button>
                <a class="btn btn-default" href="{{ route('reglement_by_facture', $facture->id) }}">Annuler</a>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        (function(){
            // Afficher / cacher caisse / banque
            const radios = document.querySelectorAll('input[name="type_versement"]');
            const blocCaisse = document.getElementById('bloc-caisse');
            const blocBanque = document.getElementById('bloc-banque');
            radios.forEach(r => r.addEventListener('change', function(){
                blocCaisse.style.display = (this.value === 'espece') ? '' : 'none';
                blocBanque.style.display = (this.value === 'bancaire') ? '' : 'none';
            }));

            // Cascade budget -> ligne -> élément -> donnée budget -> donnée ligne
            const $budget = document.getElementById('budget');
            const $ligne  = document.getElementById('ligne');
            const $elt    = document.getElementById('element');
            const $donB   = document.getElementById('donneeBudget');
            const $donL   = document.getElementById('donneeLigne');

            function fill(select, items, labelKey, valueKey){
                select.innerHTML = '<option value="">—</option>';
                (items||[]).forEach(it => {
                    const o = document.createElement('option');
                o.value = it[valueKey]; o.textContent = it[labelKey];
                select.appendChild(o);
            });
            }

            $budget.addEventListener('change', function(){
                fill($ligne, [], 'libelle_ligne_budgetaire_entree', 'id');
                fill($elt,   [], 'libelle_elements_ligne_budgetaire_entree', 'id');
                fill($donB,  [], 'label', 'id');
                fill($donL,  [], 'label', 'id');
                if (!this.value) return;
                fetch("{{ url('reglements/ajax/budget') }}/"+this.value+"/lignes")
                    .then(r=>r.json())
                .then(items=>fill($ligne, items, 'libelle_ligne_budgetaire_entree', 'id'));
            });

            $ligne.addEventListener('change', function(){
                fill($elt,  [], 'libelle_elements_ligne_budgetaire_entree', 'id');
                fill($donB, [], 'label', 'id');
                fill($donL, [], 'label', 'id');
                if (!this.value || !$budget.value) return;

                fetch("{{ url('reglements/ajax/ligne') }}/"+this.value+"/elements")
                    .then(r=>r.json())
                .then(items=>fill($elt, items, 'libelle_elements_ligne_budgetaire_entree', 'id'));

                fetch("{{ url('reglements/ajax/ligne') }}/"+this.value+"/donnees-budget?"+new URLSearchParams({id_budget:$budget.value}))
                    .then(r=>r.json())
                .then(items=>fill($donB, items, 'label', 'id'));
            });

            function refreshDonneesLigne(){
                fill($donL, [], 'label', 'id');
                if (!$elt.value || !$budget.value || !$donB.value) return;
                const params = new URLSearchParams({
                    id_budget: $budget.value,
                    id_donnee_budgetaire_entree: $donB.value
                });
                fetch("{{ url('reglements/ajax/element') }}/"+$elt.value+"/donnees-ligne?"+params.toString())
                    .then(r=>r.json())
            .then(items=>fill($donL, items, 'label', 'id'));
            }
            $elt.addEventListener('change', refreshDonneesLigne);
            $donB.addEventListener('change', refreshDonneesLigne);
        })();
    </script>
@endsection
--}}
{{--@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="text-primary">💵 Nouveau règlement — Facture N° {{ $facture->numero_facture }}</h3>

        <div class="alert alert-info">
            <strong>Étudiant :</strong> {{ $facture->etudiants->nom }}<br>
            <strong>Type :</strong> {{ $facture->type_facture === 1 ? 'Scolarité' : 'Frais' }}<br>
            <strong>Total :</strong> {{ number_format($totalFacture,0,',',' ') }} —
            <strong>Déjà payé :</strong> {{ number_format($totalPaye,0,',',' ') }} —
            <strong>Reste :</strong> {{ number_format($reste,0,',',' ') }}
        </div>

        <form method="POST" action="{{ route('store_reglement') }}">
            @csrf
            --}}{{-- Liens de base --}}{{--
            <input type="hidden" name="id_facture_etudiant" value="{{ $facture->id }}">
            <input type="hidden" name="id_etudiant" value="{{ $facture->id_etudiant }}">
            <input type="hidden" name="id_annee_academique" value="{{ $facture->id_annee_academique }}">

            --}}{{-- PÉDAGOGIE --}}{{--
            <div class="card p-3 mb-3">
                <h5>Informations pédagogiques</h5>
                <div class="row">
                    <div class="col-md-3">
                        <label>Cycle <span class="text-danger">*</span></label>
                        <select id="cycle" name="id_cycle" class="form-control" required>
                            <option value="">—</option>
                            @foreach($cycles as $c)
                                <option value="{{ $c->id }}" @selected($c->id==$facture->id_cycle)>{{ $c->nom_cycle }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Filière <span class="text-danger">*</span></label>
                        <select id="filiere" name="id_filiere" class="form-control" required>
                            <option value="">—</option>
                            @foreach($filieres as $f)
                                <option value="{{ $f->id }}" @selected($f->id==$facture->id_filiere)>{{ $f->nom_filiere }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Niveau</label>
                        <select id="niveau" name="id_niveau" class="form-control"></select>
                    </div>
                    <div class="col-md-3">
                        <label>Spécialité <span class="text-danger">*</span></label>
                        <select id="specialite" name="id_specialite" class="form-control" required></select>
                    </div>

                    <div class="col-md-6 mt-2">
                        <label>Scolarité <span class="text-danger">*</span></label>
                        <select id="scolarite" name="id_scolarite" class="form-control" required></select>
                    </div>

                    @if($facture->type_facture === 1)
                        <div class="col-md-6 mt-2">
                            <label>Tranche à régler</label>
                            <select name="id_tranche_scolarite" id="tranche" class="form-control">
                                <option value="">— (aucune / avance libre)</option>
                                <option value="0">Toutes les tranches</option>
                                @php
                                    $tranches = \App\Models\tranche_scolarite::where('id_scolarite',$facture->id_scolarite)
                                        ->orderBy('date_limite')->get();
                                @endphp
                                @foreach($tranches as $t)
                                    <option value="{{ $t->id }}">{{ $t->nom_tranche }} — {{ number_format($t->montant_tranche,0,',',' ') }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        --}}{{-- FRAIS --}}{{--
                        <input type="hidden" name="id_frais" value="{{ $facture->id_frais ?? 0 }}">
                        <div class="col-md-6 mt-2">
                            <label>Frais</label>
                            <input type="text" class="form-control" value="{{ $facture->frais->nom_frais ?? '—' }}" disabled>
                        </div>
                    @endif
                </div>
            </div>

            --}}{{-- VERSEMENT --}}{{--
            <div class="card p-3 mb-3">
                <h5>Mode de versement</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div>
                            <label class="mr-2"><input type="radio" name="type_versement" value="espece" checked> Espèce</label>
                            <label class="mr-2"><input type="radio" name="type_versement" value="bancaire"> Bancaire</label>
                            <label class="mr-2"><input type="radio" name="type_versement" value="om"> Orange Money</label>
                            <label><input type="radio" name="type_versement" value="mtn"> MTN Money</label>
                        </div>
                    </div>
                    <div class="col-md-4" id="bloc-caisse">
                        <label>Caisse (espèce)</label>
                        <select name="id_caisse" class="form-control">
                            <option value="">—</option>
                            @foreach($caisses as $c)
                                <option value="{{ $c->id }}">{{ $c->nom_caisse }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4" id="bloc-banque" style="display:none">
                        <label>Banque (bancaire)</label>
                        <select name="id_banque" class="form-control">
                            <option value="">—</option>
                            @foreach($banques as $b)
                                <option value="{{ $b->id }}">{{ $b->nom_banque }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            --}}{{-- BUDGET --}}{{--
            <div class="card p-3 mb-3">
                <h5>Affectation budgétaire</h5>
                <div class="row">
                    <div class="col-md-3">
                        <label>Budget <span class="text-danger">*</span></label>
                        <select id="budget" name="id_budget" class="form-control" required>
                            <option value="">—</option>
                            @foreach($budgets as $bud)
                                <option value="{{ $bud->id }}">{{ $bud->libelle_ligne_budget ?? ('Budget #'.$bud->id) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Ligne <span class="text-danger">*</span></label>
                        <select id="ligne" name="id_ligne_budgetaire_entree" class="form-control" required></select>
                    </div>
                    <div class="col-md-3">
                        <label>Élément <span class="text-danger">*</span></label>
                        <select id="element" name="id_element_ligne_budgetaire_entree" class="form-control" required></select>
                    </div>
                    <div class="col-md-3">
                        <label>Donnée budgétaire <span class="text-danger">*</span></label>
                        <select id="donneeBudget" name="id_donnee_budgetaire_entree" class="form-control" required></select>
                    </div>
                    <div class="col-md-3 mt-2">
                        <label>Donnée de ligne <span class="text-danger">*</span></label>
                        <select id="donneeLigne" name="id_donnee_ligne_budgetaire_entree" class="form-control" required></select>
                    </div>

                </div>
            </div>

            --}}{{-- MONTANT + MOTIF --}}{{--
            <div class="card p-3 mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <label>Montant du règlement <span class="text-danger">*</span></label>
                        <input type="number" min="0" step="0.01" class="form-control" name="montant_reglement" required>
                    </div>
                    <div class="col-md-4">
                        <label>Motif (optionnel)</label>
                        <input type="text" class="form-control" name="motif_reglement" placeholder="Ex: avance tranche 1">
                    </div>
                    <div class="col-md-4">
                        <label>Date règlement <span class="text-danger">*</span></label>
                        <input id="date_reglement" type="date" name="date_reglement" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label>Montant en lettre <span class="text-danger">*</span></label>
                        <input id="lettre" type="text" name="lettre" class="form-control" required>
                    </div>
                </div>
            </div>

            <div>
                <button class="btn btn-success">✅ Enregistrer le règlement</button>
                <a href="{{ route('reglement_by_facture', $facture->id) }}" class="btn btn-default">↩ Retour</a>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        (function(){
            // Affiche caisse/banque
            function toggleVB(){
                const val = document.querySelector('input[name="type_versement"]:checked').value;
                document.getElementById('bloc-caisse').style.display = (val==='espece') ? '' : 'none';
                document.getElementById('bloc-banque').style.display = (val==='bancaire') ? '' : 'none';
            }
            document.querySelectorAll('input[name="type_versement"]').forEach(r=>r.addEventListener('change', toggleVB));
            toggleVB();

            // Helpers
            function fill(sel, items, labelKey, valueKey, withEmpty=true){
                sel.innerHTML = withEmpty ? '<option value="">—</option>' : '';
                (items||[]).forEach(it=>{
                    const o=document.createElement('option');
                o.value = it[valueKey]; o.textContent = it[labelKey];
                sel.appendChild(o);
            });
            }

            // Pédagogie: cycle+filiere => niveaux,specialites,scolarites
            const $cycle = document.getElementById('cycle');
            const $filiere = document.getElementById('filiere');
            const $niveau = document.getElementById('niveau');
            const $spec   = document.getElementById('specialite');
            const $sco    = document.getElementById('scolarite');

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
                // pré-sélect valeurs de la facture si disponibles
                if ({{ (int)$facture->id_niveau }} > 0)   $niveau.value = "{{ $facture->id_niveau }}";
                if ({{ (int)$facture->id_specialite }} > 0) $spec.value   = "{{ $facture->id_specialite }}";
                if ({{ (int)$facture->id_scolarite }} > 0)  $sco.value    = "{{ $facture->id_scolarite }}";
            });
            }
            $cycle.addEventListener('change', refreshPedago);
            $filiere.addEventListener('change', refreshPedago);
            // init avec valeurs facture
            refreshPedago();

            // BUDGET cascade
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
                if(!this.value) return;
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
                fetch("{{ route('ajax_regl_donnees_by_element', ':id') }}".replace(':id', $elt.value) + "?"+params.toString())
                    .then(r=>r.json())
            .then(items=>fill($donL, items, 'label','id'));
            }
            $elt.addEventListener('change', refreshDonneesLigne);
            $donB.addEventListener('change', refreshDonneesLigne);
        })();
    </script>
@endsection--}}
@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="text-primary">💵 Nouveau règlement — Facture N° {{ $facture->numero_facture }}</h3>

        <div class="alert alert-info">
            <strong>Étudiant :</strong> {{ $facture->etudiants->nom }}<br>
            <strong>Type :</strong> {{ $facture->type_facture === 1 ? 'Scolarité' : 'Frais' }}<br>
            <strong>Total :</strong> {{ number_format($totalFacture,0,',',' ') }} —
            <strong>Déjà payé :</strong> {{ number_format($totalPaye,0,',',' ') }} —
            <strong>Reste :</strong> {{ number_format($reste,0,',',' ') }}
        </div>

        <form method="POST" action="{{ route('store_reglement') }}">
            @csrf

            {{-- Liens fixes (toujours envoyés) --}}
            <input type="hidden" name="id_facture_etudiant" value="{{ $facture->id }}">
            <input type="hidden" name="id_etudiant" value="{{ $facture->id_etudiant }}">
            {{--<input type="hidden" name="id_annee_academique" value="{{ $facture->id_annee_academique }}">--}}

            {{-- INFORMATIONS PÉDAGOGIQUES (verrouillées) --}}
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

                    @if($facture->type_facture === 1)
                        {{-- SCOLARITÉ : on choisit juste la tranche à régler --}}
                        <div class="col-md-4 mt-2">
                            <label>Tranche à régler</label>
                            <select name="id_tranche_scolarite" class="form-control">
                                <option value="">— (aucune / avance libre) —</option>
                                <option value="0">Toutes les tranches</option>
                                @php
                                    $tranches = \App\Models\tranche_scolarite::where('id_scolarite',$facture->id_scolarite)
                                        ->orderBy('date_limite')
                                        ->get();
                                @endphp
                                @foreach($tranches as $t)
                                    <option value="{{ $t->id }}">
                                        {{ $t->nom_tranche }}
                                        — {{ number_format($t->montant_tranche,0,',',' ') }}
                                        @if($t->date_limite) ({{ \Carbon\Carbon::parse($t->date_limite)->format('d/m/Y') }}) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        {{-- FRAIS : on affiche juste le frais attaché à la facture --}}
                        <input type="hidden" name="id_frais" value="{{ $facture->id_frais ?? 0 }}">
                        <div class="col-md-4 mt-2">
                            <label>Frais</label>
                            <input type="text" class="form-control" value="{{ $facture->frais->nom_frais ?? '—' }}" disabled>
                        </div>
                    @endif
                </div>
            </div>

            {{-- AFFECTATION BUDGÉTAIRE (verrouillée car déjà connue) --}}
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
                               value="{{ $facture->ligne_budgetaire_entree->libelle_ligne_budgetaire_entree ?? '—' }}"
                               disabled>
                        <input type="hidden" name="id_ligne_budgetaire_entree" value="{{ $facture->id_ligne_budgetaire_entree }}">
                    </div>
                    <div class="col-md-3">
                        <label>Élément</label>
                        <input type="text" class="form-control"
                               value="{{ $facture->element_ligne_budgetaire_entree->libelle_elements_ligne_budgetaire_entree ?? '—' }}"
                               disabled>
                        <input type="hidden" name="id_element_ligne_budgetaire_entree" value="{{ $facture->id_element_ligne_budgetaire_entree }}">
                    </div>
                    <div class="col-md-3">
                        <label>Donnée budgétaire</label>
                        <input type="text" class="form-control"
                               value="{{ $facture->donnee_budgetaire_entree->donnee_ligne_budgetaire_entree ?? '—' }}"
                               disabled>
                        <input type="hidden" name="id_donnee_budgetaire_entree" value="{{ $facture->id_donnee_budgetaire_entree }}">
                    </div>
                    <div class="col-md-3 mt-2">
                        <label>Donnée de ligne</label>
                        <input type="text" class="form-control"
                               value="{{ $facture->donnee_ligne_budgetaire_entree->donnee_ligne_budgetaire_entree ?? '—' }}"
                               disabled>
                        <input type="hidden" name="id_donnee_ligne_budgetaire_entree" value="{{ $facture->id_donnee_ligne_budgetaire_entree }}">
                    </div>
                </div>
            </div>

            {{-- MODE DE VERSEMENT (à saisir) --}}
            <div class="card p-3 mb-3">
                <h5>Mode de versement</h5>
                <div class="row">
                    <div class="col-md-6">
                        <label class="d-block">Choisir le mode</label>
                        <label class="mr-2">
                            <input type="radio" name="type_versement" value="espece" checked>
                            Espèce
                        </label>
                        <label class="mr-2">
                            <input type="radio" name="type_versement" value="bancaire">
                            Bancaire
                        </label>
                        <label class="mr-2">
                            <input type="radio" name="type_versement" value="om">
                            Orange Money
                        </label>
                        <label>
                            <input type="radio" name="type_versement" value="mtn">
                            MTN Money
                        </label>
                    </div>
                    <div class="col-md-3" id="bloc-caisse">
                        <label>Caisse (si espèce)</label>
                        <select name="id_caisse" class="form-control">
                            <option value="">—</option>
                            @foreach($caisses as $c)
                                <option value="{{ $c->id }}">{{ $c->nom_caisse }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3" id="bloc-banque" style="display:none">
                        <label>Banque (si bancaire)</label>
                        <select name="id_banque" class="form-control">
                            <option value="">—</option>
                            @foreach($banques as $b)
                                <option value="{{ $b->id }}">{{ $b->nom_banque }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- MONTANT + MOTIF --}}
            <div class="card p-3 mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <label>Montant du règlement <span class="text-danger">*</span></label>
                        <input type="number" min="0" step="0.01" class="form-control" name="montant_reglement" required>
                    </div>
                    <div class="col-md-4">
                        <label>Motif (optionnel)</label>
                        <input type="text" class="form-control" name="motif_reglement" placeholder="Ex: acompte tranche 1">
                    </div>
                    <div class="col-md-4">
                        <label>Date du règlement <span class="text-danger">*</span></label>
                        <input type="date" name="date_reglement" class="form-control" value="{{ now()->toDateString() }}" required>
                    </div>
                    {{-- Si ton store calcule la lettre côté serveur tu peux enlever ce champ --}}
                    <div class="col-md-4 mt-2">
                        <label>Montant en lettres (optionnel)</label>
                        <input type="text" name="lettre" class="form-control" placeholder="Ex: Cent mille francs CFA">
                    </div>
                </div>
            </div>

            <div>
                <button class="btn btn-success">✅ Enregistrer le règlement</button>
                <a href="{{ route('reglement_by_facture', $facture->id) }}" class="btn btn-secondary">↩ Retour</a>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            // toggle caisse / banque
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
