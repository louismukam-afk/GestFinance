<!DOCTYPE html>
<html lang="fr"><head><meta charset="utf-8"><title>Ordre de virement #{{ $etat->id }}</title>
<style>
@page { margin: 30px 36px 38px; }
body { font-family: DejaVu Serif, serif; font-size: 11px; color: #000; line-height: 1.4; }
.entete { width:100%; border:0; margin-bottom:22px; }
.entete td { border:0; text-align:center; vertical-align:middle; font-size:10px; width:40%; }
.entete .logo { width:20%; }
.entete img { height:90px; width:auto; }
.cef { width:100%; height:auto; margin-bottom:20px; }
h1 { text-align:center; font-size:17px; margin:18px 0; text-decoration:underline; }
p { text-align:justify; }
.liste { width:100%; border-collapse:collapse; margin:15px 0; table-layout:fixed; }
.liste th,.liste td { border:1px solid #333; padding:5px 4px; vertical-align:middle; overflow-wrap:break-word; }
.liste th { text-align:center; }
.montant { text-align:right; white-space:nowrap; }
tr { page-break-inside:avoid; }
.signature { page-break-inside:avoid; margin-top:22px; text-align:right; }
.signature p { text-align:right; }
.reference { font-size:9px; margin-bottom:12px; }
</style></head><body>
@if($etat->modele === 'cef')
    <img class="cef" src="{{ public_path('images/virements/cef.jpg') }}">
@else
    <table class="entete"><tr><td>REPUBLIQUE DU CAMEROUN<br>PAIX - TRAVAIL - PATRIE<br><strong>{{ $etat->entite_nom }}</strong><br>DIRECTION<br>TEL : 697 54 73 38</td><td class="logo"><img src="{{ public_path('images/virements/ish.jpeg') }}"></td><td>REPUBLIC OF CAMEROON<br>PEACE - WORK - FATHERLAND<br><strong>HINTEL HIGHER INSTITUTE</strong><br>DIRECTION<br>TEL : 695 32 01 31</td></tr></table>
@endif
<h1>{{ $etat->modele === 'cef' ? 'ORDRE DE VIREMENT' : 'ORDRE DE VIREMENT DES SALAIRES' }}</h1>
@if($etat->nature === 'requete')<p><strong>ETAT DES SALAIRES VACATAIRES APRES REQUETE</strong><br>Import initial #{{ $etat->id_import_parent }} - mois {{ $etat->mois_requete->format('m/Y') }} - complement de remuneration.</p>@endif
<p>{{ $etat->entite_nom }} autorise {{ $etat->banque_nom }}, agence de {{ $etat->agence }}, a effectuer les depots a partir de son compte <strong>{{ $etat->compte_debit }}</strong> au profit des enseignants dont les noms figurent sur la liste ci-dessous.</p>
<p style="white-space:pre-wrap">{{ $etat->motif }}</p>
<p class="reference">Reference : OV-{{ $etat->id }} / Import #{{ $etat->id_biometrie_import }}. Periode : {{ $etat->date_debut->format('d/m/Y') }} au {{ $etat->date_fin->format('d/m/Y') }}. Montants en FCFA, apres impot et deduction des frais de transfert.</p>
@foreach($etat->lignes->chunk(22) as $pageLignes)
<table class="liste"><thead><tr><th style="width:5%">N&deg;</th><th style="width:31%">N&deg; COMPTE</th><th style="width:42%">NOMS ET PRENOMS</th><th style="width:22%">MONTANT</th></tr></thead><tbody>
@foreach($pageLignes as $ligne)<tr><td style="text-align:center">{{ $loop->parent->index * 22 + $loop->iteration }}</td><td>{{ $ligne->compte ?: 'Sans compte confirme' }}</td><td>{{ $ligne->nom }}</td><td class="montant">{{ number_format($ligne->net_a_payer, 2, ',', ' ') }}</td></tr>@endforeach
</tbody>@if($loop->last)<tfoot><tr><th colspan="3">TOTAL</th><th class="montant">{{ number_format($total, 2, ',', ' ') }}</th></tr></tfoot>@endif</table>
@endforeach
<p><strong>Soit un total de : {{ $totalLettres }}.</strong></p>
<div class="signature"><p>Fait a {{ $etat->lieu }}, le {{ $etat->date_ordre->format('d/m/Y') }}</p><p>{{ $etat->fonction_signataire }}</p><p style="margin-top:65px"><strong>{{ $etat->signataire }}</strong></p></div>
</body></html>
