@extends('layouts.app')
@section('content')

    <div class="container">
        <h3>Remplir les éléments du Bon : {{ $bon->nom_bon_commande }}</h3>
        <p><strong>Montant attendu : </strong> {{ number_format($bon->montant_total, 0, ',', ' ') }} FCFA</p>

        <form method="POST" action="{{ route('element_bon.store', $bon->id) }}">
            @csrf
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Nom Élément</th>
                    <th>Description</th>
                    <th>Quantité</th>
                    <th>Prix Unitaire</th>
                    <th>Montant Total</th>
                    <th>Date Réalisation</th>
                </tr>
                </thead>
                <tbody>
                @for($i=0; $i < $nombre; $i++)
                    <tr>
                        <td><input type="text" name="nom_element_bon_commande[]" class="form-control" required></td>
                        <td><input type="text" name="description_elements_bon_commande[]" class="form-control"></td>
                        <td><input type="number" name="quantite_element_bon_commande[]" class="form-control qte" required></td>
                        <td><input type="number" step="0.01" name="prix_unitaire_element_bon_commande[]" class="form-control pu" required></td>
                        <td><input type="text" name="montant_total_element_bon_commande[]" class="form-control total" readonly></td>
                        <td><input type="date" name="date_realisation[]" class="form-control" required></td>
                    </tr>
                @endfor
                </tbody>
            </table>

            <h4>Total Global : <span id="totalGlobal">0</span> FCFA</h4>
            <button type="submit" class="btn btn-success">Enregistrer</button>
        </form>
    </div>

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
                document.getElementById("totalGlobal").textContent = totalGlobal;
            }

            document.querySelectorAll(".qte, .pu").forEach(function(input) {
                input.addEventListener("input", calculer);
            });
        });
    </script>
@endsection
