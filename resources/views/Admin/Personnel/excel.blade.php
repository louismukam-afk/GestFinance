<table>
    <thead>
    <tr>
        <th>Nom</th>
        <th>Nom biometrie</th>
        <th>Type de personnel</th>
        <th>Date de naissance</th>
        <th>Lieu de naissance</th>
        <th>Telephone</th>
        <th>Telephone WhatsApp</th>
        <th>Email</th>
        <th>Adresse</th>
        <th>Sexe</th>
        <th>Statut matrimonial</th>
        <th>Numero CNPS</th>
        <th>Numero contribuable</th>
        <th>Diplome</th>
        <th>Niveau d'etude</th>
        <th>Domaine de formation</th>
        <th>Date de recrutement</th>
        <th>Nationalite</th>
        <th>Mode horaire</th>
        <th>Categorie horaire</th>
        <th>Horaire travail</th>
    </tr>
    </thead>
    <tbody>
    @foreach($personnels as $p)
        <tr>
            <td>{{ $p->nom }}</td>
            <td>{{ $p->nom_biometrie }}</td>
            <td>{{ $p->type_personnel }}</td>
            <td>{{ $p->date_naissance }}</td>
            <td>{{ $p->lieu_naissance }}</td>
            <td>{{ $p->telephone }}</td>
            <td>{{ $p->telephone_whatsapp }}</td>
            <td>{{ $p->email }}</td>
            <td>{{ $p->adresse }}</td>
            <td>{{ $p->sexe }}</td>
            <td>{{ $p->statut_matrimonial }}</td>
            <td>{{ $p->numero_cnps }}</td>
            <td>{{ $p->numero_contribuable }}</td>
            <td>{{ $p->diplome }}</td>
            <td>{{ $p->niveau_etude }}</td>
            <td>{{ $p->domaine_formation }}</td>
            <td>{{ $p->date_recrutement }}</td>
            <td>{{ $p->nationalite }}</td>
            <td>{{ $p->mode_horaire }}</td>
            <td>{{ $p->categorie_horaire }}</td>
            <td>{{ $p->horaire_travail }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
