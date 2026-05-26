<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Etat de paie</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #111; }
        h2, h4 { text-align: center; margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #111; padding: 5px; }
        th { background: #e5e7eb; }
        .right { text-align: right; }
        .total { font-weight: bold; background: #f3f4f6; }
        .meta td { border: none; padding: 3px; }
    </style>
</head>
<body>
    <h2>ETAT DE PAIE</h2>
    <h4>{{ $etat->reference }}</h4>

    <table class="meta">
        <tr>
            <td><strong>Periode :</strong> {{ optional($etat->periode_debut)->format('d/m/Y') }} - {{ optional($etat->periode_fin)->format('d/m/Y') }}</td>
            <td><strong>Annee academique :</strong> {{ $etat->annee_academique->nom ?? 'Toutes' }}</td>
            <td><strong>Entite :</strong> {{ $etat->entite->nom_entite ?? 'Toutes' }}</td>
            <td><strong>Generation :</strong> {{ optional($etat->date_generation)->format('d/m/Y H:i') }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Employe permanent</th>
                @foreach($colonnesGains as $colonne)
                    <th class="right">{{ $colonne['libelle'] }}</th>
                @endforeach
                <th class="right">Total gains</th>
                @foreach($colonnesRetenues as $colonne)
                    <th class="right">{{ $colonne['libelle'] }}</th>
                @endforeach
                <th class="right">Penalite biometrie</th>
                <th class="right">Sanction</th>
                <th class="right">Acompte</th>
                <th class="right">Retenue globale</th>
                <th class="right">Net a payer</th>
            </tr>
        </thead>
        <tbody>
            @foreach($etat->lignes as $ligne)
                @php($gainsLigne = collect($ligne->detail_gains ?? [])->groupBy('code')->map(fn($items) => $items->sum('montant')))
                @php($retenuesLigne = collect($ligne->detail_retenues ?? [])->groupBy('code')->map(fn($items) => $items->sum('montant')))
                @php($retenueGlobale = $ligne->total_retenues + $ligne->penalite_biometrie + $ligne->total_sanctions + $ligne->total_acomptes)
                <tr>
                    <td>{{ $ligne->nom_personnel }}</td>
                    @foreach($colonnesGains as $colonne)
                        <td class="right">{{ number_format($gainsLigne->get($colonne['code'], 0), 0, ',', ' ') }}</td>
                    @endforeach
                    <td class="right">{{ number_format($ligne->total_gains, 0, ',', ' ') }}</td>
                    @foreach($colonnesRetenues as $colonne)
                        <td class="right">{{ number_format($retenuesLigne->get($colonne['code'], 0), 0, ',', ' ') }}</td>
                    @endforeach
                    <td class="right">{{ number_format($ligne->penalite_biometrie, 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($ligne->total_sanctions, 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($ligne->total_acomptes, 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($retenueGlobale, 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($ligne->net_a_payer, 0, ',', ' ') }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td>Total</td>
                @foreach($colonnesGains as $colonne)
                    <td class="right">{{ number_format($etat->lignes->sum(fn($ligne) => collect($ligne->detail_gains ?? [])->where('code', $colonne['code'])->sum('montant')), 0, ',', ' ') }}</td>
                @endforeach
                <td class="right">{{ number_format($etat->total_gains, 0, ',', ' ') }}</td>
                @foreach($colonnesRetenues as $colonne)
                    <td class="right">{{ number_format($etat->lignes->sum(fn($ligne) => collect($ligne->detail_retenues ?? [])->where('code', $colonne['code'])->sum('montant')), 0, ',', ' ') }}</td>
                @endforeach
                <td class="right">{{ number_format($etat->total_penalites, 0, ',', ' ') }}</td>
                <td class="right">{{ number_format($etat->total_sanctions, 0, ',', ' ') }}</td>
                <td class="right">{{ number_format($etat->total_acomptes, 0, ',', ' ') }}</td>
                <td class="right">{{ number_format($etat->total_retenues + $etat->total_penalites + $etat->total_sanctions + $etat->total_acomptes, 0, ',', ' ') }}</td>
                <td class="right">{{ number_format($etat->total_net_a_payer, 0, ',', ' ') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
