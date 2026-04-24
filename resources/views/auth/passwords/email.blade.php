<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réinitialisation du mot de passe - GESFINANCE</title>

    {{-- Import avec Vite ou Mix --}}
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        body {
            background: url("{{ asset('uploads/images/argent.png') }}") no-repeat center center fixed;
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
    <h3>Réinitialisation</h3>
    <p class="text-center mb-3">
        Entrez votre adresse email pour recevoir un lien de réinitialisation.
    </p>

    @if (session('status'))
        <div class="alert alert-success text-center" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
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

        <button type="submit" class="btn btn-primary">Envoyer le lien</button>

        <div class="login-footer">
            <a class="small d-block mt-2" href="{{ route('login') }}">
                Retour à la connexion
            </a>
        </div>
    </form>
</div>
</body>
</html>
