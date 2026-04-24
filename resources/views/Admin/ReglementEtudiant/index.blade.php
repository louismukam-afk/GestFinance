{{--
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Règlement {{ $reglement->numero_reglement }}</title>
    <style>
        * { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
        html, body { margin: 0; padding: 0; }
        .sheet { width: 100%; padding: 10mm 10mm 12mm 10mm; box-sizing: border-box; }

        .ticket {
            position: relative;
            border: 1px solid #333;
            padding: 10mm 8mm;
            margin-bottom: 8mm;
            min-height: 120mm;
            box-sizing: border-box;
        }
        .ticket .bg-watermark {
            position: absolute; inset: 0; z-index: -1; opacity: .06;
            background: url('{{ public_path('uploads/images/1759420569_logo.jpg') }}') no-repeat center center;
            background-size: 80% auto;
        }
        .ticket .wm-label {
            position: absolute; top: 48%; left: 50%;
            transform: translate(-50%,-50%) rotate(-25deg);
            font-size: 34px; font-weight: 700; color:#c1121f; opacity:.10; letter-spacing:2px; white-space:nowrap;
            z-index: 0;
        }
        .bloc { position: relative; z-index: 1; }
        .entete { margin-bottom: 8px; }
        .titre  { font-size: 16px; font-weight: bold; }
        .muted  { color:#666; }
        .mt-6 { margin-top:6px; } .mb-6 { margin-bottom:6px; }
        .mb-10 { margin-bottom: 10px; }
        .table { width:100%; border-collapse: collapse; }
        .table th, .table td { border:1px solid #333; padding:6px; vertical-align: top; }
        .grid2 { display: table; width:100%; }
        .grid2 > div { display: table-cell; width: 50%; }
        .small { font-size: 11px; }
        .right { text-align: right; }
        .header-bands { position: relative; padding: 8px 0 10px 0; }
        .header-bands:before, .header-bands:after {
            content:""; position:absolute; left:0; right:0; height:4px; background:#c1121f;
        }
        .header-bands:before { top:-2px; } .header-bands:after { bottom:-2px; }
        .row { display: table; width:100%; }
        .col { display: table-cell; vertical-align: top; padding: 2px 0; }
        .col-left { width: 60%; } .col-right { width: 40%; text-align:right; }
    </style>
</head>
<body>
@php
    $r  = $reglement;
    $et = $etudiant;
    $isSco = ((int)$r->type_reglement === 1);
    $school = config('app.name','Établissement');
    if (method_exists($r, 'entite') && $r->entite) {
        $school = $r->entite->nom_entite ?? $school;
    }
    $phone = $r->entite->telephone ?? '';
    $addr  = $r->entite->localisation ?? '';
@endphp

<div class="sheet">
    --}}
{{-- SOUCHE CLIENT --}}{{--

    <div class="ticket">
        <div class="bg-watermark"></div>
        <div class="wm-label">COPIE CLIENT</div>

        <div class="bloc">
            <div class="entete header-bands">
                <div class="row">
                    <div class="col col-left">
                        <div class="titre">RÈGLEMENT N° {{ $r->numero_reglement }}</div>
                        <div class="small muted">Date : {{ \Carbon\Carbon::parse($r->date_reglement)->format('d/m/Y') }}</div>
                        <div class="small muted">Année académique : {{ $r->annee_academique->nom ?? '—' }}</div>
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
                    {{ $r->cycles->nom_cycle ?? '—' }} / {{ $r->filieres->nom_filiere ?? '—' }}<br>
                    <strong>Niveau / Spécialité :</strong>
                    {{ $r->niveaux->nom_niveau ?? '—' }} / {{ $r->specialites->nom_specialite ?? '—' }}
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
                            @if(($tranches ?? collect())->count())
                                <div class="small muted"><em>Tranches prévues :</em></div>
                                <ul class="small" style="margin:0; padding-left:14px;">
                                    @foreach($tranches as $t)
                                        <li>{{ $t->nom_tranche }} — {{ number_format($t->montant_tranche,0,',',' ') }} — {{ \Carbon\Carbon::parse($t->date_limite)->format('d/m/Y') }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <em>Toutes les tranches</em>
                            @endif
                        </td>
                        <td class="right">{{ number_format($r->montant_reglement,0,',',' ') }}</td>
                    </tr>
                @else
                    <tr>
                        <td>Frais</td>
                        <td>{{ $r->frais->nom_frais ?? '—' }}</td>
                        <td class="right">{{ number_format($r->montant_reglement,0,',',' ') }}</td>
                    </tr>
                @endif
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="2">Montant en lettres</th>
                    <th class="right">{{ $r->lettre }}</th>
                </tr>
                </tfoot>
            </table>

            <table class="table">
                <tbody>
                <tr>
                    <td><strong>Total facture</strong></td>
                    <td class="right">{{ number_format($totalFacture,0,',',' ') }}</td>
                </tr>
                <tr>
                    <td><strong>Total déjà payé</strong></td>
                    <td class="right">{{ number_format($totalPaye,0,',',' ') }}</td>
                </tr>
                <tr>
                    <td><strong>Reste dû</strong></td>
                    <td class="right">{{ number_format($reste,0,',',' ') }}</td>
                </tr>
                </tbody>
            </table>

            <div class="grid2 mt-6">
                <div>
                    <div class="small muted">Budget :</div>
                    <div class="small">
                        {{ $r->budget->libelle_ligne_budget ?? '—' }}<br>
                        {{ $r->ligne_budgetaire_entree->libelle_ligne_budgetaire_entree ?? '—' }} >
                        {{ $r->element_ligne_budgetaire_entree->libelle_elements_ligne_budgetaire_entree ?? '—' }} >
                        {{ $r->donnee_ligne_budgetaire_entree->donnee_ligne_budgetaire_entree ?? '—' }}
                    </div>
                </div>
                <div class="right">
                    <div class="small muted">Signature & cachet</div>
                    <div style="height:60px;border:1px dashed #aaa"></div>
                </div>
            </div>
        </div>
    </div>

    --}}
{{-- SOUCHE ÉTABLISSEMENT --}}{{--

    <div class="ticket" style="margin-bottom:0">
        <div class="bg-watermark"></div>
        <div class="wm-label">SOUCHE ÉTABLISSEMENT</div>

        --}}
{{-- contenu identique --}}{{--

        @php
            // On réutilise les mêmes blocs que ci-dessus (copié-collé par simplicité/fiabilité d’impression)
        @endphp
        <div class="bloc">
            <div class="entete header-bands">
                <div class="row">
                    <div class="col col-left">
                        <div class="titre">RÈGLEMENT N° {{ $r->numero_reglement }}</div>
                        <div class="small muted">Date : {{ \Carbon\Carbon::parse($r->date_reglement)->format('d/m/Y') }}</div>
                        <div class="small muted">Année académique : {{ $r->annee_academique->nom ?? '—' }}</div>
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
                    {{ $r->cycles->nom_cycle ?? '—' }} / {{ $r->filieres->nom_filiere ?? '—' }}<br>
                    <strong>Niveau / Spécialité :</strong>
                    {{ $r->niveaux->nom_niveau ?? '—' }} / {{ $r->specialites->nom_specialite ?? '—' }}
                </div>
            </div>

            <table class="table mb-10">
                <thead>
                <tr><th>Nature</th><th>Détail</th><th class="right">Montant</th></tr>
                </thead>
                <tbody>
                @if($isSco)
                    <tr>
                        <td>Scolarité</td>
                        <td>
                            @if(($tranches ?? collect())->count())
                                <div class="small muted"><em>Tranches prévues :</em></div>
                                <ul class="small" style="margin:0; padding-left:14px;">
                                    @foreach($tranches as $t)
                                        <li>{{ $t->nom_tranche }} — {{ number_format($t->montant_tranche,0,',',' ') }} — {{ \Carbon\Carbon::parse($t->date_limite)->format('d/m/Y') }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <em>Toutes les tranches</em>
                            @endif
                        </td>
                        <td class="right">{{ number_format($r->montant_reglement,0,',',' ') }}</td>
                    </tr>
                @else
                    <tr>
                        <td>Frais</td>
                        <td>{{ $r->frais->nom_frais ?? '—' }}</td>
                        <td class="right">{{ number_format($r->montant_reglement,0,',',' ') }}</td>
                    </tr>
                @endif
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="2">Montant en lettres</th>
                    <th class="right">{{ $r->lettre }}</th>
                </tr>
                </tfoot>
            </table>

            <table class="table">
                <tbody>
                <tr>
                    <td><strong>Total facture</strong></td>
                    <td class="right">{{ number_format($totalFacture,0,',',' ') }}</td>
                </tr>
                <tr>
                    <td><strong>Total déjà payé</strong></td>
                    <td class="right">{{ number_format($totalPaye,0,',',' ') }}</td>
                </tr>
                <tr>
                    <td><strong>Reste dû</strong></td>
                    <td class="right">{{ number_format($reste,0,',',' ') }}</td>
                </tr>
                </tbody>
            </table>

            <div class="grid2 mt-6">
                <div>
                    <div class="small muted">Budget :</div>
                    <div class="small">
                        {{ $r->budget->libelle_ligne_budget ?? '—' }}<br>
                        {{ $r->ligne_budgetaire_entree->libelle_ligne_budgetaire_entree ?? '—' }} >
                        {{ $r->element_ligne_budgetaire_entree->libelle_elements_ligne_budgetaire_entree ?? '—' }} >
                        {{ $r->donnee_ligne_budgetaire_entree->donnee_ligne_budgetaire_entree ?? '—' }}
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
--}}
{{--@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="text-primary">💵 Règlements — Facture N° {{ $facture->numero_facture }}</h3>
        <p>
            <strong>Étudiant :</strong> {{ $etudiant->nom }}
            — <strong>Matricule :</strong> {{ $etudiant->matricule ?? '—' }}<br>
            <strong>Type :</strong> {{ $facture->type_facture === 1 ? 'Scolarité' : 'Frais' }} —
            <strong>Montant facture :</strong> {{ number_format($totalFacture,0,',',' ') }} —
            <strong>Payé :</strong> {{ number_format($totalPaye,0,',',' ') }} —
            <strong>Reste :</strong> {{ number_format($reste,0,',',' ') }}
        </p>

        <div class="mb-3">
            <a class="btn btn-primary" href="{{ route('reglement_from_facture', $facture->id) }}">➕ Nouveau règlement</a>
            <a class="btn btn-default" href="{{ route('factures_by_etudiant', $etudiant->id) }}">↩️ Retour factures étudiant</a>
        </div>

        <div class="table-responsive">
            <table id="regTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>N°</th>
                    <th>Date</th>
                    <th>Versement</th>
                    <th>Caisse/Banque</th>
                    <th>Montant</th>
                    <th>Utilisateur</th>
                    <th>PDF</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($reglements as $i => $r)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $r->numero_reglement }}</td>
                        <td>{{ \Carbon\Carbon::parse($r->date_reglement)->format('d/m/Y') }}</td>
                        <td>{{ strtoupper($r->type_versement) }}</td>
                        <td>
                            @if($r->type_versement === 'espece')
                                {{ $r->caisse->nom_caisse ?? '—' }}
                            @elseif($r->type_versement === 'bancaire')
                                {{ $r->banque->nom_banque ?? '—' }}
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ number_format($r->montant_reglement,0,',',' ') }}</td>
                        <td>{{ $r->user->name ?? '—' }}</td>
                        <td><a class="btn btn-xs btn-default" target="_blank" href="{{ route('reglement_pdf',$r->id) }}">🧾 PDF</a></td>
                        <td>
                            <a class="btn btn-xs btn-warning"
                            <a class="btn btn-xs btn-warning" href="{{ route('edit_reglement', $r->id) }}">✏️</a>

                            <form action="{{ route('delete_reglement', $r->id) }}" method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-danger" onclick="return confirm('Supprimer ce règlement ?')">🗑️</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted">Aucun règlement pour cette facture.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function(){
            if ($.fn && $.fn.DataTable) {
                $('#regTable').DataTable({
                    responsive: true,
                    pageLength: 25,
                    language: { url: "{{ asset('js/datatables/fr-FR.json') }}" }
                });
            }
        });
    </script>
@endsection--}}

@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="text-primary">💵 Règlements — Facture N° {{ $facture->numero_facture }}</h3>
        <p>
            <strong>Étudiant :</strong> {{ $etudiant->nom }}
            — <strong>Matricule :</strong> {{ $etudiant->matricule ?? '—' }}<br>
            <strong>Type :</strong> {{ $facture->type_facture === 1 ? 'Scolarité' : 'Frais' }} —
            <strong>Montant facture :</strong> {{ number_format($totalFacture,0,',',' ') }} —
            <strong>Payé :</strong> {{ number_format($totalPaye,0,',',' ') }} —
            <strong>Reste :</strong> {{ number_format($reste,0,',',' ') }}
        </p>

        <div class="mb-3">
            <a class="btn btn-primary" href="{{ route('reglement_from_facture', $facture->id) }}">➕ Nouveau règlement</a>
            <a class="btn btn-default" href="{{ route('factures_by_etudiant', $etudiant->id) }}">↩️ Retour factures étudiant</a>
        </div>

        <div class="table-responsive">
            <table id="regTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>N°</th>
                    <th>Date</th>
                    <th>Versement</th>
                    <th>Caisse / Banque</th>
                    <th>Montant</th>
                    <th>Montant en lettre</th>
                    <th>Motif</th>
                    <th>Utilisateur</th>
                    <th>PDF</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @php
                    // mapping pour les enregistrements où type_versement est un INT
                    $mapTypes = [
                        0 => 'Espèce',
                        1 => 'Bancaire',
                        2 => 'Orange Money',
                        3 => 'MTN Money',
                    ];
                @endphp
                @forelse($reglements as $i => $r)
                    @php
                        // 1. récupérer le type sous forme de texte
                        if (is_numeric($r->type_versement)) {
                            $typeLib = $mapTypes[(int)$r->type_versement] ?? '—';
                            $typeRaw = (int)$r->type_versement;
                        } else {
                            // si c'est string
                            $typeRaw = $r->type_versement;
                            switch ($r->type_versement) {
                                case 'espece':  $typeLib = 'Espèce'; break;
                                case 'bancaire':$typeLib = 'Bancaire'; break;
                                case 'om':      $typeLib = 'Orange Money'; break;
                                case 'mtn':     $typeLib = 'MTN Money'; break;
                                default:        $typeLib = strtoupper($r->type_versement);
                            }
                        }

                        // 2. déterminer la caisse / banque à afficher
                        $support = '—';
                        // cas INT
                        if (is_numeric($r->type_versement)) {
                            if ((int)$r->type_versement === 0) {
                                $support = $r->caisse->nom_caisse ?? '—';
                            } elseif ((int)$r->type_versement === 1) {
                                $support = $r->banque->nom_banque ?? '—';
                            }
                        } else {
                            // cas string
                            if ($r->type_versement === 'espece') {
                                $support = $r->caisse->nom_caisse ?? '—';
                            } elseif ($r->type_versement === 'bancaire') {
                                $support = $r->banque->nom_banque ?? '—';
                            }
                        }
                    @endphp
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $r->numero_reglement }}</td>
                        <td>{{ \Carbon\Carbon::parse($r->date_reglement)->format('d/m/Y') }}</td>
                        <td>{{ $typeLib }}</td>
                        <td>{{ $support }}</td>
                        <td>{{ number_format($r->montant_reglement,0,',',' ') }}</td>
                        <td>{{ $r->lettre }}</td>
                        <td>{{ $r->motif_reglement }}</td>
                        <td>{{ $r->user->name ?? '—' }}</td>
                        <td>
                            <a class="btn btn-xs btn-default" target="_blank" href="{{ route('reglement_pdf',$r->id) }}">🧾 PDF</a>
                        </td>
                        <td>
                            <a class="btn btn-xs btn-warning" href="{{ route('edit_reglement', $r->id) }}">✏️</a>
                            <form action="{{ route('delete_reglement', $r->id) }}" method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-danger" onclick="return confirm('Supprimer ce règlement ?')">🗑️</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted">Aucun règlement pour cette facture.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function(){
            if ($.fn && $.fn.DataTable) {
                $('#regTable').DataTable({
                    responsive: true,
                    pageLength: 25,
                    language: { url: "{{ asset('js/datatables/fr-FR.json') }}" }
                });
            }
        });
    </script>
@endsection

