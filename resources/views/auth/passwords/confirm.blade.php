<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmation du mot de passe - GESFINANCE</title>

    {{-- Import avec Vite ou Mix --}}
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        body {
            background: url("{{ asset('images/login-finance.png') }}") no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .confirm-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            padding: 30px;
            width: 400px;
        }
        .confirm-card h3 {
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
<div class="confirm-card">
    <h3>Confirmation</h3>
    <p class="text-center mb-3">
        Veuillez confirmer votre mot de passe avant de continuer.
    </p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="mb-3">
            <label for="password" class="form-label">Mot de passe</label>
            <input id="password" type="password"
                   class="form-control @error('password') is-invalid @enderror"
                   name="password" required autocomplete="current-password">
            @error('password')
            <span class="text-danger small">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Confirmer</button>

        <div class="login-footer">
            @if (Route::has('password.request'))
                <a class="small d-block mt-2" href="{{ route('password.request') }}">
                    Mot de passe oublié ?
                </a>
            @endif
        </div>
    </form>
</div>
</body>
</html>
