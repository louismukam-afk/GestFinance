@extends('skeleton')
@section('content')
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    {{-- Formulaire UPDATE --}}
    <form method="POST" action="{{ route('element_bon.updateAll', $bon->id) }}">
        @csrf
        @method('PUT')

        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Nom Élément</th>
                <th>Description</th>
                <th>Quantité</th>
                <th>Prix Unitaire</th>
                <th>Montant Total</th>
                <th>Date Réalisation</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($elements as $el)
                <tr>
                    <td><input type="text" name="nom_element_bon_commande[]" value="{{ $el->nom_element_bon_commande }}" class="form-control"></td>
                    <td><input type="text" name="description_elements_bon_commande[]" value="{{ $el->description_elements_bon_commande }}" class="form-control"></td>
                    <td><input type="number" name="quantite_element_bon_commande[]" value="{{ $el->quantite_element_bon_commande }}" class="form-control qte"></td>
                    <td><input type="number" step="0.01" name="prix_unitaire_element_bon_commande[]" value="{{ $el->prix_unitaire_element_bon_commande }}" class="form-control pu"></td>
                    <td><input type="text" value="{{ $el->montant_total_element_bon_commande }}" class="form-control total" readonly></td>
                    <td><input type="date" name="date_realisation[]" value="{{ $el->date_realisation }}" class="form-control"></td>
                    <td>
                        {{-- Bouton DELETE doit pointer vers un autre formulaire --}}
                        <button type="button" class="btn btn-danger btn-sm" onclick="supprimerElement({{ $el->id }})">🗑</button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <button type="submit" class="btn btn-success">Enregistrer</button>
    </form>

    {{-- Formulaire DELETE séparé (masqué) --}}
    <form id="formDelete" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>


@endsection

@section('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            function calculer() {
                let totalGlobal = 0;
                document.querySelectorAll("tbody tr").forEach(function(row) {
                    let qte = parseFloat(row.querySelector(".qte").value) || 0;
                    let pu = parseFloat(row.querySelector(".pu").value) || 0;
                    let total = qte * pu;
                    row.querySelector(".total").value = total;
                    totalGlobal += total;
                });
                let montantBon = {{ $bon->montant_total }};
                document.getElementById("totalGlobal").textContent = totalGlobal;

                if (totalGlobal > montantBon) {
                    alert("⚠️ Le total (" + totalGlobal + ") dépasse le montant du bon (" + montantBon + ")");
                }
            }

            document.querySelectorAll(".qte, .pu").forEach(function(input) {
                input.addEventListener("input", calculer);
            });

            calculer();
        });
        function supprimerElement(id) {
            if(confirm("Voulez-vous vraiment supprimer cet élément ?")) {
                let form = document.getElementById('formDelete');
                form.action = "/admin/elements/" + id; // 👈 adapte selon ta route
                form.submit();
            }
        }

    </script>
@endsection
