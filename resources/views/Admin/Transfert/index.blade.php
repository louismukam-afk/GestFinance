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

        <form method="GET" action="{{ route('transfert_management') }}" class="panel panel-default mt-3" style="padding: 15px;">
            <div class="row">
                <div class="col-md-4">
                    <label>Recherche caisse / banque</label>
                    <input type="text" name="recherche" class="form-control"
                           placeholder="Nom caisse, nom banque ou code transfert"
                           value="{{ request('recherche') }}">
                </div>
                <div class="col-md-3">
                    <label>Date debut</label>
                    <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}">
                </div>
                <div class="col-md-3">
                    <label>Date fin</label>
                    <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}">
                </div>
                <div class="col-md-2" style="padding-top: 25px;">
                    <button type="submit" class="btn btn-primary btn-block">Filtrer</button>
                </div>
            </div>
            <div class="mt-2">
                <a href="{{ route('transfert_management') }}" class="btn btn-default">Reinitialiser</a>
                <a href="{{ route('imprimer_liste_transferts', request()->only(['recherche', 'date_debut', 'date_fin'])) }}"
                   target="_blank"
                   class="btn btn-info">
                    Imprimer la liste
                </a>
            </div>
        </form>

        <div class="table-responsive mt-3">
            <table id="transfertsTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Code</th>
                    <th>Compte depart</th>
                    <th>Compte arrivee</th>
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

                        <td>{{ $t->id_banque_depart ? 'Banque - '.($t->banqueDepart->nom_banque ?? '-') : 'Caisse - '.($t->caisseDepart->nom_caisse ?? '-') }}</td>
                        <td>{{ $t->id_banque_arrivee ? 'Banque - '.($t->banqueArrivee->nom_banque ?? '-') : 'Caisse - '.($t->caisseArrivee->nom_caisse ?? '-') }}</td>

                        <td>{{ number_format($t->montant_transfert, 0, ',', ' ') }}</td>

                        <td>
                            @if($t->type_transfert == 2)
                                <span class="badge badge-info">Approvisionnement entree speciale</span>
                            @elseif($t->statut_caisse_transfert == 0)
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
                                       '{{ \Carbon\Carbon::parse($t->date_transfert)->format('Y-m-d') }}',
                                       '{{ $t->observation }}'
                                       )"
                               class="btn btn-xs btn-warning">✏️</a>

                            <a href="{{ route('imprimer_transfert', $t->id) }}"
                               target="_blank"
                               class="btn btn-xs btn-info">Imprimer</a>

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
                <form method="POST" action="{{ route('store_transfert') }}" id="add-transfert-form">
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

                        <input type="hidden" name="compte_depart_type" id="compte_depart_type">
                        <input type="hidden" name="id_caisse_depart" id="id_caisse_depart">
                        <input type="hidden" name="id_banque_depart" id="id_banque_depart">
                        <input type="hidden" name="compte_arrivee_type" id="compte_arrivee_type">
                        <input type="hidden" name="id_caisse_arrivee" id="id_caisse_arrivee">
                        <input type="hidden" name="id_banque_arrivee" id="id_banque_arrivee">

                        <div class="form-group">
                            <label>Compte depart</label>
                            <select id="compte_depart" class="form-control" required>
                                <option value="">-- Choisir le compte de depart --</option>
                                <optgroup label="Caisses">
                                    @foreach($caisses as $c)
                                        <option value="caisse:{{ $c->id }}">Caisse - {{ $c->nom_caisse }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Banques">
                                    @foreach($banques as $b)
                                        <option value="banque:{{ $b->id }}">Banque - {{ $b->nom_banque }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Compte arrivee</label>
                            <select id="compte_arrivee" class="form-control" required>
                                <option value="">-- Choisir le compte d'arrivee --</option>
                                <optgroup label="Caisses">
                                    @foreach($caisses as $c)
                                        <option value="caisse:{{ $c->id }}">Caisse - {{ $c->nom_caisse }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Banques">
                                    @foreach($banques as $b)
                                        <option value="banque:{{ $b->id }}">Banque - {{ $b->nom_banque }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Montant</label>
                            <input type="number" name="montant_transfert" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Date du transfert</label>
                            <input type="date" name="date_transfert" class="form-control" value="{{ old('date_transfert', now()->toDateString()) }}" required>
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
                            <label>Date du transfert</label>
                            <input type="date" name="date_transfert" id="edit-date-transfert" class="form-control" required>
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
        function editTransfert(id, code, depart, arrivee, montant, dateTransfert, observation){
            $('#edit-id').val(id);
            $('#edit-code').val(code);
            $('#edit-montant').val(montant);
            $('#edit-date-transfert').val(dateTransfert);
            $('#edit-observation').val(observation);
        }

        $(function () {
            function syncCompte(prefix) {
                var value = $('#compte_' + prefix).val() || '';
                var parts = value.split(':');
                var type = parts[0] || '';
                var id = parts[1] || '';

                $('#compte_' + prefix + '_type').val(type);
                $('#id_caisse_' + prefix).val(type === 'caisse' ? id : '');
                $('#id_banque_' + prefix).val(type === 'banque' ? id : '');
            }

            $('#compte_depart').on('change', function () {
                syncCompte('depart');
            });

            $('#compte_arrivee').on('change', function () {
                syncCompte('arrivee');
            });

            $('#add-transfert-form').on('submit', function () {
                syncCompte('depart');
                syncCompte('arrivee');
            });

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
