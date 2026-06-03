<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; }
        h3 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #111; padding: 5px; }
        th { background: #eee; }
        .right { text-align: right; }
    </style>
</head>
<body>
<h3>{{ strtoupper($title) }}</h3>

<table>
    <thead>
        <tr>
            <th>{{ $type === 'realises' ? 'Date realisation' : 'Date du bon' }}</th>
            <th>Bon</th>
            <th>Entite</th>
            <th>Annee</th>
            <th>Budget</th>
            <th class="right">Montant</th>
            <th class="right">Finance</th>
            <th class="right">Reste</th>
            <th>Elements</th>
        </tr>
    </thead>
    <tbody>
        @foreach($bons as $bon)
            <tr>
                <td>{{ $bon->date_etat_financement ? \Carbon\Carbon::parse($bon->date_etat_financement)->format('d/m/Y') : '-' }}</td>
                <td>{{ $bon->nom_bon_commande }}</td>
                <td>{{ $bon->entites->nom_entite ?? '-' }}</td>
                <td>{{ $bon->annee_academique->nom ?? '-' }}</td>
                <td>{{ $bon->budget->libelle_ligne_budget ?? '-' }}</td>
                <td class="right">{{ number_format($bon->montant_total, 0, ',', ' ') }}</td>
                <td class="right">{{ number_format($bon->total_decaisse, 0, ',', ' ') }}</td>
                <td class="right">{{ number_format($bon->reste_financement, 0, ',', ' ') }}</td>
                <td>
                    @foreach($bon->element_bon_commandes as $element)
                        {{ $element->nom_element_bon_commande }}:
                        {{ number_format($element->montant_total_element_bon_commande, 0, ',', ' ') }}<br>
                    @endforeach
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
