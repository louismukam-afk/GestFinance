@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="text-primary">💳 {{ $title }}</h3>
        <p><strong>Étudiant :</strong> {{ $etudiant->nom }} — Matricule: {{ $etudiant->matricule ?? '-' }}</p>

        <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" href="#add_facture">
            ➕ Nouvelle Facture
        </button>

        <div class="table-responsive mt-3">
            <table id="facturesTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Type</th>
                    <th>Cycle / Filière</th>
                    <th>Niveau / Spécialité</th>
                    <th>Détails</th>
                    <th>N°</th>
                    <th>Date</th>
                    <th>Année</th>
                    <th>Budget</th>
                    <th>Montant</th>
                    <th>PDF</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($factures as $i => $f)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $f->type_facture === 1 ? 'Scolarité' : 'Frais' }}</td>
                        <td>{{ $f->cycles->nom_cycle ?? '-' }} / {{ $f->filieres->nom_filiere ?? '-' }}</td>
                        <td>{{ $f->niveaux->nom_niveau ?? '-' }} / {{ $f->specialites->nom_specialite ?? '-' }}</td>
                        <td>
                            @if($f->type_facture === 1)
                                <div><strong>Scolarité:</strong> {{ number_format($f->scolarites->montant_total ?? 0,0,',',' ') }}</div>
                                @php
                                    $trs = \App\Models\tranche_scolarite::where('id_scolarite', $f->id_scolarite)->orderBy('date_limite')->get();
                                @endphp
                                @if($trs->count())
                                    <div class="mt-1">
                                        <strong>Tranches :</strong>
                                        <ul class="mb-0">
                                            @foreach($trs as $t)
                                                <li>
                                                    {{ $t->nom_tranche }} —
                                                    {{ number_format($t->montant_tranche,0,',',' ') }} —
                                                    {{ \Carbon\Carbon::parse($t->date_limite)->format('d/m/Y') }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            @else
                                <div><strong>Frais:</strong> {{ $f->frais->nom_frais ?? '-' }}</div>
                            @endif
                        </td>
                        <td>{{ $f->numero_facture }}</td>
                        <td>{{ \Carbon\Carbon::parse($f->date_facture)->format('d/m/Y') }}</td>
                        <td>{{ $f->id_annee_academique }}</td>
                        <td>
                            {{ $f->budget->libelle_ligne_budget ?? '—' }}<br>
                            <small>
                                {{ $f->ligne_budgetaire_entree->libelle_ligne_budgetaire_entree ?? '—' }} >
                                {{ $f->element_ligne_budgetaire_entree->libelle_elements_ligne_budgetaire_entree ?? '—' }} >
                                {{ $f->donnee_ligne_budgetaire_entree->donnee_ligne_budgetaire_entree ?? '—' }}
                            </small>
                        </td>
                        <td>{{ number_format($f->montant_total_facture,0,',',' ') }}</td>
                        <td>
                            @php $pdfPath = "storage/factures/FACT-{$f->numero_facture}.pdf"; @endphp
                            @if(file_exists(public_path($pdfPath)))
                                <a class="btn btn-xs btn-default" target="_blank" href="{{ asset($pdfPath) }}">🧾 PDF</a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <a href="#edit_facture"
                               class="btn btn-xs btn-warning"
                               data-toggle="modal" data-backdrop="false"
                               data-id="{{ $f->id }}"
                               data-type="{{ $f->type_facture }}"
                               data-cycle="{{ $f->id_cycle }}"
                               data-filiere="{{ $f->id_filiere }}"
                               data-niveau="{{ $f->id_niveau }}"
                               data-specialite="{{ $f->id_specialite }}"
                               data-scolarite="{{ $f->id_scolarite }}"
                               data-frais="{{ $f->id_frais }}"
                               data-date="{{ $f->date_facture }}"
                               data-annee="{{ $f->id_annee_academique }}"
                               data-budget="{{ $f->id_budget }}"
                               data-ligne="{{ $f->id_ligne_budgetaire_entree }}"
                               data-element="{{ $f->id_element_ligne_budgetaire_entree }}"
                               data-donnee="{{ $f->id_donnee_ligne_budgetaire_entree }}"
                               data-montant="{{ $f->montant_total_facture }}"
                               onclick="return openEditFacture(this);">✏️</a>

                            <form action="{{ route('delete_facture', $f->id) }}" method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-danger" onclick="return confirm('Supprimer cette facture ?')">🗑️</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Ajout --}}
    <div class="modal fade" id="add_facture">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form method="POST" action="{{ route('store_facture') }}">
                    @csrf
                    <input type="hidden" name="id_etudiant" value="{{ $etudiant->id }}">

                    <div class="modal-header">
                        <h4 class="modal-title">➕ Nouvelle Facture</h4>
                        <button type="button" class="close" data-dismiss="modal">x</button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            {{-- Type --}}
                            <div class="col-md-2">
                                <label>Type</label>
                                <div>
                                    <label class="mr-2"><input type="radio" name="type_facture" value="1" checked> Scolarité</label>
                                    <label><input type="radio" name="type_facture" value="0"> Frais</label>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label>Date</label>
                                <input type="date" name="date_facture" class="form-control" required>
                            </div>

                            <div class="col-md-7">
                                <label>Année Académique</label>
                                <select name="id_annee_academique" class="form-control" required>
                                    @foreach($annees as $a)
                                        <option value="{{ $a->id }}">{{ $a->nom }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Pédagogie --}}
                            <div class="col-md-3">
                                <label>Cycle</label>
                                <select id="add-cycle" name="id_cycle" class="form-control" required>
                                    <option value="">--</option>
                                    @foreach($cycles as $c)
                                        <option value="{{ $c->id }}">{{ $c->nom_cycle }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Filière</label>
                                <select id="add-filiere" name="id_filiere" class="form-control" required>
                                    <option value="">--</option>
                                    @foreach($filieres as $f)
                                        <option value="{{ $f->id }}">{{ $f->nom_filiere }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Niveau</label>
                                <select id="add-niveau" name="id_niveau" class="form-control"></select>
                            </div>
                            <div class="col-md-3">
                                <label>Spécialité</label>
                                <select id="add-specialite" name="id_specialite" class="form-control" required></select>
                            </div>

                            {{-- Scolarité (toujours sans tranche à la création) --}}
                            <div id="bloc-scolarite" class="col-12">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Scolarité</label>
                                        <select id="add-scolarite" name="id_scolarite" class="form-control" required></select>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="alert alert-info mt-4">
                                            Les tranches ne sont pas sélectionnées ici. Elles seront listées automatiquement sur la facture.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Frais (si type = 0) --}}
                            <div id="bloc-frais" class="col-12" style="display:none">
                                <input type="hidden" id="add-id-frais-hidden" name="id_frais" value="">
                                <div class="row">
                                    <div class="col-md-8">
                                        <label>Frais</label>
                                        <select id="add-frais" class="form-control">
                                            <option value="">--</option>
                                            @foreach($fraisList as $fr)
                                                <option value="{{ $fr->id }}" data-montant="{{ $fr->montant }}">
                                                    {{ $fr->nom_frais }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Montant (auto)</label>
                                        <input type="text" id="add-montant-frais" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>

                            {{-- Budget cascade --}}
                            <div class="col-md-3">
                                <label>Budget</label>
                                <select id="add-budget" name="id_budget" class="form-control" required>
                                    <option value="">--</option>
                                    @foreach($budgets as $b)
                                        <option value="{{ $b->id }}">{{ $b->libelle_ligne_budget ?? ('Budget #'.$b->id) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Ligne budgétaire</label>
                                <select id="add-ligne" name="id_ligne_budgetaire_entree" class="form-control" required></select>
                            </div>
                            <div class="col-md-3">
                                <label>Élément de ligne</label>
                                <select id="add-element" name="id_element_ligne_budgetaire_entree" class="form-control" required></select>
                            </div>
                            <div class="col-md-3">
                                <label>Donnée budgétaire</label>
                                <select id="add-donnee" name="id_donnee_ligne_budgetaire_entree" class="form-control" required></select>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-success">💾 Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Édition --}}
    <div class="modal fade" id="edit_facture">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form method="POST" action="{{ route('update_facture') }}" id="editFactureForm">
                    @csrf
                    <input type="hidden" name="id" id="edit-id">

                    <div class="modal-header">
                        <h4 class="modal-title">✏️ Modifier Facture</h4>
                        <button type="button" class="close" data-dismiss="modal">x</button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            {{-- Type --}}
                            <div class="col-md-2">
                                <label>Type</label>
                                <div>
                                    <label class="mr-2"><input type="radio" name="type_facture" id="edit-type-sco" value="1"> Scolarité</label>
                                    <label><input type="radio" name="type_facture" id="edit-type-frais" value="0"> Frais</label>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label>Date</label>
                                <input type="date" name="date_facture" id="edit-date" class="form-control" required>
                            </div>

                            <div class="col-md-7">
                                <label>Année Académique</label>
                                <select name="id_annee_academique" id="edit-annee" class="form-control" required>
                                    @foreach($annees as $a)
                                        <option value="{{ $a->id }}">{{ $a->nom }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Pédagogie --}}
                            <div class="col-md-3">
                                <label>Cycle</label>
                                <select id="edit-cycle" name="id_cycle" class="form-control" required>
                                    <option value="">--</option>
                                    @foreach($cycles as $c)
                                        <option value="{{ $c->id }}">{{ $c->nom_cycle }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Filière</label>
                                <select id="edit-filiere" name="id_filiere" class="form-control" required>
                                    <option value="">--</option>
                                    @foreach($filieres as $f)
                                        <option value="{{ $f->id }}">{{ $f->nom_filiere }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Niveau</label>
                                <select id="edit-niveau" name="id_niveau" class="form-control"></select>
                            </div>
                            <div class="col-md-3">
                                <label>Spécialité</label>
                                <select id="edit-specialite" name="id_specialite" class="form-control" required></select>
                            </div>

                            <div id="edit-bloc-scolarite" class="col-12">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Scolarité</label>
                                        <select id="edit-scolarite" name="id_scolarite" class="form-control" required></select>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="alert alert-info mt-4">Les tranches sont listées automatiquement sur la facture.</div>
                                    </div>
                                </div>
                            </div>

                            <div id="edit-bloc-frais" class="col-12" style="display:none">
                                <input type="hidden" id="edit-id-frais-hidden" name="id_frais" value="">
                                <div class="row">
                                    <div class="col-md-8">
                                        <label>Frais</label>
                                        <select id="edit-frais" class="form-control">
                                            <option value="">--</option>
                                            @foreach($fraisList as $fr)
                                                <option value="{{ $fr->id }}" data-montant="{{ $fr->montant }}">
                                                    {{ $fr->nom_frais }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Montant (auto)</label>
                                        <input type="text" id="edit-montant-frais" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>

                            {{-- Budget cascade --}}
                            <div class="col-md-3">
                                <label>Budget</label>
                                <select id="edit-budget" name="id_budget" class="form-control" required>
                                    <option value="">--</option>
                                    @foreach($budgets as $b)
                                        <option value="{{ $b->id }}">{{ $b->libelle_ligne_budget ?? ('Budget #'.$b->id) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Ligne budgétaire</label>
                                <select id="edit-ligne" name="id_ligne_budgetaire_entree" class="form-control" required></select>
                            </div>
                            <div class="col-md-3">
                                <label>Élément de ligne</label>
                                <select id="edit-element" name="id_element_ligne_budgetaire_entree" class="form-control" required></select>
                            </div>
                            <div class="col-md-3">
                                <label>Donnée budgétaire</label>
                                <select id="edit-donnee" name="id_donnee_ligne_budgetaire_entree" class="form-control" required></select>
                            </div>

                            <div class="col-md-4 mt-2">
                                <label>Montant total (info)</label>
                                <input type="text" id="edit-montant-info" class="form-control" readonly>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-primary">✔ Modifier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            // DataTables (si dispo)
            $(function(){
                if ($.fn && $.fn.DataTable) {
                    $('#facturesTable').DataTable({
                        responsive: true,
                        dom: 'Bfrtip',
                        pageLength: 25,
                        buttons: [
                            { extend: 'excelHtml5', text: '📊 Excel', className: 'btn btn-success btn-sm',
                                title: 'FACTURES ETUDIANT', exportOptions: { columns: ':not(:last-child)' } },
                            { extend: 'pdfHtml5',   text: '📄 PDF',   className: 'btn btn-danger btn-sm',
                                title: 'FACTURES ETUDIANT', exportOptions: { columns: ':not(:last-child)' } },
                            { extend: 'print',      text: '🖨 Imprimer', className: 'btn btn-info btn-sm',
                                title: 'FACTURES ETUDIANT', exportOptions: { columns: ':not(:last-child)' } },
                        ],
                        language: { url: "{{ asset('js/datatables/fr-FR.json') }}" }
                    });
                }
            });

            // Helpers
            function fillSelect($select, items, textKey, valueKey, withEmpty=true) {
                $select.empty();
                if (withEmpty) $select.append(new Option('--', ''));
                (items || []).forEach(it => $select.append(new Option(it[textKey], it[valueKey])));
            }

            // === PÉDAGOGIE AJOUT ===
            function fetchFilters(cycle, filiere) {
                if (!cycle || !filiere) {
                    fillSelect($('#add-niveau'), [], 'nom_niveau', 'id');
                    fillSelect($('#add-specialite'), [], 'nom_specialite', 'id');
                    fillSelect($('#add-scolarite'), [], 'label', 'id', true);
                    return;
                }
                $.get("{{ route('ajax_scolarite_filters') }}", { id_cycle: cycle, id_filiere: filiere })
                    .done(function(res){
                        fillSelect($('#add-niveau'), (res||{}).niveaux, 'nom_niveau', 'id');
                        fillSelect($('#add-specialite'), (res||{}).specialites, 'nom_specialite', 'id');
                        fillSelect($('#add-scolarite'), (res||{}).scolarites, 'label', 'id', true);
                    })
                    .fail(function(){ alert("Chargement des filtres impossible."); });
            }
            $('#add-cycle, #add-filiere').on('change', function(){
                fetchFilters($('#add-cycle').val(), $('#add-filiere').val());
            });

            // === TYPE AJOUT ===
            function toggleBlocksAdd(isScolarite) {
                $('#bloc-scolarite').toggle(isScolarite);
                $('#bloc-frais').toggle(!isScolarite);
                if (isScolarite) {
                    $('#add-id-frais-hidden').val('');
                    $('#add-montant-frais').val('');
                } else {
                    const id = $('#add-frais').val() || '';
                    const mt = $('#add-frais option:selected').data('montant') || '';
                    $('#add-id-frais-hidden').val(id);
                    $('#add-montant-frais').val(mt);
                }
            }
            $('input[name="type_facture"]').on('change', function(){
                toggleBlocksAdd(parseInt($(this).val(),10) === 1);
            });
            $('#add-frais').on('change', function(){
                const id = $(this).val() || '';
                const mt = $('#add-frais option:selected').data('montant') || '';
                $('#add-id-frais-hidden').val(id);
                $('#add-montant-frais').val(mt);
            });

            // === BUDGET AJOUT ===
            $('#add-budget').on('change', function(){
                const id = $(this).val();
                fillSelect($('#add-ligne'), [], 'libelle_ligne_budgetaire_entree', 'id');
                fillSelect($('#add-element'), [], 'libelle_elements_ligne_budgetaire_entree', 'id');
                fillSelect($('#add-donnee'), [], 'donnee_ligne_budgetaire_entree', 'id');
                if (!id) return;
                $.get("{{ url('ajax/budget') }}/"+id+"/lignes").done(function(items){
                    fillSelect($('#add-ligne'), items, 'libelle_ligne_budgetaire_entree', 'id');
                });
            });
            $('#add-ligne').on('change', function(){
                const id = $(this).val();
                fillSelect($('#add-element'), [], 'libelle_elements_ligne_budgetaire_entree', 'id');
                fillSelect($('#add-donnee'), [], 'donnee_ligne_budgetaire_entree', 'id');
                if (!id) return;
                $.get("{{ url('ajax/ligne') }}/"+id+"/elements").done(function(items){
                    fillSelect($('#add-element'), items, 'libelle_elements_ligne_budgetaire_entree', 'id');
                });
            });
            $('#add-element').on('change', function(){
                const id = $(this).val();
                fillSelect($('#add-donnee'), [], 'donnee_ligne_budgetaire_entree', 'id');
                if (!id) return;
                $.get("{{ url('ajax/element') }}/"+id+"/donnees").done(function(items){
                    fillSelect($('#add-donnee'), items, 'donnee_ligne_budgetaire_entree', 'id');
                });
            });

            // INIT
            toggleBlocksAdd(true); // Ajout → Scolarité par défaut

            // === ÉDITION ===
            window.openEditFacture = function(el){
                const $b = $(el);
                $('#edit-id').val($b.data('id'));
                $('#edit-date').val($b.data('date'));
                $('#edit-annee').val($b.data('annee'));
                $('#edit-cycle').val($b.data('cycle'));
                $('#edit-filiere').val($b.data('filiere'));

                const type = parseInt($b.data('type'), 10);
                if (type === 1) { $('#edit-type-sco').prop('checked', true); } else { $('#edit-type-frais').prop('checked', true); }
                toggleBlocksEdit(type === 1);

                const cycle = $b.data('cycle');
                const filiere = $b.data('filiere');

                const selectedNiv = $b.data('niveau');
                const selectedSpe = $b.data('specialite');
                const selectedSco = $b.data('scolarite');

                const selectedFrs = $b.data('frais');
                const montantInfo = $b.data('montant');
                $('#edit-montant-info').val(montantInfo);

                // Budget sélection
                const selBudget = $b.data('budget');
                const selLigne  = $b.data('ligne');
                const selElt    = $b.data('element');
                const selDon    = $b.data('donnee');

                $('#edit-budget').val(selBudget).trigger('change');

                // Charger lignes → éléments → données en cascade avec présélections
                $.get("{{ url('ajax/budget') }}/"+selBudget+"/lignes").done(function(items){
                    fillSelect($('#edit-ligne'), items, 'libelle_ligne_budgetaire_entree', 'id');
                    if (selLigne) $('#edit-ligne').val(selLigne);

                    $.get("{{ url('ajax/ligne') }}/"+selLigne+"/elements").done(function(elts){
                        fillSelect($('#edit-element'), elts, 'libelle_elements_ligne_budgetaire_entree', 'id');
                        if (selElt) $('#edit-element').val(selElt);

                        $.get("{{ url('ajax/element') }}/"+selElt+"/donnees").done(function(dons){
                            fillSelect($('#edit-donnee'), dons, 'donnee_ligne_budgetaire_entree', 'id');
                            if (selDon) $('#edit-donnee').val(selDon);
                        });
                    });
                });

                // Pédagogie
                if (cycle && filiere) {
                    $.get("{{ route('ajax_scolarite_filters') }}", { id_cycle: cycle, id_filiere: filiere })
                        .done(function(res){
                            fillSelect($('#edit-niveau'), (res||{}).niveaux, 'nom_niveau', 'id');
                            if (selectedNiv) $('#edit-niveau').val(selectedNiv);

                            fillSelect($('#edit-specialite'), (res||{}).specialites, 'nom_specialite', 'id');
                            if (selectedSpe) $('#edit-specialite').val(selectedSpe);

                            fillSelect($('#edit-scolarite'), (res||{}).scolarites, 'label', 'id', true);
                            if (selectedSco) $('#edit-scolarite').val(selectedSco);

                            $('#edit_facture').modal('show');
                        });
                } else {
                    $('#edit_facture').modal('show');
                }
                return false;
            };

            function toggleBlocksEdit(isScolarite) {
                $('#edit-bloc-scolarite').toggle(isScolarite);
                $('#edit-bloc-frais').toggle(!isScolarite);
                if (isScolarite) {
                    $('#edit-id-frais-hidden').val('');
                    $('#edit-montant-frais').val('');
                } else {
                    const id = $('#edit-frais').val() || '';
                    const mt = $('#edit-frais option:selected').data('montant') || '';
                    $('#edit-id-frais-hidden').val(id);
                    $('#edit-montant-frais').val(mt);
                }
            }

            // Changement type (édition)
            $('#editFactureForm input[name="type_facture"]').on('change', function(){
                toggleBlocksEdit(parseInt($(this).val(),10) === 1);
            });

            // Saisie frais (édition)
            $('#edit-frais').on('change', function(){
                const id  = $(this).val() || '';
                const mt  = $('#edit-frais option:selected').data('montant') || '';
                $('#edit-id-frais-hidden').val(id);
                $('#edit-montant-frais').val(mt);
            });

            // Budget édition
            $('#edit-budget').on('change', function(){
                const id = $(this).val();
                fillSelect($('#edit-ligne'), [], 'libelle_ligne_budgetaire_entree', 'id');
                fillSelect($('#edit-element'), [], 'libelle_elements_ligne_budgetaire_entree', 'id');
                fillSelect($('#edit-donnee'), [], 'donnee_ligne_budgetaire_entree', 'id');
                if (!id) return;
                $.get("{{ url('ajax/budget') }}/"+id+"/lignes").done(function(items){
                    fillSelect($('#edit-ligne'), items, 'libelle_ligne_budgetaire_entree', 'id');
                });
            });

            $('#edit-ligne').on('change', function(){
                const id = $(this).val();
                fillSelect($('#edit-element'), [], 'libelle_elements_ligne_budgetaire_entree', 'id');
                fillSelect($('#edit-donnee'), [], 'donnee_ligne_budgetaire_entree', 'id');
                if (!id) return;
                $.get("{{ url('ajax/ligne') }}/"+id+"/elements").done(function(items){
                    fillSelect($('#edit-element'), items, 'libelle_elements_ligne_budgetaire_entree', 'id');
                });
            });

            $('#edit-element').on('change', function(){
                const id = $(this).val();
                fillSelect($('#edit-donnee'), [], 'donnee_ligne_budgetaire_entree', 'id');
                if (!id) return;
                $.get("{{ url('ajax/element') }}/"+id+"/donnees").done(function(items){
                    fillSelect($('#edit-donnee'), items, 'donnee_ligne_budgetaire_entree', 'id');
                });
            });

        })();
    </script>
@endsection





{{--@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="text-primary">💳 {{ $title }}</h3>
        <p><strong>Étudiant :</strong> {{ $etudiant->nom }} — Matricule: {{ $etudiant->matricule ?? '-' }}</p>

        <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" href="#add_facture">
            ➕ Nouvelle Facture
        </button>

        <div class="table-responsive mt-3">
            <table id="facturesTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Type</th>
                    <th>Cycle / Filière</th>
                    <th>Niveau / Spécialité</th>
                    <th>Scolarité / Tranche / Frais</th>
                    <th>Numéro</th>
                    <th>Date</th>
                    <th>Année Acad.</th>
                    <th>Montant</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($factures as $i => $f)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $f->type_facture === 1 ? 'Scolarité' : 'Frais' }}</td>
                        <td>{{ $f->cycles->nom_cycle ?? '-' }} / {{ $f->filieres->nom_filiere ?? '-' }}</td>
                        <td>{{ $f->niveaux->nom_niveau ?? '-' }} / {{ $f->specialites->nom_specialite ?? '-' }}</td>
                        <td>
                            @if($f->type_facture === 1)
                                <div>Scolarité: {{ $f->scolarites ? number_format($f->scolarites->montant_total,0,',',' ') : '-' }}</div>
                                <div>Tranche: {{ $f->tranche_scolarites->nom_tranche ?? '-' }}</div>
                            @else
                                <div>Frais: {{ $f->frais->nom_frais ?? '-' }}</div>
                            @endif
                        </td>
                        <td>{{ $f->numero_facture }}</td>
                        <td>{{ \Carbon\Carbon::parse($f->date_facture)->format('d/m/Y') }}</td>
                        <td>{{ $f->id_annee_academique }}</td>
                        <td>{{ number_format($f->montant_total_facture,0,',',' ') }}</td>
                        <td>
                            <a href="#edit_facture"
                               class="btn btn-xs btn-warning"
                               data-toggle="modal" data-backdrop="false"
                               data-id="{{ $f->id }}"
                               data-type="{{ $f->type_facture }}"
                               data-cycle="{{ $f->id_cycle }}"
                               data-filiere="{{ $f->id_filiere }}"
                               data-niveau="{{ $f->id_niveau }}"
                               data-specialite="{{ $f->id_specialite }}"
                               data-scolarite="{{ $f->id_scolarite }}"
                               data-tranche="{{ $f->id_tranche_scolarite }}"
                               data-frais="{{ $f->id_frais }}"
                               data-numero="{{ $f->numero_facture }}"
                               data-date="{{ $f->date_facture }}"
                               data-annee="{{ $f->id_annee_academique }}"
                               data-montant="{{ $f->montant_total_facture }}"
                               onclick="return openEditFacture(this);">✏️</a>

                            <form action="{{ route('delete_facture', $f->id) }}" method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-danger" onclick="return confirm('Supprimer cette facture ?')">🗑️</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    --}}{{-- Modal Ajout --}}{{--
    <div class="modal fade" id="add_facture">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form method="POST" action="{{ route('store_facture') }}">
                    @csrf
                    <input type="hidden" name="id_etudiant" value="{{ $etudiant->id }}">
                    <div class="modal-header">
                        <h4 class="modal-title">➕ Nouvelle Facture</h4>
                        <button type="button" class="close" data-dismiss="modal">x</button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-2">
                                <label>Type</label>
                                <div>
                                    <label class="mr-2"><input type="radio" name="type_facture" value="1" checked> Scolarité</label>
                                    <label><input type="radio" name="type_facture" value="0"> Frais</label>
                                </div>
                            </div>

                            <div class="col-md-2"><label>Numéro</label><input type="number" name="numero_facture" class="form-control" required></div>
                            <div class="col-md-3"><label>Date</label><input type="date" name="date_facture" class="form-control" required></div>
                            <div class="col-md-5">
                                <label>Année Académique</label>
                                <select name="id_annee_academique" class="form-control" required>
                                    @foreach($annees as $a)
                                        <option value="{{ $a->id }}">{{ $a->nom }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label>Cycle</label>
                                <select id="add-cycle" name="id_cycle" class="form-control" required>
                                    <option value="">--</option>
                                    @foreach($cycles as $c)
                                        <option value="{{ $c->id }}">{{ $c->nom_cycle }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Filière</label>
                                <select id="add-filiere" name="id_filiere" class="form-control" required>
                                    <option value="">--</option>
                                    @foreach($filieres as $f)
                                        <option value="{{ $f->id }}">{{ $f->nom_filiere }}</option>
                                    @endforeach
                                </select>
                            </div>

                            --}}{{-- Dépendances AJAX --}}{{--
                            <div class="col-md-3">
                                <label>Niveau</label>
                                <select id="add-niveau" name="id_niveau" class="form-control"></select>
                            </div>
                            <div class="col-md-3">
                                <label>Spécialité</label>
                                <select id="add-specialite" name="id_specialite" class="form-control"></select>
                            </div>

                            --}}{{-- Bloc Scolarité --}}{{--
                            <div id="bloc-scolarite" class="col-12">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Scolarité (dérivée cycle+filière)</label>
                                        <select id="add-scolarite" name="id_scolarite" class="form-control">
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Tranche (optionnelle)</label>
                                        <select id="add-tranche" name="id_tranche_scolarite" class="form-control">
                                        </select>
                                    </div>
                                </div>
                            </div>

                            --}}{{-- Bloc Frais --}}{{--
                            <div id="bloc-frais" class="col-12" style="display:none">
                                <label>Frais</label>
                                <select name="id_frais" id="add-frais" class="form-control">
                                    <option value="">--</option>
                                    @foreach($fraisList as $fr)
                                        <option value="{{ $fr->id }}">{{ $fr->nom_frais }} ({{ $fr->type_frais ? 'Espèce' : 'Nature' }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label>Montant total (optionnel)</label>
                                <input type="number" step="0.01" name="montant_total_facture" class="form-control" placeholder="Laisse vide pour auto (scolarité/tranche)">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-success">💾 Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    --}}{{-- Modal Édition --}}{{--
    <div class="modal fade" id="edit_facture">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form method="POST" action="{{ route('update_facture') }}" id="editFactureForm">
                    @csrf
                    <input type="hidden" name="id" id="edit-id">

                    <div class="modal-header">
                        <h4 class="modal-title">✏️ Modifier Facture</h4>
                        <button type="button" class="close" data-dismiss="modal">x</button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-2">
                                <label>Type</label>
                                <div>
                                    <label class="mr-2"><input type="radio" name="type_facture" id="edit-type-sco" value="1"> Scolarité</label>
                                    <label><input type="radio" name="type_facture" id="edit-type-frais" value="0"> Frais</label>
                                </div>
                            </div>

                            <div class="col-md-2"><label>Numéro</label><input type="number" name="numero_facture" id="edit-numero" class="form-control" required></div>
                            <div class="col-md-3"><label>Date</label><input type="date" name="date_facture" id="edit-date" class="form-control" required></div>
                            <div class="col-md-5">
                                <label>Année Académique</label>
                                <select name="id_annee_academique" id="edit-annee" class="form-control" required>
                                    @foreach($annees as $a)
                                        <option value="{{ $a->id }}">{{ $a->nom }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label>Cycle</label>
                                <select id="edit-cycle" name="id_cycle" class="form-control" required>
                                    <option value="">--</option>
                                    @foreach($cycles as $c)
                                        <option value="{{ $c->id }}">{{ $c->nom_cycle }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Filière</label>
                                <select id="edit-filiere" name="id_filiere" class="form-control" required>
                                    <option value="">--</option>
                                    @foreach($filieres as $f)
                                        <option value="{{ $f->id }}">{{ $f->nom_filiere }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label>Niveau</label>
                                <select id="edit-niveau" name="id_niveau" class="form-control"></select>
                            </div>
                            <div class="col-md-3">
                                <label>Spécialité</label>
                                <select id="edit-specialite" name="id_specialite" class="form-control"></select>
                            </div>

                            <div id="edit-bloc-scolarite" class="col-12">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Scolarité</label>
                                        <select id="edit-scolarite" name="id_scolarite" class="form-control"></select>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Tranche</label>
                                        <select id="edit-tranche" name="id_tranche_scolarite" class="form-control"></select>
                                    </div>
                                </div>
                            </div>

                            <div id="edit-bloc-frais" class="col-12" style="display:none">
                                <label>Frais</label>
                                <select name="id_frais" id="edit-frais" class="form-control">
                                    <option value="">--</option>
                                    @foreach($fraisList as $fr)
                                        <option value="{{ $fr->id }}">{{ $fr->nom_frais }} ({{ $fr->type_frais ? 'Espèce' : 'Nature' }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label>Montant total</label>
                                <input type="number" step="0.01" name="montant_total_facture" id="edit-montant" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-primary">✔ Modifier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {

            // === DataTables
            $(function(){
                if ($.fn && $.fn.DataTable) {
                    $('#facturesTable').DataTable({
                        responsive: true,
                        dom: 'Bfrtip',
                        pageLength: 25,
                        buttons: [
                            { extend: 'excelHtml5', text: '📊 Excel', className: 'btn btn-success btn-sm',
                                title: 'FACTURES ETUDIANT', exportOptions: { columns: ':not(:last-child)' } },
                            { extend: 'pdfHtml5',   text: '📄 PDF',   className: 'btn btn-danger btn-sm',
                                title: 'FACTURES ETUDIANT', exportOptions: { columns: ':not(:last-child)' } },
                            { extend: 'print',      text: '🖨 Imprimer', className: 'btn btn-info btn-sm',
                                title: 'FACTURES ETUDIANT', exportOptions: { columns: ':not(:last-child)' } },
                        ],
                        language: { url: "{{ asset('js/datatables/fr-FR.json') }}" }
                    });
                }
            });

            // === Toggles type facture
            function toggleBlocks(prefix, isScolarite) {
                const sco = document.getElementById(prefix+'-bloc-scolarite');
                const frs = document.getElementById(prefix+'-bloc-frais');
                if (!sco || !frs) return;
                sco.style.display = isScolarite ? '' : 'none';
                frs.style.display = !isScolarite ? '' : 'none';
            }

            $('input[name="type_facture"]').on('change', function(){
                const v = parseInt($(this).val(), 10);
                toggleBlocks('add', v === 1);
            });

            // === Dépendances (add)
            function fetchFilters(cycle, filiere, cb) {
                if (!cycle || !filiere) return;
                $.get("{{ route('ajax_scolarite_filters') }}", { id_cycle: cycle, id_filiere: filiere })
                    .done(function(res){
                        // Niveaux
                        const $niv = $('#add-niveau').empty();
                        res.niveaux.forEach(n => $niv.append(new Option(n.nom_niveau, n.id)));

                        // Spécialités
                        const $spe = $('#add-specialite').empty();
                        res.specialites.forEach(s => $spe.append(new Option(s.nom_specialite, s.id)));

                        // Scolarités
                        const $sco = $('#add-scolarite').empty();
                        res.scolarites.forEach(s => $sco.append(new Option(s.label, s.id)));

                        $('#add-tranche').empty();
                        if (cb) cb();
                    });
            }

            $('#add-cycle, #add-filiere').on('change', function(){
                fetchFilters($('#add-cycle').val(), $('#add-filiere').val());
            });

            $('#add-scolarite').on('change', function(){
                const id = $(this).val();
                $('#add-tranche').empty();
                if (id) {
                    $.get("{{ url('ajax/scolarite') }}/"+id+"/tranches")
                        .done(function(trs){
                            $('#add-tranche').append(new Option('--', ''));
                            trs.forEach(t => {
                                const label = t.nom_tranche + ' (' + t.montant_tranche + ')';
                            $('#add-tranche').append(new Option(label, t.id));
                        });
                        });
                }
            });

            // === Édition
            window.openEditFacture = function(el){
                const $b = $(el);
                $('#edit-id').val($b.data('id'));
                $('#edit-numero').val($b.data('numero'));
                $('#edit-date').val($b.data('date'));
                $('#edit-annee').val($b.data('annee'));
                $('#edit-cycle').val($b.data('cycle'));
                $('#edit-filiere').val($b.data('filiere'));
                $('#edit-montant').val($b.data('montant'));

                const type = parseInt($b.data('type'), 10);
                if (type === 1) { $('#edit-type-sco').prop('checked', true); } else { $('#edit-type-frais').prop('checked', true); }
                toggleBlocks('edit', type === 1);

                // Charger dépendances (niveaux, spé, scolarités) puis sélectionner valeurs
                const cycle = $b.data('cycle');
                const filiere = $b.data('filiere');
                const selectedNiv = $b.data('niveau');
                const selectedSpe = $b.data('specialite');
                const selectedSco = $b.data('scolarite');
                const selectedTr  = $b.data('tranche');
                const selectedFrs = $b.data('frais');

                if (cycle && filiere) {
                    $.get("{{ route('ajax_scolarite_filters') }}", { id_cycle: cycle, id_filiere: filiere })
                        .done(function(res){
                            const $niv = $('#edit-niveau').empty();
                            res.niveaux.forEach(n => $niv.append(new Option(n.nom_niveau, n.id)));
                            if (selectedNiv) $niv.val(selectedNiv);

                            const $spe = $('#edit-specialite').empty();
                            res.specialites.forEach(s => $spe.append(new Option(s.nom_specialite, s.id)));
                            if (selectedSpe) $spe.val(selectedSpe);

                            const $sco = $('#edit-scolarite').empty();
                            res.scolarites.forEach(s => $sco.append(new Option(s.label, s.id)));
                            if (selectedSco) $sco.val(selectedSco);

                            $('#edit-tranche').empty();
                            if (selectedSco) {
                                $.get("{{ url('ajax/scolarite') }}/"+selectedSco+"/tranches")
                                    .done(function(trs){
                                        $('#edit-tranche').append(new Option('--',''));
                                        trs.forEach(t => {
                                            const label = t.nom_tranche + ' (' + t.montant_tranche + ')';
                                        $('#edit-tranche').append(new Option(label, t.id));
                                    });
                                        if (selectedTr) $('#edit-tranche').val(selectedTr);
                                    });
                            }

                            if (type === 0 && selectedFrs) $('#edit-frais').val(selectedFrs);

                            $('#edit_facture').modal('show');
                        });
                } else {
                    $('#edit_facture').modal('show');
                }

                return false;
            };

            // Changement type en édition
            $('#editFactureForm input[name="type_facture"]').on('change', function(){
                const v = parseInt($(this).val(),10);
                toggleBlocks('edit', v === 1);
            });

            // Dépendances (edit) quand cycle/filière changent manuellement
            function fetchFiltersEdit(cycle, filiere) {
                if (!cycle || !filiere) return;
                $.get("{{ route('ajax_scolarite_filters') }}", { id_cycle: cycle, id_filiere: filiere })
                    .done(function(res){
                        const $niv = $('#edit-niveau').empty();
                        res.niveaux.forEach(n => $niv.append(new Option(n.nom_niveau, n.id)));

                        const $spe = $('#edit-specialite').empty();
                        res.specialites.forEach(s => $spe.append(new Option(s.nom_specialite, s.id)));

                        const $sco = $('#edit-scolarite').empty();
                        res.scolarites.forEach(s => $sco.append(new Option(s.label, s.id)));

                        $('#edit-tranche').empty();
                    });
            }
            $('#edit-cycle, #edit-filiere').on('change', function(){
                fetchFiltersEdit($('#edit-cycle').val(), $('#edit-filiere').val());
            });
            $('#edit-scolarite').on('change', function(){
                const id = $(this).val();
                $('#edit-tranche').empty();
                if (id) {
                    $.get("{{ url('ajax/scolarite') }}/"+id+"/tranches")
                        .done(function(trs){
                            $('#edit-tranche').append(new Option('--', ''));
                            trs.forEach(t => {
                                const label = t.nom_tranche + ' (' + t.montant_tranche + ')';
                            $('#edit-tranche').append(new Option(label, t.id));
                        });
                        });
                }
            });

            // init blocs add
            toggleBlocks('add', true);
        })();
    </script>
@endsection--}}
{{--
@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="text-primary">💳 {{ $title }}</h3>
        <p><strong>Étudiant :</strong> {{ $etudiant->nom }} — Matricule: {{ $etudiant->matricule ?? '-' }}</p>

        <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" href="#add_facture">
            ➕ Nouvelle Facture
        </button>

        <div class="table-responsive mt-3">
            <table id="facturesTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Type</th>
                    <th>Cycle / Filière</th>
                    <th>Niveau / Spécialité</th>
                    <th>Détails</th>
                    <th>N°</th>
                    <th>Date</th>
                    <th>Année</th>
                    <th>Montant</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($factures as $i => $f)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $f->type_facture === 1 ? 'Scolarité' : 'Frais' }}</td>
                        <td>{{ $f->cycles->nom_cycle ?? '-' }} / {{ $f->filieres->nom_filiere ?? '-' }}</td>
                        <td>{{ $f->niveaux->nom_niveau ?? '-' }} / {{ $f->specialites->nom_specialite ?? '-' }}</td>
                        <td>
                            @if($f->type_facture === 1)
                                <div>Scolarité: {{ $f->scolarites ? number_format($f->scolarites->montant_total,0,',',' ') : '-' }}</div>
                                <div>Tranche: {{ $f->tranche_scolarites->nom_tranche ?? '-' }}</div>
                            @else
                                <div>Frais: {{ $f->frais->nom_frais ?? '-' }}</div>
                            @endif
                        </td>
                        <td>{{ $f->numero_facture }}</td>
                        <td>{{ \Carbon\Carbon::parse($f->date_facture)->format('d/m/Y') }}</td>
                        <td>{{ $f->id_annee_academique }}</td>
                        <td>{{ number_format($f->montant_total_facture,0,',',' ') }}</td>
                        <td>
                            <a href="#edit_facture"
                               class="btn btn-xs btn-warning"
                               data-toggle="modal" data-backdrop="false"
                               data-id="{{ $f->id }}"
                               data-type="{{ $f->type_facture }}"
                               data-cycle="{{ $f->id_cycle }}"
                               data-filiere="{{ $f->id_filiere }}"
                               data-niveau="{{ $f->id_niveau }}"
                               data-specialite="{{ $f->id_specialite }}"
                               data-scolarite="{{ $f->id_scolarite }}"
                               data-tranche="{{ $f->id_tranche_scolarite }}"
                               data-frais="{{ $f->id_frais }}"
                               data-date="{{ $f->date_facture }}"
                               data-annee="{{ $f->id_annee_academique }}"
                               data-montant="{{ $f->montant_total_facture }}"
                               onclick="return openEditFacture(this);">✏️</a>

                            <form action="{{ route('delete_facture', $f->id) }}" method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-danger" onclick="return confirm('Supprimer cette facture ?')">🗑️</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    --}}
{{-- Modal Ajout --}}{{--

    <div class="modal fade" id="add_facture">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form method="POST" action="{{ route('store_facture') }}">
                    @csrf
                    <input type="hidden" name="id_etudiant" value="{{ $etudiant->id }}">

                    <div class="modal-header">
                        <h4 class="modal-title">➕ Nouvelle Facture</h4>
                        <button type="button" class="close" data-dismiss="modal">x</button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-2">
                                <label>Type</label>
                                <div>
                                    <label class="mr-2"><input type="radio" name="type_facture" value="1" checked> Scolarité</label>
                                    <label><input type="radio" name="type_facture" value="0"> Frais</label>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label>Date</label>
                                <input type="date" name="date_facture" class="form-control" required>
                            </div>

                            <div class="col-md-7">
                                <label>Année Académique</label>
                                <select name="id_annee_academique" class="form-control" required>
                                    @foreach($annees as $a)
                                        <option value="{{ $a->id }}">{{ $a->nom }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label>Cycle</label>
                                <select id="add-cycle" name="id_cycle" class="form-control" required>
                                    <option value="">--</option>
                                    @foreach($cycles as $c)
                                        <option value="{{ $c->id }}">{{ $c->nom_cycle }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Filière</label>
                                <select id="add-filiere" name="id_filiere" class="form-control" required>
                                    <option value="">--</option>
                                    @foreach($filieres as $f)
                                        <option value="{{ $f->id }}">{{ $f->nom_filiere }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Niveau</label>
                                <select id="add-niveau" name="id_niveau" class="form-control"></select>
                            </div>
                            <div class="col-md-3">
                                <label>Spécialité</label>
                                <select id="add-specialite" name="id_specialite" class="form-control" required></select>
                            </div>

                            --}}
{{-- Bloc scolarité (par défaut) --}}{{--

                            <div id="bloc-scolarite" class="col-12">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Scolarité</label>
                                        <select id="add-scolarite" name="id_scolarite" class="form-control" required></select>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Tranche (optionnelle)</label>
                                        <select id="add-tranche" name="id_tranche_scolarite" class="form-control"></select>
                                    </div>
                                </div>
                            </div>

                            --}}
{{-- Bloc frais (caché) --}}{{--

                            <div id="bloc-frais" class="col-12" style="display:none">
                                <input type="hidden" id="add-id-frais-hidden" name="id_frais" value="">
                                <div class="row">
                                    <div class="col-md-8">
                                        <label>Frais</label>
                                        <select id="add-frais" class="form-control">
                                            <option value="">--</option>
                                            @foreach($fraisList as $fr)
                                                <option value="{{ $fr->id }}" data-montant="{{ $fr->montant }}">
                                                    {{ $fr->nom_frais }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Montant (auto)</label>
                                        <input type="text" id="add-montant-frais" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-success">💾 Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    --}}
{{-- Modal Édition --}}{{--

    <div class="modal fade" id="edit_facture">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form method="POST" action="{{ route('update_facture') }}" id="editFactureForm">
                    @csrf
                    <input type="hidden" name="id" id="edit-id">

                    <div class="modal-header">
                        <h4 class="modal-title">✏️ Modifier Facture</h4>
                        <button type="button" class="close" data-dismiss="modal">x</button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-2">
                                <label>Type</label>
                                <div>
                                    <label class="mr-2"><input type="radio" name="type_facture" id="edit-type-sco" value="1"> Scolarité</label>
                                    <label><input type="radio" name="type_facture" id="edit-type-frais" value="0"> Frais</label>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label>Date</label>
                                <input type="date" name="date_facture" id="edit-date" class="form-control" required>
                            </div>

                            <div class="col-md-7">
                                <label>Année Académique</label>
                                <select name="id_annee_academique" id="edit-annee" class="form-control" required>
                                    @foreach($annees as $a)
                                        <option value="{{ $a->id }}">{{ $a->nom }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label>Cycle</label>
                                <select id="edit-cycle" name="id_cycle" class="form-control" required>
                                    <option value="">--</option>
                                    @foreach($cycles as $c)
                                        <option value="{{ $c->id }}">{{ $c->nom_cycle }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Filière</label>
                                <select id="edit-filiere" name="id_filiere" class="form-control" required>
                                    <option value="">--</option>
                                    @foreach($filieres as $f)
                                        <option value="{{ $f->id }}">{{ $f->nom_filiere }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Niveau</label>
                                <select id="edit-niveau" name="id_niveau" class="form-control"></select>
                            </div>
                            <div class="col-md-3">
                                <label>Spécialité</label>
                                <select id="edit-specialite" name="id_specialite" class="form-control" required></select>
                            </div>

                            <div id="edit-bloc-scolarite" class="col-12">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Scolarité</label>
                                        <select id="edit-scolarite" name="id_scolarite" class="form-control" required></select>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Tranche</label>
                                        <select id="edit-tranche" name="id_tranche_scolarite" class="form-control"></select>
                                    </div>
                                </div>
                            </div>

                            <div id="edit-bloc-frais" class="col-12" style="display:none">
                                <input type="hidden" id="edit-id-frais-hidden" name="id_frais" value="">
                                <div class="row">
                                    <div class="col-md-8">
                                        <label>Frais</label>
                                        <select id="edit-frais" class="form-control">
                                            <option value="">--</option>
                                            @foreach($fraisList as $fr)
                                                <option value="{{ $fr->id }}" data-montant="{{ $fr->montant }}">
                                                    {{ $fr->nom_frais }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Montant (auto)</label>
                                        <input type="text" id="edit-montant-frais" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label>Montant total (info)</label>
                                <input type="text" id="edit-montant-info" class="form-control" readonly>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-primary">✔ Modifier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            // DataTables
            $(function(){
                if ($.fn && $.fn.DataTable) {
                    $('#facturesTable').DataTable({
                        responsive: true,
                        dom: 'Bfrtip',
                        pageLength: 25,
                        buttons: [
                            { extend: 'excelHtml5', text: '📊 Excel', className: 'btn btn-success btn-sm',
                                title: 'FACTURES ETUDIANT', exportOptions: { columns: ':not(:last-child)' } },
                            { extend: 'pdfHtml5',   text: '📄 PDF',   className: 'btn btn-danger btn-sm',
                                title: 'FACTURES ETUDIANT', exportOptions: { columns: ':not(:last-child)' } },
                            { extend: 'print',      text: '🖨 Imprimer', className: 'btn btn-info btn-sm',
                                title: 'FACTURES ETUDIANT', exportOptions: { columns: ':not(:last-child)' } },
                        ],
                        language: { url: "{{ asset('js/datatables/fr-FR.json') }}" }
                    });
                }
            });

            // Helpers selects
            function fillSelect($select, items, textKey, valueKey, withEmpty=true) {
                $select.empty();
                if (withEmpty) $select.append(new Option('--', ''));
                (items || []).forEach(it => $select.append(new Option(it[textKey], it[valueKey])));
            }

            // Toggle blocs
            function toggleBlocks(isScolarite, ctx = 'add') {
                const sco = document.getElementById(ctx === 'add' ? 'bloc-scolarite' : 'edit-bloc-scolarite');
                const frs = document.getElementById(ctx === 'add' ? 'bloc-frais' : 'edit-bloc-frais');
                if (sco && frs) {
                    sco.style.display = isScolarite ? '' : 'none';
                    frs.style.display = !isScolarite ? '' : 'none';
                }
                if (ctx === 'add') {
                    if (isScolarite) {
                        $('#add-id-frais-hidden').val('');
                    } else {
                        const id = $('#add-frais').val() || '';
                        $('#add-id-frais-hidden').val(id);
                    }
                } else {
                    if (isScolarite) {
                        $('#edit-id-frais-hidden').val('');
                        $('#edit-montant-frais').val('');
                    } else {
                        const id = $('#edit-frais').val() || '';
                        $('#edit-id-frais-hidden').val(id);
                        const mt = $('#edit-frais option:selected').data('montant') || '';
                        $('#edit-montant-frais').val(mt);
                    }
                }
            }

            // Type change (Ajout)
            $('input[name="type_facture"]').on('change', function(){
                const isScolarite = parseInt($(this).val(),10) === 1;
                toggleBlocks(isScolarite, 'add');
            });

            // Dépendances AJAX (Ajout)
            function fetchFilters(cycle, filiere) {
                if (!cycle || !filiere) {
                    fillSelect($('#add-niveau'), [], 'nom_niveau', 'id');
                    fillSelect($('#add-specialite'), [], 'nom_specialite', 'id');
                    fillSelect($('#add-scolarite'), [], 'label', 'id', true);
                    fillSelect($('#add-tranche'), [], 'nom_tranche', 'id', true);
                    return;
                }
                $.get("{{ route('ajax_scolarite_filters') }}", { id_cycle: cycle, id_filiere: filiere })
                    .done(function(res){
                        fillSelect($('#add-niveau'), (res||{}).niveaux, 'nom_niveau', 'id');
                        fillSelect($('#add-specialite'), (res||{}).specialites, 'nom_specialite', 'id');
                        fillSelect($('#add-scolarite'), (res||{}).scolarites, 'label', 'id', true);
                        fillSelect($('#add-tranche'), [], 'nom_tranche', 'id', true);
                    })
                    .fail(function(xhr){
                        console.error(xhr.responseText);
                        alert("Chargement des filtres impossible.");
                    });
            }
            $('#add-cycle, #add-filiere').on('change', function(){
                fetchFilters($('#add-cycle').val(), $('#add-filiere').val());
            });

            $('#add-scolarite').on('change', function(){
                const id = $(this).val();
                const $tr = $('#add-tranche');
                fillSelect($tr, [], 'nom_tranche', 'id', true);
                if (!id) return;
                $.get("{{ url('ajax/scolarite') }}/"+id+"/tranches")
                    .done(function(trs){
                        const items = (trs||[]).map(t => ({ id: t.id, nom_tranche: t.nom_tranche + ' ('+ t.montant_tranche +')' }));
                        fillSelect($tr, items, 'nom_tranche', 'id', true);
                    });
            });

            // Bloc Frais (Ajout)
            $('#add-frais').on('change', function(){
                const id  = $(this).val() || '';
                const mt  = $('#add-frais option:selected').data('montant') || '';
                $('#add-id-frais-hidden').val(id);
                $('#add-montant-frais').val(mt);
            });

            // === ÉDITION ===
            window.openEditFacture = function(el){
                const $b = $(el);
                $('#edit-id').val($b.data('id'));
                $('#edit-date').val($b.data('date'));
                $('#edit-annee').val($b.data('annee'));
                $('#edit-cycle').val($b.data('cycle'));
                $('#edit-filiere').val($b.data('filiere'));

                const type = parseInt($b.data('type'), 10);
                if (type === 1) { $('#edit-type-sco').prop('checked', true); } else { $('#edit-type-frais').prop('checked', true); }
                toggleBlocks(type === 1, 'edit');

                const cycle = $b.data('cycle');
                const filiere = $b.data('filiere');
                const selectedNiv = $b.data('niveau');
                const selectedSpe = $b.data('specialite');
                const selectedSco = $b.data('scolarite');
                const selectedTr  = $b.data('tranche');
                const selectedFrs = $b.data('frais');
                const montantInfo = $b.data('montant');

                $('#edit-montant-info').val(montantInfo);

                if (cycle && filiere) {
                    $.get("{{ route('ajax_scolarite_filters') }}", { id_cycle: cycle, id_filiere: filiere })
                        .done(function(res){
                            fillSelect($('#edit-niveau'), (res||{}).niveaux, 'nom_niveau', 'id');
                            if (selectedNiv) $('#edit-niveau').val(selectedNiv);

                            fillSelect($('#edit-specialite'), (res||{}).specialites, 'nom_specialite', 'id');
                            if (selectedSpe) $('#edit-specialite').val(selectedSpe);

                            fillSelect($('#edit-scolarite'), (res||{}).scolarites, 'label', 'id', true);
                            if (selectedSco) $('#edit-scolarite').val(selectedSco);

                            fillSelect($('#edit-tranche'), [], 'nom_tranche', 'id', true);

                            if (selectedSco) {
                                $.get("{{ url('ajax/scolarite') }}/"+selectedSco+"/tranches")
                                    .done(function(trs){
                                        const items = (trs||[]).map(t => ({ id: t.id, nom_tranche: t.nom_tranche + ' ('+ t.montant_tranche +')' }));
                                        fillSelect($('#edit-tranche'), items, 'nom_tranche', 'id', true);
                                        if (selectedTr) $('#edit-tranche').val(selectedTr);
                                    });
                            }

                            if (type === 0) {
                                $('#edit-id-frais-hidden').val(selectedFrs || '');
                                $('#edit-frais').val(selectedFrs || '');
                                const mt = $('#edit-frais option:selected').data('montant') || '';
                                $('#edit-montant-frais').val(mt);
                            }

                            $('#edit_facture').modal('show');
                        });
                } else {
                    $('#edit_facture').modal('show');
                }
                return false;
            };

            // Type change (Edition)
            $('#editFactureForm input[name="type_facture"]').on('change', function(){
                const isScolarite = parseInt($(this).val(),10) === 1;
                toggleBlocks(isScolarite, 'edit');
            });

            // Dépendances (Edition)
            function fetchFiltersEdit(cycle, filiere) {
                if (!cycle || !filiere) {
                    fillSelect($('#edit-niveau'), [], 'nom_niveau', 'id');
                    fillSelect($('#edit-specialite'), [], 'nom_specialite', 'id');
                    fillSelect($('#edit-scolarite'), [], 'label', 'id', true);
                    fillSelect($('#edit-tranche'), [], 'nom_tranche', 'id', true);
                    return;
                }
                $.get("{{ route('ajax_scolarite_filters') }}", { id_cycle: cycle, id_filiere: filiere })
                    .done(function(res){
                        fillSelect($('#edit-niveau'), (res||{}).niveaux, 'nom_niveau', 'id');
                        fillSelect($('#edit-specialite'), (res||{}).specialites, 'nom_specialite', 'id');
                        fillSelect($('#edit-scolarite'), (res||{}).scolarites, 'label', 'id', true);
                        fillSelect($('#edit-tranche'), [], 'nom_tranche', 'id', true);
                    });
            }
            $('#edit-cycle, #edit-filiere').on('change', function(){
                fetchFiltersEdit($('#edit-cycle').val(), $('#edit-filiere').val());
            });

            $('#edit-scolarite').on('change', function(){
                const id = $(this).val();
                fillSelect($('#edit-tranche'), [], 'nom_tranche', 'id', true);
                if (!id) return;
                $.get("{{ url('ajax/scolarite') }}/"+id+"/tranches")
                    .done(function(trs){
                        const items = (trs||[]).map(t => ({ id: t.id, nom_tranche: t.nom_tranche + ' ('+ t.montant_tranche +')' }));
                        fillSelect($('#edit-tranche'), items, 'nom_tranche', 'id', true);
                    });
            });

            // Bloc frais (Edition)
            $('#edit-frais').on('change', function(){
                const id  = $(this).val() || '';
                const mt  = $('#edit-frais option:selected').data('montant') || '';
                $('#edit-id-frais-hidden').val(id);
                $('#edit-montant-frais').val(mt);
            });

            // Init
            toggleBlocks(true, 'add'); // scolarité par défaut à l’ajout
        })();
    </script>
@endsection
--}}
