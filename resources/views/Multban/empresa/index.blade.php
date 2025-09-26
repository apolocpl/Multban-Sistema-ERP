@extends('layouts.app-master')
@section('page.title', 'Empresa')
@push('script-head')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-select/css/select.bootstrap4.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-fixedheader/css/fixedHeader.bootstrap4.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}" />
@endpush
@section('content')
    <!-- Main content -->
    <section class="content">
        @if (session()->get('success'))
            <div class="col-sm-12">
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h5><i class="icon fas fa-check"></i> Sucesso!</h5>
                    {{ session()->get('success') }}
                </div>
            </div>
        @endif

        @if (session()->get('warning'))
            <div class="col-sm-12">
                <div class="alert alert-warning alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h5><i class="icon fas fa-check"></i> Alerta!</h5>
                    {{ session()->get('warning') }}
                </div>
            </div>
        @endif

        <!-- QUADRO DO FORMULÁRIO DE PESQUISA -->
        <div class="card card-outline card-primary">

            <div class="card-body" id="filtro-pesquisa">

                <!-- PRIMEIRA LINHA DO FORMULÁRIO DE PESQUISA -->
                <div class="form-row">

                    <!-- FILTRO DO CÓDIGO DA FRANQUEADORA -->
                    <div class="form-group col-md-3">
                        <label for="cod_franqueadora">Código da Franqueadora:</label>
                        <select id="cod_franqueadora" name="cod_franqueadora"
                            class="form-control select2 select2-hidden-accessible"
                            data-placeholder="Pesquise a Franqueadora" style="width: 100%;" aria-hidden="true">
                            <option></option>
                        </select>
                    </div>

                    <!-- FILTRO DO NOME DA EMPRESA -->
                    <div class="form-group col-md-3">
                        <label for="Empresa">Empresa:</label>
                        <select id="empresa_id" name="empresa_id" class="form-control select2 select2-hidden-accessible"
                            data-placeholder="Pesquise a Empresa" style="width: 100%;" aria-hidden="true">
                        </select>
                    </div>

                    <!-- FILTRO DO STATUS DA EMPRESA -->
                    <div class="form-group col-md-2">
                        <label for="emp_sts">Status:</label>
                        <select class="form-control select2" name="emp_sts" id="emp_sts"
                            data-placeholder="Selecione" data-allow-clear="true" style="width: 100%;">
                            <option></option>
                            @foreach($status as $key => $sta)
                                <option {{$sta->emp_sts == $empresaGeral->emp_sts ? 'selected' : ''}}
                                    value="{{$sta->emp_sts}}">{{$sta->emp_sts_desc}}</option>
                            @endforeach
                        </select>
                        <span id="emp_stsError" class="text-danger text-sm"></span>
                    </div>

                </div>

                <!-- SEGUNDA LINHA DO FORMULÁRIO DE PESQUISA -->
                <div class="form-row">

                    <!-- FILTRO DO NOME multban -->
                    <div class="form-group col-md-3">
                        <label for="nome_multban">Nome multban:</label>
                        <select id="nome_multban" name="nome_multban"
                            class="form-control select2 select2-hidden-accessible"
                            data-placeholder="Pesquise o Nome multban" style="width: 100%;" aria-hidden="true">
                            <option></option>
                        </select>
                    </div>

                    <!-- FILTRO DO CNPJ -->
                    <div class="form-group col-md-3">
                        <label for="empresa_cnpj">CNPJ:</label>
                        <input type="text" id="empresa_cnpj" name="empresa_cnpj" class="form-control cnpj form-control-sm"
                            placeholder="Digite o CNPJ" />
                    </div>

                    <!-- BOTÃO PESQUISAR -->
                    <div class="form-group col-md-3 d-flex align-items-end">
                        <button type="button" id="btnPesquisar" class="btn btn-primary btn-sm"><i class="fa fa-search"></i> Pesquisar</button>
                    </div>

                </div>

            </div>

        </div>

        <!-- QUADRO DO GRID DE EMPRESAS -->
        <div class="card card-outline card-primary">

            <!-- BOTÃO PARA CRIAR NOVA EMPRESA -->
            @can('empresa.create')
                <div class="card-header">
                    <a href="/empresa/inserir" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Criar novo</a>
                </div>
            @endcan

            <!-- CORPO DO QUADRO DO GRID DE EMPRESAS -->
            <div class="card-body">

                <div class="table-responsive">
                    <table id="gridtemplate" class="table table-striped table-bordered nowrap">
                        <thead>
                            <tr>
                                <th>Ações</th>
                                <th>Código</th>
                                <th>Nome</th>
                                <th>CNPJ</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>

        </div>

    </section>

@endsection

@push('scripts')
    <!-- Select2 -->
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/select2/js/i18n/pt-BR.js') }}"></script>
    <script src="{{ asset('assets/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-select/js/dataTables.select.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-fixedheader/js/dataTables.fixedHeader.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/dist/js/app.js') }}"></script>
    <script src="{{ asset('assets/dist/js/pages/empresa/gridempresa.js') }}"></script>

    <script type="text/javascript">
        $(document).ready(function () {
            // Select2 AJAX para pesquisar nomes multban
            $('#nome_multban').select2({
                placeholder: 'Pesquise o Nome multban',
                minimumInputLength: 2,
                ajax: {
                    url: '/empresa/obter-nmult', // ajuste conforme sua rota/controller
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            parametro: params.term
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (nmult) {
                                return {
                                    id: nmult.id,
                                    text: nmult.text
                                };
                            })
                        };
                    },
                    cache: true
                }
            });

            // Select2 AJAX para pesquisar franqueadoras
            $('#cod_franqueadora').select2({
                placeholder: 'Pesquise a Franqueadora',
                minimumInputLength: 2,
                ajax: {
                    url: '/empresa/obter-franqueadoras', // ajuste conforme sua rota/controller
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            parametro: params.term
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (franqueadora) {
                                return {
                                    id: franqueadora.id,
                                    text: franqueadora.text
                                };
                            })
                        };
                    },
                    cache: true
                }
            });

            @if ($message = Session::get('success'))
                $("#empresa_id").val({{ Session::get('idModeloInserido') }})
                toastr.success("{{ $message }}", "Sucesso");
            @endif

            @if ($message = Session::get('error'))
                toastr.error("{{ $message }}", "Erro");
            @endif
            @if (count($errors) > 0)
                @foreach ($errors->all() as $error)
                    toastr.error("{{ $error }}", "Erro");
                @endforeach
            @endif
                                        });
    </script>
@endpush
