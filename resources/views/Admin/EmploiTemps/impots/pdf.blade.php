<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 28px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #171717; }
        h1 { font-size: 18px; margin: 0 0 12px; }
        table { width: 100%; border-collapse: collapse; margin: 12px 0; }
        th, td { border: 1px solid #777; padding: 6px; text-align: left; vertical-align: top; }
        th { background: #ededed; }
        .right { text-align: right; white-space: nowrap; }
        tr { page-break-inside: avoid; }
    </style>
</head>
<body>
    <h1>Consolidation des impots - Import #{{ $import->id }}</h1>
    <p><strong>{{ $import->libelle }}</strong><br>Periode : {{ $import->date_debut->format('d/m/Y') }} au {{ $import->date_fin->format('d/m/Y') }}. Montants en FCFA.</p>
    <p>Le brut fiscal correspond au montant net de consolidation apres les sanctions. L'impot est calcule sur cette base ; le net a payer est le brut fiscal moins l'impot.</p>
    @foreach($lignes->chunk(25) as $pageLignes)
        <table>
            <thead><tr><th>Nom du personnel</th><th>Mois</th><th class="right">Montant brut fiscal</th><th class="right">Taux (%)</th><th class="right">Impot</th><th class="right">Montant net a payer</th></tr></thead>
            <tbody>
                @foreach($pageLignes as $ligne)
                    <tr><td>{{ $ligne['nom'] }}</td><td>{{ $ligne['mois'] }}</td><td class="right">{{ number_format($ligne['brut'], 2, ',', ' ') }}</td><td class="right">{{ number_format((float)$ligne['taux'], 2, ',', ' ') }} %</td><td class="right">{{ number_format($ligne['impot'], 2, ',', ' ') }}</td><td class="right">{{ number_format($ligne['net'], 2, ',', ' ') }}</td></tr>
                @endforeach
            </tbody>
            @if($loop->last)
                <tfoot><tr><th colspan="2">TOTAL GENERAL</th><th class="right">{{ number_format($lignes->sum('brut'), 2, ',', ' ') }}</th><th></th><th class="right">{{ number_format($lignes->sum('impot'), 2, ',', ' ') }}</th><th class="right">{{ number_format($lignes->sum('net'), 2, ',', ' ') }}</th></tr></tfoot>
            @endif
        </table>
    @endforeach
    <p><strong>Total brut fiscal :</strong> {{ number_format($lignes->sum('brut'), 2, ',', ' ') }} FCFA<br><strong>Total des impots :</strong> {{ number_format($lignes->sum('impot'), 2, ',', ' ') }} FCFA<br><strong>Total global a payer :</strong> {{ number_format($lignes->sum('net'), 2, ',', ' ') }} FCFA</p>
</body>
</html>
