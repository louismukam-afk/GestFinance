{{--

@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="text-primary">📘 {{ $title }}</h3>

        <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" href="#add_scolarite">
            ➕ Nouvelle Scolarité
        </button>

        <div class="table-responsive mt-3">
            <table id="scolaritesTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Cycle</th>
                    <th>Filière</th>
                    <th>Niveau</th>
                    <th>Spécialité</th>
                    <th>Montant total</th>
                    <th>Inscription</th>
                    <th>Type</th>
                    <th>Date création</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($scolarites as $i => $scolarite)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $scolarite->cycles->nom_cycle ?? 'N/A' }}</td>
                        <td>{{ $scolarite->filiere->nom_filiere ?? 'N/A' }}</td>
                        <td>{{ $scolarite->niveaux->nom_niveau ?? 'N/A' }}</td>
                        <td>{{ $scolarite->specialites->nom_specialite ?? 'N/A' }}</td>
                        <td>{{ number_format($scolarite->montant_total, 0, ',', ' ') }} FCFA</td>
                        <td>{{ number_format($scolarite->inscription, 0, ',', ' ') }} FCFA</td>
                        <td>
                            @if($scolarite->type_scolarite == 0)
                                <span class="label label-info">Nouveau</span>
                            @elseif($scolarite->type_scolarite == 1)
                                <span class="label label-success">Ancien</span>
                            @else
                                <span class="label label-default">Inconnu</span>
                            @endif
                        </td>
                        <td>{{ $scolarite->created_at->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('tranche_scolarite_manage', $scolarite->id) }}"
                               class="btn btn-xs btn-info">
                                ➕ Gérer les tranches
                            </a>
                        <td>
                            <a href="#edit_scolarite" data-toggle="modal" data-backdrop="false"
                               onclick="editScolarite(
                               {{ $scolarite->id }},
                                       '{{ $scolarite->id_cycle }}',
                                       '{{ $scolarite->id_filiere }}',
                                       '{{ $scolarite->id_niveau }}',
                                       '{{ $scolarite->id_specialite }}',
                                       '{{ $scolarite->montant_total }}',
                                       '{{ $scolarite->inscription }}',
                                       '{{ $scolarite->type_scolarite }}'
                                       )"
                               class="btn btn-xs btn-warning">✏️</a>

                            <form action="{{ route('delete_scolarite', $scolarite->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-xs btn-danger" onclick="return confirm('Supprimer cette scolarité ?')">🗑️</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Ajout -->
    <div class="modal fade" id="add_scolarite">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('store_scolarite') }}">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title">➕ Nouvelle Scolarité</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group"><label>Cycle</label>
                            <select name="id_cycle" class="form-control" required>
                                @foreach($cycles as $cycle)
                                    <option value="{{ $cycle->id }}">{{ $cycle->nom_cycle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group"><label>Filière</label>
                            <select name="id_filiere" class="form-control" required>
                                @foreach($filieres as $filiere)
                                    <option value="{{ $filiere->id }}">{{ $filiere->nom_filiere }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group"><label>Niveau</label>
                            <select name="id_niveau" class="form-control" required>
                                @foreach($niveaux as $niveau)
                                    <option value="{{ $niveau->id }}">{{ $niveau->nom_niveau }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group"><label>Spécialité</label>
                            <select name="id_specialite" class="form-control" required>
                                @foreach($specialites as $specialite)
                                    <option value="{{ $specialite->id }}">{{ $specialite->nom_specialite }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group"><label>Montant total</label>
                            <input type="number" name="montant_total" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Inscription</label>
                            <input type="number" name="inscription" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Type</label>
                            <select name="type_scolarite" class="form-control" required>
                                <option value="0">Nouveau</option>
                                <option value="1">Ancien</option>
                            </select>
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
    <div class="modal fade" id="edit_scolarite">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('update_scolarite') }}" id="editScolariteForm">
                    @csrf
                    <input type="hidden" name="id" id="edit-id">
                    <div class="modal-header">
                        <h4 class="modal-title">✏️ Modifier Scolarité</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group"><label>Cycle</label>
                            <select name="id_cycle" id="edit-cycle" class="form-control" required>
                                @foreach($cycles as $cycle)
                                    <option value="{{ $cycle->id }}">{{ $cycle->nom_cycle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group"><label>Filière</label>
                            <select name="id_filiere" id="edit-filiere" class="form-control" required>
                                @foreach($filieres as $filiere)
                                    <option value="{{ $filiere->id }}">{{ $filiere->nom_filiere }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group"><label>Niveau</label>
                            <select name="id_niveau" id="edit-niveau" class="form-control" required>
                                @foreach($niveaux as $niveau)
                                    <option value="{{ $niveau->id }}">{{ $niveau->nom_niveau }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group"><label>Spécialité</label>
                            <select name="id_specialite" id="edit-specialite" class="form-control" required>
                                @foreach($specialites as $specialite)
                                    <option value="{{ $specialite->id }}">{{ $specialite->nom_specialite }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group"><label>Montant total</label>
                            <input type="number" name="montant_total" id="edit-montant-total" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Inscription</label>
                            <input type="number" name="inscription" id="edit-inscription" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Type</label>
                            <select name="type_scolarite" id="edit-type" class="form-control" required>
                                <option value="0">Nouveau</option>
                                <option value="1">Ancien</option>
                            </select>
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
            const $table = $('#scolaritesTable');
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
                            title: 'LISTE DES SCOLARITES',
                            exportOptions: { columns: ':not(:last-child)' }
                        },
                        {
                            extend: 'pdfHtml5',
                            text: '📄 PDF',
                            className: 'btn btn-danger btn-sm',
                            exportOptions: { columns: ':not(:last-child)' }
                        },
                        {
                            extend: 'print',
                            text: '🖨 Imprimer',
                            className: 'btn btn-info btn-sm',
                            exportOptions: { columns: ':not(:last-child)' }
                        }
                    ],
                    language: {
                        url: "{{ asset('js/datatables/fr-FR.json') }}"
                    }
                });
            }
        });

        function editScolarite(id, cycle, filiere, niveau, specialite, montant, inscription, type) {
            $('#edit-id').val(id);
            $('#edit-cycle').val(cycle);
            $('#edit-filiere').val(filiere);
            $('#edit-niveau').val(niveau);
            $('#edit-specialite').val(specialite);
            $('#edit-montant-total').val(montant);
            $('#edit-inscription').val(inscription);
            $('#edit-type').val(type);
        }
    </script>
@endsection
--}}
@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="text-primary">📘 {{ $title }}</h3>

        <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" href="#add_scolarite">
            ➕ Nouvelle Scolarité
        </button>

        <div class="table-responsive mt-3">
            <table id="scolaritesTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Cycle</th>
                    <th>Filière</th>
                    <th>Niveau</th>
                    <th>Spécialité</th>
                    <th>Montant total</th>
                    <th>Inscription</th>
                    <th>Type</th>
                    <th>Date création</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($scolarites as $i => $scolarite)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $scolarite->cycles->nom_cycle ?? 'N/A' }}</td>
                        <td>{{ $scolarite->filiere->nom_filiere ?? 'N/A' }}</td>
                        <td>{{ $scolarite->niveaux->nom_niveau ?? 'N/A' }}</td>
                        <td>{{ $scolarite->specialites->nom_specialite ?? 'N/A' }}</td>
                        <td>{{ number_format($scolarite->montant_total, 0, ',', ' ') }} FCFA</td>
                        <td>{{ number_format($scolarite->inscription, 0, ',', ' ') }} FCFA</td>
                        <td>
                            @if($scolarite->type_scolarite == 0)
                                <span class="label label-info">Nouveau</span>
                            @elseif($scolarite->type_scolarite == 1)
                                <span class="label label-success">Ancien</span>
                            @else
                                <span class="label label-default">Inconnu</span>
                            @endif
                        </td>
                        <td>{{ $scolarite->created_at->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('tranche_scolarite_manage', $scolarite->id) }}"
                               class="btn btn-xs btn-info">
                                ➕ Gérer les tranches
                            </a>

                            <a href="#edit_scolarite" data-toggle="modal" data-backdrop="false"
                               onclick="editScolarite(
                               {{ $scolarite->id }},
                                       '{{ $scolarite->id_cycle }}',
                                       '{{ $scolarite->id_filiere }}',
                                       '{{ $scolarite->id_niveau }}',
                                       '{{ $scolarite->id_specialite }}',
                                       '{{ $scolarite->montant_total }}',
                                       '{{ $scolarite->inscription }}',
                                       '{{ $scolarite->type_scolarite }}'
                                       )"
                               class="btn btn-xs btn-warning">✏️</a>

                            <form action="{{ route('delete_scolarite', $scolarite->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-xs btn-danger" onclick="return confirm('Supprimer cette scolarité ?')">🗑️</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Ajout -->
    <div class="modal fade" id="add_scolarite">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('store_scolarite') }}">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title">➕ Nouvelle Scolarité</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group"><label>Cycle</label>
                            <select name="id_cycle" class="form-control" required>
                                @foreach($cycles as $cycle)
                                    <option value="{{ $cycle->id }}">{{ $cycle->nom_cycle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group"><label>Filière</label>
                            <select name="id_filiere" class="form-control" required>
                                @foreach($filieres as $filiere)
                                    <option value="{{ $filiere->id }}">{{ $filiere->nom_filiere }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group"><label>Niveau</label>
                            <select name="id_niveau" class="form-control" required>
                                @foreach($niveaux as $niveau)
                                    <option value="{{ $niveau->id }}">{{ $niveau->nom_niveau }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group"><label>Spécialité</label>
                            <select name="id_specialite" class="form-control" required>
                                @foreach($specialites as $specialite)
                                    <option value="{{ $specialite->id }}">{{ $specialite->nom_specialite }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group"><label>Montant total</label>
                            <input type="number" name="montant_total" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Inscription</label>
                            <input type="number" name="inscription" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Type</label>
                            <select name="type_scolarite" class="form-control" required>
                                <option value="0">Nouveau</option>
                                <option value="1">Ancien</option>
                            </select>
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
    <div class="modal fade" id="edit_scolarite">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('update_scolarite') }}" id="editScolariteForm">
                    @csrf
                    <input type="hidden" name="id" id="edit-id">
                    <div class="modal-header">
                        <h4 class="modal-title">✏️ Modifier Scolarité</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group"><label>Cycle</label>
                            <select name="id_cycle" id="edit-cycle" class="form-control" required>
                                @foreach($cycles as $cycle)
                                    <option value="{{ $cycle->id }}">{{ $cycle->nom_cycle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group"><label>Filière</label>
                            <select name="id_filiere" id="edit-filiere" class="form-control" required>
                                @foreach($filieres as $filiere)
                                    <option value="{{ $filiere->id }}">{{ $filiere->nom_filiere }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group"><label>Niveau</label>
                            <select name="id_niveau" id="edit-niveau" class="form-control" required>
                                @foreach($niveaux as $niveau)
                                    <option value="{{ $niveau->id }}">{{ $niveau->nom_niveau }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group"><label>Spécialité</label>
                            <select name="id_specialite" id="edit-specialite" class="form-control" required>
                                @foreach($specialites as $specialite)
                                    <option value="{{ $specialite->id }}">{{ $specialite->nom_specialite }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group"><label>Montant total</label>
                            <input type="number" name="montant_total" id="edit-montant-total" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Inscription</label>
                            <input type="number" name="inscription" id="edit-inscription" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Type</label>
                            <select name="type_scolarite" id="edit-type" class="form-control" required>
                                <option value="0">Nouveau</option>
                                <option value="1">Ancien</option>
                            </select>
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
            const $table = $('#scolaritesTable');
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
                            title: 'LISTE DES SCOLARITES',
                            exportOptions: { columns: ':not(:last-child)' }
                        },
                        {
                            extend: 'pdfHtml5',
                            text: '📄 PDF',
                            className: 'btn btn-danger btn-sm',
                            exportOptions: { columns: ':not(:last-child)' }
                        },
                        {
                            extend: 'print',
                            text: '🖨 Imprimer',
                            className: 'btn btn-info btn-sm',
                            exportOptions: { columns: ':not(:last-child)' }
                        }
                    ],
                    language: {
                        url: "{{ asset('js/datatables/fr-FR.json') }}"
                    }
                });
            }
        });

        function editScolarite(id, cycle, filiere, niveau, specialite, montant, inscription, type) {
            $('#edit-id').val(id);
            $('#edit-cycle').val(cycle);
            $('#edit-filiere').val(filiere);
            $('#edit-niveau').val(niveau);
            $('#edit-specialite').val(specialite);
            $('#edit-montant-total').val(montant);
            $('#edit-inscription').val(inscription);
            $('#edit-type').val(type);
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