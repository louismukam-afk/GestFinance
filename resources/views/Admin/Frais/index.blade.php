{{--@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="text-primary">💰 {{ $title }}</h3>

        <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" href="#add_frais">
            ➕ Nouveau Frais
        </button>

        <div class="table-responsive mt-3">
            <table id="fraisTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Description</th>
                    <th>Type</th>
                    <th>Montant</th>
                    <th>Date création</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($frais as $i => $f)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $f->nom_frais }}</td>
                        <td>{{ $f->description }}</td>
                        <td>
                        <span class="label {{ $f->type_frais==1 ? 'label-success' : 'label-info' }}">
                            {{ $f->type_label }}
                        </span>
                        </td>
                        <td>
                            @if($f->type_frais==1)
                                {{ number_format($f->montant, 0, ',', ' ') }} FCFA
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $f->created_at->format('d/m/Y') }}</td>
                        <td>
                            <a href="#edit_frais" data-toggle="modal" data-backdrop="false"
                               onclick="editFrais({{ $f->id }}, `{{ $f->nom_frais }}`, `{{ $f->description }}`, `{{ $f->type_frais }}`, `{{ $f->montant }}`)"
                               class="btn btn-xs btn-warning">✏️</a>

                            <form action="{{ route('delete_frais', $f->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-xs btn-danger" onclick="return confirm('Supprimer ce frais ?')">🗑️</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    --}}{{-- Modal Ajout --}}{{--
    <div class="modal fade" id="add_frais">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('store_frais') }}">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title">➕ Nouveau Frais</h4>
                        <button type="button" class="close" data-dismiss="modal">x</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group"><label>Nom du frais</label>
                            <input type="text" name="nom_frais" class="form-control" required>
                        </div>

                        <div class="form-group"><label>Description</label>
                            <textarea name="description" class="form-control"></textarea>
                        </div>

                        <div class="form-group"><label>Type de frais</label>
                            <select name="type_frais" id="type_frais_create" class="form-control" required>
                                <option value="1">Espèce</option>
                                <option value="0">Nature</option>
                            </select>
                        </div>

                        <div class="form-group" id="montant_group_create">
                            <label>Montant (FCFA)</label>
                            <input type="number" name="montant" id="montant_create" class="form-control" min="0" step="0.01">
                            <small class="text-muted">Obligatoire si le type est <strong>Espèce</strong>.</small>
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
    <div class="modal fade" id="edit_frais">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('update_frais') }}">
                    @csrf
                    <input type="hidden" name="id" id="edit-id">

                    <div class="modal-header">
                        <h4 class="modal-title">✏️ Modifier Frais</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group"><label>Nom du frais</label>
                            <input type="text" name="nom_frais" id="edit-nom" class="form-control" required>
                        </div>

                        <div class="form-group"><label>Description</label>
                            <textarea name="description" id="edit-description" class="form-control"></textarea>
                        </div>

                        <div class="form-group"><label>Type de frais</label>
                            <select name="type_frais" id="edit-type" class="form-control" required>
                                <option value="1">Espèce</option>
                                <option value="0">Nature</option>
                            </select>
                        </div>

                        <div class="form-group" id="montant_group_edit">
                            <label>Montant (FCFA)</label>
                            <input type="number" name="montant" id="edit-montant" class="form-control" min="0" step="0.01">
                            <small class="text-muted">Obligatoire si le type est <strong>Espèce</strong>.</small>
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
        (function() {
            // DataTables basique
            const $table = $('#fraisTable');
            if ($table.length) {
                $table.DataTable({
                    responsive: true,
                    dom: 'Bfrtip',
                    pageLength: 25,
                    buttons: [
                        { extend: 'excelHtml5', text: '📊 Excel', className: 'btn btn-success btn-sm',
                            title: 'LISTE DES FRAIS', exportOptions: { columns: ':not(:last-child)' } },
                        { extend: 'pdfHtml5',   text: '📄 PDF',   className: 'btn btn-danger btn-sm',
                            title: 'LISTE DES FRAIS', exportOptions: { columns: ':not(:last-child)' } },
                        { extend: 'print',      text: '🖨 Imprimer', className: 'btn btn-info btn-sm',
                            title: 'LISTE DES FRAIS', exportOptions: { columns: ':not(:last-child)' } },
                    ],
                    language: { url: "{{ asset('js/datatables/fr-FR.json') }}" }
                });
            }

            // Toggle Montant (create)
            const $typeCreate = $('#type_frais_create');
            const $montantGroupCreate = $('#montant_group_create');
            function refreshCreate() {
                if ($typeCreate.val() === '1') { // Espèce
                    $montantGroupCreate.show();
                } else { // Nature
                    $montantGroupCreate.hide();
                    $('#montant_create').val('');
                }
            }
            $typeCreate.on('change', refreshCreate);
            refreshCreate();

            // Toggle Montant (edit)
            const $typeEdit = $('#edit-type');
            const $montantGroupEdit = $('#montant_group_edit');
            function refreshEdit() {
                if ($typeEdit.val() === '1') { // Espèce
                    $montantGroupEdit.show();
                } else { // Nature
                    $montantGroupEdit.hide();
                    $('#edit-montant').val('');
                }
            }
            $typeEdit.on('change', refreshEdit);

            // Remplissage du modal d'édition
            window.editFrais = function(id, nom, description, type, montant) {
                $('#edit-id').val(id);
                $('#edit-nom').val(nom);
                $('#edit-description').val(description);
                $('#edit-type').val(type);
                $('#edit-montant').val(montant);
                refreshEdit();
            };
        })();
    </script>
@endsection--}}
@extends('layouts.app')

@section('content')
    <h3 class="text-primary">💰 {{ $title }}</h3>

    <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" href="#add_frais">
        <span class="glyphicon glyphicon-plus"></span> ➕ Nouveau Frais
    </button>

    <table class="table table-bordered table-striped table-condensed" style="margin-top: 15px;">
        <thead>
        <tr>
            <th>#</th>
            <th>Nom</th>
            <th>Description</th>
            <th>Type</th>
            <th>Montant</th>
            <th>Date création</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        @php $i=1; @endphp
        @foreach($frais as $n)
            <tr>
                <td>{{ $i++ }}</td>
                <td>{{ $n->nom_frais }}</td>
                <td>{{ $n->description }}</td>
                <td>{{ $n->type_label }}</td>
                <td>
                    @if($n->isEspece())
                        <strong>{{ number_format($n->montant, 0, ',', ' ') }}</strong>
                    @else
                        —
                    @endif
                </td>
                <td>{{ $n->created_at->format('d/m/Y') }}</td>
                <td>


                                <a href="#edit_frais" data-toggle="modal" data-backdrop="false"
                                   onclick="editer_frais({{ $n->id }}, `{{ $n->nom_frais }}`, `{{ $n->description }}`, `{{ $n->type_frais }}`, `{{ $n->montant }}`)"
                                   class="btn btn-xs btn-warning">✏️</a>

                                <form action="{{ route('delete_frais', $n->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-xs btn-danger" onclick="return confirm('Supprimer ce frais ?')">🗑️</button>
                                </form>

                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{-- Modal Ajout --}}
    <div class="modal fade" id="add_frais">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="add_frais_form" action="{{ route('store_frais') }}">
                    @csrf
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">x</button>
                        <h4 class="modal-title">Remplir les champs</h4>
                    </div>
                    <div class="modal-body">
                        <fieldset>
                            <div class="form-group">
                                <label for="nom_frais">Nom : <span class="required">*</span></label>
                                <input class="form-control" id="nom_frais" name="nom_frais" type="text" value="{{ old('nom_frais') }}" required>
                            </div>

                            <div class="form-group"><label>Description</label>
                                <textarea name="description" class="form-control">{{ old('description') }}</textarea>
                            </div>

                            {{-- Type via 2 checkboxes exclusives + hidden réel --}}
                            <div class="alert alert-info">
                                <label class="mr-3">
                                    <input type="checkbox" id="chk-nature-create"> Nature
                                </label>
                                <label>
                                    <input type="checkbox" id="chk-espece-create"> Espèce
                                </label>
                                <input type="hidden" name="type_frais" id="type_frais_create" value="0">
                            </div>

                            <div class="form-group" id="montant_form_group" style="display:none">
                                <label>Montant :</label>
                                <input class="form-control" name="montant" type="number" id="montant_frais" value="{{ old('montant') }}" min="0" step="0.01" disabled>
                                <small class="text-muted">Obligatoire si le type est <strong>Espèce</strong>.</small>
                            </div>

                            <input class="btn btn-success" type="submit" value="Confirmer">
                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Édition --}}
    <div class="modal fade" id="edit_frais">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="edit_frais_form" action="{{ route('update_frais') }}">
                    @csrf
                    <input type="hidden" name="id" id="edit_id_frais" value=""/>
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">x</button>
                        <h4 class="modal-title">Remplir les champs</h4>
                    </div>
                    <div class="modal-body">
                        <fieldset>
                            <div class="form-group">
                                <label for="edit_nom">Nom : <span class="required">*</span></label>
                                <input class="form-control" id="edit_nom" name="nom_frais" type="text" required>
                            </div>

                            <div class="form-group"><label>Description</label>
                                <textarea name="description" id="edit-description" class="form-control"></textarea>
                            </div>

                            <div class="alert alert-info">
                                <label class="mr-3">
                                    <input type="checkbox" id="chk-nature-edit"> Nature
                                </label>
                                <label>
                                    <input type="checkbox" id="chk-espece-edit"> Espèce
                                </label>
                                <input type="hidden" name="type_frais" id="edit-type" value="0">
                            </div>

                            <div class="form-group" id="edit-montant_form_group" style="display:none">
                                <label>Montant :</label>
                                <input class="form-control" id="edit_montant" name="montant" type="number" min="0" step="0.01" disabled>
                                <small class="text-muted">Obligatoire si le type est <strong>Espèce</strong>.</small>
                            </div>

                            <input class="btn btn-success" type="submit" value="Confirmer">
                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        (function() {
            // Helpers
            function toggleUI(isEspece, $hidden, $grp, $input, $chkNature, $chkEspece) {
                $chkNature.prop('checked', !isEspece);
                $chkEspece.prop('checked', isEspece);
                $hidden.val(isEspece ? '1' : '0');
                if (isEspece) { $grp.show(); $input.prop('disabled', false); }
                else { $grp.hide(); $input.val(''); $input.prop('disabled', true); }
            }
            function enforceOne($chkNature, $chkEspece, setFn) {
                if (!$chkNature.prop('checked') && !$chkEspece.prop('checked')) setFn(false);
            }

            // ===== CREATE =====
            const $hiddenCreate = $('#type_frais_create');
            const $grpCreate    = $('#montant_form_group');
            const $mntCreate    = $('#montant_frais');
            const $chkNatureC   = $('#chk-nature-create');
            const $chkEspeceC   = $('#chk-espece-create');

            function setCreate(isEspece){ toggleUI(isEspece, $hiddenCreate, $grpCreate, $mntCreate, $chkNatureC, $chkEspeceC); }
            setCreate(false); // Nature par défaut

            $(document).on('change', '#chk-nature-create', function(){ setCreate(false); });
            $(document).on('change', '#chk-espece-create', function(){ setCreate(true); });
            $(document).on('click',  '#chk-nature-create, #chk-espece-create', function(){ setTimeout(()=>enforceOne($chkNatureC, $chkEspeceC, setCreate),0); });

            // ===== EDIT =====
            const $hiddenEdit = $('#edit-type');
            const $grpEdit    = $('#edit-montant_form_group');
            const $mntEdit    = $('#edit_montant');
            const $chkNatureE = $('#chk-nature-edit');
            const $chkEspeceE = $('#chk-espece-edit');

            function setEdit(isEspece){ toggleUI(isEspece, $hiddenEdit, $grpEdit, $mntEdit, $chkNatureE, $chkEspeceE); }

            // Ouvrir modal => synchroniser UI
            $('#edit_frais').on('shown.bs.modal', function() {
                const typeVal = String($hiddenEdit.val());
                setEdit(typeVal === '1');
            });

            $(document).on('change', '#chk-nature-edit', function(){ setEdit(false); });
            $(document).on('change', '#chk-espece-edit', function(){ setEdit(true); });
            $(document).on('click',  '#chk-nature-edit, #chk-espece-edit', function(){ setTimeout(()=>enforceOne($chkNatureE, $chkEspeceE, setEdit),0); });

            // Remplissage du modal d'édition — ORDRE OK: (id, nom, description, type, montant)
            window.editer_frais = function(id, nom_frais, description, type_frais, montant) {
                $('#edit_id_frais').val(id);
                $('#edit_nom').val(nom_frais);
                $('#edit-description').val(description || '');
                $('#edit-type').val(String(type_frais));   // '0' ou '1'
                $('#edit_montant').val(montant || '');
                setEdit(String(type_frais) === '1');       // positionner l'UI
            };
        })();
    </script>
@endsection




@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('dashboard') }}"><strong>Administration</strong></a></li>
        {{--<li><a href="{{ route('budget') }}"><strong>budget</strong></a></li>--}}

        <li class="active"><strong>{{ $title }}</strong></li>
    </ol>
@endsection