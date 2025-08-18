@extends('layouts.app-master')
@section('page.title', 'Agendamento')
@push('script-head')
<link href="{{ asset('assets/plugins/select2/css/select2.min.css') }}" rel="stylesheet" />
<!-- summernote -->
<link rel="stylesheet" href="{{ asset('assets/plugins/summernote/summernote-bs4.css') }}">
<!-- Tempusdominus Bootstrap 4 -->
<link rel="stylesheet"
    href="{{ asset('assets/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">

@endpush

@section('content')
<!-- Main content -->
<section class="content">
    @if($routeAction)
    <form class="form-horizontal" id="formPrincipal" autocomplete="off" role="form" method="POST"
        action="{{ route('agendamento.update', $agendamento->id) }}">
        @method('PATCH')
        @else

        <form autocomplete="off" class="form-horizontal" id="formPrincipal" role="form" method="POST"
            action="{{ route('agendamento.store') }}">
            @method('POST')
            @endif
            @csrf
            @include('Multban.template.updatetemplate')



            <div class="card card-primary">
                <div class="card-body">

                    <div class="form-row">

                        <div class="form-group col-md-3">
                            <label for="agendamento_id">Protocolo:</label>
                            <div class="input-group input-group-sm">
                                <!--O código tem que ser registrado automaticamnete após a criação do usuário-->
                                <input autocomplete="off" class="form-control  form-control-sm" id="agendamento_id"
                                    name="agendamento_id" value="{{str_pad($agendamento->id, 12, '0', STR_PAD_LEFT)}}"
                                    placeholder="Protocolo" readonly="">
                            </div>
                        </div>

                        <div class="form-group col-md-3">
                            <label for="status">Status:*</label>
                            <select class="form-control select2" name="status" id="status"
                                data-placeholder="Selecione" style="width: 100%;">
                                <option></option>
                                @foreach ($status as $sts)
                                <option value="{{$sts->agendamento_sts}}" {{ $sts->agendamento_sts == $agendamento->status ?
                                    'selected' : '' }}>{{$sts->agendamento_sts_desc}}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label for="cliente_id">Nome:* @if($agendamento->cliente)
                                <a href="/cliente/{{$agendamento->cliente->cliente_id}}/alterar" class="text- text-primary" >[ <i class="fa fa-address-card"></i> Ver prontuário]</a>
                            @endif</label>
                            <select id="cliente_id" name="cliente_id"
                                class="form-control select2 select2-hidden-accessible"
                                data-placeholder="Pesquise o paciente" style="width: 100%;" aria-hidden="true">
                                @if($agendamento->cliente)
                                <option value="{{$agendamento->cliente->cliente_id}}">
                                    {{str_pad($agendamento->cliente->cliente_id, 5, '0', STR_PAD_LEFT)}} - {{$agendamento->cliente->cliente_nome}}
                                </option>
                                @endif
                            </select>
                        </div>

                        <div class="form-group col-md-3">
                            <label for="cliente_doc">CPF:*</label>
                            <input autocomplete="off" type="text" class="form-control cpf form-control-sm"
                                id="cliente_doc" name="cliente_doc"
                                value="{{!empty($agendamento->cliente) ? $agendamento->cliente->cliente_doc : ''}}"
                                placeholder="CPF" maxlength="14">

                        </div>

                        <div class="form-group col-md-3">
                            <label for="cliente_rg">RG:</label>
                            <input autocomplete="off" type="text" class="form-control cpf form-control-sm"
                                id="cliente_rg" name="cliente_rg"
                                value="{{!empty($agendamento->cliente) ? $agendamento->cliente->cliente_rg : ''}}"
                                placeholder="RG" maxlength="10">

                        </div>

                    </div>

                    <div class="form-row">

                        <div class="form-group col-md-3">
                            <label for="cliente_cel">Número de Celular:*</label>
                            <input autocomplete="off" type="text" class="form-control cell_with_ddd form-control-sm"
                                id="cliente_cel" name="cliente_cel"
                                value="{{!empty($agendamento->cliente) ? $agendamento->cliente->cliente_cel : ''}}"
                                placeholder="Digite o Celular">
                        </div>

                        <div class="form-group col-md-3">
                            <label for="cliente_telfixo">Telefone Fixo:</label>
                            <input autocomplete="off" type="text" class="form-control phone_with_ddd form-control-sm"
                                id="cliente_telfixo" name="cliente_telfixo"
                                value="{{!empty($agendamento->cliente) ? $agendamento->cliente->cliente_telfixo : ''}}"
                                placeholder="Digite o Telefone Fixo">
                        </div>

                        <div class="form-group col-md-3">
                            <label for="cliente_email">E-mail:*</label>
                            <input autocomplete="off" type="email" class="form-control  form-control-sm"
                                id="cliente_email" name="cliente_email"
                                value="{{!empty($agendamento->cliente) ? $agendamento->cliente->cliente_email : ''}}"
                                placeholder="Digite o E-mail">
                        </div>
                    </div>

                    <div class="form-row">

                        <div class="form-group col-md-3">
                            <label for="cliente_dt_nasc">Data de Nascimento:*</label>
                            <input autocomplete="off" type="text"
                                class="form-control datetimepicker-input form-control-sm" id="cliente_dt_nasc" name="cliente_dt_nasc"
                                value="{{!empty($agendamento->cliente) ? formatarData( $agendamento->cliente->cliente_dt_nasc,'Y-m-d', 'd/m/Y') : ''}}"
                                data-toggle="datetimepicker" data-target="#cliente_dt_nasc" placeholder="Data de Nascimento">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="convenio">Convênio:</label>
                              <select class="form-control select2" style="width: 100%;" data-placeholder="Selecione o Convênio"
                                            id="convenio_id" name="convenio_id">
                                            <option></option>
                                            @foreach($convenios as $key => $convenio)
                                            <option value="{{$convenio->convenio_id}}" @if($agendamento->cliente)
                                                {{$convenio->convenio_id == $agendamento->cliente->convenio_id ? 'selected' : ''}}
                                            @endif>{{$convenio->convenio_desc}}
                                            </option>
                                            @endforeach
                                        </select>
                        </div>

                        <div class="form-group col-md-3">
                            <label for="nro_carteirinha">Nro carteirinha:</label>
                            <input autocomplete="off" type="text" class="form-control form-control-sm"
                                id="nro_carteirinha" name="nro_carteirinha" value="{{!empty($agendamento->cliente) ? $agendamento->cliente->carteirinha : ''}}"
                                placeholder="Nro carteirinha">

                        </div>
                    </div>

                    <hr>
                    <div class="form-row">
                        <p class="text-muted">
                        <h4>Informações do atendimento:*</h4>
                        </p>
                    </div>


                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label for="user_id">Profissional:*</label>
                            <select class="form-control select2" name="user_id" id="user_id"
                                data-placeholder="Selecione" style="width: 100%;">
                                <option></option>
                                @foreach ($users as $user)
                                <option value="{{$user->user_id}}" {{ $user->user_id == $agendamento->user_id ?
                                    'selected' : '' }}>{{$user->user_name}} @if ($user->cargo) - {{$user->cargo->user_func_desc}}
                                    @endif </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-2">
                            <label for="date">Data:*</label>
                            <input autocomplete="off" type="text"
                                class="form-control datetimepicker-input form-control-sm" id="date" name="date"
                                value="{{$agendamento->date}}" data-toggle="datetimepicker" placeholder="dd/mm/aaaa"
                                data-target="#date">
                            <span id="dateError" class="text-danger text-sm"></span>
                        </div>


                        <div class="form-group col-md-2">
                            <label for="start">Início:*</label>
                            <input autocomplete="off" type="text"
                                class="form-control datetimepicker-input form-control-sm" id="start" name="start"
                                value="{{$agendamento->start}}" data-toggle="datetimepicker" placeholder="hh:mm"
                                data-target="#start">
                            <span id="startError" class="text-danger text-sm"></span>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="end">Término:*</label>
                            <input autocomplete="off" role="presentation" type="text"
                                class="form-control datetimepicker-input form-control-sm" id="end" name="end"
                                value="{{$agendamento->end}}" data-toggle="datetimepicker" placeholder="hh:mm"
                                data-target="#end">
                            <span id="endError" class="text-danger text-sm"></span>

                            <!-- /.input group -->
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label for="agendamento_tipo">Tipo de atendimento:*</label>
                            <select id="agendamento_tipo" name="agendamento_tipo" class="form-control select2"
                                data-placeholder="Selecione" style="width: 100%;" required aria-hidden="true">
                                <option></option>
                                @foreach ($tipos as $tipo)
                                <option value="{{$tipo->agendamento_tipo}}" {{ $tipo->agendamento_tipo
                                    == $agendamento->agendamento_tipo ? 'selected' : ''
                                    }}>{{$tipo->agendamento_tipo_desc}}</option>
                                @endforeach
                            </select>
                        </div>


                        <div class="form-group col-md-3">
                            <label for="observacao">Observações:</label>
                            <textarea id="observacao" name="observacao" rows="3" class="form-control form-control-sm"
                                rows="3">{{ $agendamento->observacao }}</textarea>
                        </div>

                    </div>

                </div>
            </div>




        </form>
    </form>
</section>
<!-- /.content -->

@endsection

@push('scripts')




<script type="text/javascript">
    $(document).ready(function(e) {

        // Inicializa o seletor de data
        $('#date').datetimepicker({
            format: 'DD/MM/YYYY',
            locale: 'pt-br'
        });

        // Inicializa o seletor de hora
        $('#start').datetimepicker({
            format: 'LT',
            locale: 'pt-br'
        });

        $('#end').datetimepicker({
            format: 'LT',
            locale: 'pt-br'
        });
        $('#cliente_dt_nasc').datetimepicker({
            format: 'DD/MM/YYYY',
            locale: 'pt-br'
        });

        // Verifica o status do usuário e ajusta o texto do botão de ativação/inativação
        if ("{{$agendamento->user_sts}}" == "EX" ) {
            $("#btnInativar").text("Ativar");
            $("#btnInativar").prepend('<i class="fa fa-check"></i> ');
            $("#btnExcluir").prop('disabled', true);


        } else if ("{{$agendamento->user_sts}}" == "AT") {
            $("#btnInativar").text("Inativar");
            $("#btnInativar").prepend('<i class="fa fa-ban"></i> ');
            $("#btnExcluir").prop('disabled', false);}
         else {
            $("#btnInativar").text("Inativar");
            $("#btnInativar").prepend('<i class="fa fa-ban"></i> ');
        }


    });
</script>

<script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/i18n/pt-BR.js') }}"></script>
<script src="{{ asset('assets/plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
<!-- InputMask -->
<script src="{{ asset('assets/plugins/moment/moment.min.js') }}"></script>
<!-- Summernote -->
<script src="{{ asset('assets/plugins/summernote/summernote-bs4.min.js') }}"></script>
<!-- Moment -->
<script src="{{ asset('assets/plugins/moment/moment-with-locales.js') }}"></script>
<script src="{{ asset('assets/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
<script src="{{ asset('assets/plugins/daterangepicker/daterangepicker.js') }}"></script>
<script src="{{ asset('assets/plugins/inputmask/min/jquery.inputmask.bundle.min.js') }}"></script>
<link rel="stylesheet" href="{{asset('assets/dist/css/app.css') }}" />
<script src="{{asset('assets/dist/js/app.js') }}"></script>
<script src="{{asset('assets/dist/js/pages/agendamento/agendamento.js') }}"></script>

@endpush
