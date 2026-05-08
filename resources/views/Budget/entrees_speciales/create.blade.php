@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>Nouvelle entree speciale</h3>
        @include('Budget.entrees_speciales._form')
    </div>
@endsection

@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('budget') }}"><strong>Budget</strong></a></li>
        <li><a href="{{ route('entrees_speciales.index') }}"><strong>Entrees speciales</strong></a></li>
        <li class="active"><strong>Creation</strong></li>
    </ol>
@endsection
