<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouveau mot de passe - GESFINANCE</title>

    {{-- Import avec Vite ou Mix --}}
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        body {
            background: url("{{ asset('uploads/images/inscription.png') }}") no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .reset-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            padding: 30px;
            width: 400px;
        }
        .reset-card h3 {
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
        .login-footer {
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>
<div class="reset-card">
    <h3>Nouveau mot de passe</h3>
    <p class="text-center mb-3">
        Saisissez votre adresse email et définissez un nouveau mot de passe.
    </p>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <label for="email" class="form-label">Adresse Email</label>
            <input id="email" type="email"
                   class="form-control @error('email') is-invalid @enderror"
                   name="email" value="{{ $email ?? old('email') }}" required autofocus>
            @error('email')
            <span class="text-danger small">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Nouveau mot de passe</label>
            <input id="password" type="password"
                   class="form-control @error('password') is-invalid @enderror"
                   name="password" required autocomplete="new-password">
            @error('password')
            <span class="text-danger small">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password-confirm" class="form-label">Confirmer le mot de passe</label>
            <input id="password-confirm" type="password"
                   class="form-control"
                   name="password_confirmation" required autocomplete="new-password">
        </div>

        <button type="submit" class="btn btn-primary">Réinitialiser</button>

        <div class="login-footer">
            <a class="small d-block mt-2" href="{{ route('login') }}">
                Retour à la connexion
            </a>
        </div>
    </form>
</div>
</body>
</html>
