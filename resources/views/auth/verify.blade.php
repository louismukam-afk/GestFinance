<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Vérification de l’email - GESFINANCE</title>

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
        .verify-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            padding: 30px;
            width: 450px;
        }
        .verify-card h3 {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
            color: #1391e8;
        }
        .btn-primary, .btn-link {
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
<div class="verify-card">
    <h3>Vérification de l’email</h3>

    @if (session('resent'))
        <div class="alert alert-success text-center" role="alert">
            Un nouveau lien de vérification a été envoyé à votre adresse email.
        </div>
    @endif

    <p class="text-center mb-3">
        Avant de continuer, veuillez vérifier votre boîte mail.<br>
        Si vous n’avez pas reçu l’email, vous pouvez en demander un autre :
    </p>

    <form method="POST" action="{{ route('verification.resend') }}">
        @csrf
        <button type="submit" class="btn btn-primary">
            Renvoyer le lien de vérification
        </button>
    </form>

    <div class="login-footer">
        <a class="small d-block mt-2" href="{{ route('login') }}">
            Retour à la connexion
        </a>
    </div>
</div>
</body>
</html>
