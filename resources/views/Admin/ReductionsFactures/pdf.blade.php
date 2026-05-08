<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reductions factures</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 4px; }
        th { background: #eee; }
        .text-end { text-align: right; }
    </style>
</head>
<body>
<h3>Reductions de factures etudiants</h3>
<p>Periode : {{ $dateDebut ?: 'Debut' }} - {{ $dateFin ?: 'Aujourd hui' }}</p>
@include('Admin.ReductionsFactures._table', ['print' => true])
</body>
</html>
