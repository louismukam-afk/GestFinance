<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Liste des transferts' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; }
        .header { text-align: center; border-bottom: 2px solid #8b1024; padding-bottom: 8px; margin-bottom: 12px; }
        .title { font-size: 18px; font-weight: bold; text-transform: uppercase; color: #8b1024; }
        .subtitle { font-size: 11px; margin-top: 4px; }
        .filters { margin-bottom: 10px; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #444; padding: 5px; vertical-align: top; }
        th { background: #f2f2f2; text-align: left; }
        .right { text-align: right; }
        .center { text-align: center; }
    </style>
</head>
<body>
<div class="header">
    <div class="title">{{ $title ?? 'Liste des transferts' }}</div>
    <div class="subtitle">Document genere le {{ now()->format('d/m/Y H:i') }}</div>
</div>

<div class="filters">
    <strong>Recherche :</strong> {{ $recherche ?: 'Toutes les caisses et banques' }} |
    <strong>Date debut :</strong> {{ $dateDebut ? \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') : 'Toutes' }} |
    <strong>Date fin :</strong> {{ $dateFin ? \Carbon\Carbon::parse($dateFin)->format('d/m/Y') : 'Toutes' }}
</div>

<table>
    <thead>
    <tr>
        <th class="center">#</th>
        <th>Code</th>
        <th>Date</th>
        <th>Compte depart</th>
        <th>Compte arrivee</th>
        <th class="right">Montant</th>
        <th class="right">Solde apres</th>
        <th>Statut</th>
        <th>Utilisateur</th>
        <th>Observation</th>
    </tr>
    </thead>
    <tbody>
    @forelse($transferts as $i => $t)
        @php
            $depart = $t->id_banque_depart
                ? 'Banque - '.optional($t->banqueDepart)->nom_banque
                : 'Caisse - '.optional($t->caisseDepart)->nom_caisse;
            $arrivee = $t->id_banque_arrivee
                ? 'Banque - '.optional($t->banqueArrivee)->nom_banque
                : 'Caisse - '.optional($t->caisseArrivee)->nom_caisse;
            $statut = $t->type_transfert == 2
                ? 'Approvisionnement entree speciale'
                : ($t->statut_caisse_transfert == 0 ? 'Sortie' : 'Entree');
        @endphp
        <tr>
            <td class="center">{{ $i + 1 }}</td>
            <td>{{ $t->code_transfert }}</td>
            <td>{{ $t->date_transfert ? \Carbon\Carbon::parse($t->date_transfert)->format('d/m/Y') : '-' }}</td>
            <td>{{ $depart }}</td>
            <td>{{ $arrivee }}</td>
            <td class="right">{{ number_format($t->montant_transfert, 0, ',', ' ') }}</td>
            <td class="right">{{ number_format($t->sode_caisse, 0, ',', ' ') }}</td>
            <td>{{ $statut }}</td>
            <td>{{ optional($t->user)->name ?: '-' }}</td>
            <td>{{ $t->observation ?: '-' }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="10" class="center">Aucun transfert trouve.</td>
        </tr>
    @endforelse
    </tbody>
    <tfoot>
    <tr>
        <th colspan="5" class="right">Total</th>
        <th class="right">{{ number_format($transferts->sum('montant_transfert'), 0, ',', ' ') }}</th>
        <th colspan="4"></th>
    </tr>
    </tfoot>
</table>
</body>
</html>
