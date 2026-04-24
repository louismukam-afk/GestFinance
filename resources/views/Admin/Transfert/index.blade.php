@extends('layouts.app')

@section('content')
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    <div class="container">
        <h3 class="text-primary">🔄 {{ $title }}</h3>

        <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" href="#add_transfert">
            ➕ Nouveau Transfert
        </button>

        <div class="table-responsive mt-3">
            <table id="transfertsTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Code</th>
                    <th>Caisse départ</th>
                    <th>Caisse arrivée</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th>Solde après</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
                </thead>

                <tbody>
                @foreach($transferts as $i => $t)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $t->code_transfert }}</td>

                        <td>{{ $t->caisseDepart->nom_caisse ?? '-' }}</td>
                        <td>{{ $t->caisseArrivee->nom_caisse ?? '-' }}</td>

                        <td>{{ number_format($t->montant_transfert, 0, ',', ' ') }}</td>

                        <td>
                            @if($t->statut_caisse_transfert == 0)
                                <span class="badge badge-danger">Sortie (-)</span>
                            @else
                                <span class="badge badge-success">Entrée (+)</span>
                            @endif
                        </td>

                        <td>{{ number_format($t->sode_caisse, 0, ',', ' ') }}</td>

                        <td>{{ \Carbon\Carbon::parse($t->date_transfert)->format('d/m/Y') }}</td>

                        <td>
                            <!-- EDIT -->
                            <a href="#edit_transfert"
                               data-toggle="modal"
                               onclick="editTransfert(
                               {{ $t->id }},
                                       '{{ $t->code_transfert }}',
                               {{ $t->id_caisse_depart }},
                               {{ $t->id_caisse_arrivee }},
                               {{ $t->montant_transfert }},
                                       '{{ $t->observation }}'
                                       )"
                               class="btn btn-xs btn-warning">✏️</a>

                            <!-- DELETE -->
                            <form action="{{ route('delete_transfert',$t->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-xs btn-danger" onclick="return confirm('Supprimer ?')">🗑️</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- 🔥 MODAL AJOUT -->
    <div class="modal fade" id="add_transfert">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('store_transfert') }}">
                    @csrf

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">x</button>

                        <h4>➕ Nouveau Transfert</h4>
                    </div>

                    <div class="modal-body">
                        <div class="row mb-3">
                            @foreach($caisses1 as $c)
                                <div class="col-md-3">
                                    <div class="card shadow-sm border-left-primary">
                                        <div class="card-body">
                                            <h6 class="text-muted">{{ $c->nom_caisse }}</h6>
                                            <h5 class="text-success font-weight-bold">
                                                {{ number_format($c->solde_calcule, 0, ',', ' ') }} FCFA
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="form-group">
                            <label>Code transfert</label>
                            <input type="text" name="code_transfert" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Caisse départ</label>
                            <select name="id_caisse_depart" class="form-control" required>
                                @foreach($caisses as $c)
                                    <option value="{{ $c->id }}">{{ $c->nom_caisse }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Caisse arrivée</label>
                            <select name="id_caisse_arrivee" class="form-control" required>
                                @foreach($caisses as $c)
                                    <option value="{{ $c->id }}">{{ $c->nom_caisse }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Montant</label>
                            <input type="number" name="montant_transfert" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Observation</label>
                            <textarea name="observation" class="form-control"></textarea>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-success">💾 Enregistrer</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- ✏️ MODAL EDIT -->
    <div class="modal fade" id="edit_transfert">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('update_transfert') }}">
                    @csrf

                    <input type="hidden" name="id" id="edit-id">

                    <div class="modal-header">
                        <h4>✏️ Modifier Transfert</h4>
                    </div>

                    <div class="modal-body">

                        <div class="form-group">
                            <label>Code</label>
                            <input type="text" name="code_transfert" id="edit-code" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Montant</label>
                            <input type="number" name="montant_transfert" id="edit-montant" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Observation</label>
                            <textarea name="observation" id="edit-observation" class="form-control"></textarea>
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
        function editTransfert(id, code, depart, arrivee, montant, observation){
            $('#edit-id').val(id);
            $('#edit-code').val(code);
            $('#edit-montant').val(montant);
            $('#edit-observation').val(observation);
        }

        $(function () {
            $('#transfertsTable').DataTable({
                responsive: true,
                pageLength: 25,
                dom: 'Bfrtip',
                buttons: [
                    { extend: 'excelHtml5', text: '📊 Excel', className: 'btn btn-success btn-sm' },
                    { extend: 'pdfHtml5', text: '📄 PDF', className: 'btn btn-danger btn-sm' },
                    { extend: 'print', text: '🖨 Imprimer', className: 'btn btn-info btn-sm' }
                ],
                language: {
                    url: "{{ asset('js/datatables/fr-FR.json') }}"
                }
            });
        });
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