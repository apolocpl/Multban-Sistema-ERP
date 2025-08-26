@extends('layouts.app-master')
@section('page.title', 'Produto')
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

                    <!-- FILTRO DO NOME DA EMPRESA -->
                    <div class="form-group col-md-3">
                        <label for="empresa_id">Empresa:*</label>
                        <select id="empresa_id" name="empresa_id" class="form-control select2 select2-hidden-accessible"
                            data-placeholder="Pesquise a Empresa" style="width: 100%;" aria-hidden="true" required>
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label id="produto_id">Código do Produto:</label>
                        <div class="input-group input-group-sm">
                            <input type="id" id="produto_id" name="produto_id" class="form-control  form-control-sm"
                                placeholder="Digite o código do produto">
                        </div>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="produto_tipo">Tipo de Produto:</label>
                        <select class="form-control select2" id="produto_tipo" name="produto_tipo" data-placeholder="Selecione o Tipo de Produto" data-allow-clear="true" style="width: 100%;">
                            <option></option>
                            @foreach($tipos as $tipo)
                                <option value="{{$tipo->produto_tipo}}">{{$tipo->produto_tipo_desc ?? $tipo->produto_tipo}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <!-- SEGUNDA LINHA DO FORMULÁRIO DE PESQUISA -->
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="produto_dmf">Descrição do Produto:</label>
                        <select id="produto_dmf" name="produto_dmf" class="form-control select2 select2-hidden-accessible"
                            data-placeholder="Pesquise o Nome do Produto" style="width: 100%;" aria-hidden="true">
                        </select>
                        <input type="hidden" id="produto_dmf_id" name="produto_dmf_id" value="">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="produto_sts">Status:</label>
                        <select class="form-control select2" id="produto_sts" name="produto_sts" data-placeholder="Selecione o Status" data-allow-clear="true" style="width: 100%;">
                            <option></option>
                            @foreach($status as $sta)
                                <option value="{{$sta->produto_sts}}">{{$sta->produto_sts_desc ?? $sta->produto_sts}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3 align-self-end d-flex">
                        <button type="button" id="btnPesquisar" class="btn btn-primary btn-sm"><i class="fa fa-search"></i> Pesquisar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- QUADRO DO GRID DE PRODUTOS -->
        <div class="card card-outline card-primary">

            <!-- BOTÃO PARA CRIAR NOVO PRODUTO -->
            @can('produto.store')
                <div class="card-header">
                    <a href="/produto/inserir" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Criar novo</a>
                </div>
            @endcan

            <!-- CORPO DO QUADRO DO GRID DE PRODUTOS -->
            <div class="card-body">

                <div class="table-responsive">
                    <table id="gridtemplate" class="table table-striped table-bordered nowrap">
                        <thead>
                            <tr>
                                <th>Ações</th>
                                <th>Código do Produto</th>
                                <th>Tipo de Produto</th>
                                <th>Descrição Curta</th>
                                <th>Descrição Média</th>
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
    <script src="{{ asset('assets/dist/js/pages/produto/gridproduto.js') }}"></script>

    <script type="text/javascript">

    $(document).ready(function () {

        $('#inputPesquisa').on('keyup', function (e) {
            if (e.key === 'Enter') {
                $('#btnPesquisar').trigger('click');
            }
        });

        $(".alert-dismissible")
            .fadeTo(2000, 500)
            .slideUp(500, function () {
            $(".alert-dismissible").alert("close");
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
