@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="text-primary">{{ $title }}</h3>

    @php
        $items = [
            ['href' => route('personnel_management'), 'icon' => 'fa-users', 'label' => '1. Personnel'],
            ['href' => route('personnels_banques.index'), 'icon' => 'fa-university', 'label' => 'Banques du personnel'],
            ['href' => route('personnel_entites.index'), 'icon' => 'fa-sitemap', 'label' => '2. Affectation entites'],
            ['href' => route('plages_horaires.index'), 'icon' => 'fa-clock-o', 'label' => '3. Plages horaires'],
            ['href' => route('emplois_permanents.index'), 'icon' => 'fa-briefcase', 'label' => '4. Emplois permanents'],
            ['href' => route('salaires_permanents.index'), 'icon' => 'fa-credit-card', 'label' => '5. Salaires permanents'],
            ['href' => route('paie_permanents.index') . '#rubriques-salaire', 'icon' => 'fa-list', 'label' => '6. Rubriques salaire'],
            ['href' => route('paie_permanents.index') . '#affectations-rubriques', 'icon' => 'fa-link', 'label' => '7. Affecter rubriques'],
            ['href' => route('paie_permanents.index') . '#baremes-irpp', 'icon' => 'fa-percent', 'label' => '8. Baremes IRPP'],
            ['href' => route('paie_permanents.index') . '#acomptes-personnel', 'icon' => 'fa-minus-circle', 'label' => '9. Acomptes'],
            ['href' => route('biometrie_permanents.index'), 'icon' => 'fa-id-card', 'label' => '10. Biometrie permanents'],
            ['href' => route('paie_permanents.index'), 'icon' => 'fa-calculator', 'label' => '11. Generer paie'],
            ['href' => route('paie_permanents.index') . '#bulletins-paie', 'icon' => 'fa-list-alt', 'label' => '12. Bulletins de paie'],
            ['href' => route('paie_permanents.index') . '#etats-paie', 'icon' => 'fa-table', 'label' => '13. Etats de paie'],
            ['href' => route('discipline_personnels.index'), 'icon' => 'fa-exclamation-triangle', 'label' => 'Discipline personnel'],
            ['href' => route('matieres.index'), 'icon' => 'fa-book', 'label' => 'Matieres'],
            ['href' => route('programmes_specialites.index'), 'icon' => 'fa-book', 'label' => 'Programmes specialite'],
            ['href' => route('cours_enseignants.index'), 'icon' => 'fa-calendar', 'label' => 'Cours enseignants'],
            ['href' => route('cours_enseignants.emploi_specialite'), 'icon' => 'fa-table', 'label' => 'Emplois specialites'],
            ['href' => route('cours_enseignants.emploi_enseignant'), 'icon' => 'fa-user', 'label' => 'Emplois enseignants'],
            ['href' => route('cours_enseignants.volumes_specialite'), 'icon' => 'fa-bar-chart', 'label' => 'Volumes horaires'],
            ['href' => route('biometrie_heures.index'), 'icon' => 'fa-calculator', 'label' => 'Decompte heures'],
            ['href' => route('biometrie_heures.historique'), 'icon' => 'fa-history', 'label' => 'Historique consolidations vacataires'],
            ['href' => route('salles.index'), 'icon' => 'fa-building', 'label' => 'Salles'],
            ['href' => route('taux_horaires.index'), 'icon' => 'fa-money', 'label' => 'Taux horaires'],
            ['href' => route('taux_impots.index'), 'icon' => 'fa-percent', 'label' => 'Taux d impots'],
            ['href' => route('virements_vacataires.index'), 'icon' => 'fa-exchange', 'label' => 'Etats de virement enseignants'],
            ['href' => route('virements_vacataires.historique'), 'icon' => 'fa-history', 'label' => 'Historique ordres de virement'],
            ['href' => route('bulletins_vacataires.index'), 'icon' => 'fa-file-text-o', 'label' => 'Bulletins de paie vacataires'],
        ];
    @endphp

    <div class="row text-center pad-top">
        @foreach($items as $item)
            <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
                <div class="div-square">
                    <a href="{{ $item['href'] }}">
                        <i class="fa {{ $item['icon'] }} fa-5x"></i>
                        <h4>{{ $item['label'] }}</h4>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

@section('breadcrumb')
<ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
    <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
    <li class="active"><strong>{{ $title }}</strong></li>
</ol>
@endsection
