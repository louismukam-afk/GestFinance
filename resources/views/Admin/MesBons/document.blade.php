@extends('layouts.app')

@section('styles')
    @include('Admin.MesBons.partials.document_styles')
@endsection

@section('content')
    <div class="doc-actions">
        <button type="button" class="btn btn-primary" onclick="window.print()">Imprimer</button>
        <a href="{{ route('mes_bons.document_pdf', $bon) }}" class="btn btn-danger">Exporter PDF</a>
        <a href="{{ route('mes_bons.attente') }}" class="btn btn-default">Retour</a>
    </div>

    @include('Admin.MesBons.partials.document', ['bon' => $bon, 'elements' => $elements])
@endsection

@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent; padding: 4px 10px">
        <li class="breadcrumb-item"><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li class="breadcrumb-item"><a href="{{ route('mes_bons.attente') }}"><strong>Mes bons</strong></a></li>
        <li class="breadcrumb-item active"><strong>Bon de commande</strong></li>
    </ol>
@endsection

@section('scripts')
    @if(request('print'))
        <script>
            window.addEventListener('load', function () {
                window.print();
            });
        </script>
    @endif
@endsection
