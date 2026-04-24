@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Nouvelle ligne budgétaire</h2>
        <form method="POST" action="{{ route('ligne_budgetaire_entrees.store') }}">
            @csrf
            @include('ligne_budgetaire_entrees.form')
            <button type="submit" class="btn btn-success">Enregistrer</button>
        </form>
    </div>
@endsection
