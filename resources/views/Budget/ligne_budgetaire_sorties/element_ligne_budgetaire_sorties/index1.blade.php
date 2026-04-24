@extends('layouts.app')
@section('content')

    <div class="container">
        <h3>
            <i class="glyphicon glyphicon-list"></i> Éléments liés à la ligne budgétaire sortie :
            <span class="text-primary">{{ $ligne->libelle_ligne_budgetaire_sortie }}</span>
        </h3>

        {{-- ✅ Bouton retour --}}
        <a href="{{ route('ligne_budgetaire_sorties.index') }}" class="btn btn-default" style="margin-bottom:15px;">
            <i class="glyphicon glyphicon-arrow-left"></i> Retour aux lignes budgétaires sorties
        </a>

        {{-- ✅ Ajouter de nouveaux éléments --}}
        <a href="{{ route('element_sorties.create', $ligne->id) }}" class="btn btn-primary" style="margin-bottom:15px;">
            <i class="glyphicon glyphicon-plus"></i> Ajouter des éléments
        </a>

        <div class="table-responsive">
            <table id="tableElements" class="table table-bordered table-striped table-condensed">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Libellé Élément</th>
                    <th>Code Élément</th>
                    <th>N° Compte Élément</th>
                    <th>Description Élément</th>
                    <th>Date Création</th>
                    <th>Ligne Budgétaire Sortie</th> {{-- ✅ nouvelle colonne --}}
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
                        <td>
                            {{-- ✅ afficher la ligne associée --}}
                            {{ $el->ligneBudgetaireSortie->libelle_ligne_budgetaire_sortie ?? 'Non défini' }}
                        </td>
                        <td>{{ $el->user->name ?? 'N/A' }}</td>
                        <td>
                            {{-- ✅ Modifier --}}
                            <a href="{{ route('element_sorties.editForm', $el->id) }}"
                               class="btn btn-xs btn-primary">
                                <span class="glyphicon glyphicon-edit"></span>
                            </a>

                            {{-- ✅ Supprimer --}}
                            <form action="{{ route('element_sorties.destroy', $el->id) }}"
                                  method="POST"
                                  style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-xs btn-danger" onclick="return confirm('Supprimer cet élément ?')">
                                    <span class="glyphicon glyphicon-trash"></span>
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

@section('scripts')
    <script>
        $(function () {
            $('#tableElements').DataTable({
                responsive: true,
                pageLength: 25,
                language: {
                    url: "{{ asset('js/datatables/fr-FR.json') }}"
                }
            });
        });
    </script>
@endsection
