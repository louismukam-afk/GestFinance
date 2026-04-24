<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Règlement N° {{ $reg->numero_reglement ?? '-' }}</title>
    @php
        $logo = optional($reg->entite)->logo;
    @endphp
    <style>
        * { font-family: DejaVu Sans, Arial, sans-serif; }
        html, body { margin:0; padding:0; }
        .page   { width: 100%; padding: 12px 16px; box-sizing: border-box; }
        .copy   { position: relative; width: 100%; min-height: 480px; padding: 12px 16px; box-sizing: border-box; border:1px solid #bbb; }
        .bar    { height: 6px; background:#c00; margin-bottom: 6px; }
        .entete { display:flex; justify-content: space-between; align-items:flex-start; }
        .h1     { font-size:16px; font-weight:700; margin:2px 0 4px 0; }
        .muted  { color:#666; font-size: 11px; }
        .grid2  { display:grid; grid-template-columns: 1fr 1fr; gap: 6px 14px; margin-top:6px; }
        .table  { width:100%; border-collapse: collapse; margin-top:8px; }
        .table th, .table td { border:1px solid #333; padding:6px; font-size: 12px; }
        .right  { text-align:right; }
        .center { text-align:center; }
        .sep    { margin: 10px 0; border-top:1px dashed #999; }
        .small  { font-size:11px; }
        .wm     { /* filigrane */
            position:absolute; inset:0;
            background-image: url('{{ $logo ? asset($logo) : asset("uploads/images/1759420569_logo.jpg") }}');
            {{--background-image: url('{{ $reg->entite->logo ? asset($reg->entite->logo) : asset('uploads/images/1759420569_logo.jpg') }}');--}}
            background-repeat:no-repeat;
            background-position:center center;
            background-size: 60%;
            opacity:0.06;
            z-index:0;
/*
            background-image: url('{{ asset('uploads/images/1759420569_logo.jpg') }}');
*/
           /* background-repeat:no-repeat;
            background-position:center center;
            background-size: 60%;
            opacity:0.06;
            z-index:0;*/
        }
        .content { position:relative; z-index:1; }
    </style>
</head>
<body>
@php
    $et    = $reg->etudiants;
    $date  = \Carbon\Carbon::parse($reg->date_reglement ?? now())->format('d/m/Y');
    $libType = [
        0=>'frais',
        1=>'scolarité'
    ][$reg->type_reglement ?? 0];

    // libellé versement (si tu as stocké un entier)
    $libVersement = [
        0=>'Espèce',
        1=>'Bancaire',
        2=>'Orange Money',
        3=>'MTN Money'
    ][$reg->type_versement ?? 0] ?? (is_string($reg->type_versement) ? ucfirst($reg->type_versement) : '—');

    // Totaux (si disponibles – sinon laisse tel quel)
    $totalFacture = 0; $totalPaye = 0; $reste = 0;
    if (!empty($reg->id_facture_etudiant)) {
        $fact = \App\Models\facture_etudiant::find($reg->id_facture_etudiant);
        if ($fact) {
            $totalFacture = (float)$fact->montant_total_facture;
            $totalPaye    = (float)\App\Models\reglement_etudiant::where('id_facture_etudiant',$fact->id)->sum('montant_reglement');
            $reste        = max(0, $totalFacture - $totalPaye);
        }
    }
@endphp

<div class="page" >



    {{-- 1ère souche (Client) --}}
    {{--<div class="copy" style="background-image: ({{($reg->entite->logo ?? '—')}})">--}}
    <div class="copy">

    <div class="wm"></div>
        <div class="content">
            <div class="bar"></div>
            <div class="entete">
                <div>
                    <div class="h1">RÈGLEMENT N° {{ $reg->numero_reglement ?? '-' }}</div>
                    <div class="muted">Date : {{ $date }}</div>
                    <div class="muted">Type : {{ ucfirst($libType) }} — Mode de paiement : {{ $libVersement }}</div>
                    <div class="muted"><strong>Année académique :</strong> {{($reg->annee_academique->nom ?? '—')}}</div>
                </div>
                <div class="right small">
                     <div><strong>Entite :{{($reg->entite->nom_entite ?? '—')}} / {{($reg->entite->telephone ?? '—')}} </strong></div>
                     {{--<div><strong>{{ config('app.name', 'Mon Établissement') }}</strong></div>--}}
                    @if(optional($reg->user)->name)
                        <div>Caissier(ère) : {{ $reg->user->name }}</div>
                    @endif
                </div>
            </div>

            <div class="grid2 small" style="margin-top:8px;">
                <div>

                    <strong>Étudiant :</strong> {{ $et->nom ?? '—' }}<br>
                    <strong>Matricule :</strong> {{ $et->matricule ?? '—' }}<br>
                    <span class="muted">Tel : {{ $et->telephone_whatsapp ?? '—' }}</span>
                </div>
                <div class="right">
                    @if($libType === 'scolarité')
                        <strong>Cycle/Filière :</strong>
                        {{ optional($reg->ligne_budgetaire_entree)->libelle_ligne_budgetaire_entree ? ($reg->cycles->nom_cycle ?? '—') : ($reg->cycles->nom_cycle ?? '—') }}
                        / {{ $reg->filieres->nom_filiere ?? '—' }}<br>
                        <strong>Niveau/Spécialité :</strong>
                        {{ $reg->niveaux->nom_niveau ?? '—' }} / {{ $reg->specialites->nom_specialite ?? '—' }}
                    @else
                        <strong>Frais :</strong> {{ $reg->frais->nom_frais ?? '—' }}
                    @endif
                </div>
            </div>

            <table class="table">
                <thead>
                <tr>
                    <th>Libellé</th>
                    <th>Détail</th>
                    <th class="right">Montant</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Règlement {{ ucfirst($libType) }}</td>
                    <td>
                        {{ $reg->motif_reglement ?? '—' }}
                        @if($libType === 'scolarité' && $reg->id_tranche_scolarite)
                            @php $tr = \App\Models\tranche_scolarite::find($reg->id_tranche_scolarite); @endphp
                            @if($tr)
                                <div class="small muted">Tranche : {{ $tr->nom_tranche }} ({{ number_format($tr->montant_tranche,0,',',' ') }})</div>
                            @endif
                        @endif
                    </td>
                    <td class="right">{{ number_format($reg->montant_reglement ?? 0, 0, ',', ' ') }}</td>
                </tr>
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="2" class="right">Montant en lettres</th>
                    <th class="right">{{ $reg->lettre ?? '—' }}</th>
                </tr>
                </tfoot>
            </table>

            <div class="grid2 small" style="margin-top:8px;">
                <div>
                    <div class="muted">Affectation budgétaire :</div>
                    <div>
                        {{ $reg->budget->libelle_ligne_budget ?? '—' }}<br>
                        {{ $reg->ligne_budgetaire_entree->libelle_ligne_budgetaire_entree ?? '—' }} >
                        {{ $reg->donnee_budgetaire_entree->donnee_ligne_budgetaire_entree ?? $reg->donnee_budgetaire_entree->code_donnee_budgetaire_entree ?? '—' }} >
                        {{ $reg->donnee_ligne_budgetaire_entree->donnee_ligne_budgetaire_entree ?? '—' }}
                    </div>
                </div>
                <div class="right">
                    <div class="muted">Signature & cachet</div>
                    <div style="height:60px;border:1px dashed #aaa"></div>
                </div>
            </div>

            @if($totalFacture > 0)
                <div class="sep"></div>
                <table class="table">
                    <tbody>
                    <tr>
                        <td><strong>Total facture</strong></td>
                        <td class="right">{{ number_format($totalFacture,0,',',' ') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Déjà payé (incluant ce règlement)</strong></td>
                        <td class="right">{{ number_format($totalPaye,0,',',' ') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Reste à payer</strong></td>
                        <td class="right">{{ number_format($reste,0,',',' ') }}</td>
                    </tr>
                    </tbody>
                </table>
            @endif

            <div class="center small muted" style="margin-top:10px;">Souche Client</div>
        </div>

    </div>

    <div class="sep"></div>

    {{-- 2ème souche (Établissement) --}}
    <div class="copy" style="margin-top:8px;">
        <div class="wm"></div>
        <div class="content">
            <div class="bar"></div>
            <div class="entete">
                <div>
                    <div class="h1">RÈGLEMENT N° {{ $reg->numero_reglement ?? '-' }}</div>
                    <div class="muted">Date : {{ $date }}</div>
                    <div class="muted">Type : {{ ucfirst($libType) }} — Mode : {{ $libVersement }}</div>
                    <div class="muted"><strong>Année académique :</strong> {{($reg->annee_academique->nom ?? '—')}}</div>

                </div>
                <div class="right small">
                    <div><strong>Entite :{{($reg->entite->nom_entite ?? '—')}} / {{($reg->entite->telephone ?? '—')}} </strong></div>

                    {{--<div><strong>{{ config('app.name', 'Mon Établissement') }}</strong></div>--}}
                    @if(optional($reg->user)->name)
                        <div>Caissier(ère) : {{ $reg->user->name }}</div>
                    @endif
                </div>
            </div>

            <div class="grid2 small" style="margin-top:8px;">
                <div>
                    <strong>Étudiant :</strong> {{ $et->nom ?? '—' }}<br>
                    <strong>Matricule :</strong> {{ $et->matricule ?? '—' }}<br>
                    <span class="muted">Tel : {{ $et->telephone_whatsapp ?? '—' }}</span>
                </div>
                <div class="right">
                    @if($libType === 'scolarité')
                        <strong>Cycle/Filière :</strong>
                        {{ $reg->cycles->nom_cycle ?? '—' }} / {{ $reg->filieres->nom_filiere ?? '—' }}<br>
                        <strong>Niveau/Spécialité :</strong>
                        {{ $reg->niveaux->nom_niveau ?? '—' }} / {{ $reg->specialites->nom_specialite ?? '—' }}
                    @else
                        <strong>Frais :</strong> {{ $reg->frais->nom_frais ?? '—' }}
                    @endif
                </div>
            </div>

            <table class="table">
                <thead>
                <tr>
                    <th>Libellé</th>
                    <th>Détail</th>
                    <th class="right">Montant versé</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Règlement {{ ucfirst($libType) }}</td>
                    <td>
                        {{ $reg->motif_reglement ?? '—' }}
                        @if($libType === 'scolarité' && $reg->id_tranche_scolarite)
                            @php $tr = \App\Models\tranche_scolarite::find($reg->id_tranche_scolarite); @endphp
                            @if($tr)
                                <div class="small muted">Tranche : {{ $tr->nom_tranche }} ({{ number_format($tr->montant_tranche,0,',',' ') }})</div>
                            @endif
                        @endif
                    </td>
                    <td class="right">{{ number_format($reg->montant_reglement ?? 0, 0, ',', ' ') }}</td>
                </tr>
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="2" class="right">Montant en lettres</th>
                    <th class="right">{{ $reg->lettre ?? '—' }}</th>
                </tr>
                </tfoot>
            </table>

            <div class="grid2 small" style="margin-top:8px;">
                <div>
                    <div class="muted">Affectation budgétaire :</div>
                    <div>
                        {{ $reg->budget->libelle_ligne_budget ?? '—' }}<br>
                        {{ $reg->ligne_budgetaire_entree->libelle_ligne_budgetaire_entree ?? '—' }} >
                        {{ $reg->donnee_budgetaire_entree->donnee_ligne_budgetaire_entree ?? $reg->donnee_budgetaire_entree->code_donnee_budgetaire_entree ?? '—' }} >
                        {{ $reg->donnee_ligne_budgetaire_entree->donnee_ligne_budgetaire_entree ?? '—' }}
                    </div>
                </div>
                <div class="right">
                    <div class="muted">Signature & cachet</div>
                    <div style="height:60px;border:1px dashed #aaa"></div>
                </div>
            </div>

            @if($totalFacture > 0)
                <div class="sep"></div>
                <table class="table">
                    <tbody>
                    <tr>
                        <td><strong>Total facture</strong></td>
                        <td class="right">{{ number_format($totalFacture,0,',',' ') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Déjà payé (incluant ce règlement)</strong></td>
                        <td class="right">{{ number_format($totalPaye,0,',',' ') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Reste à payer</strong></td>
                        <td class="right">{{ number_format($reste,0,',',' ') }}</td>
                    </tr>
                    </tbody>
                </table>
            @endif

            <div class="center small muted" style="margin-top:10px;">Souche Établissement</div>
        </div>
    </div>
</div>
</body>
</html>
