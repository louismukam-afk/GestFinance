@extends('layouts.app')
@section('content')
<div class="container">
    <h3 class="text-primary">{{ $title }}</h3>
    <p>{{ $import->libelle }} : {{ $import->date_debut->format('d/m/Y') }} au {{ $import->date_fin->format('d/m/Y') }}</p>
    @if(!$import->estRequete())<p>Les heures, les sanctions et l'impôt de cet ordre sont calculés séparément pour l'import sélectionné.</p>@endif
    @if($import->estRequete())<p><strong>Salaires vacataires apres requete</strong> - Import initial #{{ $import->id_import_parent }}, mois {{ $import->mois_requete->format('m/Y') }}. Complement uniquement, apres deduction des droits deja couverts. Le brut du complement est affiche avant impot ; le net a virer tient aussi compte des frais eventuellement saisis.</p>@endif
    <a class="btn btn-default" href="{{ route('virements_vacataires.index') }}"><i class="fa fa-arrow-left"></i> Etats de virement</a>
    @if($errors->any())<div class="alert alert-danger" style="margin-top:15px"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="GET" class="row" style="margin-top:20px">
        <div class="col-sm-4 form-group"><label for="entite">Entite des cours</label><select id="entite" name="id_entite" class="form-control" required><option value="">Choisir</option>@foreach($entites as $entite)<option value="{{ $entite->id }}" @selected(request('id_entite') == $entite->id)>{{ $entite->nom_entite }}</option>@endforeach</select></div>
        <div class="col-sm-4 form-group"><label for="banque">Banque de virement</label><select id="banque" name="id_banque" class="form-control" required><option value="">Choisir</option>@foreach($banques as $banque)<option value="{{ $banque->id }}" @selected(request('id_banque') == $banque->id)>{{ $banque->nom_banque }}</option>@endforeach</select></div>
        <div class="col-sm-4 form-group">
            <input type="hidden" name="inclure_banque" value="0">
            <label style="display:block"><input type="checkbox" name="inclure_banque" value="1" @checked(request()->boolean('inclure_banque', true))> Banque choisie</label>
            <label style="display:block"><input type="checkbox" name="inclure_autres" value="1" @checked(request()->boolean('inclure_autres'))> Autres banques</label>
            <label style="display:block"><input type="checkbox" name="inclure_sans_banque" value="1" @checked(request()->boolean('inclure_sans_banque'))> Sans banque ou sans numero de compte</label>
            <button class="btn btn-primary" type="submit"><i class="fa fa-filter"></i> Afficher</button>
        </div>
    </form>
    @if(request('id_entite') && request('id_banque'))
    <form method="POST" action="{{ route('virements_vacataires.store', $import) }}" id="virementForm">
        @csrf
        <input type="hidden" name="id_entite" value="{{ request('id_entite') }}"><input type="hidden" name="id_banque" value="{{ request('id_banque') }}"><input type="hidden" name="inclure_autres" value="{{ request()->boolean('inclure_autres') ? 1 : 0 }}"><input type="hidden" name="inclure_banque" value="{{ request()->boolean('inclure_banque', true) ? 1 : 0 }}"><input type="hidden" name="inclure_sans_banque" value="{{ request()->boolean('inclure_sans_banque') ? 1 : 0 }}"><input type="hidden" name="empreinte" value="{{ $empreinte }}">
        <h4>Beneficiaires</h4>
        <p>Le net a virer est arrondi au franc superieur pour chaque beneficiaire. Le total est la somme des montants arrondis.</p>
        <p><strong>Cette entite / cet import :</strong> {{ $suivi['total'] }} enseignant(s) avec un net positif ; {{ $suivi['valides'] }} deja inclus dans un ordre valide ; {{ $suivi['restants'] }} restant(s).</p>
        <div class="table-responsive"><table class="table table-bordered table-striped" style="min-width:1080px">
            <thead><tr><th><input type="checkbox" id="selectionTous" aria-label="Selectionner tous les beneficiaires eligibles"></th><th>Enseignant / banque</th><th>Numero de compte</th><th>Brut fiscal</th><th>Impot</th><th>Net apres impot</th><th>Frais de transfert</th><th>Net a virer</th></tr></thead>
            <tbody>@forelse($lignes as $ligne)
                @php($id = $ligne['id_personnel'])
                @php($bloque = $ligne['net_avant_frais'] <= 0 || ($ligne['reprise_a_verifier'] ?? false))
                <tr data-net="{{ $ligne['net_avant_frais'] }}">
                    <td><input type="checkbox" class="beneficiaire" name="personnels[]" value="{{ $id }}" aria-label="Selectionner {{ $ligne['nom'] }}" @disabled($bloque) @checked(!$bloque && in_array($id, old('personnels', [])))></td>
                    <td>{{ $ligne['nom'] }}<br><small>{{ $ligne['banque_personnel'] ?: 'Sans banque' }}</small>@if($ligne['reprise_a_verifier'] ?? false)<br><strong>Reprise de droits a verifier</strong>@elseif($ligne['net_avant_frais'] <= 0)<br><strong>Aucun complement positif</strong>@endif</td>
                    <td style="min-width:230px"><input class="form-control" name="comptes[{{ $id }}]" value="{{ old('comptes.'.$id, $ligne['compte']) }}" maxlength="255" aria-label="Compte de {{ $ligne['nom'] }}" @disabled($bloque)><label><input type="checkbox" name="sans_compte[{{ $id }}]" value="1" @checked(old('sans_compte.'.$id)) @disabled($bloque)> Sans compte confirme</label></td>
                    <td>{{ number_format($ligne['brut_fiscal'], 2, ',', ' ') }}@if($import->estRequete())<br><small>Revise : {{ number_format($ligne['droit_revise'], 2, ',', ' ') }}<br>Deja couvert : {{ number_format($ligne['deja_couvert'], 2, ',', ' ') }}</small>@endif</td><td>{{ number_format($ligne['impot'], 2, ',', ' ') }}</td><td>{{ number_format($ligne['net_avant_frais'], 2, ',', ' ') }}</td>
                    <td style="min-width:145px"><label><input type="checkbox" class="avec-frais" name="transfert[{{ $id }}]" value="1" @checked(old('transfert.'.$id)) @disabled($bloque)> Imputer</label><input type="number" class="form-control montant-frais" name="frais[{{ $id }}]" value="{{ old('frais.'.$id, 3500) }}" min="0" max="10000000" step="0.01" aria-label="Frais de transfert de {{ $ligne['nom'] }}" @disabled($bloque)></td>
                    <td class="net-virement" style="white-space:nowrap"></td>
                </tr>
            @empty<tr><td colspan="8">Aucun enseignant pour cette selection.</td></tr>@endforelse</tbody>
        </table></div>
        <p><strong>Selection : <span id="nombreSelection">0</span> beneficiaire(s). Frais : <span id="totalFrais">0</span> FCFA. Net a virer : <span id="totalNet">0</span> FCFA.</strong></p>
        <h4>Ordre de virement</h4>
        <div class="row">
            <div class="col-sm-4 form-group"><label for="modele">Modele</label><select name="modele" id="modele" class="form-control"><option value="ish" @selected(old('modele', $modeleDefaut) === 'ish')>Institut Superieur Hintel</option><option value="cef" @selected(old('modele', $modeleDefaut) === 'cef')>Centre de Formation Hintel</option></select></div>
            <div class="col-sm-4 form-group"><label for="agence">Agence bancaire</label><input id="agence" name="agence" class="form-control" value="{{ old('agence', 'Etoudi') }}" maxlength="255" required></div>
            <div class="col-sm-4 form-group"><label for="compteDebit">Compte a debiter de l'entite</label><input id="compteDebit" name="compte_debit" class="form-control" value="{{ old('compte_debit') }}" maxlength="255" required></div>
            <div class="col-sm-12 form-group"><label for="motif">Motif de l'ordre de virement</label><textarea id="motif" name="motif" rows="3" class="form-control" maxlength="3000" required>{{ old('motif', $import->estRequete() ? $import->libelle.'. '.$import->motif_requete : '') }}</textarea></div>
            <div class="col-sm-4 form-group"><label for="dateOrdre">Date de l'ordre</label><input id="dateOrdre" name="date_ordre" type="date" class="form-control" value="{{ old('date_ordre', date('Y-m-d')) }}" required></div>
            <div class="col-sm-4 form-group"><label for="lieuOrdre">Lieu</label><input id="lieuOrdre" name="lieu" class="form-control" value="{{ old('lieu', 'Yaounde') }}" maxlength="255" required></div>
            <div class="col-sm-4 form-group"><label for="signataire">Signataire</label><input id="signataire" name="signataire" class="form-control" value="{{ old('signataire', 'SUWO KENGNE ARMELLE LINDA') }}" maxlength="255" required></div>
            <div class="col-sm-12 form-group"><label for="fonction">Fonction du signataire</label><input id="fonction" name="fonction_signataire" class="form-control" value="{{ old('fonction_signataire', 'La Directrice Administrative et Financiere') }}" maxlength="255" required></div>
        </div>
        <label style="display:block"><input type="checkbox" name="confirmation" value="1" required> Je confirme les beneficiaires, comptes, frais et montants de cet etat definitif.</label>
        <button type="submit" class="btn btn-success" id="validerEtat"><i class="fa fa-check"></i> Valider l'etat de virement</button>
    </form>
    @endif
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('virementForm');
    if (!form) return;
    var format = new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    var formatFrais = new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    function calculer() {
        var count = 0, fraisTotal = 0, netTotal = 0;
        form.querySelectorAll('tr[data-net]').forEach(function (row) {
            var fraisInput = row.querySelector('.montant-frais');
            var frais = row.querySelector('.avec-frais').checked ? Number(fraisInput.value || 0) : 0;
            var net = Math.ceil(Math.round((Number(row.dataset.net) - frais) * 100) / 100);
            row.querySelector('.net-virement').textContent = format.format(net);
            row.querySelector('.net-virement').classList.toggle('text-danger', net <= 0);
            if (row.querySelector('.beneficiaire').checked) { count++; fraisTotal += frais; netTotal += net; }
        });
        document.getElementById('nombreSelection').textContent = count;
        document.getElementById('totalFrais').textContent = formatFrais.format(fraisTotal);
        document.getElementById('totalNet').textContent = format.format(netTotal);
        document.getElementById('validerEtat').disabled = count === 0;
    }
    document.getElementById('selectionTous').addEventListener('change', function () {
        form.querySelectorAll('.beneficiaire:not(:disabled)').forEach(function (input) { input.checked = this.checked; }, this);
        calculer();
    });
    form.addEventListener('input', calculer);
    form.addEventListener('change', calculer);
    form.addEventListener('submit', function () { document.getElementById('validerEtat').disabled = true; });
    calculer();
});
</script>
@endsection
