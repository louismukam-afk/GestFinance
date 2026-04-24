@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="text-primary">🎓 {{ $title }}</h3>

        <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" href="#add_etudiant">
            ➕ Nouvel Étudiant
        </button>

        <div class="table-responsive mt-3">
            <table id="etudiantsTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Photo</th>
                    <th>Nom</th>
                    <th>Matricule</th>
                    <th>Téléphone WhatsApp</th>
                    <th>Date/Lieu Naissance</th>
                    <th>Sexe</th>
                    <th>Email</th>
                    <th>Adresse</th>
                    <th>Origine (Dept / Région)</th>
                    <th>Tuteur (Nom/Tél)</th>
                    <th>Père (Nom/Tél)</th>
                    <th>Mère (Nom/Tél)</th>
                    <th>Dernier Établissement</th>
                    <th>Date création</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($etudiants as $i => $e)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>
                            @if($e->photo)
                                <img src="{{ asset($e->photo) }}" alt="Photo" width="50" height="50" style="object-fit:cover;border-radius:6px;">
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $e->nom }}</td>
                        <td>{{ $e->matricule }}</td>
                        <td>{{ $e->telephone_whatsapp }}</td>
                        <td>{{ \Carbon\Carbon::parse($e->date_naissance)->format('d/m/Y') }}<br><small>{{ $e->lieu_naissance }}</small></td>
                        <td>{{ $e->sexe }}</td>
                        <td>{{ $e->email }}</td>
                        <td>{{ $e->adresse }}</td>
                        <td>{{ $e->departement_origine }} / {{ $e->region_origine }}</td>
                        <td>{{ $e->nom_tuteur }}<br><small>{{ $e->telephone_tuteur }}</small></td>
                        <td>{{ $e->nom_pere }}<br><small>{{ $e->telephone_whatsapp_pere }}</small></td>
                        <td>{{ $e->nom_mere }}<br><small>{{ $e->telephone_2_etudiants }}</small></td>
                        <td>{{ $e->dernier_etablissement_frequente }}</td>
                        <td>{{ $e->created_at->format('d/m/Y') }}</td>
                        <td>
                            <a href="#edit_etudiant" data-toggle="modal" data-backdrop="false"
                               onclick="editEtudiant({{ $e->id }},
                                       `{{ $e->nom }}`,
                                       `{{ $e->matricule }}`,
                                       `{{ $e->telephone_whatsapp }}`,
                                       `{{ $e->date_naissance }}`,
                                       `{{ $e->lieu_naissance }}`,
                                       `{{ $e->sexe }}`,
                                       `{{ $e->email }}`,
                                       `{{ $e->adresse }}`,
                                       `{{ $e->departement_origine }}`,
                                       `{{ $e->region_origine }}`,
                                       `{{ $e->nom_pere }}`,
                                       `{{ $e->telephone_whatsapp_pere }}`,
                                       `{{ $e->nom_mere }}`,
                                       `{{ $e->nom_tuteur }}`,
                                       `{{ $e->telephone_tuteur }}`,
                                       `{{ $e->telephone_2_etudiants }}`,
                                       `{{ $e->adresse_tuteur }}`,
                                       `{{ $e->dernier_etablissement_frequente }}`
                                       )"
                               class="btn btn-xs btn-warning">✏️</a>
                            <a href="{{ route('factures_by_etudiant', $e->id) }}" class="btn btn-xs btn-primary">💳 Factures</a>

                            <form action="{{ route('delete_etudiant', $e->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-xs btn-danger" onclick="return confirm('Supprimer cet étudiant ?')">🗑️</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Ajout --}}
    <div class="modal fade" id="add_etudiant">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form method="POST" action="{{ route('store_etudiant') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title">➕ Nouvel Étudiant</h4>
                        <button type="button" class="close" data-dismiss="modal">x</button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4"><label>Nom</label><input type="text" name="nom" class="form-control" required></div>
                            <div class="col-md-4"><label>Matricule</label><input type="text" name="matricule" class="form-control" disabled></div>
                            <div class="col-md-4"><label>Téléphone WhatsApp</label><input type="text" name="telephone_whatsapp" class="form-control" required></div>

                            <div class="col-md-4"><label>Date de naissance</label><input type="date" name="date_naissance" class="form-control" required></div>
                            <div class="col-md-4"><label>Lieu de naissance</label><input type="text" name="lieu_naissance" class="form-control" required></div>
                            <div class="col-md-4">
                                <label>Sexe</label>
                                <select name="sexe" class="form-control" required>
                                    <option value="Masculin">Masculin</option>
                                    <option value="Féminin">Féminin</option>
                                    <option value="Autre">Autre</option>
                                </select>
                            </div>

                            <div class="col-md-4"><label>Email</label><input type="email" name="email" class="form-control"></div>
                            <div class="col-md-4"><label>Adresse</label><input type="text" name="adresse" class="form-control"></div>
                            <div class="col-md-4"><label>Département d’origine</label><input type="text" name="departement_origine" class="form-control"></div>

                            <div class="col-md-4"><label>Région d’origine</label><input type="text" name="region_origine" class="form-control"></div>
                            <div class="col-md-4"><label>Nom du père</label><input type="text" name="nom_pere" class="form-control"></div>
                            <div class="col-md-4"><label>Tél. WhatsApp père</label><input type="text" name="telephone_whatsapp_pere" class="form-control"></div>

                            <div class="col-md-4"><label>Nom de la mère</label><input type="text" name="nom_mere" class="form-control"></div>
                            <div class="col-md-4"><label>Nom du tuteur</label><input type="text" name="nom_tuteur" class="form-control"></div>
                            <div class="col-md-4"><label>Téléphone tuteur</label><input type="text" name="telephone_tuteur" class="form-control"></div>

                            <div class="col-md-4"><label>Téléphone 2 Étudiant</label><input type="text" name="telephone_2_etudiants" class="form-control"></div>
                            <div class="col-md-4"><label>Adresse du tuteur</label><input type="text" name="adresse_tuteur" class="form-control"></div>
                            <div class="col-md-4"><label>Dernier établissement fréquenté</label><input type="text" name="dernier_etablissement_frequente" class="form-control"></div>

                            <div class="col-md-4"><label>Photo</label><input type="file" name="photo" class="form-control"></div>
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
    <div class="modal fade" id="edit_etudiant">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form method="POST" action="{{ route('update_etudiant') }}" enctype="multipart/form-data" id="editEtudiantForm">
                    @csrf
                    <input type="hidden" name="id" id="edit-id">
                    <div class="modal-header">
                        <h4 class="modal-title">✏️ Modifier Étudiant</h4>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4"><label>Nom</label><input type="text" name="nom" id="edit-nom" class="form-control" required></div>
                            <div class="col-md-4"><label>Matricule</label><input type="text" name="matricule" id="edit-matricule" class="form-control" ></div>
                            <div class="col-md-4"><label>Téléphone WhatsApp</label><input type="text" name="telephone_whatsapp" id="edit-telw" class="form-control" required></div>

                            <div class="col-md-4"><label>Date de naissance</label><input type="date" name="date_naissance" id="edit-dn" class="form-control" required></div>
                            <div class="col-md-4"><label>Lieu de naissance</label><input type="text" name="lieu_naissance" id="edit-ln" class="form-control" required></div>
                            <div class="col-md-4">
                                <label>Sexe</label>
                                <select name="sexe" id="edit-sexe" class="form-control" required>
                                    <option value="Masculin">Masculin</option>
                                    <option value="Féminin">Féminin</option>
                                    <option value="Autre">Autre</option>
                                </select>
                            </div>

                            <div class="col-md-4"><label>Email</label><input type="email" name="email" id="edit-email" class="form-control"></div>
                            <div class="col-md-4"><label>Adresse</label><input type="text" name="adresse" id="edit-adresse" class="form-control"></div>
                            <div class="col-md-4"><label>Département d’origine</label><input type="text" name="departement_origine" id="edit-dept" class="form-control"></div>

                            <div class="col-md-4"><label>Région d’origine</label><input type="text" name="region_origine" id="edit-region" class="form-control"></div>
                            <div class="col-md-4"><label>Nom du père</label><input type="text" name="nom_pere" id="edit-np" class="form-control"></div>
                            <div class="col-md-4"><label>Tél. WhatsApp père</label><input type="text" name="telephone_whatsapp_pere" id="edit-twp" class="form-control"></div>

                            <div class="col-md-4"><label>Nom de la mère</label><input type="text" name="nom_mere" id="edit-nm" class="form-control"></div>
                            <div class="col-md-4"><label>Nom du tuteur</label><input type="text" name="nom_tuteur" id="edit-nt" class="form-control"></div>
                            <div class="col-md-4"><label>Téléphone tuteur</label><input type="text" name="telephone_tuteur" id="edit-telt" class="form-control"></div>

                            <div class="col-md-4"><label>Téléphone 2 Étudiant</label><input type="text" name="telephone_2_etudiants" id="edit-tel2" class="form-control"></div>
                            <div class="col-md-4"><label>Adresse du tuteur</label><input type="text" name="adresse_tuteur" id="edit-adrt" class="form-control"></div>
                            <div class="col-md-4"><label>Dernier établissement fréquenté</label><input type="text" name="dernier_etablissement_frequente" id="edit-dernier" class="form-control"></div>

                            <div class="col-md-4"><label>Photo (nouvelle)</label><input type="file" name="photo" class="form-control"></div>
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
        $(function () {
            const $table = $('#etudiantsTable');
            if ($table.length) {
                $table.DataTable({
//                    responsive: true,
                    responsive: false,
                    scrollX: true,
                    autoWidth: false,
                    dom: 'Bfrtip',
                    pageLength: 25,
                    buttons: [
                        { extend: 'excelHtml5', text: '📊 Excel', className: 'btn btn-success btn-sm', title: 'LISTE DES ETUDIANTS', exportOptions: { columns: ':not(:last-child)' } },
                        { extend: 'pdfHtml5',   text: '📄 PDF',   className: 'btn btn-danger btn-sm',  title: 'LISTE DES ETUDIANTS', exportOptions: { columns: ':not(:last-child)' } },
                        { extend: 'print',      text: '🖨 Imprimer', className: 'btn btn-info btn-sm', title: 'LISTE DES ETUDIANTS', exportOptions: { columns: ':not(:last-child)' } },
                    ],
                    language: { url: "{{ asset('js/datatables/fr-FR.json') }}" }
                });
            }
        });

        function editEtudiant(id, nom, matricule, telw, dn, ln, sexe, email, adresse, dept, region, np, twp, nm, nt, telt, tel2, adrt, dernier) {
            $('#edit-id').val(id);
            $('#edit-nom').val(nom);
            $('#edit-matricule').val(matricule);
            $('#edit-telw').val(telw);
            $('#edit-dn').val(dn);
            $('#edit-ln').val(ln);
            $('#edit-sexe').val(sexe);
            $('#edit-email').val(email);
            $('#edit-adresse').val(adresse);
            $('#edit-dept').val(dept);
            $('#edit-region').val(region);
            $('#edit-np').val(np);
            $('#edit-twp').val(twp);
            $('#edit-nm').val(nm);
            $('#edit-nt').val(nt);
            $('#edit-telt').val(telt);
            $('#edit-tel2').val(tel2);
            $('#edit-adrt').val(adrt);
            $('#edit-dernier').val(dernier);
        }
    </script>
@endsection
@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('etudiant_management') }}"><strong>Gestion des Budgets</strong></a></li>
        <li><a href="{{ route('etudiant') }}"><strong>Gestion des étudiants</strong></a></li>--}}
        <li class="active"><strong>{{ $title }}</strong></li>
    </ol>
@endsection