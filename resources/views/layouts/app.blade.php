<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'GESFINANCE') }}</title>

    <!-- Vite -->
@vite(['resources/sass/app.scss', 'resources/js/app.js'])

<!-- Bootstrap & Custom -->
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('css/font-awesome.css') }}" rel="stylesheet"/>
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet"/>

    <!-- DataTables Buttons CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css"/>

    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow-x: hidden; /* ✅ empêche le scroll horizontal */
        }

        .page {
            min-height: 100vh; /* occupe toute la hauteur */
            display: flex;
            flex-direction: column;
        }

        .content {
            flex: 1; /* ✅ pousse le footer en bas */
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 5px 10px !important;
            margin: 2px !important;
            border-radius: 4px;
            border: 1px solid #ddd !important;
            text-align: right;
        }

        .dataTables_wrapper .dataTables_paginate {
            text-align: right !important;
            margin-top: 10px;
        }


        .footer {
            background: #2e353d;
            height: 60px;
            text-align: center;
            color: white;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100% !important;
            table-layout: auto;
        }
        @media print {
            @page  {
                margin-top: 2%;
                margin-bottom: 2%;
                margin-left: 2%;
                margin-right: 2%;
                size: A4,A3 portrait;
            }

            .wrapper,
            .navbar,
            .sidebar,
            .breadcrumb,
            .btn,
            form,
            select,
            input,
            .alert,
            nav,
            footer,
            .nav-side-menu,
            .menu-list,
            .upper-band,
            header {
                display: none !important;
            }


            /* ================================
            CONTENU
            ================================= */
            .container {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0;
                padding: 0;
            }
        }
    </style>

    @yield('styles')
</head>
<body>
<div id="app">

    <!-- 🔹 Sidebar -->
    <div class="nav-side-menu">
        <div class="brand">
            Bienvenue
            @auth
            <a href="#mod_edit_pass" data-toggle="modal">
                <strong>{{ Auth::user()->name }}</strong>
            </a>
            @endauth
        </div>
        <i class="fa fa-bars fa-2x toggle-btn" data-toggle="collapse" data-target="#menu-content"></i>

        <div class="menu-list">
            <ul id="menu-content" class="menu-content collapse out">
                <li>
                    <a href="{{ route('home') }}" class="menu-item"><i class="fa fa-home fa-lg"></i> Accueil</a>
                </li>
                <li>
                    <a href="{{ route('dashboard') }}" class="menu-item"><i class="fa fa-cog fa-lg"></i> Administration</a>
                </li>
                @auth
                <li>
                    <a href="{{ route('logout') }}" class="menu-item"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                       style="color: #1391e8;">
                        <i class="fa fa-user fa-lg"></i> Déconnexion
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
                @endauth
            </ul>
        </div>
    </div>

    <!-- 🔹 Contenu principal -->
    <div class="page">
        <div class="content">
            <!-- Bande supérieure -->
            <div class="upper-band">
                <div class="content pull-right">
                    <a href="" class="upper-band-el">
                        <span class="fa fa-table"></span> Paramètres
                    </a>
                </div>
            </div>

            <!-- Fil d’ariane -->
            <div class="band-gray">@yield('breadcrumb')</div>

            <!-- Zone principale -->
            <main class="py-4">
                <div class="container myContainer" style="padding-top: 10px">
                    @if(count($errors) > 0)
                        <div class="alert alert-danger">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success">
                            @if(is_array(session('success')))
                                @foreach(session('success') as $msg)
                                    <p>{{ $msg }}</p>
                                @endforeach
                            @else
                                <strong>{{ session('success') }}</strong>
                            @endif
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>

        <!-- 🔹 Footer -->
        <footer class="footer">
            <p>&copy; Copyright {{ date('Y') }} - GESFINANCE</p>
        </footer>
    </div>
</div>

<!-- Bootstrap -->
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/bootstrap.min.js') }}"></script>

<!-- Script custom -->
<script src="{{ URL::asset('js/custom.js') }}"></script>
@yield('scripts')
</body>
</html>
