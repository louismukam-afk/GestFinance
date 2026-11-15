@extends('layouts.app')

@section('content')
    <div class="container">
        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Erreur :</strong> {{ $errors->first() }}
            </div>
        @endif

        <div class="clearfix" style="margin-bottom: 15px;">
            <h3 class="text-primary pull-left"><i class="fa fa-pencil"></i> Modifier le personnel</h3>
            <a href="{{ route('personnel_management') }}" class="btn btn-default pull-right">
                <i class="fa fa-arrow-left"></i> Retour
            </a>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>{{ $personnel->nom }}</strong>
            </div>
            <div class="panel-body">
                <form method="POST" action="{{ route('update_personnel') }}" id="personnelEditForm">
                    @csrf
                    <input type="hidden" name="id" value="{{ $personnel->id }}">

                    @include('Admin.Personnel.partials.form_fields', ['personnel' => $personnel, 'disabled' => true])

                    <div class="text-right">
                        <button type="button" class="btn btn-warning" id="enableEdit">
                            <i class="fa fa-unlock"></i> Degriser et modifier
                        </button>
                        <button class="btn btn-primary" id="saveEdit" disabled>
                            <i class="fa fa-save"></i> Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            var enableButton = document.getElementById('enableEdit');
            var saveButton = document.getElementById('saveEdit');
            var fields = document.querySelectorAll('#personnelEditForm .personnel-field');

            if (enableButton) {
                enableButton.addEventListener('click', function () {
                    fields.forEach(function (field) {
                        field.disabled = false;
                    });
                    saveButton.disabled = false;
                    enableButton.disabled = true;
                });
            }
        })();
    </script>
@endsection

@section('breadcrumb')
    <ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
        <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
        <li><a href="{{ route('personnel_management') }}"><strong>Gestion du personnel</strong></a></li>
        <li class="active"><strong>{{ $title }}</strong></li>
    </ol>
@endsection
