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
                    <th>Entité</th>
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
                        <td>{{ $f->entite->nom_entite ?? '-' }}</td>
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
                        <td>{{ $f->Annee_academique->nom }}</td>
                        <td>
                            {{ $f->budget->libelle_ligne_budget ?? '—' }}<br>
                            <small>
                                {{ $f->ligne_budgetaire_entree->libelle_ligne_budgetaire_entree ?? '—' }} >
                                {{ $f->element_ligne_budgetaire_entree->libelle_elements_ligne_budgetaire_entree ?? '—' }} >
                                {{ $f->donnee_budgetaire_entree->donnee_ligne_budgetaire_entree ?? '—' }} >
                                {{ $f->donnee_ligne_budgetaire_entree->donnee_ligne_budgetaire_entree ?? '—' }}
                            </small>
                        </td>
                        <td>{{ number_format($f->montant_total_facture,0,',',' ') }}</td>
                        <td>
                            <a class="btn btn-xs btn-info" href="{{ route('facture_download', $f->id) }}">
                                PDF
                            </a>
                            <a class="btn btn-xs btn-outline-primary" target="_blank" href="{{ route('facture_pdf', $f->id) }}">
                                Apercu
                            </a>
                        </td>

                        {{-- <td>
                             @php
                                 $relativePath = "uploads/images/files/factures/FACT-{$f->numero_facture}.pdf";
                                 $exists = \Illuminate\Support\Facades\Storage::disk('public')->exists($relativePath);
                                 $url    = $exists ? \Illuminate\Support\Facades\Storage::url($relativePath) : null;
                             @endphp

                             @if($exists)
                                 <a class="btn btn-xs btn-default" target="_blank" href="{{ $url }}">🧾 PDF</a>
                             @else
                                 --}}{{-- Fallback : bouton pour (re)générer/afficher la facture --}}{{--
                                 <a class="btn btn-xs btn-info" target="_blank" href="{{ route('facture_pdf', $f->id) }}">🖨 Générer</a>
                             @endif
                         </td>--}}

                        <td>
                            <a href="#edit_facture"
                               class="btn btn-xs btn-warning"
                               data-toggle="modal" data-backdrop="false"
                               data-id="{{ $f->id }}"
                               data-type="{{ $f->type_facture }}"
                               data-entite="{{ $f->id_entite }}"
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
                               data-donnee-budgetaire="{{ $f->id_donnee_budgetaire_entree }}"
                               data-donnee="{{ $f->id_donnee_ligne_budgetaire_entree }}"
                               data-montant="{{ $f->montant_total_facture }}"
                               onclick="return openEditFacture(this);">✏️</a>
                            {{-- Dans la colonne Actions de ta table des factures --}}
                           {{-- <a class="btn btn-xs btn-success"
                               href="{{ route('reglement_from_facture', $f->id) }}">
                                💵 Réglement
                            </a>--}}
                            <a class="btn btn-xs btn-success" href="{{ route('reglement_by_facture', $f->id) }}">💵 Régler</a>


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
                                <label>Entité</label>
                                <select name="id_entite" class="form-control" required>
                                    @foreach($entites as $e)
                                        <option value="{{ $e->id }}">{{ $e->nom_entite}}</option>
                                    @endforeach
                                </select>
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

                            {{-- Scolarité (tranche auto) --}}
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

                            {{-- Budget cascade (5 niveaux) --}}
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
                                <label>Donnée budgétaire (parent)</label>
                                <select id="add-donnee-budgetaire" class="form-control" required></select>
                                <input type="hidden" name="id_donnee_budgetaire_entree" id="add-id-donnee-budgetaire-hidden">
                            </div>
                            <div class="col-md-3 mt-2">
                                <label>Donnée LIGNE (enfant)</label>
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
                                <label>Entité</label>
                                <select name="id_entite" id="edit-entite" class="form-control" required>
                                    @foreach($entites as $e)
                                        <option value="{{ $e->id }}">{{ $e->nom_entite }}</option>
                                    @endforeach
                                </select>
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
                                <label>Donnée budgétaire (parent)</label>
                                <select id="edit-donnee-budgetaire" class="form-control" required></select>
                                <input type="hidden" name="id_donnee_budgetaire_entree" id="edit-id-donnee-budgetaire-hidden">
                            </div>
                            <div class="col-md-3 mt-2">
                                <label>Donnée LIGNE (enfant)</label>
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
            // DataTables si présent
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

            /* ===== AJOUT : pédagogie ===== */
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

            /* ===== AJOUT : type facture ===== */
            function toggleBlocksAdd(isScolarite) {

                $('#bloc-scolarite').toggle(isScolarite);
                $('#bloc-frais').toggle(!isScolarite);

                if (isScolarite) {
                    // === SCOLARITÉ ===
                    $('#add-scolarite')
                        .prop('required', true)
                        .prop('disabled', false);

                    $('#add-specialite')
                        .prop('required', true)
                        .prop('disabled', false);

                    // Frais désactivé
                    $('#add-id-frais-hidden')
                        .prop('required', false)
                        .prop('disabled', true)
                        .val('');

                    $('#add-frais').prop('disabled', true);
                    $('#add-montant-frais').val('');

                } else {
                    // === FRAIS ===
                    $('#add-id-frais-hidden')
                        .prop('required', true)
                        .prop('disabled', false);

                    $('#add-frais').prop('disabled', false);

                    // Scolarité DÉSACTIVÉE (clé du problème)
                    $('#add-scolarite')
                        .prop('required', false)
                        .prop('disabled', true)
                        .val('');

                   /* $('#add-specialite')
                        .prop('required', false)
                        .prop('disabled', true)
                        .val('');*/

                    const id = $('#add-frais').val() || '';
                    const mt = $('#add-frais option:selected').data('montant') || '';
                    $('#add-id-frais-hidden').val(id);
                    $('#add-montant-frais').val(mt);
                }
            }


            function toggleBlocksAdd1(isScolarite) {
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

            /* ===== AJOUT : cascade budget ===== */
            function fill($sel, items, text='libelle', val='id', withEmpty=true){
                $sel.empty();
                if (withEmpty) $sel.append(new Option('--',''));
                (items||[]).forEach(it => $sel.append(new Option(it[text], it[val])));
            }

            $('#add-budget').on('change', function(){
                const id = $(this).val();
                fill($('#add-ligne'), [], 'libelle_ligne_budgetaire_entree', 'id');
                fill($('#add-element'), [], 'libelle_elements_ligne_budgetaire_entree', 'id');
                fill($('#add-donnee-budgetaire'), [], 'donnee_ligne_budgetaire_entree','id');
                fill($('#add-donnee'), [], 'donnee_ligne_budgetaire_entree','id');
                $('#add-id-donnee-budgetaire-hidden').val('');
                if (!id) return;
                $.get("{{ url('ajax/budget') }}/"+id+"/lignes").done(function(items){
                    fill($('#add-ligne'), items, 'libelle_ligne_budgetaire_entree', 'id');
                });
            });

            $('#add-ligne').on('change', function(){
                const id = $(this).val();
                fill($('#add-element'), [], 'libelle_elements_ligne_budgetaire_entree', 'id');
                fill($('#add-donnee-budgetaire'), [], 'donnee_ligne_budgetaire_entree','id');
                fill($('#add-donnee'), [], 'donnee_ligne_budgetaire_entree','id');
                $('#add-id-donnee-budgetaire-hidden').val('');
                if (!id) return;
                $.get("{{ url('ajax/ligne') }}/"+id+"/elements").done(function(items){
                    fill($('#add-element'), items, 'libelle_elements_ligne_budgetaire_entree', 'id');
                });
            });

            // Élément → Données budgétaires parent
            $('#add-element').on('change', function(){
                fill($('#add-donnee-budgetaire'), [], 'donnee_ligne_budgetaire_entree','id');
                fill($('#add-donnee'), [], 'donnee_ligne_budgetaire_entree','id');
                $('#add-id-donnee-budgetaire-hidden').val('');

                const ide = $(this).val(), idb = $('#add-budget').val();
                if (!ide || !idb) return;

                $.get("{{ route('ajax_donnees_budgetaires_by_element', '_ID_') }}".replace('_ID_', ide), { id_budget: idb })
                    .done(function(rows){
                        fill($('#add-donnee-budgetaire'), rows, 'donnee_ligne_budgetaire_entree', 'id');
                    });
            });

            // Donnée budgétaire parent → Données ligne enfant
            $('#add-donnee-budgetaire').on('change', function(){
                const ide = $('#add-element').val(),
                    idb = $('#add-budget').val(),
                    iddb = $(this).val();

                $('#add-id-donnee-budgetaire-hidden').val(iddb || '');
                fill($('#add-donnee'), [], 'donnee_ligne_budgetaire_entree','id');

                if (!ide || !idb || !iddb) return;

                $.get("{{ route('ajax_donnees_ligne_by_element', '_ID_') }}".replace('_ID_', ide),
                    { id_budget: idb, id_donnee_budgetaire: iddb })
                    .done(function(rows){
                        fill($('#add-donnee'), rows, 'donnee_ligne_budgetaire_entree', 'id');
                    });
            });

            // INIT
            toggleBlocksAdd(true); // Ajout → Scolarité par défaut

            /* ===== ÉDITION ===== */
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

            $('#editFactureForm input[name="type_facture"]').on('change', function(){
                toggleBlocksEdit(parseInt($(this).val(),10) === 1);
            });

            $('#edit-frais').on('change', function(){
                const id  = $(this).val() || '';
                const mt  = $('#edit-frais option:selected').data('montant') || '';
                $('#edit-id-frais-hidden').val(id);
                $('#edit-montant-frais').val(mt);
            });

            // Budget édition cascade
            $('#edit-budget').on('change', function(){
                const id = $(this).val();
                fill($('#edit-ligne'), [], 'libelle_ligne_budgetaire_entree', 'id');
                fill($('#edit-element'), [], 'libelle_elements_ligne_budgetaire_entree', 'id');
                fill($('#edit-donnee-budgetaire'), [], 'donnee_ligne_budgetaire_entree','id');
                fill($('#edit-donnee'), [], 'donnee_ligne_budgetaire_entree','id');
                $('#edit-id-donnee-budgetaire-hidden').val('');
                if (!id) return;
                $.get("{{ url('ajax/budget') }}/"+id+"/lignes").done(function(items){
                    fill($('#edit-ligne'), items, 'libelle_ligne_budgetaire_entree', 'id');
                });
            });

            $('#edit-ligne').on('change', function(){
                const id = $(this).val();
                fill($('#edit-element'), [], 'libelle_elements_ligne_budgetaire_entree', 'id');
                fill($('#edit-donnee-budgetaire'), [], 'donnee_ligne_budgetaire_entree','id');
                fill($('#edit-donnee'), [], 'donnee_ligne_budgetaire_entree','id');
                $('#edit-id-donnee-budgetaire-hidden').val('');
                if (!id) return;
                $.get("{{ url('ajax/ligne') }}/"+id+"/elements").done(function(items){
                    fill($('#edit-element'), items, 'libelle_elements_ligne_budgetaire_entree', 'id');
                });
            });

            $('#edit-element').on('change', function(){
                fill($('#edit-donnee-budgetaire'), [], 'donnee_ligne_budgetaire_entree','id');
                fill($('#edit-donnee'), [], 'donnee_ligne_budgetaire_entree','id');
                $('#edit-id-donnee-budgetaire-hidden').val('');

                const ide = $(this).val(), idb = $('#edit-budget').val();
                if (!ide || !idb) return;

                $.get("{{ route('ajax_donnees_budgetaires_by_element', '_ID_') }}".replace('_ID_', ide), { id_budget: idb })
                    .done(function(rows){
                        fill($('#edit-donnee-budgetaire'), rows, 'donnee_ligne_budgetaire_entree', 'id');
                    });
            });

            $('#edit-donnee-budgetaire').on('change', function(){
                const ide = $('#edit-element').val(),
                    idb = $('#edit-budget').val(),
                    iddb = $(this).val();

                $('#edit-id-donnee-budgetaire-hidden').val(iddb || '');
                fill($('#edit-donnee'), [], 'donnee_ligne_budgetaire_entree','id');

                if (!ide || !idb || !iddb) return;

                $.get("{{ route('ajax_donnees_ligne_by_element', '_ID_') }}".replace('_ID_', ide),
                    { id_budget: idb, id_donnee_budgetaire: iddb })
                    .done(function(rows){
                        fill($('#edit-donnee'), rows, 'donnee_ligne_budgetaire_entree', 'id');
                    });
            });

            // Pré-sélection en édition
            function presetBudgetDonneesForEdit(selBudget, selLigne, selElt, selDonBudget, selDonLigne) {
                $('#edit-budget').val(selBudget).trigger('change');

                $.get("{{ url('ajax/budget') }}/"+selBudget+"/lignes").done(function(lignes){
                    fill($('#edit-ligne'), lignes, 'libelle_ligne_budgetaire_entree', 'id');
                    if (selLigne) $('#edit-ligne').val(selLigne);

                    $.get("{{ url('ajax/ligne') }}/"+selLigne+"/elements").done(function(elts){
                        fill($('#edit-element'), elts, 'libelle_elements_ligne_budgetaire_entree', 'id');
                        if (selElt) $('#edit-element').val(selElt);

                        $.get("{{ route('ajax_donnees_budgetaires_by_element', '_ID_') }}".replace('_ID_', selElt), { id_budget: selBudget })
                            .done(function(dbs){
                                fill($('#edit-donnee-budgetaire'), dbs, 'donnee_ligne_budgetaire_entree', 'id');
                                if (selDonBudget) $('#edit-donnee-budgetaire').val(selDonBudget);
                                $('#edit-id-donnee-budgetaire-hidden').val(selDonBudget || '');

                                $.get("{{ route('ajax_donnees_ligne_by_element', '_ID_') }}".replace('_ID_', selElt),
                                    { id_budget: selBudget, id_donnee_budgetaire: selDonBudget })
                                    .done(function(dls){
                                        fill($('#edit-donnee'), dls, 'donnee_ligne_budgetaire_entree', 'id');
                                        if (selDonLigne) $('#edit-donnee').val(selDonLigne);
                                    });
                            });
                    });
                });
            }

            // Pédago en édition
            function presetPedagoForEdit(cycle, filiere, selectedNiv, selectedSpe, selectedSco) {
                if (cycle && filiere) {
                    $.get("{{ route('ajax_scolarite_filters') }}", { id_cycle: cycle, id_filiere: filiere })
                        .done(function(res){
                            fillSelect($('#edit-niveau'), (res||{}).niveaux, 'nom_niveau', 'id');
                            if (selectedNiv) $('#edit-niveau').val(selectedNiv);

                            fillSelect($('#edit-specialite'), (res||{}).specialites, 'nom_specialite', 'id');
                            if (selectedSpe) $('#edit-specialite').val(selectedSpe);

                            fillSelect($('#edit-scolarite'), (res||{}).scolarites, 'label', 'id', true);
                            if (selectedSco) $('#edit-scolarite').val(selectedSco);
                        });
                }
            }

            // Ouvre modal édition
            window.openEditFacture = function(el){
                const $b = $(el);
                $('#edit-id').val($b.data('id'));
                $('#edit-date').val($b.data('date'));
                $('#edit-entite').val($b.data('entite'));
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

                // montant info
                const montantInfo = $b.data('montant');
                $('#edit-montant-info').val(montantInfo);

                // Frais présélection
                const selectedFrs = $b.data('frais');
                if (selectedFrs) {
                    $('#edit-frais').val(selectedFrs).trigger('change');
                    const mt  = $('#edit-frais option:selected').data('montant') || '';
                    $('#edit-id-frais-hidden').val(selectedFrs);
                    $('#edit-montant-frais').val(mt);
                }

                // Budget présélection complète
                presetBudgetDonneesForEdit(
                    $b.data('budget'), $b.data('ligne'), $b.data('element'),
                    $b.data('donnee-budgetaire'), $b.data('donnee')
                );

                // Pédagogie présélection
                presetPedagoForEdit(cycle, filiere, selectedNiv, selectedSpe, selectedSco);

                $('#edit_facture').modal('show');
                return false;
            };

        })();
    </script>
@endsection
@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('etudiant_management') }}"><strong>liste des étudiants</strong></a></li>
        <li><a href="{{ route('etudiant') }}"><strong>Gestion des étudiants</strong></a></li>
        <li class="active"><strong>{{ $title }}</strong></li>
    </ol>
@endsection
