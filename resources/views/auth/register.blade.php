<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - GESFINANCE</title>

    {{-- Import avec Vite (ou Mix selon ta version Laravel) --}}
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        body {
            background: url("{{ asset('uploads/images/inscription.png') }}") no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .register-card {
            background: rgba(255,255,255,0.95);
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            padding: 30px;
            width: 450px;
        }

        .register-card h3 {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
            color: #1391e8;
        }

        .form-control {
            border-radius: 10px;
        }

        .btn-primary {
            width: 100%;
            border-radius: 10px;
        }

        .register-footer {
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>
<div class="register-card">
    <h3>Créer un compte</h3>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Nom complet</label>
            <input id="name" type="text"
                   class="form-control @error('name') is-invalid @enderror"
                   name="name" value="{{ old('name') }}" required autofocus>
            @error('name')
            <span class="text-danger small">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Adresse Email</label>
            <input id="email" type="email"
                   class="form-control @error('email') is-invalid @enderror"
                   name="email" value="{{ old('email') }}" required>
            @error('email')
            <span class="text-danger small">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Mot de passe</label>
            <input id="password" type="password"
                   class="form-control @error('password') is-invalid @enderror"
                   name="password" required>
            @error('password')
            <span class="text-danger small">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password-confirm" class="form-label">Confirmer le mot de passe</label>
            <input id="password-confirm" type="password"
                   class="form-control"
                   name="password_confirmation" required>
        </div>

        <button type="submit" class="btn btn-primary">S’inscrire</button>

        <div class="register-footer">
            <p class="small mt-3">
                Déjà un compte ?
                <a href="{{ route('login') }}">Se connecter</a>
            </p>
        </div>
    </form>
</div>
</body>
</html>
