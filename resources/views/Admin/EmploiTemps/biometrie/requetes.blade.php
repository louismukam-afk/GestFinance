@extends('layouts.app')
@section('content')
<div class="container">
    <h3 class="text-primary">Suivi des requêtes des enseignants</h3>
    <p>Choisissez un import initial pour retrouver ses dossiers et les séances corrigées.</p>
    <a class="btn btn-default" href="{{ route('biometrie_heures.index') }}">Décompte heures</a>
    <a class="btn btn-primary" href="{{ route('biometrie_heures.manual.create') }}">Nouvelle requête</a>
    @if(session('success'))<div class="alert alert-success mt-3">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger mt-3">{{ $errors->first() }}</div>@endif
    <form method="GET" class="form-inline" style="margin:20px 0">
        <label for="importParent">Import initial &nbsp;</label>
        <select id="importParent" name="id_import_parent" class="form-control">
            <option value="">Tous les imports</option>
            @foreach($parents as $parent)<option value="{{ $parent->id }}" @selected(request('id_import_parent') == $parent->id)>#{{ $parent->id }} - {{ $parent->libelle }}</option>@endforeach
        </select>
        <button class="btn btn-primary" type="submit">Afficher</button>
    </form>
    @forelse($requetes as $dossier)
        @php($ordresDossier = $ordres->get($dossier->id, collect()))
        @php($personnelsPayes = $ordresDossier->flatMap(fn($ordre) => $ordre->lignes->pluck('id_personnel')))
        <div class="card mb-4">
            <div class="card-header"><strong>Dossier #{{ $dossier->id }}</strong> - Import initial #{{ $dossier->id_import_parent }} - {{ $dossier->mois_requete?->format('m/Y') }}
                @if($ordresDossier->isNotEmpty())<span class="badge badge-warning">Ordre validé</span>@else<span class="badge badge-info">Sans ordre validé</span>@endif
            </div>
            <div class="card-body">
                <p><strong>Motif et décision :</strong> {{ $dossier->motif_requete }}</p>
                @if($ordresDossier->isEmpty())
                <form method="POST" action="{{ route('biometrie_heures.requetes.update', $dossier) }}" style="margin-bottom:15px">
                    @csrf @method('PUT')
                    <label for="motif-{{ $dossier->id }}">Modifier le motif</label>
                    <textarea id="motif-{{ $dossier->id }}" name="motif_requete" class="form-control" rows="2" maxlength="3000" required>{{ $dossier->motif_requete }}</textarea>
                    <button class="btn btn-default btn-sm" type="submit">Enregistrer le motif</button>
                </form>
                @else<p>Le motif est figé car un ordre a été validé. Les corrections des bénéficiaires déjà inclus sont également figées.</p>@endif
                <a class="btn btn-primary btn-sm" href="{{ route('biometrie_heures.manual.create', ['id_biometrie_import' => $dossier->id]) }}">Ouvrir le dossier</a>
                @foreach($ordresDossier as $ordre)<a class="btn btn-default btn-sm" href="{{ route('virements_vacataires.show', $ordre) }}">Ordre #{{ $ordre->id }}</a>@endforeach
                <div class="table-responsive" style="margin-top:15px"><table class="table table-bordered table-striped">
                    <thead><tr><th>Date</th><th>Enseignant</th><th>Matière / plage</th><th>Décision</th><th>Heures avant / après</th><th>Sanction avant / après</th><th>Justification</th><th>Action</th></tr></thead>
                    <tbody>@forelse($dossier->heures_realisees as $heure)
                        <tr><td>{{ $heure->date_seance->format('d/m/Y') }}</td><td>{{ $heure->personnel->nom ?? '-' }}</td><td>{{ $heure->cours->programme->matiere->nom_matiere ?? '-' }}<br>{{ substr($heure->heure_debut_prevue, 0, 5) }} - {{ substr($heure->heure_fin_prevue, 0, 5) }}</td><td>{{ $heure->libelle_type_requete }}</td><td>{{ number_format($heure->duree_avant_requete ?? 0, 2, ',', ' ') }} / {{ number_format($heure->duree_realisee, 2, ',', ' ') }} h</td><td>{{ number_format($heure->sanction_avant_requete ?? 0, 0, ',', ' ') }} / {{ number_format($heure->sanction_annulee ? 0 : max($heure->duree_prevue - $heure->duree_realisee, 0) * $heure->montant_taux, 0, ',', ' ') }} FCFA</td><td>{{ $heure->observation }}</td><td>
                            @if($personnelsPayes->contains($heure->id_personnel))<span>Ordre validé</span>@else<a class="btn btn-warning btn-sm" href="{{ route('biometrie_heures.manual.create', ['id_biometrie_import' => $dossier->id, 'modifier_correction' => $heure->id]) }}">Modifier</a>@endif
                        </td></tr>
                    @empty<tr><td colspan="8">Aucune séance corrigée dans ce dossier.</td></tr>@endforelse</tbody>
                </table></div>
            </div>
        </div>
    @empty<div class="alert alert-info">Aucune requête pour cette sélection.</div>@endforelse
</div>
@endsection
