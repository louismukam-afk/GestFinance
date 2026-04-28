@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-4">
        <h3>Journal des operations</h3>
        <p class="text-muted">Audit des actions effectuees par les utilisateurs.</p>
    </div>

    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-2">
            <label>Date debut</label>
            <input type="date" name="date_debut" value="{{ request('date_debut') }}" class="form-control">
        </div>
        <div class="col-md-2">
            <label>Date fin</label>
            <input type="date" name="date_fin" value="{{ request('date_fin') }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label>Utilisateur</label>
            <select name="id_user" class="form-control">
                <option value="">Tous</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('id_user') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label>Methode</label>
            <select name="method" class="form-control">
                <option value="">Toutes</option>
                @foreach($methods as $method)
                    <option value="{{ $method }}" {{ request('method') === $method ? 'selected' : '' }}>
                        {{ $method }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label>Route</label>
            <input type="text" name="route_name" value="{{ request('route_name') }}" class="form-control" placeholder="Ex: retour_caisses.store">
        </div>
        <div class="col-md-12 text-center">
            <button class="btn btn-primary">Filtrer</button>
            <a href="{{ route('audit.index') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>Utilisateur</th>
                    <th>Methode</th>
                    <th>Route</th>
                    <th>URL</th>
                    <th>Statut</th>
                    <th>Donnees</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ optional($log->created_at)->format('Y-m-d H:i:s') }}</td>
                        <td>{{ $log->user_name ?? optional($log->user)->name ?? '-' }}</td>
                        <td><span class="badge bg-info">{{ $log->method }}</span></td>
                        <td>{{ $log->route_name ?? '-' }}</td>
                        <td>{{ $log->uri }}</td>
                        <td>{{ $log->status_code }}</td>
                        <td>
                            <small>{{ json_encode($log->payload, JSON_UNESCAPED_UNICODE) }}</small>
                        </td>
                        <td>{{ $log->ip_address }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">Aucune operation trouvee.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $logs->links() }}
</div>
@endsection

@section('breadcrumb')
<ol class="breadcrumb" style="background-color: transparent; padding: 4px 10px">
    <li class="breadcrumb-item"><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
    <li class="breadcrumb-item active"><strong>Journal des operations</strong></li>
</ol>
@endsection
