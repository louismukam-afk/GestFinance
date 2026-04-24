<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'GESFINANCE') }}</title>

    <!-- Bootstrap CSS -->
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/font-awesome.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('js/datatables/datatables.min.css') }}" />

    <!-- DataTables Buttons CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css" />

    @yield('styles')
</head>
<body>
<div id="app">

    <!-- 🔹 Sidebar (mise en forme type skeleton) -->
    <div class="nav-side-menu">
        <div class="brand">
            Bienvenue
            @auth
            <strong>{{ Auth::user()->name }}</strong>
            @endauth
        </div>
        <i class="fa fa-bars fa-2x toggle-btn" data-toggle="collapse" data-target="#menu-content"></i>

        <div class="menu-list">
            <ul id="menu-content" class="menu-content collapse out">
                <li><a href="{{ url('/') }}" class="menu-item"><i class="fa fa-home fa-lg"></i> Accueil</a></li>
                <li><a href="{{ route('dashboard') }}" class="menu-item"><i class="fa fa-cog fa-lg"></i> Administration</a></li>

                <!-- 🔹 Déconnexion -->
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
    <div class="container-fluid page" style="padding-right:0">
        <div class="big-container col-md-12" style="padding-left:0; padding-right:0">

            <div class="upper-band" style="overflow-x: auto">
                <div class="content pull-right">
                    <a href="" class="upper-band-el"><span class="fa fa-table"></span> Paramètres</a>
                </div>
            </div>

            <div class="band-gray">@yield('breadcrumb')</div>

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
                            <strong>{{ session('success') }}</strong>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>



        </div>
        <div class="footer">
            <p class="text-center" style="color: white;">
                <span class="glyphicon glyphicon-copyright-mark"></span>Copyright  2025
            </p>
        </div>
    </div>
    <!-- 🔹 Footer -->

</div>

<!-- Scripts -->
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/bootstrap.min.js') }}"></script>

<!-- DataTables & Export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script src="{{ URL::asset('js/custom.js') }}"></script>
@yield('scripts')
</body>
</html>
