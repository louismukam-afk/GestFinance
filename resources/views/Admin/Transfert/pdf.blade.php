<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Fiche de transfert' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        .header { text-align: center; border-bottom: 2px solid #8b1024; padding-bottom: 10px; margin-bottom: 18px; }
        .title { font-size: 20px; font-weight: bold; text-transform: uppercase; color: #8b1024; }
        .subtitle { font-size: 12px; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        th, td { border: 1px solid #444; padding: 7px 8px; vertical-align: top; }
        th { width: 32%; background: #f2f2f2; text-align: left; }
        .amount { font-size: 16px; font-weight: bold; }
        .section { margin-top: 18px; margin-bottom: 8px; font-weight: bold; color: #8b1024; }
        .signatures { margin-top: 45px; width: 100%; }
        .signature { width: 45%; display: inline-block; text-align: center; vertical-align: top; }
        .line { margin-top: 45px; border-top: 1px solid #111; padding-top: 6px; }
    </style>
</head>
<body>
@php
    $depart = $transfert->id_banque_depart
        ? 'Banque - '.optional($transfert->banqueDepart)->nom_banque
        : 'Caisse - '.optional($transfert->caisseDepart)->nom_caisse;
    $arrivee = $transfert->id_banque_arrivee
        ? 'Banque - '.optional($transfert->banqueArrivee)->nom_banque
        : 'Caisse - '.optional($transfert->caisseArrivee)->nom_caisse;
@endphp

<div class="header">
    <div class="title">Fiche de transfert</div>
    <div class="subtitle">Document justificatif genere le {{ now()->format('d/m/Y H:i') }}</div>
</div>

<table>
    <tr><th>Code transfert</th><td>{{ $transfert->code_transfert }}</td></tr>
    <tr><th>Date transfert</th><td>{{ $transfert->date_transfert ? \Carbon\Carbon::parse($transfert->date_transfert)->format('d/m/Y') : '-' }}</td></tr>
    <tr><th>Compte depart</th><td>{{ $depart }}</td></tr>
    <tr><th>Compte arrivee</th><td>{{ $arrivee }}</td></tr>
    <tr><th>Montant transfere</th><td class="amount">{{ number_format($transfert->montant_transfert, 0, ',', ' ') }} FCFA</td></tr>
    <tr><th>Solde apres depart</th><td>{{ number_format($transfert->sode_caisse, 0, ',', ' ') }} FCFA</td></tr>
    <tr>
        <th>Type operation</th>
        <td>
            @if($transfert->type_transfert == 2)
                Approvisionnement automatique entree speciale
            @else
                Transfert manuel
            @endif
        </td>
    </tr>
    <tr><th>Utilisateur createur</th><td>{{ optional($transfert->user)->name ?: '-' }}</td></tr>
    <tr><th>Dernier editeur</th><td>{{ optional($transfert->userLast)->name ?: '-' }}</td></tr>
    <tr><th>Observation</th><td>{{ $transfert->observation ?: '-' }}</td></tr>
</table>

@if($transfert->entree_speciale)
    <div class="section">Entree speciale rattachee</div>
    <table>
        <tr><th>Code</th><td>{{ $transfert->entree_speciale->code_entree }}</td></tr>
        <tr><th>Libelle</th><td>{{ $transfert->entree_speciale->libelle }}</td></tr>
    </table>
@endif

<div class="signatures">
    <div class="signature">
        <div class="line">Responsable depart</div>
    </div>
    <div class="signature" style="float:right;">
        <div class="line">Responsable arrivee</div>
    </div>
</div>
</body>
</html>
