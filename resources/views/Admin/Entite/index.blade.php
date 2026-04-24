{{--@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="text-primary">🏢 {{ $title }}</h3>

        <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" href="#add_entite">
            ➕ Nouvelle Entité
        </button>

        <div class="table-responsive mt-3">
            <table id="entitesTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Localisation</th>
                    <th>Téléphone</th>
                    <th>Email</th>
                    <th>Description</th>
                    <th>Logo</th>
                    <th>Date création</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($entites as $i => $entite)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $entite->nom_entite }}</td>
                        <td>{{ $entite->localisation }}</td>
                        <td>{{ $entite->telephone }}</td>
                        <td>{{ $entite->email }}</td>
                        <td>{{ $entite->description }}</td>
                        <td>{{ $entite->logo }}</td>
                        <td>{{ $entite->created_at->format('d/m/Y') }}</td>
                        <td>
                            <a href="#edit_entite" data-toggle="modal" data-backdrop="false"
                               onclick="editEntite({{ $entite->id }}, '{{ $entite->nom_entite }}', '{{ $entite->localisation }}', '{{ $entite->telephone }}', '{{ $entite->email }}', '{{ $entite->description }}', '{{ $entite->logo }}')"
                               class="btn btn-xs btn-warning">✏️</a>

                            <form action="{{ route('delete_entite', $entite->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-xs btn-danger" onclick="return confirm('Supprimer cette entité ?')">🗑️</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Ajout -->
    <div class="modal fade" id="add_entite">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('store_entite') }}">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title">➕ Nouvelle Entité</h4>
                        <button type="button" class="close" data-dismiss="modal">x</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group"><label>Nom</label>
                            <input type="text" name="nom_entite" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Localisation</label>
                            <input type="text" name="localisation" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Téléphone</label>
                            <input type="text" name="telephone" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="form-group"><label>Description</label>
                            <textarea name="description" class="form-control"></textarea>
                        </div>
                        <div class="form-group"><label>Logo</label>
                            <input type="text" name="logo" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-success">💾 Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Édition -->
    <div class="modal fade" id="edit_entite">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('update_entite') }}" id="editEntiteForm">
                    @csrf
                    <input type="hidden" name="id" id="edit-id">
                    <div class="modal-header">
                        <h4 class="modal-title">✏️ Modifier Entité</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group"><label>Nom</label>
                            <input type="text" name="nom_entite" id="edit-nom" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Localisation</label>
                            <input type="text" name="localisation" id="edit-localisation" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Téléphone</label>
                            <input type="text" name="telephone" id="edit-telephone" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Email</label>
                            <input type="email" name="email" id="edit-email" class="form-control">
                        </div>
                        <div class="form-group"><label>Description</label>
                            <textarea name="description" id="edit-description" class="form-control"></textarea>
                        </div>
                        <div class="form-group"><label>Logo</label>
                            <input type="text" name="logo" id="edit-logo" class="form-control">
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
            const $table = $('#entitesTable');
            if ($table.length) {
                $table.DataTable({
                    responsive: true,
                    dom: 'Bfrtip',
                    pageLength: 25,
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            text: '📊 Excel',
                            className: 'btn btn-success btn-sm',
                            title: 'LISTE DES ENTITES',
                            exportOptions: { columns: ':not(:last-child)' }
                        },
                        {
                            extend: 'pdfHtml5',
                            text: '📄 PDF',
                            className: 'btn btn-danger btn-sm',
                            title: 'LISTE DES ENTITES',
                            exportOptions: { columns: ':not(:last-child)' }
                        },
                        {
                            extend: 'print',
                            text: '🖨 Imprimer',
                            className: 'btn btn-info btn-sm',
                            title: 'LISTE DES ENTITES',
                            exportOptions: { columns: ':not(:last-child)' }
                        }
                    ],
                    language: {
                        url: "{{ asset('js/datatables/fr-FR.json') }}"
                    }
                });
            }
        });

        function editEntite(id, nom, localisation, telephone, email, description, logo) {
            $('#edit-id').val(id);
            $('#edit-nom').val(nom);
            $('#edit-localisation').val(localisation);
            $('#edit-telephone').val(telephone);
            $('#edit-email').val(email);
            $('#edit-description').val(description);
            $('#edit-logo').val(logo);
        }
    </script>
@endsection--}}
@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="text-primary">🏢 {{ $title }}</h3>

        <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" href="#add_entite">
            ➕ Nouvelle Entité
        </button>

        <div class="table-responsive mt-3">
            <table id="entitesTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Localisation</th>
                    <th>Téléphone</th>
                    <th>Email</th>
                    <th>Description</th>
                    <th>Logo</th>
                    <th>Date création</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($entites as $i => $entite)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $entite->nom_entite }}</td>
                        <td>{{ $entite->localisation }}</td>
                        <td>{{ $entite->telephone }}</td>
                        <td>{{ $entite->email }}</td>
                        <td>{{ $entite->description }}</td>
                        <td>
                            @if($entite->logo)
                                <img src="{{ asset($entite->logo) }}" alt="Logo" width="50" height="50">
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $entite->created_at->format('d/m/Y') }}</td>
                        <td>
                            <!-- Bouton Edit -->
                            <a href="#edit_entite" data-toggle="modal" data-backdrop="false"
                               onclick="editEntite({{ $entite->id }}, '{{ $entite->nom_entite }}', '{{ $entite->localisation }}', '{{ $entite->telephone }}', '{{ $entite->email }}', '{{ $entite->description }}')"
                               class="btn btn-xs btn-warning">✏️</a>

                            <!-- Bouton Delete -->
                            <form action="{{ route('delete_entite', $entite->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-xs btn-danger" onclick="return confirm('Supprimer cette entité ?')">🗑️</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Ajout -->
    <div class="modal fade" id="add_entite">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('store_entite') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title">➕ Nouvelle Entité</h4>
                        <button type="button" class="close" data-dismiss="modal">x</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group"><label>Nom</label>
                            <input type="text" name="nom_entite" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Localisation</label>
                            <input type="text" name="localisation" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Téléphone</label>
                            <input type="text" name="telephone" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="form-group"><label>Description</label>
                            <textarea name="description" class="form-control"></textarea>
                        </div>
                        <div class="form-group"><label>Logo</label>
                            <input type="file" name="logo" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-success">💾 Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Édition -->
    <div class="modal fade" id="edit_entite">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('update_entite') }}" enctype="multipart/form-data" id="editEntiteForm">
                    @csrf
                    <input type="hidden" name="id" id="edit-id">
                    <div class="modal-header">
                        <h4 class="modal-title">✏️ Modifier Entité</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group"><label>Nom</label>
                            <input type="text" name="nom_entite" id="edit-nom" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Localisation</label>
                            <input type="text" name="localisation" id="edit-localisation" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Téléphone</label>
                            <input type="text" name="telephone" id="edit-telephone" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Email</label>
                            <input type="email" name="email" id="edit-email" class="form-control">
                        </div>
                        <div class="form-group"><label>Description</label>
                            <textarea name="description" id="edit-description" class="form-control"></textarea>
                        </div>
                        <div class="form-group"><label>Logo</label>
                            <input type="file" name="logo" id="edit-logo" class="form-control">
                            {{-- On pourrait afficher l’ancien logo en aperçu si on le souhaite --}}
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
            const $table = $('#entitesTable');
            if ($table.length) {
                $table.DataTable({
                    responsive: true,
                    dom: 'Bfrtip',
                    pageLength: 25,
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            text: '📊 Excel',
                            className: 'btn btn-success btn-sm',
                            title: 'LISTE DES ENTITES',
                            exportOptions: { columns: ':not(:last-child)' }
                        },
                        {
                            extend: 'pdfHtml5',
                            text: '📄 PDF',
                            className: 'btn btn-danger btn-sm',
                            title: 'LISTE DES ENTITES',
                            exportOptions: { columns: ':not(:last-child)' }
                        },
                        {
                            extend: 'print',
                            text: '🖨 Imprimer',
                            className: 'btn btn-info btn-sm',
                            title: 'LISTE DES ENTITES',
                            exportOptions: { columns: ':not(:last-child)' }
                        }
                    ],
                    language: {
                        url: "{{ asset('js/datatables/fr-FR.json') }}"
                    }
                });
            }
        });

        function editEntite(id, nom, localisation, telephone, email, description) {
            $('#edit-id').val(id);
            $('#edit-nom').val(nom);
            $('#edit-localisation').val(localisation);
            $('#edit-telephone').val(telephone);
            $('#edit-email').val(email);
            $('#edit-description').val(description);
        }
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