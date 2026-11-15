@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="text-primary"><i class="fa fa-plus"></i> {{ $title }}</h3>
        <form method="POST" action="{{ route('store_personnel') }}">
            @csrf
            @include('Admin.Personnel.partials.form_fields', ['personnel' => null, 'disabled' => false])
            <div class="text-right">
                <a href="{{ route('personnel_management') }}" class="btn btn-default">Annuler</a>
                <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Enregistrer</button>
            </div>
        </form>
    </div>
@endsection

@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('personnel_management') }}"><strong>Gestion du personnel</strong></a></li>
        <li class="active"><strong>{{ $title }}</strong></li>
    </ol>
@endsection
