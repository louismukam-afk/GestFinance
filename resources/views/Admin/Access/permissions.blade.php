@extends('layouts.app')

@section('styles')
<style>
    .permission-section {
        background: #fff8e1;
        border: 1px solid #f0dfaa;
        border-radius: 6px;
        padding: 18px;
        margin-bottom: 18px;
    }

    .permission-section h5 {
        color: #111;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 14px;
    }

    .permission-item {
        display: block;
        padding: 7px 8px;
        margin-bottom: 6px;
        border-radius: 4px;
        background: rgba(255, 255, 255, 0.65);
    }

    .permission-item:hover {
        background: #fff;
    }

    .permission-action {
        margin-left: 8px;
        font-weight: 600;
        font-size: 12px;
    }

    .permission-meta {
        display: block;
        margin-left: 25px;
        color: #6c757d;
        font-size: 11px;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Permissions du role : {{ $role->name }}</h3>
            <p class="text-muted mb-0">{{ $role->description ?: 'Selectionner les actions autorisees pour ce role.' }}</p>
        </div>
        <a href="{{ route('access.index') }}" class="btn btn-secondary">Retour</a>
    </div>

    <form method="POST" action="{{ route('access.roles.permissions', $role) }}" id="role-permissions-form">
        @csrf

        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-md-6">
                        <label>Rechercher une action</label>
                        <input type="text" class="form-control" id="permission-search" placeholder="Ex: facture, caisse, supprimer, pdf">
                    </div>
                    <div class="col-md-6 text-md-end">
                        <button type="button" class="btn btn-outline-primary permission-toggle" data-action="check-all">Tout cocher</button>
                        <button type="button" class="btn btn-outline-secondary permission-toggle" data-action="uncheck-all">Tout decocher</button>
                        <button type="submit" class="btn btn-success">Enregistrer les permissions</button>
                    </div>
                </div>
            </div>
        </div>

        @foreach($sections as $section => $items)
            <div class="permission-section">
                <h5>{{ $section }}</h5>
                <div class="row permission-list">
                    @foreach($items as $item)
                        @php($permission = $item['permission'])
                        <label class="col-md-6 permission-item">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" {{ $role->permissions->contains($permission->id) ? 'checked' : '' }}>
                            <span class="permission-action {{ $item['danger'] ? 'text-danger' : 'text-primary' }}">
                                {{ $item['action'] }}
                            </span>
                            <strong>{{ $item['context'] }}</strong>
                            <span class="permission-meta">{{ $permission->method }} / {{ $permission->uri }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="text-end mb-4">
            <button type="submit" class="btn btn-success">Enregistrer les permissions</button>
        </div>
    </form>
</div>
@endsection

@section('breadcrumb')
<ol class="breadcrumb" style="background-color: transparent; padding: 4px 10px">
    <li class="breadcrumb-item"><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
    <li class="breadcrumb-item"><a href="{{ route('access.index') }}"><strong>Roles et permissions</strong></a></li>
    <li class="breadcrumb-item active"><strong>{{ $role->name }}</strong></li>
</ol>
@endsection

@section('scripts')
<script>
document.getElementById('permission-search').addEventListener('input', function () {
    const term = this.value.toLowerCase();

    document.querySelectorAll('.permission-item').forEach(function (item) {
        item.style.display = item.innerText.toLowerCase().includes(term) ? '' : 'none';
    });
});

document.querySelectorAll('.permission-toggle').forEach(function (button) {
    button.addEventListener('click', function () {
        const checked = button.dataset.action === 'check-all';

        document.querySelectorAll('.permission-item').forEach(function (item) {
            if (item.style.display !== 'none') {
                item.querySelector('input[type="checkbox"]').checked = checked;
            }
        });
    });
});
</script>
@endsection
