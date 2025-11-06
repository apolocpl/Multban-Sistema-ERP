<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page.title', 'Multban') | {{$empresa->emp_rzsoc}}</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/fonts-pro/css/all.min.css') }}">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <!-- pace-progress -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/pace-progress/themes/multban/pace-theme-flat-top.css') }}">
    @stack('script-head')

    @if($empresa->whiteLabel)
        <link rel="stylesheet" href="{{ asset('storage/white-label/empresa-'.$empresa->emp_id. '/multban.min.css') }}">
    @else
        <link rel="stylesheet" href="{{ asset('assets/dist/css/multban.min.css') }}">
    @endif
    <link rel="stylesheet" href="{{ asset('assets/plugins/toastr/toastr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.css') }}">

    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
    @if($empresa->whiteLabel)
        @if($empresa->whiteLabel->mini_logo)
            <link rel="icon" type="image/png"
            href="{{ asset('storage/white-label/empresa-'.$empresa->emp_id. '/'. $empresa->whiteLabel->mini_logo) }}">
        @endif
    @else
        <link rel="icon" type="image/png"
        href="{{asset('assets/dist/img/logo-amarela-min.png')}}">
    @endif
        {{--@vite(['resources/css/app.css', 'resources/js/app.js'])--}}
</head>

<body
    class="hold-transition sidebar-mini layout-fixed pace-primary layout-footer-fixed text-sm {!! $minimizarMenu ? 'sidebar-collapse' : '' !!}">
    <div class="se-pre-con"></div>
    <div class="wrapper">

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav navbar-nav-pdv">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <!-- Notifications Dropdown Menu -->
                @if(Request::is('pdv'))

                <!--li class="nav-item p-1"><a class="btn btn-success" target="_blank" href="/guiche">Guiche</a></li-->
                <li class="nav-item p-1"><a class="btn btn-block btn-outline-primary" href="/">Sair do PDV</a></li>

                <li class="nav-item dropdown d-block d-sm-none">
                    <a class="nav-link" id="carrinhoIcon" type="button">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="badge badge-success navbar-badge countcart">0</span>
                    </a>
                </li>
                @endif

                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="far fa-bell"></i>
                        <span class="badge badge-warning navbar-badge">{{$notificacaoContador}}</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <span class="dropdown-item dropdown-header">{{$notificacaoContador}} Notificações</span>

                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item dropdown-footer">Ver todas</a>
                    </div>
                </li>
                <!-- User Profile Dropdown Menu -->
                <li class="nav-item dropdown user-menu">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                        <span class="d-none d-md-inline">{{auth()->user()->user_name}}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <!-- User image -->
                        <li class="user-header bg-primary">
                            <img src="{{empty(auth()->user()->image) ? url('/assets/dist/img/') . '/' . 'no-product-image.png' : url('/storage/images/usuario') . '/' . $user->image}}"
                                class="img-circle elevation-2" alt="User Image">

                            <p>
                                {{auth()->user()->user_name}}
                                <small>Data de cadastro: {{date_format(date_create(auth()->user()->dthr_cr), 'd/m/Y H:i:s')}}</small>
                            </p>
                        </li>
                        <li class="user-footer">
                            <a href="/perfil" class="btn btn-default btn-flat">Perfil</a>
                            <form method="POST" style="margin-top: -37px;" action="{{ route('logout') }}">
                                @csrf
                                <a href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="btn btn-default btn-flat float-right">Sair</a>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        @include('Multban.menu.menu')

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <ol class="breadcrumb ">
                                @if(count(Request::segments()) == 1 & Request::segment(1) != "home")
                                <li class="breadcrumb-item"><a href="/">Home</a></li>
                                <li class="breadcrumb-item active">@yield('page.title')</li>
                                @elseif(count(Request::segments()) > 1 & Request::segment(1) != "home")
                                <li class="breadcrumb-item"><a href="/">Home</a></li>
                                <li class="breadcrumb-item"><a href="/{{Request::segment(1)}}">@yield('page.title')</a>
                                </li>
                                @php
                                    $defaultBreadcrumb = Request::segment(2);
                                    if (is_string($defaultBreadcrumb) && $defaultBreadcrumb !== '') {
                                        $defaultBreadcrumb = ucfirst($defaultBreadcrumb);
                                    } else {
                                        $defaultBreadcrumb = '';
                                    }
                                    $breadcrumbLabel = $__env->yieldContent('page.breadcrumb', $defaultBreadcrumb);
                                    if (! is_string($breadcrumbLabel)) {
                                        $breadcrumbLabel = '';
                                    } else {
                                        $breadcrumbLabel = trim($breadcrumbLabel);
                                    }
                                @endphp
                                <li class="breadcrumb-item active">{{ $breadcrumbLabel }}</li>
                                @else
                                <li class="breadcrumb-item active">Home</li>
                                @endif
                            </ol>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            <!-- /.content-header -->
            @yield('content')


        </div>
        <!-- /.content-wrapper -->
            <!--TEXTO DE COPYRIGHT NO FOOTER-->
            <footer class="main-footer">
                <strong>Copyright &copy; 2023 - {{date('Y')}} MULTBAN.</strong>
                Todos os Direitos Reservados - CNPJ: 01.179.943/0001-96 - <a tabindex="-1"
                href="#" class="text-secundary">Desenvolvido por Multban - Divisão de Sistemas.</a>

                <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 1.0.0.0
                </div>
              </footer>

            <!-- Control Sidebar -->
            <aside class="control-sidebar control-sidebar-dark">
                <!-- Control sidebar content goes here -->
            </aside>
            <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->
    <!-- jQuery -->
    <script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- overlayScrollbars -->
    <script src="{{ asset('assets/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <!-- pace-progress -->
    <script src="{{ asset('assets/plugins/pace-progress/pace.min.js') }}"></script>
    <!-- Multban App -->
    <script src="{{ asset('assets/dist/js/multban.min.js') }}"></script>
    <!-- pre loading -->
    <script src="{{ asset('assets/plugins/modernizr/2.8.3/modernizr.js') }}"></script>
    <!-- shortcut -->
    <script src="{{asset('assets/plugins/shortcut/shortcut.js') }}"></script>

    <script src="{{ asset('assets/plugins/jquery-mask/jquery.mask.js') }}"></script>
    <!-- toastr -->
    <script src="{{ asset('assets/plugins/toastr/toastr.min.js') }}"></script>
    <!-- sweetalert -->
    <script src="{{ asset('assets/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <!-- lodash -->
    <script src="{{ asset('assets/plugins/lodash/lodash.min.js') }}"></script>

    <!-- formatCurrency -->
    <script src="{{ asset('assets/plugins/formatCurrency/jquery.formatCurrency-1.4.0.js') }}"></script>
    <script src="{{ asset('assets/plugins/formatCurrency/i18n/jquery.formatCurrency.pt-BR.js') }}"></script>

    <style>
        .no-js #loader {
            display: none;
        }

        .js #loader {
            display: block;
            position: absolute;
            left: 100px;
            top: 0;
        }

        .se-pre-con {
            position: fixed;
            left: 0px;
            top: 0px;
            width: 100%;
            height: 100%;
            z-index: 9999;
            background: url('{{ asset("assets/dist/img/loading/preloader_3.gif") }}') center no-repeat #fff;
        }
    </style>
    <script>
        let isClosing = false;
        // Wait for window load
        $(window).on('load', function() {
            // Animate loader off screen
             $(".se-pre-con").fadeOut("slow");
        });

        window.addEventListener('unload', function () {
            return;
            dataF = new FormData();
            dataF.append('_token', $('meta[name="csrf-token"]').attr("content"));
            navigator.sendBeacon('{{route("logout")}}', dataF);
        });



    </script>
    @stack('scripts')
</body>

</html>
