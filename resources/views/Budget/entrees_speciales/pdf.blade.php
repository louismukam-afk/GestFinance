<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Recu entree speciale' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        .header { text-align: center; border-bottom: 2px solid #8b1024; padding-bottom: 10px; margin-bottom: 18px; }
        .title { font-size: 20px; font-weight: bold; text-transform: uppercase; color: #8b1024; }
        .subtitle { font-size: 12px; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        th, td { border: 1px solid #444; padding: 7px 8px; vertical-align: top; }
        th { width: 32%; background: #f2f2f2; text-align: left; }
        .section { margin-top: 18px; margin-bottom: 8px; font-weight: bold; color: #8b1024; }
        .amount { font-size: 16px; font-weight: bold; }
        .signatures { margin-top: 45px; width: 100%; }
        .signature { width: 45%; display: inline-block; text-align: center; vertical-align: top; }
        .line { margin-top: 45px; border-top: 1px solid #111; padding-top: 6px; }
        .right { text-align: right; }
    </style>
</head>
<body>
@php
    $compte = $entree->id_banque
        ? 'Banque - '.optional($entree->banque)->nom_banque
        : 'Caisse - '.optional($entree->caisse)->nom_caisse;
@endphp

<div class="header">
    <div class="title">Recu / fiche entree speciale</div>
    <div class="subtitle">Document justificatif genere le {{ now()->format('d/m/Y H:i') }}</div>
</div>

<table>
    <tr><th>Code entree</th><td>{{ $entree->code_entree }}</td></tr>
    <tr><th>Libelle</th><td>{{ $entree->libelle }}</td></tr>
    <tr><th>Type</th><td>{{ $types[$entree->type_entree] ?? ucfirst($entree->type_entree) }}</td></tr>
    <tr><th>Date entree</th><td>{{ optional($entree->date_entree)->format('d/m/Y') }}</td></tr>
    <tr><th>Compte recepteur</th><td>{{ $compte }}</td></tr>
    <tr><th>Statut</th><td>{{ ucfirst($entree->statut) }}</td></tr>
    <tr><th>Montant</th><td class="amount">{{ number_format($entree->montant, 0, ',', ' ') }} FCFA</td></tr>
    <tr><th>Montant rembourse</th><td>{{ number_format($entree->montant_rembourse, 0, ',', ' ') }} FCFA</td></tr>
    <tr><th>Encaisse net</th><td>{{ number_format($entree->montant_net_encaisse, 0, ',', ' ') }} FCFA</td></tr>
</table>

<div class="section">Tiers</div>
<table>
    <tr><th>Nom</th><td>{{ $entree->nom_tiers }}</td></tr>
    <tr><th>Telephone</th><td>{{ $entree->telephone_tiers ?: '-' }}</td></tr>
    <tr><th>Adresse</th><td>{{ $entree->adresse_tiers ?: '-' }}</td></tr>
</table>

<div class="section">Imputation et suivi</div>
<table>
    <tr><th>Budget</th><td>{{ optional($entree->budget)->libelle_ligne_budget ?: '-' }}</td></tr>
    <tr><th>Annee utilisation</th><td>{{ optional($entree->annee_utilisation)->nom ?: '-' }}</td></tr>
    <tr><th>Annee remboursement</th><td>{{ optional($entree->annee_remboursement)->nom ?: '-' }}</td></tr>
    <tr><th>Date contraction dette</th><td>{{ optional($entree->date_contraction_dette)->format('d/m/Y') ?: '-' }}</td></tr>
    <tr><th>Date remboursement final</th><td>{{ optional($entree->date_remboursement)->format('d/m/Y') ?: '-' }}</td></tr>
    <tr><th>Utilisateur</th><td>{{ optional($entree->user)->name ?: '-' }}</td></tr>
    <tr><th>Observations</th><td>{{ $entree->observations ?: '-' }}</td></tr>
</table>

@if($entree->echeances->count())
    <div class="section">Echeances</div>
    <table>
        <thead>
        <tr>
            <th>Nom</th>
            <th>Date</th>
            <th class="right">Montant</th>
            <th>Statut</th>
        </tr>
        </thead>
        <tbody>
        @foreach($entree->echeances as $echeance)
            <tr>
                <td>{{ $echeance->nom_echeance }}</td>
                <td>{{ optional($echeance->date_echeance)->format('d/m/Y') }}</td>
                <td class="right">{{ number_format($echeance->montant, 0, ',', ' ') }}</td>
                <td>{{ $echeance->statut }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

<div class="signatures">
    <div class="signature">
        <div class="line">Service comptable</div>
    </div>
    <div class="signature" style="float:right;">
        <div class="line">Beneficiaire / tiers</div>
    </div>
</div>
</body>
</html>
