@extends('layouts.app')
@section('content')
<div class="container">
    <h3 class="text-primary">{{ $title }}</h3>
    @if($etat->nature === 'requete')<p><strong>Salaires vacataires apres requete</strong> - Import initial #{{ $etat->id_import_parent }}, mois {{ $etat->mois_requete->format('m/Y') }}</p>@endif
    <a class="btn btn-default" href="{{ route('virements_vacataires.historique') }}"><i class="fa fa-history"></i> Historique des ordres</a>
    <a class="btn btn-default" href="{{ route('virements_vacataires.index') }}"><i class="fa fa-arrow-left"></i> Etats de virement</a>
    <a class="btn btn-danger" href="{{ route('virements_vacataires.pdf', $etat) }}" target="_blank" rel="noopener"><i class="fa fa-file-pdf-o"></i> Ordre de virement PDF</a>
    <p style="margin-top:20px"><strong>{{ $etat->entite_nom }} / {{ $etat->banque_nom }}</strong><br>Import #{{ $etat->id_biometrie_import }} : {{ $etat->date_debut->format('d/m/Y') }} au {{ $etat->date_fin->format('d/m/Y') }}<br>Valide le {{ $etat->valide_at->format('d/m/Y H:i') }}. Compte a debiter : {{ $etat->compte_debit }}. Agence : {{ $etat->agence }}.</p>
    @include('Admin.EmploiTemps.virements.introduction', ['etat' => $etat])
    <p style="white-space:pre-wrap">{{ $etat->motif }}</p>
    @if($etat->lignes->contains(fn($ligne) => collect($ligne->details ?? [])->sum('ajustement_arrondi_virement') > 0))
        <p>Le net a virer comprend un ajustement d'arrondi au franc superieur.</p>
    @endif
    <div class="table-responsive"><table class="table table-bordered table-striped">
        <thead><tr><th>Nom</th><th>Compte</th><th>Brut fiscal</th><th>Impot</th><th>Net apres impot</th><th>Frais</th><th>Net a virer</th></tr></thead>
        <tbody>@foreach($etat->lignes as $ligne)<tr><td>{{ $ligne->nom }}</td><td>{{ $ligne->compte ?: 'Sans compte confirme' }}</td><td>{{ number_format($ligne->brut_fiscal, 2, ',', ' ') }}</td><td>{{ number_format($ligne->impot, 2, ',', ' ') }}</td><td>{{ number_format($ligne->net_avant_frais, 2, ',', ' ') }}</td><td>{{ number_format($ligne->frais_transfert, 2, ',', ' ') }}</td><td>{{ number_format($ligne->net_a_payer, 2, ',', ' ') }}</td></tr>@endforeach</tbody>
        <tfoot><tr><th colspan="2">TOTAL FCFA</th>@foreach(['brut_fiscal','impot','net_avant_frais','frais_transfert','net_a_payer'] as $champ)<th>{{ number_format($etat->lignes->sum($champ), 2, ',', ' ') }}</th>@endforeach</tr></tfoot>
    </table></div>
</div>
@endsection
