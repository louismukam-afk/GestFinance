<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page { size: A4; margin: 15mm; }
        body { font-family: Arial, sans-serif; color: #171717; margin: 24px; }
        h1 { font-size: 22px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; border: 1px solid #555; text-align: left; overflow-wrap: anywhere; }
        th { background: #eee; }
        tr { break-inside: avoid; }
        @media print { .print-tools { display: none; } body { margin: 0; } }
    </style>
</head>
<body>
    <div class="print-tools"><button type="button" onclick="window.print()">Imprimer</button></div>
    <h1>{{ $banque->nom_banque }} - Liste du personnel</h1>
    <p>{{ $personnels->count() }} personnel(s) - {{ now()->format('d/m/Y') }}</p>
    <table>
        <thead><tr><th>No</th><th>Nom du personnel</th><th>Numero de compte</th></tr></thead>
        <tbody>
            @forelse($personnels as $personnel)
                <tr><td>{{ $loop->iteration }}</td><td>{{ $personnel->nom }}</td><td>{{ $personnel->niveau_etude ?: '-' }}</td></tr>
            @empty
                <tr><td colspan="3">Aucun personnel affecte pour ces criteres.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
