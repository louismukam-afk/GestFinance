@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Modifier ligne budgétaire</h2>
        <form method="POST" action="{{ route('ligne_budgetaire_entrees.update', $ligne->id) }}">
            @csrf
            @method('PUT')
            @include('ligne_budgetaire_entrees.form', ['ligne' => $ligne])
            <button type="submit" class="btn btn-primary">Mettre à jour</button>
        </form>
    </div>
@endsection
