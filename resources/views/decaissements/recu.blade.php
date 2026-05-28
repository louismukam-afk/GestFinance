<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Recu de decaissement</title>
    @php $isA5 = ($format ?? 'a4') === 'a5'; @endphp
    <style>
        @page {
            size: {{ $isA5 ? 'A5 landscape' : 'A4 portrait' }};
            margin: {{ $isA5 ? '8px' : '28px' }};
        }
        body { font-family: DejaVu Sans, sans-serif; font-size: {{ $isA5 ? '8.2px' : '12px' }}; color: #111; line-height: {{ $isA5 ? '1.15' : '1.35' }}; }
        .watermark { position: fixed; top: {{ $isA5 ? '54px' : '235px' }}; left: {{ $isA5 ? '230px' : '135px' }}; width: {{ $isA5 ? '210px' : '330px' }}; opacity: 0.08; z-index: -1; }
        .header { border-bottom: 2px solid #222; padding-bottom: {{ $isA5 ? '3px' : '12px' }}; margin-bottom: {{ $isA5 ? '4px' : '18px' }}; }
        .logo { width: {{ $isA5 ? '34px' : '78px' }}; height: {{ $isA5 ? '34px' : '78px' }}; object-fit: contain; float: left; margin-right: {{ $isA5 ? '8px' : '14px' }}; }
        .title { text-align: center; font-size: {{ $isA5 ? '12px' : '22px' }}; font-weight: bold; margin-top: {{ $isA5 ? '1px' : '4px' }}; text-transform: uppercase; }
        .subtitle { text-align: center; font-size: {{ $isA5 ? '7.5px' : '13px' }}; margin-top: 2px; }
        .clear { clear: both; }
        table { width: 100%; border-collapse: collapse; margin-top: {{ $isA5 ? '3px' : '12px' }}; table-layout: {{ $isA5 ? 'fixed' : 'auto' }}; }
        th, td {
            border: 1px solid #333;
            padding: {{ $isA5 ? '2.5px' : '7px' }};
            vertical-align: top;
            @if($isA5)
            word-wrap: break-word;
            overflow-wrap: break-word;
            @endif
        }
        th { background: #eee; text-align: left; }
        .box { border: 1px solid #333; padding: {{ $isA5 ? '4px' : '10px' }}; margin-top: {{ $isA5 ? '3px' : '12px' }}; }
        .amount { font-size: {{ $isA5 ? '9.5px' : '18px' }}; font-weight: bold; }
        .right { text-align: right; }
        .signatures { margin-top: {{ $isA5 ? '6px' : '55px' }}; width: 100%; page-break-inside: avoid; }
        .signature { width: 45%; display: inline-block; text-align: center; }
        .line { border-top: 1px solid #000; margin-top: {{ $isA5 ? '18px' : '55px' }}; padding-top: {{ $isA5 ? '2px' : '4px' }}; }
        .footer { margin-top: {{ $isA5 ? '4px' : '28px' }}; font-size: {{ $isA5 ? '6.8px' : '10px' }}; text-align: center; color: #555; }
        .compact th { width: 16%; }
        .compact td { width: 34%; }
        .amounts th, .amounts td { width: 25%; }
        .a5-layout { width: 100%; border-collapse: collapse; table-layout: fixed; margin-top: 3px; }
        .a5-layout > tbody > tr > td { border: 0; padding: 0 3px; vertical-align: top; }
        .a5-left { width: 64%; }
        .a5-right { width: 36%; }
        .a5-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-top: 0; }
        .a5-table th { width: 24%; }
        .a5-table td { width: 26%; }
        .a5-table th,
        .a5-table td {
            border: 1px solid #333;
            padding: 2px;
            font-size: 7.2px;
            line-height: 1.08;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .a5-amount th,
        .a5-amount td {
            border: 1px solid #333;
            padding: 2px;
            font-size: 7.2px;
            line-height: 1.08;
        }
        .a5-sign { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .a5-sign td { border: 0; padding: 0 4px; text-align: center; font-size: 7px; }
        .a5-sign-line { border-top: 1px solid #000; margin-top: 22px; padding-top: 2px; }
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

@if($isA5)
<table class="a5-layout">
    <tr>
        <td class="a5-left">
            <table class="a5-table">
                <tr>
                    <th>Recu</th>
                    <td>{{ $decaissement->numero_depense }}</td>
                    <th>Date</th>
                    <td>{{ \Carbon\Carbon::parse($decaissement->date_depense)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <th>Bon</th>
                    <td>{{ $bon->nom_bon_commande }} - BC{{ $bon->id }}</td>
                    <th>Benef.</th>
                    <td>{{ $decaissement->personnels->nom ?? $bon->personnels->nom ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Paiement</th>
                    <td>{{ $modePaiement ?: '-' }}</td>
                    <th>Agent</th>
                    <td>{{ $decaissement->user->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Objet</th>
                    <td colspan="3">{{ \Illuminate\Support\Str::limit($bon->description_bon_commande ?: $bon->nom_bon_commande, 120) }}</td>
                </tr>
                <tr>
                    <th>Motif</th>
                    <td colspan="3">{{ \Illuminate\Support\Str::limit($decaissement->motif ?: '-', 120) }}</td>
                </tr>
                <tr>
                    <th>Budget</th>
                    <td>{{ \Illuminate\Support\Str::limit($bon->budget->libelle_ligne_budget ?? $decaissement->budgets->libelle_ligne_budget ?? '-', 55) }}</td>
                    <th>Ligne</th>
                    <td>{{ \Illuminate\Support\Str::limit($bon->ligne_budgetaire_sortie->libelle_ligne_budgetaire_sortie ?? $decaissement->ligne_budgetaire_sorties->libelle_ligne_budgetaire_sortie ?? '-', 55) }}</td>
                </tr>
                <tr>
                    <th>Element</th>
                    <td>{{ \Illuminate\Support\Str::limit($bon->elements_ligne_budgetaire_sortie->libelle_elements_ligne_budgetaire_sortie ?? $decaissement->elements_ligne_budgetaire_sorties->libelle_elements_ligne_budgetaire_sortie ?? '-', 55) }}</td>
                    <th>Annee</th>
                    <td>{{ $bon->annee_academique->nom ?? $decaissement->annee_academiques->nom ?? '-' }}</td>
                </tr>
            </table>
        </td>
        <td class="a5-right">
            <table class="a5-amount">
                <tr>
                    <th>Montant bon</th>
                    <td class="right">{{ number_format($bon->montant_total, 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <th>Avant ce recu</th>
                    <td class="right">{{ number_format($totalAvant, 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <th>Decaisse</th>
                    <td class="right amount">{{ number_format($decaissement->montant, 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <th>Reste</th>
                    <td class="right">{{ number_format($resteApres, 0, ',', ' ') }}</td>
                </tr>
            </table>

            <table class="a5-sign">
                <tr>
                    <td>
                        <div class="a5-sign-line">Signature caissiere</div>
                        <div>{{ \Illuminate\Support\Str::limit($decaissement->user->name ?? '', 28) }}</div>
                    </td>
                    <td>
                        <div class="a5-sign-line">Signature beneficiaire</div>
                        <div>{{ \Illuminate\Support\Str::limit($decaissement->personnels->nom ?? $bon->personnels->nom ?? '', 28) }}</div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<div class="footer">
    Recu genere le {{ now()->format('d/m/Y H:i') }}. Document a faire signer apres remise des fonds.
</div>
@else

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

<table class="amounts">
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
@endif
@if($autoPrint ?? false)
    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
@endif
</body>
</html>
