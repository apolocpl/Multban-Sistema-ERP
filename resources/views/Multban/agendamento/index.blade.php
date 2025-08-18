@extends('layouts.app-master')
@section('page.title', 'Agenda')
@push('script-head')
<!-- Select2 -->
<link rel="stylesheet" href="/assets/plugins/select2/css/select2.min.css">
<link rel="stylesheet" href="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/datatables-select/css/select.bootstrap4.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/plugins/datatables-fixedheader/css/fixedHeader.bootstrap4.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}" />

@endpush
@section('content')
<!-- Main content -->
<section class="content">

    @if (count($errors) > 0)
    @foreach ($errors->all() as $error)
    <div class="col-sm-12">
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-check"></i> Alerta!</h5>
            {{ $error }}
        </div>
    </div>

    @endforeach
    @endif


    <div class="container-fluid">
        <div class="row">

            <div class="col-md-12">

                <div class="sticky-top mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Agendamento</h4>
                        </div>
                        <div class="card-body">
                            <!-- the events -->
                            <div class="input-group mb-3">
                                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                                    Agendar
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('agendamento.create') }}" id="btnAgendamento">Atendimento</a>
                                </div>
                            </div>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
            </div>
        </div>
        <div class="row">
            <!-- /.col -->
            <div class="col-md-12">
                <div class="card card-primary">
                    <div class="card-body p-0">
                        <!-- THE CALENDAR -->
                        <div id="calendar"></div>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->
@endsection



@push('scripts')

<!-- Select2 -->
<script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/i18n/pt-BR.js') }}"></script>
<script src="{{ asset('assets/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-select/js/dataTables.select.min.js')}}"></script>
<script src="{{ asset('assets/plugins/datatables-fixedheader/js/dataTables.fixedHeader.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('assets/plugins/moment/moment.min.js') }}"></script>
<script src="{{ asset('assets/plugins/fullcalendar-6.1.18/dist/index.global.min.js') }}"></script>
<script src="{{ asset('assets/plugins/fullcalendar-6.1.18/dist/locales/pt-br.global.min.js') }}"></script>

<script src="{{ asset('assets/dist/js/app.js') }}"></script>
<script src="{{ asset('assets/dist/js/pages/agendamento/indexagendamento.js') }}"></script>

<script type="text/javascript">
    $(document).ready(function () {

        @if ($message = Session::get('success'))
            $("#empresa_id").val({{ Session::get('idModeloInserido') }})
            toastr.success("{{ $message }}", "Sucesso");
        @endif
        @if (count($errors) > 0)
            @foreach ($errors->all() as $error)
                toastr.error("{{ $error }}", "Erro");
            @endforeach
        @endif
    });
</script>

@if ($message = Session::get('success'))
<script>
    toastr.success("{{ $message }}", "Sucesso");
            console.log('idModeloInserido', "{{ Session::get('idModeloInserido') }}");
            $("#inputPesquisa").val("{{ Session::get('idModeloInserido') }}");
            setTimeout(function(){
                $("#btnPesquisar").trigger("click");
                $("#inputPesquisa").val("");
            }, 200);
</script>
@endif

@if ($message = Session::get('error'))
<script>
    $("#inputPesquisa").val("{{ Session::get('idModeloInserido') }}");
        toastr.error("{{ $message }}", "Erro");
        setTimeout(function(){
            $("#btnPesquisar").trigger("click");
            $("#inputPesquisa").val("");
        }, 200);
</script>
@endif

@if (count($errors) > 0)
<script>
    var errors = {!! json_encode($errors->all()) !!};
            errors.forEach(function(error) {
                toastr.error(error, "Erro");
            });
</script>
@endif

@endpush
