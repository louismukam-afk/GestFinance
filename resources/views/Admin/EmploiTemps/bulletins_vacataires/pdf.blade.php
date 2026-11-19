<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><title>Bulletins vacataires - import #{{ $import->id }}</title><style>
@page { margin:32px; }
body { font-family:DejaVu Sans,sans-serif; font-size:10px; color:#111; }
h1 { font-size:17px; margin:0 0 12px; } h2 { font-size:14px; margin:15px 0 8px; }
.bulletin { page-break-before:always; } .bulletin:first-child { page-break-before:auto; }
.entete { border-bottom:1px solid #666; padding-bottom:12px; margin-bottom:15px; }
.logo { height:55px; width:auto; float:right; }
table { width:100%; border-collapse:collapse; margin:12px 0; table-layout:fixed; }
th,td { border:1px solid #999; padding:6px 4px; vertical-align:middle; overflow-wrap:break-word; }
th { background:#eee; } tr { page-break-inside:avoid; }
.montant { text-align:right; white-space:nowrap; } .total { font-size:14px; font-weight:bold; text-align:right; }
.bloc { page-break-inside:avoid; } .signature { margin-top:35px; width:100%; }
</style></head><body>
@foreach($bulletins as $bulletin)
<div class="bulletin">
    <div class="entete"><img class="logo" src="{{ public_path('images/virements/ish.jpeg') }}"><h1>BULLETIN DE PAIE VACATAIRE</h1><strong>{{ $bulletin['nom'] }}</strong><br>Reference : BV-{{ $import->id }}-{{ $bulletin['id_personnel'] }}<br>Periode : {{ $import->date_debut->format('d/m/Y') }} au {{ $import->date_fin->format('d/m/Y') }}<br>Statut : <strong>{{ $bulletin['statut'] }}</strong></div>
    @foreach($bulletin['entites'] as $ligne)
        <h2>{{ $ligne['entite_nom'] }}</h2>
        <p>Banque : {{ $ligne['banque'] ?: 'Non affectee' }}<br>Compte : {{ $ligne['compte'] ?: 'Non renseigne' }}<br>{{ $ligne['valide'] ? 'Ordre valide OV-'.$ligne['id_virement'] : 'Provisoire - frais de transfert non valides' }}</p>
        @foreach(collect($ligne['details'])->chunk(12) as $details)
        <table><thead><tr><th>Mois</th><th>Heures prevues</th><th>Heures realisees</th><th>Montant realise</th><th>Sanction retenue</th><th>Brut fiscal</th><th>Impot</th></tr></thead><tbody>
        @foreach($details as $detail)<tr><td>{{ $detail['mois'] }}</td>@foreach(['heures_prevues','heures_realisees','montant_realise','sanction_retenue','brut_fiscal','impot'] as $champ)<td class="montant">{{ isset($detail[$champ]) ? number_format($detail[$champ], 2, ',', ' ') : '-' }}</td>@endforeach</tr>@endforeach
        </tbody></table>
        @endforeach
        @if($ligne['valide'] && collect($ligne['details'])->contains(fn($d) => !isset($d['heures_prevues'])))<p>Detail des heures non archive dans cet ancien ordre.</p>@endif
        <p class="montant">Brut fiscal : {{ number_format($ligne['brut_fiscal'], 2, ',', ' ') }} | Impot : {{ number_format($ligne['impot'], 2, ',', ' ') }} | Frais : {{ number_format($ligne['frais_transfert'], 2, ',', ' ') }} | <strong>Net : {{ number_format($ligne['net_a_payer'], 2, ',', ' ') }} FCFA</strong></p>
    @endforeach
    <div class="bloc"><table><tr><th>Brut fiscal apres sanctions</th><th>Impot</th><th>Net apres impot</th><th>Frais de transfert</th><th>Net a payer</th></tr><tr>@foreach(['brut_fiscal','impot','net_avant_frais','frais_transfert','net_a_payer'] as $champ)<td class="montant">{{ number_format($bulletin[$champ], 2, ',', ' ') }}</td>@endforeach</tr></table>
    <p class="total">NET A PAYER : {{ number_format($bulletin['net_a_payer'], 2, ',', ' ') }} FCFA</p>
    <p>Arrete a : {{ (new \App\Services\MontantEnLettres())->francs($bulletin['net_a_payer']) }}.</p>
    <p class="signature">Administration <span style="float:right">Signature du beneficiaire</span></p></div>
</div>
@endforeach
</body></html>
