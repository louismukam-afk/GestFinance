
{{--
@php
    // Variables attendues: $facture, $etudiant, $tranches (Collection)
    $isFrais = ((int)$facture->type_facture === 0);
    //$school  = config('app.name', 'Établissement');
    $school  = $facture->entite->nom_entite;
    $now     = \Carbon\Carbon::now()->format('d/m/Y H:i');
@endphp
        <!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Facture N° {{ $facture->numero_facture }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color:#111; }
        .copy { width: 48.5%; display: inline-block; vertical-align: top; border:1px solid #ccc; padding:12px; margin:0 0.5%; }
        h2 { margin: 0 0 8px 0; font-size: 16px; }
        .meta, .meta td { font-size: 12px; }
        .row { display:flex; gap:12px; }
        .col { flex:1; }
        table { width:100%; border-collapse: collapse; }
        th, td { border:1px solid #ccc; padding:6px; text-align:left; }
        .no-border td, .no-border th { border:0; }
        .right { text-align:right; }
        .mt-1 { margin-top:6px; } .mt-2 { margin-top:10px; } .mt-3 { margin-top:14px; }
        .small { font-size: 11px; color:#555; }
    </style>
</head>
<body>

@for($i=0;$i<2;$i++)
    <div class="copy">
        <h2>{{ $school }} — FACTURE N° {{ $facture->numero_facture }}</h2>
        <table class="no-border meta">
            <tr>
                <td><strong>Date :</strong> {{ \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') }}</td>
                <td><strong>Année acad. :</strong> {{ $facture->Annee_academique->nom }}</td>
                <td class="right small">Imprimé le {{ $now }}</td>
            </tr>
        </table>

        <div class="row mt-2">
            <div class="col">
                <table class="no-border">
                    <tr><td><strong>Étudiant :</strong> {{ $etudiant->nom }}</td></tr>
                    <tr><td><strong>Matricule :</strong> {{ $etudiant->matricule ?? '—' }}</td></tr>
                    <tr><td><strong>Cycle / Filière :</strong>
                            {{ $facture->cycles->nom_cycle ?? '—' }} / {{ $facture->filieres->nom_filiere ?? '—' }}</td></tr>
                    <tr><td><strong>Niveau / Spécialité :</strong>
                            {{ $facture->niveaux->nom_niveau ?? '—' }} / {{ $facture->specialites->nom_specialite ?? '—' }}</td></tr>
                </table>
            </div>
            <div class="col">
                <table class="no-border">
                    <tr><td><strong>Budget :</strong> {{ $facture->budget->libelle_ligne_budget ?? '—' }}</td></tr>
                    <tr><td><strong>Ligne :</strong> {{ $facture->ligne_budgetaire_entree->libelle_ligne_budgetaire_entree ?? '—' }}</td></tr>
                    <tr><td><strong>Élément :</strong> {{ $facture->element_ligne_budgetaire_entree->libelle_elements_ligne_budgetaire_entree ?? '—' }}</td></tr>
                    <tr><td><strong>Donnée budgétaire :</strong> {{ $facture->donnee_budgetaire_entree->donnee_ligne_budgetaire_entree ?? '—' }}</td></tr>
                    <tr><td><strong>Donnée ligne :</strong> {{ $facture->donnee_ligne_budgetaire_entree->donnee_ligne_budgetaire_entree ?? '—' }}</td></tr>
                </table>
            </div>
        </div>

        <h3 class="mt-2" style="margin:8px 0;">Détails</h3>
        <table>
            <thead>
            <tr>
                <th>Intitulé</th>
                <th class="right">Montant</th>
            </tr>
            </thead>
            <tbody>
            @if($isFrais)
                <tr>
                    <td>Frais : {{ $facture->frais->nom_frais ?? '—' }}</td>
                    <td class="right">{{ number_format($facture->montant_total_facture,0,',',' ') }}</td>
                </tr>
            @else
                <tr>
                    <td>Scolarité : {{ $facture->scolarites?->specialites?->nom_specialite ?? '—' }}</td>
                    <td class="right">{{ number_format($facture->montant_total_facture,0,',',' ') }}</td>
                </tr>
                @if(isset($tranches) && $tranches->count())
                    <tr>
                        <td colspan="2">
                            <strong>Tranches :</strong>
                            <ul style="margin:6px 0 0 16px;">
                                @foreach($tranches as $t)
                                    <li>
                                        {{ $t->nom_tranche }} —
                                        {{ number_format($t->montant_tranche,0,',',' ') }} —
                                        {{ \Carbon\Carbon::parse($t->date_limite)->format('d/m/Y') }}
                                    </li>
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                @endif
            @endif
            </tbody>
            <tfoot>
            <tr>
                <th class="right">TOTAL</th>
                <th class="right">{{ number_format($facture->montant_total_facture,0,',',' ') }}</th>
            </tr>
            </tfoot>
        </table>

        <p class="small mt-3">
            Arrêtée la présente facture à la somme de : <em>{{ number_format($facture->montant_total_facture,0,',',' ') }} FCFA</em>.
        </p>
        <div class="row mt-2">
            <div class="col">
                <div class="small">Signature & cachet établissement</div>
                <div style="height:60px;border:1px dashed #ccc;margin-top:6px;"></div>
            </div>
            <div class="col">
                <div class="small">Visa de l’étudiant / du payeur</div>
                <div style="height:60px;border:1px dashed #ccc;margin-top:6px;"></div>
            </div>
        </div>
    </div>
@endfor
 body{
            background: url("{{ asset('uploads/images/1759420569_logo.jpg') }}")no-repeat center center fixed;
            background-size: 110mm!important;
            width: 100%;
            height: 135mm;
        }
</body>
</html>
--}}
       {{-- <!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Facture {{ $facture->numero_facture }}</title>
    <style>

        * { font-family: DejaVu Sans, Arial, sans-serif; font-size: 16px;
        }
        .wrap { width: 100%; }
        .bloc { width: 100%; padding: 10px 15px; box-sizing: border-box; }
        .entete { display:flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px; }
        .titre  { font-size: 16px; font-weight: bold; }
        .muted  { color:#666; }
        .mt-6 { margin-top: 6px; } .mt-10 { margin-top: 10px; }
        .mb-6 { margin-bottom: 6px; } .mb-10 { margin-bottom: 10px; }
        .table { width:100%; border-collapse: collapse; }
        .table th, .table td { border:1px solid #333; padding:6px; }
        .grid2 { display:grid; grid-template-columns: 1fr 1fr; gap: 6px 12px; }
        .small { font-size: 11px; }
        .separator { margin: 14px 0; border-top: 1px dashed #999; }
        .right { text-align: right; }
        .center { text-align: center; }
    </style>
</head>
<body>
@php
    $entiteNom = config('app.name', 'Mon Établissement');
     $school  = $facture->entite->nom_entite;
    $et = $etudiant;
    $isSco = ((int)$facture->type_facture === 1);
@endphp

@for($i=0; $i<2; $i++)
    <div class="wrap">
        <div class="bloc">
            <div class="entete">
                <div>
                    <div class="titre">FACTURE N° {{ $facture->numero_facture }}</div>
                    <div class="small muted">Date : {{ \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') }}</div>
                    <div class="small muted">Année académique : {{ $facture->Annee_academique->nom  }}</div>
                </div>
                <div class="right">
                    <div><strong>{{ $school }}</strong>
                       {{$facture->entite->telephone}}
                        {{ $facture->entite->localisation}}
                    </div>
                    <div class="small muted">Caissier(ère) : {{ $caissier }}</div>
                </div>
            </div>

            <div class="grid2 mb-10">
                <div>
                    <strong>Étudiant :</strong> {{ $et->nom }}<br>
                    <strong>Matricule :</strong> {{ $et->matricule ?? '—' }}<br>
                    <span class="small muted">Tel : {{ $et->telephone_whatsapp ?? '—' }} / {{ $et->telephone_2_etudiants ?? '—' }}</span>
                </div>
                <div class="right">
                    <strong>Cycle / Filière :</strong>
                    {{ $facture->cycles->nom_cycle ?? '—' }} / {{ $facture->filieres->nom_filiere ?? '—' }}<br>
                    <strong>Niveau / Spécialité :</strong>
                    {{ $facture->niveaux->nom_niveau ?? '—' }} / {{ $facture->specialites->nom_specialite ?? '—' }}
                </div>
            </div>

            <table class="table mb-10">
                <thead>
                <tr>
                    <th>Nature</th>
                    <th>Détail</th>
                    <th class="right">Montant</th>
                </tr>
                </thead>
                <tbody>
                @if($isSco)
                    <tr>
                        <td>Scolarité</td>
                        <td>
                            Total scolarité :
                            <strong>{{ number_format($facture->scolarites->montant_total ?? 0, 0, ',', ' ') }}</strong>
                            @if(($tranches ?? collect())->count())
                                <div class="small muted mt-6"><em>Tranches prévues :</em></div>
                                <ul class="small">
                                    @foreach($tranches as $t)
                                        <li>{{ $t->nom_tranche }} — {{ number_format($t->montant_tranche,0,',',' ') }}
                                            — {{ \Carbon\Carbon::parse($t->date_limite)->format('d/m/Y') }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </td>
                        <td class="right">{{ number_format($facture->montant_total_facture,0,',',' ') }}</td>
                    </tr>
                @else
                    <tr>
                        <td>Frais</td>
                        <td>{{ $facture->frais->nom_frais ?? '—' }}</td>
                        <td class="right">{{ number_format($facture->montant_total_facture,0,',',' ') }}</td>
                    </tr>
                @endif
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="2" class="right">TOTAL</th>
                    <th class="right">{{ number_format($facture->montant_total_facture,0,',',' ') }}</th>
                </tr>
                </tfoot>
            </table>

            <div class="grid2">
                <div>
                    <div class="small muted">Budget :</div>
                    <div class="small">
                        {{ $facture->budget->libelle_ligne_budget ?? '—' }}<br>
                        {{ $facture->ligne_budgetaire_entree->libelle_ligne_budgetaire_entree ?? '—' }} >
                        {{ $facture->element_ligne_budgetaire_entree->libelle_elements_ligne_budgetaire_entree ?? '—' }} >
                        {{ $facture->donnee_ligne_budgetaire_entree->donnee_ligne_budgetaire_entree ?? '—' }}
                    </div>
                </div>
                <div class="right">
                    <div class="small muted">Signature & cachet</div>
                    <div style="height:60px;border:1px dashed #aaa"></div>
                </div>
            </div>

            <div class="center small muted mt-10">Merci pour votre confiance.</div>
        </div>
    </div>

    @if($i==0)
        <div class="separator"></div>
    @endif
@endfor
</body>
</html>

--}}
       {{-- <!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Facture {{ $facture->numero_facture }}</title>
    <style>
        /* Dompdf: tailles en mm/pt gérées. On reste simple et compatible */
        * { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
        html, body { margin: 0; padding: 0; }

        /* === FILIGRANE PAGE ENTIÈRE (les 2 souches en profitent) === */
        .page-bg {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            z-index: -1;
            opacity: 0.06; /* visibilité légère */
            background: url('{{ asset('uploads/images/1759420569_logo.jpg') }}') no-repeat center center;
            background-size: 80% auto; /* ajuste si besoin (60–100%) */
        }

        .wrap { width: 100%; }
        .bloc { width: 100%; padding: 10px 15px; box-sizing: border-box; }
        .entete { margin-bottom: 8px; }
        .titre  { font-size: 16px; font-weight: bold; }
        .muted  { color:#666; }
        .mt-6 { margin-top: 6px; } .mt-10 { margin-top: 10px; }
        .mb-6 { margin-bottom: 6px; } .mb-10 { margin-bottom: 10px; }
        .table { width:100%; border-collapse: collapse; }
        .table th, .table td { border:1px solid #333; padding:6px; vertical-align: top; }
        .grid2 { display: table; width:100%; }
        .grid2 > div { display: table-cell; width: 50%; }
        .small { font-size: 11px; }
        .separator { margin: 14px 0; border-top: 1px dashed #999; }
        .right { text-align: right; }
        .center { text-align: center; }

        /* === BANDES ROUGES DANS L’EN-TÊTE === */
        .header-bands {
            position: relative;
            padding: 8px 0 10px 0;
        }
        .header-bands:before,
        .header-bands:after {
            content: "";
            position: absolute;
            left: 0; right: 0;
            height: 4px;            /* épaisseur de la bande */
            background: #c1121f;    /* rouge (ajuste au besoin) */
        }
        .header-bands:before { top: -2px; }  /* bande du haut */
        .header-bands:after  { bottom: -2px; } /* bande du bas */

        .row {
            display: table;
            width: 100%;
        }
        .col {
            display: table-cell;
            vertical-align: top;
            padding: 2px 0;
        }
        .col-left  { width: 60%; }
        .col-right { width: 40%; text-align: right; }
    </style>
</head>
<body>
<div class="page-bg"></div>

@php
    $entiteNom = config('app.name', 'Mon Établissement');
    $school    = $facture->entite->nom_entite ?? $entiteNom;
    $phone     = $facture->entite->telephone ?? '';
    $addr      = $facture->entite->localisation ?? '';
    $et        = $etudiant;
    $isSco     = ((int)$facture->type_facture === 1);
@endphp

@for($i=0; $i<2; $i++)
    <div class="wrap">
        <div class="bloc">
            <!-- EN-TÊTE AVEC BANDES ROUGES -->
            <div class="entete header-bands">
                <div class="row">
                    <div class="col col-left">
                        <div class="titre">FACTURE N° {{ $facture->numero_facture }}</div>
                        <div class="small muted">
                            Date : {{ \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') }}
                        </div>
                        <div class="small muted">
                            Année académique : {{ $facture->Annee_academique->nom ?? '—' }}
                        </div>
                    </div>
                    <div class="col col-right">
                        <div><strong>{{ $school }}</strong></div>
                        @if($phone)<div class="small">{{ $phone }}</div>@endif
                        @if($addr)<div class="small">{{ $addr }}</div>@endif
                        <div class="small muted">Caissier(ère) : {{ $caissier }}</div>
                    </div>
                </div>
            </div>

            <div class="grid2 mb-10">
                <div>
                    <strong>Étudiant :</strong> {{ $et->nom }}<br>
                    <strong>Matricule :</strong> {{ $et->matricule ?? '—' }}<br>
                    <span class="small muted">
                        Tel : {{ $et->telephone_whatsapp ?? '—' }} / {{ $et->telephone_2_etudiants ?? '—' }}
                    </span>
                </div>
                <div class="right">
                    <strong>Cycle / Filière :</strong>
                    {{ $facture->cycles->nom_cycle ?? '—' }} / {{ $facture->filieres->nom_filiere ?? '—' }}<br>
                    <strong>Niveau / Spécialité :</strong>
                    {{ $facture->niveaux->nom_niveau ?? '—' }} / {{ $facture->specialites->nom_specialite ?? '—' }}
                </div>
            </div>

            <table class="table mb-10">
                <thead>
                <tr>
                    <th>Nature</th>
                    <th>Détail</th>
                    <th class="right">Montant</th>
                </tr>
                </thead>
                <tbody>
                @if($isSco)
                    <tr>
                        <td>Scolarité</td>
                        <td>
                            Total scolarité :
                            <strong>{{ number_format($facture->scolarites->montant_total ?? 0, 0, ',', ' ') }}</strong>
                            @if(($tranches ?? collect())->count())
                                <div class="small muted mt-6"><em>Tranches prévues :</em></div>
                                <ul class="small" style="margin:0; padding-left:14px;">
                                    @foreach($tranches as $t)
                                        <li>
                                            {{ $t->nom_tranche }}
                                            — {{ number_format($t->montant_tranche,0,',',' ') }}
                                            — {{ \Carbon\Carbon::parse($t->date_limite)->format('d/m/Y') }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </td>
                        <td class="right">{{ number_format($facture->montant_total_facture,0,',',' ') }}</td>
                    </tr>
                @else
                    <tr>
                        <td>Frais</td>
                        <td>{{ $facture->frais->nom_frais ?? '—' }}</td>
                        <td class="right">{{ number_format($facture->montant_total_facture,0,',',' ') }}</td>
                    </tr>
                @endif
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="2" class="right">TOTAL</th>
                    <th class="right">{{ number_format($facture->montant_total_facture,0,',',' ') }}</th>
                </tr>
                </tfoot>
            </table>

            <div class="grid2">
                <div>
                    <div class="small muted">Budget :</div>
                    <div class="small">
                        {{ $facture->budget->libelle_ligne_budget ?? '—' }}<br>
                        {{ $facture->ligne_budgetaire_entree->libelle_ligne_budgetaire_entree ?? '—' }} >
                        {{ $facture->element_ligne_budgetaire_entree->libelle_elements_ligne_budgetaire_entree ?? '—' }} >
                        {{ $facture->donnee_ligne_budgetaire_entree->donnee_ligne_budgetaire_entree ?? '—' }}
                    </div>
                </div>
                <div class="right">
                    <div class="small muted">Signature & cachet</div>
                    <div style="height:60px;border:1px dashed #aaa"></div>
                </div>
            </div>

            <div class="center small muted mt-10">Merci pour votre confiance.</div>
        </div>
    </div>

    @if($i==0)
        <div class="separator"></div>
    @endif
@endfor
</body>
</html>
--}}
        <!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Facture {{ $facture->numero_facture }}</title>
    <style>
        * { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
        html, body { margin: 0; padding: 0; }
        .sheet { width: 100%; padding: 10mm 10mm 12mm 10mm; box-sizing: border-box; }

        /* un “ticket” = une souche sur la même page */
        .ticket {
            position: relative;
            border: 1px solid #333;
            padding: 10mm 8mm;
            margin-bottom: 8mm;      /* espace entre les 2 souches */
            min-height: 120mm;       /* pour forcer la hauteur et bien tenir sur une page A4 */
            box-sizing: border-box;
        }

        /* filigrane image */
        .ticket .bg-watermark {
            position: absolute;
            inset: 0;
            z-index: -1;
            opacity: .06;
            background: url('{{ asset('uploads/images/1759420569_logo.jpg') }}') no-repeat center center;
            background-size: 80% auto;   /* ajuste si besoin */
        }

        /* libellé en filigrane (texte) */
        .ticket .wm-label {
            position: absolute;
            top: 48%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-25deg);
            font-size: 34px;
            letter-spacing: 2px;
            color: #c1121f;
            opacity: .10;
            font-weight: 700;
            white-space: nowrap;
            z-index: 0;
        }

        .bloc { position: relative; z-index: 1; }
        .entete { margin-bottom: 8px; }
        .titre  { font-size: 16px; font-weight: bold; }
        .muted  { color:#666; }
        .mt-6 { margin-top: 6px; } .mt-10 { margin-top: 10px; }
        .mb-6 { margin-bottom: 6px; } .mb-10 { margin-bottom: 10px; }
        .table { width:100%; border-collapse: collapse; }
        .table th, .table td { border:1px solid #333; padding:6px; vertical-align: top; }
        .grid2 { display: table; width:100%; }
        .grid2 > div { display: table-cell; width: 50%; }
        .small { font-size: 11px; }
        .right { text-align: right; }

        /* bandes rouges d’en-tête */
        .header-bands { position: relative; padding: 8px 0 10px 0; }
        .header-bands:before, .header-bands:after {
            content: ""; position: absolute; left: 0; right: 0; height: 4px; background: #c1121f;
        }
        .header-bands:before { top: -2px; }
        .header-bands:after  { bottom: -2px; }

        .row { display: table; width: 100%; }
        .col { display: table-cell; vertical-align: top; padding: 2px 0; }
        .col-left  { width: 60%; }
        .col-right { width: 40%; text-align: right; }
    </style>
</head>
<body>
@php
    $school = $facture->entite->nom_entite ?? config('app.name', 'Mon Établissement');
    $phone  = $facture->entite->telephone ?? '';
    $addr   = $facture->entite->localisation ?? '';
    $et     = $etudiant;
    $isSco  = ((int)$facture->type_facture === 1);
@endphp

<div class="sheet">

    {{-- ====== SOUCHE 1 : COPIE CLIENT ====== --}}
    <div class="ticket">
        <div class="bg-watermark"></div>
        <div class="wm-label">COPIE CLIENT</div>

        <div class="bloc">
            <div class="entete header-bands">
                <div class="row">
                    <div class="col col-left">
                        <div class="titre">FACTURE N° {{ $facture->numero_facture }}</div>
                        <div class="small muted">Date : {{ \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') }}</div>
                        <div class="small muted">Année académique : {{ $facture->Annee_academique->nom ?? '—' }}</div>
                    </div>
                    <div class="col col-right">
                        <div><strong>{{ $school }}</strong></div>
                        @if($phone)<div class="small">{{ $phone }}</div>@endif
                        @if($addr)<div class="small">{{ $addr }}</div>@endif
                        <div class="small muted">Caissier(ère) : {{ $caissier }}</div>
                    </div>
                </div>
            </div>

            <div class="grid2 mb-10">
                <div>
                    <strong>Étudiant :</strong> {{ $et->nom }}<br>
                    <strong>Matricule :</strong> {{ $et->matricule ?? '—' }}<br>
                    <span class="small muted">Tel : {{ $et->telephone_whatsapp ?? '—' }} / {{ $et->telephone_2_etudiants ?? '—' }}</span>
                </div>
                <div class="right">
                    <strong>Cycle / Filière :</strong>
                    {{ $facture->cycles->nom_cycle ?? '—' }} / {{ $facture->filieres->nom_filiere ?? '—' }}<br>
                    <strong>Niveau / Spécialité :</strong>
                    {{ $facture->niveaux->nom_niveau ?? '—' }} / {{ $facture->specialites->nom_specialite ?? '—' }}
                </div>
            </div>

            <table class="table mb-10">
                <thead>
                <tr>
                    <th>Nature</th><th>Détail</th><th class="right">Montant</th>
                </tr>
                </thead>
                <tbody>
                @if($isSco)
                    <tr>
                        <td>Scolarité</td>
                        <td>
                            Total scolarité :
                            <strong>{{ number_format($facture->scolarites->montant_total ?? 0, 0, ',', ' ') }}</strong>
                            @if(($tranches ?? collect())->count())
                                <div class="small muted mt-6"><em>Tranches prévues :</em></div>
                                <ul class="small" style="margin:0; padding-left:14px;">
                                    @foreach($tranches as $t)
                                        <li>{{ $t->nom_tranche }} — {{ number_format($t->montant_tranche,0,',',' ') }} — {{ \Carbon\Carbon::parse($t->date_limite)->format('d/m/Y') }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </td>
                        <td class="right">{{ number_format($facture->montant_total_facture,0,',',' ') }}</td>
                    </tr>
                @else
                    <tr>
                        <td>Frais</td>
                        <td>{{ $facture->frais->nom_frais ?? '—' }}</td>
                        <td class="right">{{ number_format($facture->montant_total_facture,0,',',' ') }}</td>
                    </tr>
                @endif
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="2" class="right">TOTAL</th>
                    <th class="right">{{ number_format($facture->montant_total_facture,0,',',' ') }}</th>
                </tr>
                </tfoot>
            </table>

            <div class="grid2">
                <div>
                    <div class="small muted">Budget :</div>
                    <div class="small">
                        {{ $facture->budget->libelle_ligne_budget ?? '—' }}<br>
                        {{ $facture->ligne_budgetaire_entree->libelle_ligne_budgetaire_entree ?? '—' }} >
                        {{ $facture->element_ligne_budgetaire_entree->libelle_elements_ligne_budgetaire_entree ?? '—' }} >
                        {{ $facture->donnee_ligne_budgetaire_entree->donnee_ligne_budgetaire_entree ?? '—' }}
                    </div>
                </div>
                <div class="right">
                    <div class="small muted">Signature & cachet</div>
                    <div style="height:60px;border:1px dashed #aaa"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ====== SOUCHE 2 : SOUCHE ÉTABLISSEMENT ====== --}}
    <div class="ticket" style="margin-bottom:0">
        <div class="bg-watermark"></div>
        <div class="wm-label">SOUCHE ÉTABLISSEMENT</div>

        {{-- contenu identique à la souche 1 --}}
        <div class="bloc">
            <div class="entete header-bands">
                <div class="row">
                    <div class="col col-left">
                        <div class="titre">FACTURE N° {{ $facture->numero_facture }}</div>
                        <div class="small muted">Date : {{ \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') }}</div>
                        <div class="small muted">Année académique : {{ $facture->Annee_academique->nom ?? '—' }}</div>
                    </div>
                    <div class="col col-right">
                        <div><strong>{{ $school }}</strong></div>
                        @if($phone)<div class="small">{{ $phone }}</div>@endif
                        @if($addr)<div class="small">{{ $addr }}</div>@endif
                        <div class="small muted">Caissier(ère) : {{ $caissier }}</div>
                    </div>
                </div>
            </div>

            <div class="grid2 mb-10">
                <div>
                    <strong>Étudiant :</strong> {{ $et->nom }}<br>
                    <strong>Matricule :</strong> {{ $et->matricule ?? '—' }}<br>
                    <span class="small muted">Tel : {{ $et->telephone_whatsapp ?? '—' }} / {{ $et->telephone_2_etudiants ?? '—' }}</span>
                </div>
                <div class="right">
                    <strong>Cycle / Filière :</strong>
                    {{ $facture->cycles->nom_cycle ?? '—' }} / {{ $facture->filieres->nom_filiere ?? '—' }}<br>
                    <strong>Niveau / Spécialité :</strong>
                    {{ $facture->niveaux->nom_niveau ?? '—' }} / {{ $facture->specialites->nom_specialite ?? '—' }}
                </div>
            </div>

            <table class="table mb-10">
                <thead>
                <tr>
                    <th>Nature</th><th>Détail</th><th class="right">Montant</th>
                </tr>
                </thead>
                <tbody>
                @if($isSco)
                    <tr>
                        <td>Scolarité</td>
                        <td>
                            Total scolarité :
                            <strong>{{ number_format($facture->scolarites->montant_total ?? 0, 0, ',', ' ') }}</strong>
                            @if(($tranches ?? collect())->count())
                                <div class="small muted mt-6"><em>Tranches prévues :</em></div>
                                <ul class="small" style="margin:0; padding-left:14px;">
                                    @foreach($tranches as $t)
                                        <li>{{ $t->nom_tranche }} — {{ number_format($t->montant_tranche,0,',',' ') }} — {{ \Carbon\Carbon::parse($t->date_limite)->format('d/m/Y') }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </td>
                        <td class="right">{{ number_format($facture->montant_total_facture,0,',',' ') }}</td>
                    </tr>
                @else
                    <tr>
                        <td>Frais</td>
                        <td>{{ $facture->frais->nom_frais ?? '—' }}</td>
                        <td class="right">{{ number_format($facture->montant_total_facture,0,',',' ') }}</td>
                    </tr>
                @endif
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="2" class="right">TOTAL</th>
                    <th class="right">{{ number_format($facture->montant_total_facture,0,',',' ') }}</th>
                </tr>
                </tfoot>
            </table>

            <div class="grid2">
                <div>
                    <div class="small muted">Budget :</div>
                    <div class="small">
                        {{ $facture->budget->libelle_ligne_budget ?? '—' }}<br>
                        {{ $facture->ligne_budgetaire_entree->libelle_ligne_budgetaire_entree ?? '—' }} >
                        {{ $facture->element_ligne_budgetaire_entree->libelle_elements_ligne_budgetaire_entree ?? '—' }} >
                        {{ $facture->donnee_ligne_budgetaire_entree->donnee_ligne_budgetaire_entree ?? '—' }}
                    </div>
                </div>
                <div class="right">
                    <div class="small muted">Signature & cachet</div>
                    <div style="height:60px;border:1px dashed #aaa"></div>
                </div>
            </div>
        </div>
    </div>

</div>
</body>
</html>
@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('etudiant_management') }}"><strong>liste des étudiants</strong></a></li>
        <li><a href="{{ route('etudiant') }}"><strong>Gestion des étudiants</strong></a></li>--}}
        {{--<li class="active"><strong>{{ $title }}</strong></li>--}}
    </ol>
@endsection