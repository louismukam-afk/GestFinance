@extends('layouts.app')
@section('content')

    <div class="container">
        <h3>
            📑 Éléments de la ligne budgétaire sortie :
            <span class="text-primary">{{ $ligne->libelle_ligne_budgetaire_sortie }}</span>
        </h3>

        {{-- ✅ Bouton retour --}}
        <a href="{{ route('ligne_budgetaire_sorties.index') }}" class="btn btn-default" style="margin-bottom:15px;">
            <i class="glyphicon glyphicon-arrow-left"></i> Retour aux lignes budgétaires sorties
        </a>

        {{-- ✅ Boutons actions --}}
        <a href="{{ route('element_sorties.create', $ligne->id) }}" class="btn btn-primary" style="margin-bottom:15px;">
            <i class="glyphicon glyphicon-plus"></i> Ajouter des éléments
        </a>

        <a href="{{ route('element_sorties.exportExcel', $ligne->id) }}" class="btn btn-success" style="margin-bottom:15px;">
            <i class="glyphicon glyphicon-download"></i> Exporter Excel
        </a>

        <a href="{{ route('element_sorties.exportPdf', $ligne->id) }}" class="btn btn-danger" style="margin-bottom:15px;">
            <i class="glyphicon glyphicon-file"></i> Exporter PDF
        </a>

        <div class="table-responsive">
            <table id="tableElements" class="table table-bordered table-striped table-condensed">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Libellé</th>
                    <th>Code</th>
                    <th>N° Compte</th>
                    <th>Description</th>
                    <th>Date Création</th>
                    <th>Utilisateur</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @php $i = 1; @endphp
                @foreach($elements as $el)
                    <tr>
                        <td>{{ $i++ }}</td>
                        <td>{{ $el->libelle_elements_ligne_budgetaire_sortie }}</td>
                        <td>{{ $el->code_elements_ligne_budgetaire_sortie }}</td>
                        <td>{{ $el->numero_compte_elements_ligne_budgetaire_sortie }}</td>
                        <td>{{ $el->description }}</td>
                        <td>{{ $el->date_creation }}</td>
                        <td>{{ $el->user->name ?? 'N/A' }}</td>
                        <td>
                            {{-- ✅ Modifier --}}
                            <a href="{{ route('element_sorties.edit', $el->id) }}" class="btn btn-xs btn-primary">
                                <span class="glyphicon glyphicon-edit"></span>
                            </a>

                            {{-- ✅ Supprimer --}}
                            <form action="{{ route('element_sorties.destroy', $el->id) }}"
                                  method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-xs btn-danger" onclick="return confirm('Supprimer cet élément ?')">
                                    <span class="glyphicon glyphicon-trash"></span>
                                </button>
                            </form>

                            {{-- ✅ Export individuel --}}
                            <a href="{{ route('element_sorties.exportPdfOne', $el->id) }}" class="btn btn-xs btn-danger">
                                <span class="glyphicon glyphicon-file"></span> PDF
                            </a>
                            <a href="{{ route('element_sorties.exportExcelOne', $el->id) }}" class="btn btn-xs btn-success">
                                <span class="glyphicon glyphicon-download"></span> Excel
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        $(function () {
            $('#tableElements').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                pageLength: 25,
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '📊 Excel',
                        className: 'btn btn-success btn-sm',
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
        });
    </script>
@endsection
