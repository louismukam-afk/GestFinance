<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Login - GESFINANCE</title>

    {{-- Import Vite/Mix --}}
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        body {
            background: url("{{ asset('uploads/images/argent.png') }}") no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .login-card {
            background: rgba(255,255,255,0.9);
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            padding: 30px;
            width: 400px;
        }
        .login-card h3 {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
            color: #1391e8;
        }
        .login-card h4 {
            text-align: center;
            margin-bottom: 10px;
            font-weight: bold;
            color:#000000;
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
<div class="login-card">
    <h3>GESTFINANCE</h3>
    <h4 >Connexion</h4>
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Adresse Email</label>
            <input id="email" type="email"
                   class="form-control @error('email') is-invalid @enderror"
                   name="email" value="{{ old('email') }}" required autofocus>
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

        <div class="mb-3 form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label" for="remember">Se souvenir de moi</label>
        </div>

        <button type="submit" class="btn btn-primary">Se connecter</button>


        <div class="login-footer">
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="btn btn-link">S’enregistrer</a>
            @endif

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
