@extends('layouts.app')
@section('content')

    <div class="container">
        <h3>Éléments du Bon : {{ $bon->nom_bon_commande }}</h3>
        <p><strong>Montant du Bon :</strong> {{ number_format($bon->montant_total, 0, ',', ' ') }} FCFA</p>

        <a href="{{ route('element_bon.exportPdf', $bon->id) }}" class="btn btn-danger">📄 Exporter en PDF</a>

        <table class="table table-bordered table-striped" style="margin-top:20px;">
            <thead>
            <tr>
                <th>#</th>
                <th>Nom Élément</th>
                <th>Description</th>
                <th>Quantité</th>
                <th>Prix Unitaire</th>
                <th>Montant Total</th>
                <th>Date Réalisation</th>
            </tr>
            </thead>
            <tbody>
            @foreach($elements as $i => $el)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $el->nom_element_bon_commande }}</td>
                    <td>{{ $el->description_elements_bon_commande }}</td>
                    <td>{{ $el->quantite_element_bon_commande }}</td>
                    <td>{{ number_format($el->prix_unitaire_element_bon_commande, 0, ',', ' ') }} FCFA</td>
                    <td>{{ number_format($el->montant_total_element_bon_commande, 0, ',', ' ') }} FCFA</td>
                    <td>{{ $el->date_realisation }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

@endsection
@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('bon_commande_management') }}"><strong>Gestion des bons de commandes</strong></a></li>
        {{--<li><a href="{{ route('budget') }}"><strong>budget</strong></a></li>--}}

        <li class="active"><strong>{{ $title }}</strong></li>
    </ol>
@endsection