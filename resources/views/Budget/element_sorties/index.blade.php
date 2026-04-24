@extends('layouts.app')
@section('content')

    <div class="container">
        <h3>
            Éléments de la ligne budgétaire sortie :
            <span class="text-primary">{{ $ligne->libelle_ligne_budgetaire_sortie }}</span>
        </h3>

        <a href="{{ route('element_sorties.create', $ligne->id) }}" class="btn btn-success" style="margin-bottom:15px;">
            ➕ Ajouter d'autres éléments
        </a>
        <a href="{{ route('ligne_budgetaire_sorties.index') }}" class="btn btn-default" style="margin-bottom:15px;">
            ⬅ Retour aux lignes sorties
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
                    <th>Date création</th>
                    <th>Utilisateur</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @php $i=1; @endphp
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
                            <a href="{{ route('element_sorties.edit', $el->id) }}" class="btn btn-xs btn-primary">
                                ✏ Modifier
                            </a>
                            <form action="{{ route('element_sorties.destroy', $el->id) }}" method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-danger" onclick="return confirm('Supprimer cet élément ?')">
                                    🗑 Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection
@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('budget') }}"><strong>budget</strong></a></li>
     <li><a href="{{ route('budget') }}"><strong>budget</strong></a></li>

        <li class="active"><strong>{{ $title }}</strong></li>
    </ol>
    @endsection
@section('scripts')
    <script>
    /*    $(function () {
            $('#tableElements').DataTable({
                responsive: true,
                pageLength: 25,
                language: { url: "{{ asset('js/datatables/fr-FR.json') }}" }
            });
        });
*/

        $(function () {
            const $table = $('#tableElements');
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
                            title: 'Elements ligne',
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
    </script>
@endsection
