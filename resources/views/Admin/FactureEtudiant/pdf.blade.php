@php
    $school = $facture->entite->nom_entite ?? config('app.name', 'Etablissement');
    $phone = $facture->entite->telephone ?? '';
    $address = $facture->entite->localisation ?? '';
    $student = $etudiant ?? $facture->etudiants;
    $cashier = $caissier ?? optional($facture->user)->name ?? 'Non defini';
    $isScolarite = (int) $facture->type_facture === 1;
    $amount = (float) $facture->montant_total_facture;
    $copies = ['COPIE CLIENT', 'SOUCHE ETABLISSEMENT'];
    $logoPath = $facture->entite->logo ?? 'uploads/images/1759420569_logo.jpg';
    $logoFullPath = public_path($logoPath);
    $logoSrc = null;

    if ($logoPath && file_exists($logoFullPath)) {
        $mime = mime_content_type($logoFullPath) ?: 'image/png';
        $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoFullPath));
    }

    $trancheText = collect($tranches ?? [])->map(function ($tranche) {
        $date = $tranche->date_limite
            ? \Carbon\Carbon::parse($tranche->date_limite)->format('d/m/Y')
            : '';

        return trim(($tranche->nom_tranche ?? 'Tranche') . ' : ' .
            number_format((float) ($tranche->montant_tranche ?? 0), 0, ',', ' ') . ' FCFA' .
            ($date ? ' - ' . $date : ''));
    })->implode(' | ');
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Facture {{ $facture->numero_facture }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 7mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #111;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 9.8px;
            line-height: 1.18;
        }

        .copy {
            position: relative;
            height: 127mm;
            padding: 4mm;
            overflow: hidden;
            border: 1px solid #222;
            page-break-inside: avoid;
        }

        .copy + .copy {
            margin-top: 3mm;
        }

        .logo-bg {
            position: absolute;
            top: 50%;
            left: 50%;
            z-index: 0;
            width: 82mm;
            max-height: 82mm;
            opacity: .09;
            transform: translate(-50%, -50%);
        }

        .copy-label {
            position: absolute;
            top: 49%;
            left: 50%;
            z-index: 0;
            color: #b21f2d;
            font-size: 28px;
            font-weight: 700;
            opacity: .08;
            transform: translate(-50%, -50%) rotate(-24deg);
            white-space: nowrap;
        }

        .content {
            position: relative;
            z-index: 1;
        }

        .top-band {
            border-top: 4px solid #b21f2d;
            border-bottom: 4px solid #b21f2d;
            padding: 4px 0;
            margin-bottom: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .plain td {
            border: 0;
            padding: 1px 0;
            vertical-align: top;
        }

        .details th,
        .details td {
            border: 1px solid #333;
            padding: 3px 4px;
            vertical-align: top;
        }

        .details th {
            background: #f1f1f1;
            font-weight: 700;
        }

        .title {
            font-size: 15px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .school {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .muted {
            color: #555;
        }

        .small {
            font-size: 9px;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .total {
            font-size: 12px;
            font-weight: 700;
        }

        .section-title {
            margin: 4px 0 2px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .signature {
            height: 17mm;
            border: 1px dashed #888;
            margin-top: 2px;
        }

        .cut-line {
            height: 4mm;
            border-top: 1px dashed #777;
            margin: 2mm 0 0;
        }
    </style>
</head>
<body>
@foreach($copies as $copyIndex => $copyLabel)
    <div class="copy">
        @if($logoSrc)
            <img class="logo-bg" src="{{ $logoSrc }}" alt="">
        @endif
        <div class="copy-label">{{ $copyLabel }}</div>
        <div class="content">
            <div class="top-band">
                <table class="plain">
                    <tr>
                        <td style="width: 58%;">
                            <div class="title">Facture N° {{ $facture->numero_facture }}</div>
                            <div>Date : {{ \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') }}</div>
                            <div>Annee academique : {{ $facture->Annee_academique->nom ?? 'Non defini' }}</div>
                        </td>
                        <td class="right" style="width: 42%;">
                            <div class="school">{{ $school }}</div>
                            @if($phone)
                                <div>{{ $phone }}</div>
                            @endif
                            @if($address)
                                <div>{{ $address }}</div>
                            @endif
                            <div class="muted">Caissier(e) : {{ $cashier }}</div>
                        </td>
                    </tr>
                </table>
            </div>

            <table class="plain">
                <tr>
                    <td style="width: 52%;">
                        <div class="section-title">Etudiant</div>
                        <div><strong>Nom :</strong> {{ $student->nom ?? 'Non defini' }}</div>
                        <div><strong>Matricule :</strong> {{ $student->matricule ?? 'Non defini' }}</div>
                        <div><strong>Telephone :</strong> {{ $student->telephone_whatsapp ?? 'Non defini' }}</div>
                    </td>
                    <td style="width: 48%;">
                        <div class="section-title">Classement academique</div>
                        <div><strong>Cycle / Filiere :</strong> {{ $facture->cycles->nom_cycle ?? 'Non defini' }} / {{ $facture->filieres->nom_filiere ?? 'Non defini' }}</div>
                        <div><strong>Niveau / Specialite :</strong> {{ $facture->niveaux->nom_niveau ?? 'Non defini' }} / {{ $facture->specialites->nom_specialite ?? 'Non defini' }}</div>
                    </td>
                </tr>
            </table>

            <div class="section-title">Detail de la facture</div>
            <table class="details">
                <thead>
                <tr>
                    <th style="width: 18%;">Nature</th>
                    <th>Designation</th>
                    <th style="width: 22%;" class="right">Montant</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>{{ $isScolarite ? 'Scolarite' : 'Frais' }}</td>
                    <td>
                        @if($isScolarite)
                            {{ $facture->scolarites->specialites->nom_specialite ?? $facture->specialites->nom_specialite ?? 'Scolarite' }}
                            @if($trancheText)
                                <div class="small muted"><strong>Tranches :</strong> {{ $trancheText }}</div>
                            @endif
                        @else
                            {{ $facture->frais->nom_frais ?? 'Frais' }}
                        @endif
                    </td>
                    <td class="right">{{ number_format($amount, 0, ',', ' ') }} FCFA</td>
                </tr>
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="2" class="right total">TOTAL</th>
                    <th class="right total">{{ number_format($amount, 0, ',', ' ') }} FCFA</th>
                </tr>
                </tfoot>
            </table>

            <div class="section-title">Imputation budgetaire</div>
            <table class="details">
                <tr>
                    <th style="width: 22%;">Budget</th>
                    <td>{{ $facture->budget->libelle_ligne_budget ?? 'Non defini' }}</td>
                </tr>
                <tr>
                    <th>Ligne</th>
                    <td>{{ $facture->ligne_budgetaire_entree->libelle_ligne_budgetaire_entree ?? 'Non defini' }}</td>
                </tr>
                <tr>
                    <th>Element</th>
                    <td>{{ $facture->element_ligne_budgetaire_entree->libelle_elements_ligne_budgetaire_entree ?? 'Non defini' }}</td>
                </tr>
                <tr>
                    <th>Donnee</th>
                    <td>{{ $facture->donnee_ligne_budgetaire_entree->donnee_ligne_budgetaire_entree ?? $facture->donnee_budgetaire_entree->donnee_ligne_budgetaire_entree ?? 'Non defini' }}</td>
                </tr>
            </table>

            <table class="plain" style="margin-top: 6px;">
                <tr>
                    <td style="width: 50%;">
                        <div class="muted small">Signature et cachet etablissement</div>
                        <div class="signature"></div>
                    </td>
                    <td style="width: 50%; padding-left: 8px;">
                        <div class="muted small">Signature etudiant / payeur</div>
                        <div class="signature"></div>
                    </td>
                </tr>
            </table>

            <div class="center small muted" style="margin-top: 4px;">
                Arretee la presente facture a la somme de {{ number_format($amount, 0, ',', ' ') }} FCFA.
            </div>
        </div>
    </div>

    @if($copyIndex === 0)
        <div class="cut-line"></div>
    @endif
@endforeach
</body>
</html>
