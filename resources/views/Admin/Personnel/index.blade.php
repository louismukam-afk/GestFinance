{{--@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="text-primary">👥 {{ $title }}</h3>

        <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" href="#add_personnel">
            ➕ Nouveau Personnel
        </button>

        <a href="{{ route('personnels.import') }}" class="btn btn-success">
            Importer Excel
        </a>

        <div class="table-responsive mt-3">
            <table id="personnelsTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Sexe</th>
                    <th>Date/Lieu Naissance</th>
                    <th>Adresse</th>
                    <th>Contact</th>
                    <th>Statut Matrimonial</th>
                    <th>Diplôme / Niveau</th>
                    <th>Domaine</th>
                    <th>Nationalité</th>
                    <th>Date Recrutement</th>
                    <th>Type</th>
                    <th>Horaire</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($personnels as $i => $p)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $p->nom }}</td>
                        <td>{{ $p->sexe }}</td>
                        <td>{{ \Carbon\Carbon::parse($p->date_naissance)->format('d/m/Y') }}<br><small>{{ $p->lieu_naissance }}</small></td>
                        <td>{{ $p->adresse }}</td>
                        <td>
                            <div>{{ $p->telephone }}</div>
                            @if($p->telephone_whatsapp)<small>WhatsApp: {{ $p->telephone_whatsapp }}</small>@endif
                            @if($p->email)<div><small>{{ $p->email }}</small></div>@endif
                            @if($p->numero_cnps)<div><small>CNPS: {{ $p->numero_cnps }}</small></div>@endif
                            @if($p->numero_contribuable)<div><small>Contribuable: {{ $p->numero_contribuable }}</small></div>@endif
                        </td>
                        <td>{{ $p->statut_matrimonial }}</td>
                        <td>{{ $p->diplome }}<br><small>{{ $p->niveau_etude }}</small></td>
                        <td>{{ $p->domaine_formation }}</td>
                        <td>{{ $p->nationalite }}</td>
                        <td>{{ \Carbon\Carbon::parse($p->date_recrutement)->format('d/m/Y') }}</td>
                        <td>{{ ucfirst($p->type_personnel ?? 'permanent') }}</td>
                        <td>{{ ucfirst($p->mode_horaire ?? 'strict') }}<br><small>{{ str_replace('_', ' ', $p->categorie_horaire ?? 'standard') }} / {{ str_replace('_', ' ', $p->horaire_travail ?? '-') }}</small></td>
                        <td>
                            <a href="#edit_personnel" data-toggle="modal" data-backdrop="false"
                               onclick="editPersonnel({{ $p->id }},
                                       `{{ $p->nom }}`,
                                       `{{ $p->date_naissance }}`,
                                       `{{ $p->lieu_naissance }}`,
                                       `{{ $p->adresse }}`,
                                       `{{ $p->sexe }}`,
                                       `{{ $p->statut_matrimonial }}`,
                                       `{{ $p->email }}`,
                                       `{{ $p->telephone }}`,
                                       `{{ $p->telephone_whatsapp }}`,
                                       `{{ $p->diplome }}`,
                                       `{{ $p->niveau_etude }}`,
                                       `{{ $p->domaine_formation }}`,
                                       `{{ $p->date_recrutement }}`,
                                        `{{ $p->nationalite }}`,
                                        `{{ $p->type_personnel ?? 'permanent' }}`
                                       )" class="btn btn-xs btn-warning">✏️</a>

                            <form action="{{ route('delete_personnel', $p->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-xs btn-danger" onclick="return confirm('Supprimer ce personnel ?')">🗑️</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    --}}{{-- Modal Ajout --}}{{--
    <div class="modal fade" id="add_personnel">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form method="POST" action="{{ route('store_personnel') }}">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title">➕ Nouveau Personnel</h4>
                        <button type="button" class="close" data-dismiss="modal">x</button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4"><label>Nom</label><input type="text" name="nom" class="form-control" required></div>
                            <div class="col-md-4"><label>Date de naissance</label><input type="date" name="date_naissance" class="form-control" required></div>
                            <div class="col-md-4"><label>Lieu de naissance</label><input type="text" name="lieu_naissance" class="form-control" required></div>

                            <div class="col-md-4"><label>Adresse</label><input type="text" name="adresse" class="form-control" required></div>
                            <div class="col-md-4">
                                <label>Sexe</label>
                                <select name="sexe" class="form-control" required>
                                    <option value="Masculin">Masculin</option>
                                    <option value="Féminin">Féminin</option>
                                    <option value="Autre">Autre</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Statut matrimonial</label>
                                <select name="statut_matrimonial" class="form-control" required>
                                    <option value="Célibataire">Célibataire</option>
                                    <option value="Marié(e)">Marié(e)</option>
                                    <option value="Divorcé(e)">Divorcé(e)</option>
                                    <option value="Veuf(ve)">Veuf(ve)</option>
                                </select>
                            </div>

                            <div class="col-md-4"><label>Email</label><input type="email" name="email" class="form-control"></div>
                            <div class="col-md-4"><label>Téléphone</label><input type="text" name="telephone" class="form-control" required></div>
                            <div class="col-md-4"><label>Téléphone WhatsApp</label><input type="text" name="telephone_whatsapp" class="form-control"></div>
                            <div class="col-md-4"><label>Numero CNPS</label><input type="text" name="numero_cnps" class="form-control"></div>
                            <div class="col-md-4"><label>Numero contribuable</label><input type="text" name="numero_contribuable" class="form-control"></div>
                            <div class="col-md-4"><label>Numero CNPS</label><input type="text" name="numero_cnps" class="form-control"></div>
                            <div class="col-md-4"><label>Numero contribuable</label><input type="text" name="numero_contribuable" class="form-control"></div>

                            <div class="col-md-4"><label>Diplôme</label><input type="text" name="diplome" class="form-control"></div>
                            <div class="col-md-4"><label>Niveau d’étude</label><input type="text" name="niveau_etude" class="form-control"></div>
                            <div class="col-md-4"><label>Domaine de formation</label><input type="text" name="domaine_formation" class="form-control"></div>

                            <div class="col-md-4"><label>Date de recrutement</label><input type="date" name="date_recrutement" class="form-control" required></div>
                            <div class="col-md-4"><label>Nationalité</label><input type="text" name="nationalite" class="form-control" required></div>
                            <div class="col-md-4"><label>Type personnel</label><select name="type_personnel" class="form-control" required><option value="permanent">Permanent</option><option value="vacataire">Vacataire</option></select></div>
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
    <div class="modal fade" id="edit_personnel">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form method="POST" action="{{ route('update_personnel') }}" id="editPersonnelForm">
                    @csrf
                    <input type="hidden" name="id" id="edit-id">

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">x</button>

                        <h4 class="modal-title">✏️ Modifier Personnel</h4>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4"><label>Nom</label><input type="text" name="nom" id="edit-nom" class="form-control" required></div>
                            <div class="col-md-4"><label>Date de naissance</label><input type="date" name="date_naissance" id="edit-dn" class="form-control" required></div>
                            <div class="col-md-4"><label>Lieu de naissance</label><input type="text" name="lieu_naissance" id="edit-ln" class="form-control" required></div>

                            <div class="col-md-4"><label>Adresse</label><input type="text" name="adresse" id="edit-adresse" class="form-control" required></div>
                            <div class="col-md-4">
                                <label>Sexe</label>
                                <select name="sexe" id="edit-sexe" class="form-control" required>
                                    <option value="Masculin">Masculin</option>
                                    <option value="Féminin">Féminin</option>
                                    <option value="Autre">Autre</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Statut matrimonial</label>
                                <select name="statut_matrimonial" id="edit-statut" class="form-control" required>
                                    <option value="Célibataire">Célibataire</option>
                                    <option value="Marié(e)">Marié(e)</option>
                                    <option value="Divorcé(e)">Divorcé(e)</option>
                                    <option value="Veuf(ve)">Veuf(ve)</option>
                                </select>
                            </div>

                            <div class="col-md-4"><label>Email</label><input type="email" name="email" id="edit-email" class="form-control"></div>
                            <div class="col-md-4"><label>Téléphone</label><input type="text" name="telephone" id="edit-telephone" class="form-control" required></div>
                            <div class="col-md-4"><label>Téléphone WhatsApp</label><input type="text" name="telephone_whatsapp" id="edit-whatsapp" class="form-control"></div>
                            <div class="col-md-4"><label>Numero CNPS</label><input type="text" name="numero_cnps" id="edit-numero-cnps" class="form-control"></div>
                            <div class="col-md-4"><label>Numero contribuable</label><input type="text" name="numero_contribuable" id="edit-numero-contribuable" class="form-control"></div>
                            <div class="col-md-4"><label>Numero CNPS</label><input type="text" name="numero_cnps" id="edit-numero-cnps" class="form-control"></div>
                            <div class="col-md-4"><label>Numero contribuable</label><input type="text" name="numero_contribuable" id="edit-numero-contribuable" class="form-control"></div>

                            <div class="col-md-4"><label>Diplôme</label><input type="text" name="diplome" id="edit-diplome" class="form-control"></div>
                            <div class="col-md-4"><label>Niveau d’étude</label><input type="text" name="niveau_etude" id="edit-niveau" class="form-control"></div>
                            <div class="col-md-4"><label>Domaine de formation</label><input type="text" name="domaine_formation" id="edit-domaine" class="form-control"></div>

                            <div class="col-md-4"><label>Date de recrutement</label><input type="date" name="date_recrutement" id="edit-dr" class="form-control" required></div>
                            <div class="col-md-4"><label>Nationalité</label><input type="text" name="nationalite" id="edit-nationalite" class="form-control" required></div>
                            <div class="col-md-4"><label>Type personnel</label><select name="type_personnel" id="edit-type-personnel" class="form-control" required><option value="permanent">Permanent</option><option value="vacataire">Vacataire</option></select></div>
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
            const $table = $('#personnelsTable');
            if ($table.length) {
                $table.DataTable({
                    responsive: true,
                    dom: 'Bfrtip',
                    pageLength: 25,
                    buttons: [
                        { extend: 'excelHtml5', text: '📊 Excel', className: 'btn btn-success btn-sm',
                            title: 'LISTE DU PERSONNEL', exportOptions: { columns: ':not(:last-child)' } },
                        { extend: 'pdfHtml5',   text: '📄 PDF',   className: 'btn btn-danger btn-sm',
                            title: 'LISTE DU PERSONNEL', exportOptions: { columns: ':not(:last-child)' } },
                        { extend: 'print',      text: '🖨 Imprimer', className: 'btn btn-info btn-sm',
                            title: 'LISTE DU PERSONNEL', exportOptions: { columns: ':not(:last-child)' } },
                    ],
                    language: { url: "{{ asset('js/datatables/fr-FR.json') }}" }
                });
            }

            // Remplissage du modal d'édition
            window.editPersonnel = function(id, nom, dn, ln, adresse, sexe, statut, email, tel, whatsapp, diplome, niveau, domaine, dr, nationalite, typePersonnel) {
                $('#edit-id').val(id);
                $('#edit-nom').val(nom);
                $('#edit-dn').val(dn);
                $('#edit-ln').val(ln);
                $('#edit-adresse').val(adresse);
                $('#edit-sexe').val(sexe);
                $('#edit-statut').val(statut);
                $('#edit-email').val(email);
                $('#edit-telephone').val(tel);
                $('#edit-whatsapp').val(whatsapp);
                $('#edit-diplome').val(diplome);
                $('#edit-niveau').val(niveau);
                $('#edit-domaine').val(domaine);
                $('#edit-dr').val(dr);
                $('#edit-nationalite').val(nationalite);
                $('#edit-type-personnel').val(typePersonnel || 'permanent');
            };
        })();
    </script>
@endsection--}}
@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="text-primary">👥 {{ $title }}</h3>

        <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" href="#add_personnel">
            ➕ Nouveau Personnel
        </button>

        <div class="table-responsive mt-3">
            <table id="personnelsTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Sexe</th>
                    <th>Date/Lieu Naissance</th>
                    <th>Adresse</th>
                    <th>Contact</th>
                    <th>Statut Matrimonial</th>
                    <th>Diplôme / Niveau</th>
                    <th>Domaine</th>
                    <th>Nationalité</th>
                    <th>Date Recrutement</th>
                    <th>Type</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($personnels as $i => $p)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $p->nom }}</td>
                        <td>{{ $p->sexe }}</td>
                        <td>{{ \Carbon\Carbon::parse($p->date_naissance)->format('d/m/Y') }}<br><small>{{ $p->lieu_naissance }}</small></td>
                        <td>{{ $p->adresse }}</td>
                        <td>
                            <div>{{ $p->telephone }}</div>
                            @if($p->telephone_whatsapp)<small>WhatsApp: {{ $p->telephone_whatsapp }}</small>@endif
                            @if($p->email)<div><small>{{ $p->email }}</small></div>@endif
                            @if($p->numero_cnps)<div><small>CNPS: {{ $p->numero_cnps }}</small></div>@endif
                            @if($p->numero_contribuable)<div><small>Contribuable: {{ $p->numero_contribuable }}</small></div>@endif
                        </td>
                        <td>{{ $p->statut_matrimonial }}</td>
                        <td>{{ $p->diplome }}<br><small>{{ $p->niveau_etude }}</small></td>
                        <td>{{ $p->domaine_formation }}</td>
                        <td>{{ $p->nationalite }}</td>
                        <td>{{ \Carbon\Carbon::parse($p->date_recrutement)->format('d/m/Y') }}</td>
                        <td>{{ ucfirst($p->type_personnel ?? 'permanent') }}</td>
                        <td>
                            <a href="#edit_personnel"
                               class="btn btn-xs btn-warning"
                               data-toggle="modal" data-backdrop="false"
                               data-id="{{ $p->id }}"
                               data-nom="{{ e($p->nom) }}"
                               data-dn="{{ $p->date_naissance }}"
                               data-ln="{{ e($p->lieu_naissance) }}"
                               data-adresse="{{ e($p->adresse) }}"
                               data-sexe="{{ $p->sexe }}"
                               data-statut="{{ $p->statut_matrimonial }}"
                               data-email="{{ $p->email }}"
                               data-telephone="{{ $p->telephone }}"
                               data-whatsapp="{{ $p->telephone_whatsapp }}"
                               data-numero-cnps="{{ $p->numero_cnps }}"
                               data-numero-contribuable="{{ $p->numero_contribuable }}"
                               data-diplome="{{ e($p->diplome) }}"
                               data-niveau="{{ e($p->niveau_etude) }}"
                               data-domaine="{{ e($p->domaine_formation) }}"
                               data-dr="{{ $p->date_recrutement }}"
                               data-nationalite="{{ e($p->nationalite) }}"
                               data-type-personnel="{{ $p->type_personnel ?? 'permanent' }}"
                               data-mode-horaire="{{ $p->mode_horaire ?? 'strict' }}"
                               data-categorie-horaire="{{ $p->categorie_horaire ?? 'standard' }}"
                               data-horaire-travail="{{ $p->horaire_travail }}"
                               onclick="return openEdit(this);">✏️</a>

                            <form action="{{ route('delete_personnel', $p->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-xs btn-danger" onclick="return confirm('Supprimer ce personnel ?')">🗑️</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Ajout --}}
    <div class="modal fade" id="add_personnel">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form method="POST" action="{{ route('store_personnel') }}">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title">➕ Nouveau Personnel</h4>
                        <button type="button" class="close" data-dismiss="modal">x</button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4"><label>Nom</label><input type="text" name="nom" class="form-control" required></div>
                            <div class="col-md-4"><label>Date de naissance</label><input type="date" name="date_naissance" class="form-control" required></div>
                            <div class="col-md-4"><label>Lieu de naissance</label><input type="text" name="lieu_naissance" class="form-control" required></div>

                            <div class="col-md-4"><label>Adresse</label><input type="text" name="adresse" class="form-control" required></div>
                            <div class="col-md-4">
                                <label>Sexe</label>
                                <select name="sexe" class="form-control" required>
                                    <option value="Masculin">Masculin</option>
                                    <option value="Féminin">Féminin</option>
                                    <option value="Autre">Autre</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Statut matrimonial</label>
                                <select name="statut_matrimonial" class="form-control" required>
                                    <option value="Célibataire">Célibataire</option>
                                    <option value="Marié(e)">Marié(e)</option>
                                    <option value="Divorcé(e)">Divorcé(e)</option>
                                    <option value="Veuf(ve)">Veuf(ve)</option>
                                </select>
                            </div>

                            <div class="col-md-4"><label>Email</label><input type="email" name="email" class="form-control"></div>
                            <div class="col-md-4"><label>Téléphone</label><input type="text" name="telephone" class="form-control" required></div>
                            <div class="col-md-4"><label>Téléphone WhatsApp</label><input type="text" name="telephone_whatsapp" class="form-control"></div>
                            <div class="col-md-4"><label>Numero CNPS</label><input type="text" name="numero_cnps" class="form-control"></div>
                            <div class="col-md-4"><label>Numero contribuable</label><input type="text" name="numero_contribuable" class="form-control"></div>

                            <div class="col-md-4"><label>Diplôme</label><input type="text" name="diplome" class="form-control"></div>
                            <div class="col-md-4"><label>Niveau d’étude</label><input type="text" name="niveau_etude" class="form-control"></div>
                            <div class="col-md-4"><label>Domaine de formation</label><input type="text" name="domaine_formation" class="form-control"></div>

                            <div class="col-md-4"><label>Date de recrutement</label><input type="date" name="date_recrutement" class="form-control" required></div>
                            <div class="col-md-4"><label>Nationalité</label><input type="text" name="nationalite" class="form-control" required></div>
                            <div class="col-md-4"><label>Type personnel</label><select name="type_personnel" class="form-control" required><option value="permanent">Permanent</option><option value="vacataire">Vacataire</option></select></div>
                            <div class="col-md-4"><label>Mode horaire</label><select name="mode_horaire" class="form-control"><option value="strict">Strict</option><option value="souple">Souple</option></select></div>
                            <div class="col-md-4"><label>Categorie horaire</label><select name="categorie_horaire" class="form-control"><option value="standard">Standard</option><option value="conseil_administration">Conseil d'administration</option><option value="chef_service">Chef de service</option><option value="coordination">Membre de coordination</option></select></div>
                            <div class="col-md-4"><label>Horaire travail</label><select name="horaire_travail" class="form-control"><option value="">-- Aucun --</option><option value="permanent_jour">Permanent jour</option><option value="permanent_soir">Permanent soir</option><option value="vacataire_jour">Vacataire jour</option><option value="vacataire_soir">Vacataire soir</option></select></div>
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
    <div class="modal fade" id="edit_personnel">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form method="POST" action="{{ route('update_personnel') }}" id="editPersonnelForm">
                    @csrf
                    <input type="hidden" name="id" id="edit-id">

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">x</button>
                        <h4 class="modal-title">✏️ Modifier Personnel</h4>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4"><label>Nom</label><input type="text" name="nom" id="edit-nom" class="form-control" required></div>
                            <div class="col-md-4"><label>Date de naissance</label><input type="date" name="date_naissance" id="edit-dn" class="form-control" required></div>
                            <div class="col-md-4"><label>Lieu de naissance</label><input type="text" name="lieu_naissance" id="edit-ln" class="form-control" required></div>

                            <div class="col-md-4"><label>Adresse</label><input type="text" name="adresse" id="edit-adresse" class="form-control" required></div>
                            <div class="col-md-4">
                                <label>Sexe</label>
                                <select name="sexe" id="edit-sexe" class="form-control" required>
                                    <option value="Masculin">Masculin</option>
                                    <option value="Féminin">Féminin</option>
                                    <option value="Autre">Autre</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Statut matrimonial</label>
                                <select name="statut_matrimonial" id="edit-statut" class="form-control" required>
                                    <option value="Célibataire">Célibataire</option>
                                    <option value="Marié(e)">Marié(e)</option>
                                    <option value="Divorcé(e)">Divorcé(e)</option>
                                    <option value="Veuf(ve)">Veuf(ve)</option>
                                </select>
                            </div>

                            <div class="col-md-4"><label>Email</label><input type="email" name="email" id="edit-email" class="form-control"></div>
                            <div class="col-md-4"><label>Téléphone</label><input type="text" name="telephone" id="edit-telephone" class="form-control" required></div>
                            <div class="col-md-4"><label>Téléphone WhatsApp</label><input type="text" name="telephone_whatsapp" id="edit-whatsapp" class="form-control"></div>
                            <div class="col-md-4"><label>Numero CNPS</label><input type="text" name="numero_cnps" id="edit-numero-cnps" class="form-control"></div>
                            <div class="col-md-4"><label>Numero contribuable</label><input type="text" name="numero_contribuable" id="edit-numero-contribuable" class="form-control"></div>

                            <div class="col-md-4"><label>Diplôme</label><input type="text" name="diplome" id="edit-diplome" class="form-control"></div>
                            <div class="col-md-4"><label>Niveau d’étude</label><input type="text" name="niveau_etude" id="edit-niveau" class="form-control"></div>
                            <div class="col-md-4"><label>Domaine de formation</label><input type="text" name="domaine_formation" id="edit-domaine" class="form-control"></div>

                            <div class="col-md-4"><label>Date de recrutement</label><input type="date" name="date_recrutement" id="edit-dr" class="form-control" required></div>
                            <div class="col-md-4"><label>Nationalité</label><input type="text" name="nationalite" id="edit-nationalite" class="form-control" required></div>
                            <div class="col-md-4"><label>Type personnel</label><select name="type_personnel" id="edit-type-personnel" class="form-control" required><option value="permanent">Permanent</option><option value="vacataire">Vacataire</option></select></div>
                            <div class="col-md-4"><label>Mode horaire</label><select name="mode_horaire" id="edit-mode-horaire" class="form-control"><option value="strict">Strict</option><option value="souple">Souple</option></select></div>
                            <div class="col-md-4"><label>Categorie horaire</label><select name="categorie_horaire" id="edit-categorie-horaire" class="form-control"><option value="standard">Standard</option><option value="conseil_administration">Conseil d'administration</option><option value="chef_service">Chef de service</option><option value="coordination">Membre de coordination</option></select></div>
                            <div class="col-md-4"><label>Horaire travail</label><select name="horaire_travail" id="edit-horaire-travail" class="form-control"><option value="">-- Aucun --</option><option value="permanent_jour">Permanent jour</option><option value="permanent_soir">Permanent soir</option><option value="vacataire_jour">Vacataire jour</option><option value="vacataire_soir">Vacataire soir</option></select></div>
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
            // ——— DataTables init (avec garde)
            $(function() {
                try {
                    if (window.jQuery && $.fn && $.fn.DataTable) {
                        const $table = $('#personnelsTable');
                        if ($table.length) {
                            $table.DataTable({
//                                responsive: true,
                                responsive: false,
                                scrollX: true,
                                autoWidth: false,
                                dom: 'Bfrtip',
                                pageLength: 25,
                                buttons: [
                                    { extend: 'excelHtml5', text: '📊 Excel', className: 'btn btn-success btn-sm',
                                        title: 'LISTE DU PERSONNEL', exportOptions: { columns: ':not(:last-child)' } },
                                    { extend: 'pdfHtml5',   text: '📄 PDF',   className: 'btn btn-danger btn-sm',
                                        title: 'LISTE DU PERSONNEL', exportOptions: { columns: ':not(:last-child)' } },
                                    { extend: 'print',      text: '🖨 Imprimer', className: 'btn btn-info btn-sm',
                                        title: 'LISTE DU PERSONNEL', exportOptions: { columns: ':not(:last-child)' } },
                                ],
                                language: { url: "{{ asset('js/datatables/fr-FR.json') }}" }
                            });
                        }
                    } else {
                        console.warn('DataTables non chargé : vérifie l’inclusion de jQuery + DataTables (CSS/JS + extensions Buttons).');
                    }
                } catch (e) {
                    console.error('Erreur DataTables:', e);
                }
            });

            // ——— Handler d’ouverture du modal d’édition via data-*
            window.openEdit = function (el) {
                var $b = $(el);
                $('#edit-id').val($b.data('id'));
                $('#edit-nom').val($b.data('nom'));
                $('#edit-dn').val($b.data('dn'));
                $('#edit-ln').val($b.data('ln'));
                $('#edit-adresse').val($b.data('adresse'));
                $('#edit-sexe').val($b.data('sexe'));
                $('#edit-statut').val($b.data('statut'));
                $('#edit-email').val($b.data('email'));
                $('#edit-telephone').val($b.data('telephone'));
                $('#edit-whatsapp').val($b.data('whatsapp'));
                $('#edit-numero-cnps').val($b.data('numero-cnps'));
                $('#edit-numero-contribuable').val($b.data('numero-contribuable'));
                $('#edit-diplome').val($b.data('diplome'));
                $('#edit-niveau').val($b.data('niveau'));
                $('#edit-domaine').val($b.data('domaine'));
                $('#edit-dr').val($b.data('dr'));
                $('#edit-nationalite').val($b.data('nationalite'));
                $('#edit-type-personnel').val($b.data('type-personnel') || 'permanent');
                $('#edit-mode-horaire').val($b.data('mode-horaire') || 'strict');
                $('#edit-categorie-horaire').val($b.data('categorie-horaire') || 'standard');
                $('#edit-horaire-travail').val($b.data('horaire-travail') || '');

                // Ouvre explicitement le modal (au cas où le data-toggle ne l’ouvre pas)
                $('#edit_personnel').modal('show');
                return false;
            };
        })();
    </script>
@endsection

@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('personnel') }}"><strong>Gestion du personnel</strong></a></li>
        <li class="active"><strong>{{ $title }}</strong></li>
    </ol>
@endsection

