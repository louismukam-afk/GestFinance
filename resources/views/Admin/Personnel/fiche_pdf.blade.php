<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        h1 { text-align: center; font-size: 20px; margin-bottom: 4px; }
        .subtitle { text-align: center; margin-bottom: 22px; color: #666; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #777; padding: 8px; text-align: left; vertical-align: top; }
        th { width: 35%; background: #f1f1f1; }
    </style>
</head>
<body>
    <h1>Fiche d'identification du personnel</h1>
    <div class="subtitle">{{ $personnel->nom }}</div>

    <table>
        <tr><th>Nom</th><td>{{ $personnel->nom ?: '-' }}</td></tr>
        <tr><th>Date de naissance</th><td>{{ $personnel->date_naissance ? \Carbon\Carbon::parse($personnel->date_naissance)->format('d/m/Y') : '-' }}</td></tr>
        <tr><th>Lieu de naissance</th><td>{{ $personnel->lieu_naissance ?: '-' }}</td></tr>
        <tr><th>Adresse</th><td>{{ $personnel->adresse ?: '-' }}</td></tr>
        <tr><th>Sexe</th><td>{{ $personnel->sexe ?: '-' }}</td></tr>
        <tr><th>Statut matrimonial</th><td>{{ $personnel->statut_matrimonial ?: '-' }}</td></tr>
        <tr><th>Email</th><td>{{ $personnel->email ?: '-' }}</td></tr>
        <tr><th>Telephone</th><td>{{ $personnel->telephone ?: '-' }}</td></tr>
        <tr><th>Telephone WhatsApp</th><td>{{ $personnel->telephone_whatsapp ?: '-' }}</td></tr>
        <tr><th>Numero CNPS</th><td>{{ $personnel->numero_cnps ?: '-' }}</td></tr>
        <tr><th>Numero contribuable</th><td>{{ $personnel->numero_contribuable ?: '-' }}</td></tr>
        <tr><th>Diplome</th><td>{{ $personnel->diplome ?: '-' }}</td></tr>
        <tr><th>Niveau d'etude</th><td>{{ $personnel->niveau_etude ?: '-' }}</td></tr>
        <tr><th>Domaine de formation</th><td>{{ $personnel->domaine_formation ?: '-' }}</td></tr>
        <tr><th>Date de recrutement</th><td>{{ $personnel->date_recrutement ? \Carbon\Carbon::parse($personnel->date_recrutement)->format('d/m/Y') : '-' }}</td></tr>
        <tr><th>Nationalite</th><td>{{ $personnel->nationalite ?: '-' }}</td></tr>
        <tr><th>Type personnel</th><td>{{ ucfirst($personnel->type_personnel ?? 'permanent') }}</td></tr>
        <tr><th>Mode horaire</th><td>{{ ucfirst($personnel->mode_horaire ?? 'strict') }}</td></tr>
        <tr><th>Categorie horaire</th><td>{{ str_replace('_', ' ', $personnel->categorie_horaire ?? 'standard') }}</td></tr>
        <tr><th>Horaire travail</th><td>{{ str_replace('_', ' ', $personnel->horaire_travail ?? '-') }}</td></tr>
    </table>
</body>
</html>
