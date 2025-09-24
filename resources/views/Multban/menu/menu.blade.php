<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">

    <!-- Brand Logo -->
    <a href="/" class="brand-link">
        @php
        $temEmpresas = false;
        $temEmpresas = !empty($empresa);

        @endphp

        @if($empresa->whiteLabel)
            @if ($empresa->whiteLabel->mini_logo)
                <img src="{{asset('storage/white-label/empresa-'.$empresa->emp_id. '/mini-logo.png')}}"
                alt="{{$temEmpresas ? $empresa->emp_rzsoc : 'Empresa não cadastrada'}}"
                title="{{$temEmpresas ? $empresa->emp_rzsoc : 'Sem cadastro'}}" class="brand-image" style="opacity: .8">
            @else
                <img src="{{ asset('assets/dist/img/logo-amarela-min.png')}}"
                alt="{{$temEmpresas ? $empresa->emp_rzsoc : 'Empresa não cadastrada'}}"
                title="{{$temEmpresas ? $empresa->emp_rzsoc : 'Sem cadastro'}}" class="brand-image" style="opacity: .8">
            @endif
        @else
            <img src="{{ asset('assets/dist/img/logo-amarela-min.png')}}"
            alt="{{$temEmpresas ? $empresa->emp_rzsoc : 'Empresa não cadastrada'}}"
            title="{{$temEmpresas ? $empresa->emp_rzsoc : 'Sem cadastro'}}" class="brand-image" style="opacity: .8">
        @endif



        <span class="brand-text">
            @if($empresa->whiteLabel)
            @if ($empresa->whiteLabel->logo_h)
                <img src="{{asset('storage/white-label/empresa-'.$empresa->emp_id. '/logo-h.png')}}" alt="Logo multban"
                class="logo-multban">
            @else
                <img src="{{asset('assets/dist/img/logo-amarela.png')}}" alt="Logo multban" class="logo-multban">
            @endif

            @else
                <img src="{{asset('assets/dist/img/logo-amarela.png')}}" alt="Logo multban" class="logo-multban">
            @endif
        </span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            {{$menus}}
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
