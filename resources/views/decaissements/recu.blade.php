<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Recu de decaissement</title>
    <style>
        @page { margin: 28px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        .watermark { position: fixed; top: 235px; left: 135px; width: 330px; opacity: 0.08; z-index: -1; }
        .header { border-bottom: 2px solid #222; padding-bottom: 12px; margin-bottom: 18px; }
        .logo { width: 78px; height: 78px; object-fit: contain; float: left; margin-right: 14px; }
        .title { text-align: center; font-size: 22px; font-weight: bold; margin-top: 8px; text-transform: uppercase; }
        .subtitle { text-align: center; font-size: 13px; margin-top: 4px; }
        .clear { clear: both; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #333; padding: 7px; vertical-align: top; }
        th { background: #eee; text-align: left; }
        .box { border: 1px solid #333; padding: 10px; margin-top: 12px; }
        .amount { font-size: 18px; font-weight: bold; }
        .right { text-align: right; }
        .signatures { margin-top: 55px; width: 100%; }
        .signature { width: 45%; display: inline-block; text-align: center; }
        .line { border-top: 1px solid #000; margin-top: 55px; padding-top: 6px; }
        .footer { margin-top: 28px; font-size: 10px; text-align: center; color: #555; }
    </style>
</head>
<body>
@php
    $entite = $bon->entites;
    $logo = optional($entite)->logo;
    $logoPath = $logo ? public_path($logo) : null;
    if ($logo && !file_exists($logoPath)) {
        $logoPath = public_path('uploads/images/'.$logo);
    }
@endphp

@if($logoPath && file_exists($logoPath))
    <img src="{{ $logoPath }}" class="watermark">
@endif

<div class="header">
    @if($logoPath && file_exists($logoPath))
        <img src="{{ $logoPath }}" class="logo">
    @endif
    <div class="title">Recu de decaissement</div>
    <div class="subtitle">
        {{ $entite->nom_entite ?? 'Entite non definie' }}
        @if(optional($entite)->telephone) | Tel : {{ $entite->telephone }} @endif
        @if(optional($entite)->email) | {{ $entite->email }} @endif
    </div>
    <div class="clear"></div>
</div>

<table>
    <tr>
        <th>Numero recu</th>
        <td>{{ $decaissement->numero_depense }}</td>
        <th>Date decaissement</th>
        <td>{{ \Carbon\Carbon::parse($decaissement->date_depense)->format('d/m/Y') }}</td>
    </tr>
    <tr>
        <th>Bon de commande</th>
        <td>{{ $bon->nom_bon_commande }} - BC{{ $bon->id }}</td>
        <th>Beneficiaire</th>
        <td>{{ $decaissement->personnels->nom ?? $bon->personnels->nom ?? '-' }}</td>
    </tr>
    <tr>
        <th>Mode de paiement</th>
        <td>{{ $modePaiement ?: '-' }}</td>
        <th>Caissiere / Agent</th>
        <td>{{ $decaissement->user->name ?? '-' }}</td>
    </tr>
</table>

<div class="box">
    <strong>Objet du bon :</strong> {{ $bon->description_bon_commande ?: $bon->nom_bon_commande }}<br>
    <strong>Motif du decaissement :</strong> {{ $decaissement->motif ?: '-' }}
</div>

<table>
    <tr>
        <th>Budget</th>
        <td>{{ $bon->budget->libelle_ligne_budget ?? $decaissement->budgets->libelle_ligne_budget ?? '-' }}</td>
    </tr>
    <tr>
        <th>Ligne budgetaire</th>
        <td>{{ $bon->ligne_budgetaire_sortie->libelle_ligne_budgetaire_sortie ?? $decaissement->ligne_budgetaire_sorties->libelle_ligne_budgetaire_sortie ?? '-' }}</td>
    </tr>
    <tr>
        <th>Element</th>
        <td>{{ $bon->elements_ligne_budgetaire_sortie->libelle_elements_ligne_budgetaire_sortie ?? $decaissement->elements_ligne_budgetaire_sorties->libelle_elements_ligne_budgetaire_sortie ?? '-' }}</td>
    </tr>
    <tr>
        <th>Annee academique</th>
        <td>{{ $bon->annee_academique->nom ?? $decaissement->annee_academiques->nom ?? '-' }}</td>
    </tr>
</table>

<table>
    <tr>
        <th class="right">Montant du bon</th>
        <th class="right">Deja decaisse avant ce recu</th>
        <th class="right">Montant decaisse</th>
        <th class="right">Reste a decaisser</th>
    </tr>
    <tr>
        <td class="right">{{ number_format($bon->montant_total, 0, ',', ' ') }} FCFA</td>
        <td class="right">{{ number_format($totalAvant, 0, ',', ' ') }} FCFA</td>
        <td class="right amount">{{ number_format($decaissement->montant, 0, ',', ' ') }} FCFA</td>
        <td class="right">{{ number_format($resteApres, 0, ',', ' ') }} FCFA</td>
    </tr>
</table>

<div class="signatures">
    <div class="signature">
        <div class="line">Signature de la caissiere</div>
        <div>{{ $decaissement->user->name ?? '' }}</div>
    </div>
    <div class="signature" style="float: right;">
        <div class="line">Signature du beneficiaire</div>
        <div>{{ $decaissement->personnels->nom ?? $bon->personnels->nom ?? '' }}</div>
    </div>
</div>

<div class="clear"></div>

<div class="footer">
    Recu genere le {{ now()->format('d/m/Y H:i') }}. Document a faire signer par le beneficiaire apres remise des fonds.
</div>
</body>
</html>
