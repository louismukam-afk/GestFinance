@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Gestion des roles et permissions</h3>
            <p class="text-muted">Attribuer les actions autorisees a chaque utilisateur.</p>
        </div>
        <form method="POST" action="{{ route('access.sync') }}">
            @csrf
            <button class="btn btn-primary">Synchroniser les routes</button>
        </form>
    </div>

    <div class="card mb-4">
        <div class="card-header"><strong>Nouveau role</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('access.roles.store') }}" class="row g-3">
                @csrf
                <div class="col-md-4">
                    <label>Nom du role</label>
                    <input type="text" name="name" class="form-control" placeholder="Ex: Caissiere" required>
                </div>
                <div class="col-md-6">
                    <label>Description</label>
                    <input type="text" name="description" class="form-control" placeholder="Responsabilite du role">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-success w-100">Creer</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><strong>Attribuer les roles aux utilisateurs</strong></div>
        <div class="card-body table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Utilisateur</th>
                        <th>Email</th>
                        <th>Statut</th>
                        <th>Super admin</th>
                        <th>Roles</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <form method="POST" action="{{ route('access.users.roles', $user) }}">
                                @csrf
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <select name="statut_utilisateur" class="form-control">
                                        <option value="actif" {{ $user->statut_utilisateur === 'actif' ? 'selected' : '' }}>
                                            Actif
                                        </option>
                                        <option value="non_actif" {{ $user->statut_utilisateur !== 'actif' ? 'selected' : '' }}>
                                            Non actif
                                        </option>
                                    </select>
                                </td>
                                <td class="text-center">
                                    <input type="checkbox" name="is_super_admin" value="1" {{ $user->isSuperAdmin() ? 'checked' : '' }}>
                                </td>
                                <td>
                                    <div class="row">
                                        @foreach($roles as $role)
                                            <label class="col-md-4">
                                                <input type="checkbox" name="roles[]" value="{{ $role->id }}" {{ $user->roles->contains($role->id) ? 'checked' : '' }}>
                                                {{ $role->name }}
                                            </label>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary">Enregistrer</button>
                                </td>
                            </form>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Permissions par role</strong></div>
        <div class="card-body">
            @forelse($roles as $role)
                <form method="POST" action="{{ route('access.roles.permissions', $role) }}" class="mb-4 border rounded p-3">
                    @csrf
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h4 class="mb-0">{{ $role->name }}</h4>
                            <small class="text-muted">{{ $role->description }}</small>
                        </div>
                        <button class="btn btn-success">Enregistrer les permissions</button>
                    </div>

                    <input type="text" class="form-control permission-search mb-3" placeholder="Rechercher une route ou une action">

                    <div class="row permission-list">
                        @foreach($permissions as $permission)
                            <label class="col-md-4 mb-2 permission-item">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" {{ $role->permissions->contains($permission->id) ? 'checked' : '' }}>
                                <strong>{{ $permission->label }}</strong>
                                <br>
                                <small class="text-muted">{{ $permission->method }} / {{ $permission->uri }}</small>
                            </label>
                        @endforeach
                    </div>
                </form>
            @empty
                <div class="alert alert-info">Cree d'abord un role pour attribuer des permissions.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@section('breadcrumb')
<ol class="breadcrumb" style="background-color: transparent; padding: 4px 10px">
    <li class="breadcrumb-item"><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
    <li class="breadcrumb-item active"><strong>Roles et permissions</strong></li>
</ol>
@endsection

@section('scripts')
<script>
document.querySelectorAll('.permission-search').forEach(function (input) {
    input.addEventListener('input', function () {
        const term = input.value.toLowerCase();
        input.closest('form').querySelectorAll('.permission-item').forEach(function (item) {
            item.style.display = item.innerText.toLowerCase().includes(term) ? '' : 'none';
        });
    });
});
</script>
@endsection
