@extends('layouts.app-master')
@section('page.title', 'Programa de Pontos')
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
                        <label for="Empresa">Empresa:</label>
                        <select id="empresa_id" name="empresa_id" class="form-control select2 select2-hidden-accessible"
                            data-placeholder="Pesquise a Empresa" style="width: 100%;" aria-hidden="true">
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Categoria do Cartão:</label>
                        <select class="form-control select2" id="card_categ" name="card_categ" data-placeholder="Selecione a Catgoria do Cartão" data-allow-clear="true" style="width: 100%;">
                            <option></option>
                            @foreach($card_categ as $cat)
                                <option value="{{ $cat->categ }}">{{ $cat->descricao }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3 align-self-end d-flex">
                        <button type="button" id="btnPesquisar" class="btn btn-primary btn-sm"><i class="fa fa-search"></i> Pesquisar</button>
                    </div>

                </div>
            </div>
        </div>

        <!-- QUADRO DO GRID DE PROGRAMAS -->
        <div class="card card-outline card-primary">

            <!-- BOTÃO PARA CRIAR NOVO PROGRAMA -->
            @can('programa-de-pontos.store')
            <div class="card-header">
                <a href="javascript:void(0)" id="btnCriarPrograma" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Criar novo</a>
            </div>
            @endcan

            <!-- CORPO DO QUADRO DO GRID DE PROGRAMAS -->
            <div class="card-body">
                <div class="table-responsive">
                    <table id="gridtemplate" class="table table-striped table-bordered nowrap">
                        <thead>
                            <tr>
                                <th>Ações</th>
                                <th>Categoria do Cartão</th>
                                <th>Valor Gasto</th>
                                <th>Equivale a</th>
                                <th>Exclusivo para Cartão</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="modalCriarPrograma" tabindex="-1" role="dialog" aria-labelledby="modalCriarProgramaLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalCriarProgramaLabel">Programa de Pontos</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="formCriarPrograma">
                            <input type="hidden" id="prgpts_id" name="prgpts_id" value="">
                            <div class="form-group">
                                <label>Categoria do Cartão:</label>
                                <select class="form-control select2" id="card_categ_modal" name="card_categ_modal" data-placeholder="Selecione a Catgoria do Cartão" data-allow-clear="true" style="width: 100%;">
                                    <option></option>
                                    @foreach($card_categ as $cat)
                                        <option value="{{ $cat->categ }}">{{ $cat->descricao }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="prgpts_valor">Valor Gasto:</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">R$</span>
                                    </div>
                                    <input type="text" class="form-control  form-control-sm" id="prgpts_valor" name="prgpts_valor" placeholder="Digite o valor do programa" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="prgpts_eq">Equivale a:</label>
                                <input type="text" class="form-control  form-control-sm" id="prgpts_eq" name="prgpts_eq" placeholder="Digite o que equivale" required>
                            </div>
                            <div class="form-group form-check">
                                <input type="checkbox" class="form-check-input" id="prgpts_sc" name="prgpts_sc">
                                <label class="form-check-label" for="prgpts_sc">Exclusivo para Cartão</label>
                            </div>
                            <div class="form-group">
                                <label for="prgpts_sts">Status:</label>
                                <select class="form-control select2" id="prgpts_sts" name="prgpts_sts" data-placeholder="Selecione o Status" style="width: 100%;">
                                    <option></option>
                                    @foreach($prgpts_sts as $status)
                                        <option value="{{ $status->status }}">{{ $status->descricao }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secundary-multban btn-sm" data-dismiss="modal"><i class="icon fas fa-times"></i> Fechar</button>
                        <button type="button" class="btn btn-primary btn-sm" id="btnSalvarPrograma"><i class="icon fas fa-save"></i> Salvar</button>
                    </div>
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
    <script src="{{ asset('assets/dist/js/pages/Cartoes/gridcartoes.js') }}"></script>

    <script type="text/javascript">

        $(document).ready(function(){

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
