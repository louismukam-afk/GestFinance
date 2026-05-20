<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1cm;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #111;
        }
        h2, h3 {
            margin: 0;
            text-align: center;
            text-transform: uppercase;
        }
        .meta {
            margin: 8px 0 12px;
            text-align: center;
            font-size: 10px;
        }
        .header {
            position: relative;
            min-height: 65px;
        }
        .logo {
            position: absolute;
            left: 0;
            top: 0;
           
            max-width: 60px;
            max-height: 60px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #333;
            padding: 4px;
            vertical-align: top;
        }
        th {
            background: #e5e5e5;
            text-align: center;
        }
        .jour {
            width: 70px;
            text-transform: uppercase;
            font-weight: bold;
            background: #f0f0f0;
        }
        .cours {
            margin-bottom: 5px;
            padding-bottom: 4px;
            border-bottom: 1px solid #ddd;
        }
        .cours:last-child {
            border-bottom: none;
        }
        .pause {
            text-align: center;
            vertical-align: middle;
            background: #f7f7f7;
            font-weight: bold;
        }
        .signature {
            margin-top: 45px;
            width: 220px;
            margin-left: auto;
            text-align: center;
            page-break-inside: avoid;
        }
        .signature-line {
            margin-top: 65px;
            border-top: 1px solid #333;
        }
    </style>
</head>
<body>
    @if($rows->isNotEmpty())
        @php
            $first = $rows->first();
            $salles = $rows->pluck('salle')->unique()->filter()->implode(', ');
            $semestres = $rows->pluck('semestre')->unique()->filter()->implode(', ');
            $entiteLabel = optional($selectedEntite)->nom_entite ?: ($first['entite'] ?? '-');
            $anneeLabel = optional($selectedAnnee)->nom ?: ($first['annee'] ?? '-');
        @endphp
        <div class="header">
            @if($logoSrc)
                <img src="{{ $logoSrc }}" class="logo" alt="">
            @endif
            <h2>Emploi du temps</h2>
            <div class="meta">
                <strong>Entite :</strong> {{ $entiteLabel }}
                &nbsp;&nbsp; <strong>Annee academique :</strong> {{ $anneeLabel }}<br>
                <strong style=" margin: top 50px;">Cycle :</strong> {{ $first['cycle'] ?? '-' }}
                &nbsp;&nbsp; <strong>Filiere :</strong> {{ $first['filiere'] ?? '-' }}
                &nbsp;&nbsp; <strong>Specialite :</strong> {{ $first['specialite'] ?? '-' }}
                &nbsp;&nbsp; <strong>Niveau :</strong> {{ $first['niveau'] ?? '-' }}
                &nbsp;&nbsp; <strong>Salle :</strong> {{ $salles ?: '-' }}
                &nbsp;&nbsp; <strong>Semestre :</strong> {{ $semestres ?: '-' }}
                &nbsp;&nbsp; <strong>Periode :</strong> {{ request('date_debut') ?: '-' }} - {{ request('date_fin') ?: '-' }}
            </div>
        </div>
    @else
        <h2>{{ $title }}</h2>
    @endif

    <table>
        <thead>
            <tr>
                <th>Jour</th>
                @foreach($plages as $plage)
                    <th>{{ substr($plage->heure_debut, 0, 5) }} - {{ substr($plage->heure_fin, 0, 5) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($jours as $jourIndex => $jourLabel)
                <tr>
                    <td class="jour">{{ $jourLabel }}</td>
                    @foreach($plages as $plage)
                        <td class="{{ $plage->type_plage === 'pause' ? 'pause' : '' }}">
                            @if($plage->type_plage === 'pause')
                                Pause
                            @else
                                @php
                                    $cellItems = collect($matrix[$jourIndex][$plage->id] ?? [])->groupBy(function ($item) {
                                        return implode('|', [
                                            $item['type_matiere'] ?? '',
                                            $item['code'] ?? '',
                                            $item['matiere'] ?? '',
                                            $item['enseignant'] ?? '',
                                            $item['salle'] ?? '',
                                            $item['periode'] ?? '',
                                            $item['volume_total'] ?? '',
                                        ]);
                                    });
                                @endphp
                                @foreach($cellItems as $items)
                                    @php
                                        $item = $items->first();
                                        $codesSpecialites = $items->pluck('specialite_code')->filter()->unique()->implode(', ');
                                        $showCodes = $codesSpecialites && (($item['type_matiere'] ?? '') === 'transversale' || $items->pluck('specialite_code')->filter()->unique()->count() > 1);
                                    @endphp
                                    <div class="cours">
                                        <strong>{{ $item['code'] ? $item['code'].' : ' : '' }}{{ $item['matiere'] }}</strong><br>
                                        @if($showCodes)
                                            Specialites : {{ $codesSpecialites }}<br>
                                        @endif
                                        Seance : {{ number_format($item['volume'], 1, ',', ' ') }}H / Total : {{ number_format($item['volume_total'], 1, ',', ' ') }}H<br>
                                        {{ $item['enseignant'] }}<br>
                                        @unless($hideContextInCells)
                                            {{ $item['cycle'] }} / {{ $item['filiere'] }} / {{ $item['specialite'] }} / {{ $item['niveau'] }}<br>
                                        @endunless
                                        Salle : {{ $item['salle'] }}<br>
                                        {{ $item['periode'] }}
                                    </div>
                                @endforeach
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($rows->isEmpty())
        <p>Aucun emploi du temps trouve pour cette periode.</p>
    @endif

    <div class="signature">
        <strong>Chef de departement</strong>
        <div class="signature-line"></div>
    </div>
</body>
</html>
