@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>➕ Ajouter des tranches de scolarité</h3>

        <p>
            <strong>Scolarité ID :</strong> {{ $scolarite->montant_total }} <br>
            <strong>Cycle :</strong> {{ $scolarite->cycles->nom_cycle }} <br>
            <strong>Filière :</strong> {{ $scolarite->filiere->nom_filiere }} <br>
            <strong>Niveau :</strong> {{ $scolarite->niveaux->nom_niveau }} <br>
            <strong>Spécialité :</strong> {{ $scolarite->specialites->nom_specialite }}
        </p>

        <form method="POST" action="{{ route('tranche_scolarite.store', $scolarite->id) }}">
            @csrf
            <table class="table table-bordered" id="tranchesTable">
                <thead>
                <tr>
                    <th>Nom tranche</th>
                    <th>Date limite</th>
                    <th>Montant</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><input type="text" name="nom_tranche[]" class="form-control" required></td>
                    <td><input type="date" name="date_limite[]" class="form-control" required></td>
                    <td><input type="number" step="0.01" name="montant_tranche[]" class="form-control" required></td>
                    <td><button type="button" class="btn btn-danger remove-row">🗑️</button></td>
                </tr>
                </tbody>
            </table>

            <button type="button" class="btn btn-secondary" id="addRow">➕ Ajouter une ligne</button>
            <button type="submit" class="btn btn-success">💾 Enregistrer</button>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("addRow").addEventListener("click", function() {
                let row = `<tr>
                    <td><input type="text" name="nom_tranche[]" class="form-control" required></td>
                    <td><input type="date" name="date_limite[]" class="form-control" required></td>
                    <td><input type="number" step="0.01" name="montant_tranche[]" class="form-control" required></td>
                    <td><button type="button" class="btn btn-danger remove-row">🗑️</button></td>
                </tr>`;
                document.querySelector("#tranchesTable tbody").insertAdjacentHTML("beforeend", row);
            });

            document.addEventListener("click", function(e) {
                if (e.target.classList.contains("remove-row")) {
                    e.target.closest("tr").remove();
                }
            });
        });
    </script>
@endsection
