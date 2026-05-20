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
            font-size: 10px;
            color: #111;
        }
        h2 {
            margin: 0 0 12px;
            text-align: center;
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #333;
            padding: 5px;
            vertical-align: top;
        }
        th {
            background: #e5e5e5;
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    <h2>{{ $title }}</h2>
    <table>
        <thead>
            <tr>
                <th>Specialite</th>
                <th>Cycle / Filiere / Niveau</th>
                <th>Annee / Entite</th>
                <th>Matiere</th>
                <th>Code</th>
                <th>Semestre</th>
                <th>Type</th>
                <th>Prevu</th>
                <th>Heures realisees</th>
                <th>Reste</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                @php $reste = max($row['prevu'] - $row['realise'], 0); @endphp
                <tr>
                    <td>{{ $row['specialite'] }}</td>
                    <td>{{ $row['cycle'] }}<br>{{ $row['filiere'] }} / {{ $row['niveau'] }}</td>
                    <td>{{ $row['annee'] }}<br>{{ $row['entite'] }}</td>
                    <td>{{ $row['matiere'] }}</td>
                    <td>{{ $row['code'] ?? '-' }}</td>
                    <td>{{ $row['semestre'] ?? '-' }}</td>
                    <td>{{ ucfirst($row['type'] ?? '-') }}</td>
                    <td class="text-right">{{ number_format($row['prevu'], 1, ',', ' ') }} h</td>
                    <td class="text-right">{{ number_format($row['realise'], 1, ',', ' ') }} h</td>
                    <td class="text-right">{{ number_format($reste, 1, ',', ' ') }} h</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align:center">Aucune matiere trouvee.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
