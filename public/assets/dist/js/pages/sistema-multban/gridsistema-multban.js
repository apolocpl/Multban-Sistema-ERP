$(document).ready(function () {
    $(function () {

        "use strict";

        bsCustomFileInput.init();

        function readURLImage(input, id) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $(id).attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#mini_logo").change(function () {
            readURLImage(this, '.brand-image');
        });
        $("#logo_h").change(function () {
            readURLImage(this, '.logo-multban');
        });

        //color picker with addon
        $('#fd_color, #fdsel_color,#ft_color,#ftsel_color,#bg_menu_ac_color,#menu_ac_color,#bg_item_menu_ac_color,#text_color_df').colorpicker();

        $('#text_color_df').on('colorpickerChange', function (event) {
            const root = document.documentElement;
            if (event.color) {
                root.style.setProperty('--text-color', event.color.toString());
                $('.text_color_df .fa-square').css('color', event.color.toString());
            }
        });

        $('#fd_color').on('colorpickerChange', function (event) {
            if (event.color) {
                $('.fd_color .fa-square').css('color', event.color.toString());
                const root = document.documentElement;
                root.style.setProperty('--primary-multban', event.color.toString());
            }

        });

        $('#fdsel_color').on('colorpickerChange', function (event) {
            if (event.color) {
                $('.fdsel_color .fa-square').css('color', event.color.toString());
                const root = document.documentElement;
                root.style.setProperty('--primary-hover-multban', event.color.toString());
            }
        });

        $('#ft_color').on('colorpickerChange', function (event) {
            if (event.color) {
                $('.ft_color .fa-square').css('color', event.color.toString());
                const root = document.documentElement;
                root.style.setProperty('--secondary-multban', event.color.toString());
            }
        });

        $('#ftsel_color').on('colorpickerChange', function (event) {
            if (event.color) {
                const root = document.documentElement;
                root.style.setProperty('--secondary-hover-multban', event.color.toString());
                root.style.setProperty('--secondary-bd-hover-multban', event.color.toString());
                $('.ftsel_color .fa-square').css('color', event.color.toString());
            }
        });

        $('#bg_menu_ac_color').on('colorpickerChange', function (event) {
            if (event.color) {
                const root = document.documentElement;
                root.style.setProperty('--bg-menu-active-multban', event.color.toString());
                $('.bg_menu_ac_color .fa-square').css('color', event.color.toString());
            }
        });

        $('#menu_ac_color').on('colorpickerChange', function (event) {
            if (event.color) {
                const root = document.documentElement;
                root.style.setProperty('--text-menu-active-multban', event.color.toString());
                $('.menu_ac_color .fa-square').css('color', event.color.toString());
            }
        });

        $('#bg_item_menu_ac_color').on('colorpickerChange', function (event) {
            if (event.color) {
                const root = document.documentElement;
                root.style.setProperty('--bg-item-menu-active-multban', event.color.toString());
                $('.bg_item_menu_ac_color .fa-square').css('color', event.color.toString());
            }
        });

        function isNumeric(n) {
            return !isNaN(parseFloat(n)) && isFinite(n);
        }

        window.gridmultmaisjs = ({
            submitForm: function (formId, btnSubmit, btnPesquisar, url, modal) {
                $(btnSubmit).text("Salvando...");
                $(btnSubmit).desabilitar();
                try {
                    $.ajaxSetup({
                        headers: {
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                        },
                    });

                    const myDiv = document.getElementById(formId);
                    const inputElements = myDiv.querySelectorAll('input, select, textarea, file');

                    var formData = new FormData();
                    var isEdit = $('#is_edit').val();
                    if (isEdit === "0") {
                        formData.append("emp_id", $("#empresa_id option:selected").val());
                    } else {
                        formData.append("emp_id", $(btnSubmit).data('emp-id'));
                    }
                        formData.append("t_name", $("#t_name").val());
                        formData.append("v_id", $("#v_id").val());
                        formData.append("tabela_bdm", $("#tabela_bdm option:selected").val());
                        inputElements.forEach(input => {

                            if (input.type === 'checkbox') {
                                formData.append(input.name, input.checked ? 'x' : '');
                            } else if (input.type === 'file') {
                                const file = input.files[0];
                                if (input.value.length > 0) {
                                    // Append the file to the FormData object
                                    formData.append(input.name, file, file.name);

                                }
                            }

                            else {
                                formData.append(input.name, !isNumeric(input.value.replace("%", "").replace(".", "").replace(",", ".")) ? input.value : $.tratarValor(input.value));
                            }

                        });

                        $.ajax({
                            type: "POST",
                            url: url,
                            data: formData,
                            cache: false,
                            dataType: "json",
                            contentType: false,
                            processData: false,
                            success: function (data) {
                                Swal.fire(data.title, data.text, data.type);
                                $(btnSubmit).html('<i class="icon fas fa-save"></i> Salvar');
                                $(btnSubmit).habilitar();
                                $(btnSubmit).attr('data-emp-id', '');
                                $("#" + btnPesquisar).trigger('click');
                                $("#" + modal).modal('hide');
                            },
                            error: function (xhr, status, error) {

                                if (xhr.status === 422) {
                                    let errors = xhr.responseJSON.message;
                                    Object.keys(errors).forEach(async function (key, value) {

                                        $("#" + key).addClass("is-invalid");

                                        $("#" + key)
                                            .closest(".form-group")
                                            .find(".select2-selection")
                                            .css("border-color", "#dc3545")
                                            .addClass("text-danger");
                                    });

                                    Swal.fire("Erro", "Existem um ou mais campos obrigatórios não preenchidos.", "error");
                                }

                                else if (XMLHttpRequest.status == 401) {
                                    Swal.fire({
                                        title: "Erro",
                                        text:
                                            "Sua sessão expirou, é preciso fazer o login novamente.",
                                        icon: "error",
                                        showCancelButton: false,
                                        allowOutsideClick: false,
                                    }).then(function (result) {
                                        $.limparBloqueioSairDaTela();
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire(xhr.responseJSON.title, xhr.responseJSON.text, xhr.responseJSON.type);
                                }

                                $(btnSubmit).html('<i class="icon fas fa-save"></i> Salvar');
                                $(btnSubmit).habilitar();

                            },
                        });
                    } catch (error) {
                        $(btnSubmit).html('<i class="icon fas fa-save"></i> Salvar');
                        $(btnSubmit).habilitar();
                        toastr.error(error);
                        console.error(error);
                    }

                },
                swalDelete: function (data, urlDelete) {
                    Swal.fire({
                        title: "Excluir?",
                        text: "Deseja realmente excluir?",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Sim, excluir!",
                        showLoaderOnConfirm: true,
                        preConfirm: function () {
                            return new Promise(function (resolve) {
                                var token = $('meta[name="csrf-token"]').attr(
                                    "content"
                                );
                                var url = "/" + urlDelete + "/" + data.emp_id;

                                $.ajax({
                                    header: {
                                        "X-CSRF-TOKEN": token,
                                    },
                                    url: url,
                                    type: "post",
                                    data: data,
                                })
                                    .done(function (response) {
                                        Swal.fire({
                                            title: response.title,
                                            text: response.text,
                                            icon: response.type,
                                            showCancelButton: false,
                                            allowOutsideClick: false,
                                        }).then(function (result) {

                                            console.log(result)
                                            if (response.type === "error") return;

                                            if (result.value) {
                                                $("#" + response.btnPesquisar).trigger('click');
                                            }
                                        });
                                    })
                                    .fail(function (xhr, status, error) {

                                        if (xhr.status === 401 || xhr.responseJSON.message === "CSRF token mismatch.") {
                                            Swal.fire({
                                                title: "Erro",
                                                text:
                                                    "Sua sessão expirou, é preciso fazer o login novamente.",
                                                icon: "error",
                                                showCancelButton: false,
                                                allowOutsideClick: false,
                                            }).then(function (result) {
                                                $.limparBloqueioSairDaTela();
                                                location.reload();
                                            });
                                        } else if (xhr.status == 400) {
                                            Swal.fire(
                                                "Oops...",
                                                xhr.responseJSON.message,
                                                "error"
                                            );
                                        } else if (xhr.status == 422) {
                                            Swal.fire(
                                                "Oops...",
                                                xhr.responseJSON.message,
                                                "error"
                                            );
                                        }
                                        else if (xhr.status == 406) {
                                            Swal.fire(
                                                "Oops...",
                                                xhr.responseJSON.message,
                                                "error"
                                            );
                                        } else {
                                            Swal.fire(
                                                "Oops...",
                                                "Algo deu errado ao tentar excluir!",
                                                "error"
                                            );
                                        }
                                    });
                            });
                        },
                        allowOutsideClick: false,
                    });
                },
                gridDataTable: function (
                    colunas,
                    colunasConfiguracao,
                    colunaFixa,
                    selectStyle,
                    url,
                    id,
                    formId
                ) {

                    var dataTable = $("#" + id)
                        .on("processing.dt", function (e, settings, processing) {
                            if (processing) {
                                Pace.stop();
                                Pace.start();
                            } else {
                                Pace.stop();
                            }
                        })
                        .DataTable({
                            dom: 'rt<"bottom"iflp>',
                            processing: true,
                            serverSide: false,
                            responsive: false,
                            paging: true,
                            lengthChange: true,
                            "pageLength": 100,
                            lengthMenu: [
                                [10, 50, 100, -1],
                                [10, 50, 100, 'Todos']
                            ],
                            searching: false,
                            ordering: true,
                            info: true,
                            autoWidth: false,
                            rowId: "id",
                            language: {
                                select: {
                                    rows: {
                                        _: "%d selecionados",
                                        0: "",
                                        1: "1 selecionado",
                                    },
                                },
                                sEmptyTable: "Nenhum registro encontrado",
                                sInfo:
                                    "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                                sInfoEmpty: "Mostrando 0 até 0 de 0 registros",
                                sInfoFiltered: "(Filtrados de _MAX_ registros)",
                                sInfoPostFix: "",
                                sInfoThousands: ".",
                                sLengthMenu: "_MENU_ resultados por página",
                                sLoadingRecords: "Carregando...",
                                sProcessing: "Processando...",
                                sZeroRecords: "Nenhum registro encontrado",
                                sSearch: "Pesquisar",
                                oPaginate: {
                                    sNext: "Próximo",
                                    sPrevious: "Anterior",
                                    sFirst: "Primeiro",
                                    sLast: "Último",
                                },
                                oAria: {
                                    sSortAscending:
                                        ": Ordenar colunas de forma ascendente",
                                    sSortDescending:
                                        ": Ordenar colunas de forma descendente",
                                },
                            },
                            ajax: {
                                url: '/config-sistema-multmais/' + url,
                                type: 'POST',
                                data: function (d) {
                                    const myDiv = document.getElementById(formId);
                                    const inputElements = myDiv.querySelectorAll('input, select, textarea,file');
                                    var token = $('meta[name="csrf-token"]').attr("content");
                                    var formData = new FormData();
                                    formData.append("emp_id", $("#empresa_id option:selected").val());
                                    formData.append("tabela_bdm", $("#tabela_bdm option:selected").val());
                                    formData.append("_token", token);
                                    inputElements.forEach(input => {
                                        formData.append(input.name, input.value);
                                    });

                                    // Append DataTables' parameters to your custom FormData
                                    // for (var key in d) {
                                    //     if (d.hasOwnProperty(key)) {
                                    //         formData.append(key, d[key]);
                                    //     }
                                    // }
                                    return formData;
                                },
                                processData: false, // Essential for FormData
                                contentType: false  // Essential for FormData
                            },
                            columns: colunas,
                            columnDefs: colunasConfiguracao,
                            fixedColumns: colunaFixa,
                            select: selectStyle,
                            order: [[1, "desc"]],
                        });

                    new $.fn.dataTable.FixedHeader(dataTable);

                },
            });

        $('body').on('click', '#btnCriarAlias, #btnCriarConexaoAPI, #btnCriarConexaoDB, #btnCriarMsg, #btnCriarPadroesPlano, #btnCriarWl, #btnCriarWorkFlow', function () {
            $("#is_edit").val("0");

            var modal = $(this).data('modal');
            $('#modalAliasLabel').html('Criar Novo Alias');
            $('#modalCriarConexaoAPILabel').html('Criar Novo Parâmetros de Conexão');
            $('#modalConexaoDBLabel').html('Criar Nova Conexão de banco de dados');
            $('#modalPadroesPlanoLabel').html('Criar Novo Padrão de Planos');
            $('#modalWlLabel').html('Criar White Label');
            $('#modalMensagemLabel').html('Criar Padrão de Mensagem');
            $('#modalWorkFlowLabel').html('Criar Work Flow');

            const myDiv = document.getElementById(modal);
            const inputElements = myDiv.querySelectorAll('input, select, textarea, file');

            inputElements.forEach(input => {
                $('#' + input.name).val("");
                $('#' + input.name).prop("checked", false);
                $('#' + input.name).trigger("change");
                $('#' + input.name).habilitar();
                $('#' + input.name).removeClass('is-invalid');
            });
        });

        //Initialize Select2 Elements TESTE
        $('.select2').select2();
        ns.comboBoxSelectTags("empresa_id", "/config-sistema-multmais/obter-empresas", "emp_id");
        ns.comboBoxSelectTags("tabela_filtro", "/config-sistema-multmais/obter-tabelas");

        $('body').on('click', '#btnPesquisarFbd', function () {
            $('#gridconexao').DataTable().clear().destroy();
            gridmultmaisjs.gridDataTable(colunasConexaoDB, colunasConfigConexaoDB, true, false, "obtergridpesquisa", "gridconexao", "tabs-conexao");
        });

        $('body').on('click', '#btnPesquisarAlias', function () {
            $('#gridtemplate-alias').DataTable().clear().destroy();
            gridmultmaisjs.gridDataTable(colunasAlias, colunasConfigAlias, true, false, "obtergridpesquisa-alias", "gridtemplate-alias", "tabs-alias");
        });

        $('body').on('click', '#btnPesquisarFapi', function () {
            $('#gridapis').DataTable().clear().destroy();
            gridmultmaisjs.gridDataTable(colunasApis, colunasConfigApis, true, false, "obtergridpesquisa-apis", "gridapis", "tabs-apis");
        });

        $('body').on('click', '#btnPesquisarTpPlano', function () {
            $('#gridtemplate-pdplan').DataTable().clear().destroy();
            gridmultmaisjs.gridDataTable(colunasTpPlan, colunasConfigTpPlan, true, false, "obtergridpesquisa-padroes-de-planos", "gridtemplate-pdplan", "tabs-padroes-planos");
        });

        $('body').on('click', '#btnPesquisarWl', function () {
            $('#gridtemplate-wl').DataTable().clear().destroy();
            gridmultmaisjs.gridDataTable(colunasWl, colunasConfigWl, true, false, "obtergridpesquisa-white-label", "gridtemplate-wl", "tabs-white-label-tab");
        });

        $('body').on('click', '#btnPesquisarPdMsg', function () {
            $('#gridtemplate-pdmsg').DataTable().clear().destroy();
            gridmultmaisjs.gridDataTable(colunasPdMsg, [], true, false, "obtergridpesquisa-padroes-de-mensagens", "gridtemplate-pdmsg", "tabs-padrao-msg");
        });

        $('body').on('click', '#btnPesquisarWf', function () {
            $('#gridtemplate-wf').DataTable().clear().destroy();
            gridmultmaisjs.gridDataTable(colunasWf, [], true, false, "obtergridpesquisa-work-flow", "gridtemplate-wf", "tabs-work-flow");
        });

        $('body').on('click', '#btnPesquisarTbdm', function () {

            $('#gridtemplate-dm').DataTable().clear().destroy();

            $('#header-dt').html('<th>Ação</th>');

            var dynamicColumns = [];

            dynamicColumns.push({
                data: 'action',
                title: 'action'
            });

            $.ajax({
                url: '/config-sistema-multmais/obtergridpesquisa-dados-mestre',
                method: 'POST',
                dataType: 'json',
                data: { _token: $('meta[name="csrf-token"]').attr("content"), get_columns: true, tabela_bdm: $("#tabela_bdm option:selected").val(), emp_id: $("#empresa_id option:selected").val() },
                success: function (response) {
                    //console.log(response)
                    response.columnDefinitions.forEach(function (colDef) {
                        $('#header-dt').append(`<th>${colDef}</th>`);
                        dynamicColumns.push({
                            data: colDef,
                            title: colDef
                        });
                    });

                    var dataTable = $("#gridtemplate-dm")
                        .on("processing.dt", function (e, settings, processing) {
                            if (processing) {
                                Pace.stop();
                                Pace.start();
                            } else {
                                Pace.stop();
                            }
                        })
                        .DataTable({
                            dom: 'rt<"bottom"iflp>',
                            data: response.dataTables.original.data,
                            processing: true,
                            serverSide: false,
                            responsive: false,
                            paging: true,
                            lengthChange: true,
                            "pageLength": 100,
                            lengthMenu: [
                                [10, 50, 100, -1],
                                [10, 50, 100, 'Todos']
                            ],
                            searching: false,
                            ordering: true,
                            info: true,
                            autoWidth: false,
                            rowId: "id",
                            language: {
                                select: {
                                    rows: {
                                        _: "%d selecionados",
                                        0: "",
                                        1: "1 selecionado",
                                    },
                                },
                                sEmptyTable: "Nenhum registro encontrado",
                                sInfo:
                                    "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                                sInfoEmpty: "Mostrando 0 até 0 de 0 registros",
                                sInfoFiltered: "(Filtrados de _MAX_ registros)",
                                sInfoPostFix: "",
                                sInfoThousands: ".",
                                sLengthMenu: "_MENU_ resultados por página",
                                sLoadingRecords: "Carregando...",
                                sProcessing: "Processando...",
                                sZeroRecords: "Nenhum registro encontrado",
                                sSearch: "Pesquisar",
                                oPaginate: {
                                    sNext: "Próximo",
                                    sPrevious: "Anterior",
                                    sFirst: "Primeiro",
                                    sLast: "Último",
                                },
                                oAria: {
                                    sSortAscending:
                                        ": Ordenar colunas de forma ascendente",
                                    sSortDescending:
                                        ": Ordenar colunas de forma descendente",
                                },
                            },
                            columns: dynamicColumns,
                            columnDefs: [],
                            fixedColumns: true,
                            //select: selectStyle,
                            order: [[1, "desc"]],
                        });

                    new $.fn.dataTable.FixedHeader(dataTable);

                    //gridmultmaisjs.gridDataTable(dynamicColumns, colunasConfigWl, true, false, "obtergridpesquisa-dados-mestre", "gridtemplate-dm", "tabs-dados-mestres-tab");

                },
                error: function (xhr, status, error) {
                    console.error("Error fetching data:", error);
                }
            });
        });


        $('body').on('click', '#btnCriarTbDm', function () {
            $("#is_edit").val("0");

            $('#modalTbDmLabel').html('Criar Novo Dado Mestre');
            $('#formTbDm .modal-body').html('');

            Pace.restart();
            Pace.track(function () {
                var token = $('meta[name="csrf-token"]').attr(
                    "content"
                );
                var url = "/config-sistema-multmais/create-dados-mestre";
                console.log(url)
                $.ajax({
                    header: {
                        "X-CSRF-TOKEN": token,
                    },
                    url: url,
                    type: "get",
                    data: { tabela_bdm: $('#tabela_bdm option:selected').val() },
                })
                    .done(function (response) {
                        console.log(response);

                        $('#formTbDm .modal-body').append(response.form);

                    })
                    .fail(function (xhr, status, error) {

                        if (xhr.status === 401 || xhr.responseJSON.message === "CSRF token mismatch.") {
                            Swal.fire({
                                title: "Erro",
                                text:
                                    "Sua sessão expirou, é preciso fazer o login novamente.",
                                icon: "error",
                                showCancelButton: false,
                                allowOutsideClick: false,
                            }).then(function (result) {
                                $.limparBloqueioSairDaTela();
                                location.reload();
                            });
                        } else if (xhr.status == 400) {
                            Swal.fire(
                                "Oops...",
                                xhr.responseJSON.message,
                                "error"
                            );
                        } else if (xhr.status == 422) {
                            Swal.fire(
                                "Oops...",
                                xhr.responseJSON.message,
                                "error"
                            );
                        }
                        else if (xhr.status == 406) {
                            Swal.fire(
                                "Oops...",
                                xhr.responseJSON.message,
                                "error"
                            );
                        } else {
                            Swal.fire(
                                "Oops...",
                                "Algo deu errado!",
                                "error"
                            );
                        }
                    });
            });

        });

        $(document).on('click', '.delete_id', function (e) {
            e.preventDefault();
            var token = $('meta[name="csrf-token"]').attr(
                "content"
            );
            var dados = {};

            dados.id = $(this).data('id');
            dados.name = $(this).data('name');
            dados.emp_id = $(this).data('emp-id');
            dados.tp_plano = $(this).data('tp-plano');
            dados.fornec = $(this).data('fornec');
            dados.grupo = $(this).data('grupo');
            dados.subgrp = $(this).data('subgrp');
            dados.tab_name = $(this).data('tab-name');
            dados.canal_id = $(this).data('canal-id');
            dados.tabela = $(this).data('tabela');
            dados.campo = $(this).data('campo');

            var url = $(this).data('url');

            dados._token = token;
            dados._method = "delete";

            gridmultmaisjs.swalDelete(dados, 'config-sistema-multmais/' + url);
        });

        $('#tabela').on('select2:select', function (e) {
            console.log('select event');
            var data = e.params.data;
            console.log(data);
            $("#campo").desabilitar();

            Pace.restart();
            Pace.track(function () {
                var token = $('meta[name="csrf-token"]').attr(
                    "content"
                );
                var url = "/config-sistema-multmais/get-columns-from-table/" + data.id;
                console.log(url)
                $.ajax({
                    header: {
                        "X-CSRF-TOKEN": token,
                    },
                    dataType: 'json',
                    url: url,
                    type: "get",
                    processData: false,
                    contentType: false,
                })
                    .done(function (response) {

                        console.log(response);
                        $("#campo").empty();
                        $('#campo').select2('destroy');
                        $("#campo").select2({
                            data: response
                        });

                        $("#campo").habilitar();
                    })
                    .fail(function (xhr, status, error) {
                        $("#campo").habilitar();

                        if (xhr.status === 401 || xhr.responseJSON.message === "CSRF token mismatch.") {
                            Swal.fire({
                                title: "Erro",
                                text:
                                    "Sua sessão expirou, é preciso fazer o login novamente.",
                                icon: "error",
                                showCancelButton: false,
                                allowOutsideClick: false,
                            }).then(function (result) {
                                $.limparBloqueioSairDaTela();
                                location.reload();
                            });
                        } else if (xhr.status == 400) {
                            Swal.fire(
                                "Oops...",
                                xhr.responseJSON.message,
                                "error"
                            );
                        } else if (xhr.status == 422) {
                            Swal.fire(
                                "Oops...",
                                xhr.responseJSON.message,
                                "error"
                            );
                        }
                        else if (xhr.status == 406) {
                            Swal.fire(
                                "Oops...",
                                xhr.responseJSON.message,
                                "error"
                            );
                        } else {
                            Swal.fire(
                                "Oops...",
                                "Algo deu errado!",
                                "error"
                            );
                        }
                    });
            });
        });

        $('body').on('click', '.btn-work-flow', function () {

            var tabela = $(this).data('tabela');
            var campo = $(this).data('campo');
            var emp_id = $(this).data('emp-id');
            $('#btnSalvarWl').attr('data-emp-id', emp_id);
            $('#is_edit').val("1");

            Pace.restart();
            Pace.track(function () {
                var token = $('meta[name="csrf-token"]').attr(
                    "content"
                );
                var url = "/config-sistema-multmais/edit-work-flow/" + emp_id;
                console.log(url)
                $.ajax({
                    header: {
                        "X-CSRF-TOKEN": token,
                    },
                    url: url,
                    type: "get",
                    data: { emp_id: emp_id, tabela: tabela, campo: campo },
                })
                    .done(function (response) {
                        $("#campo").empty();
                        $('#campo').select2('destroy');
                        const myDiv = document.getElementById('modalWorkFlow');
                        const inputElements = myDiv.querySelectorAll('input, select, textarea, file');

                        inputElements.forEach(input => {

                            $('#' + input.name).val(response.data[input.name]);

                            $('#' + input.name).trigger("change");
                            $('#' + input.name).trigger("keyup");
                            $('#' + input.name).removeClass('is-invalid');
                        });

                        $("#campo").select2({
                            data: response.columns
                        });

                        $("#emp_id").val(response.data.emp_id);

                        $('#modalWorkFlowLabel').html('Editar Work Flow');

                        $("#modalWorkFlow").modal('show');

                    })
                    .fail(function (xhr, status, error) {

                        if (xhr.status === 401 || xhr.responseJSON.message === "CSRF token mismatch.") {
                            Swal.fire({
                                title: "Erro",
                                text:
                                    "Sua sessão expirou, é preciso fazer o login novamente.",
                                icon: "error",
                                showCancelButton: false,
                                allowOutsideClick: false,
                            }).then(function (result) {
                                $.limparBloqueioSairDaTela();
                                location.reload();
                            });
                        } else if (xhr.status == 400) {
                            Swal.fire(
                                "Oops...",
                                xhr.responseJSON.message,
                                "error"
                            );
                        } else if (xhr.status == 422) {
                            Swal.fire(
                                "Oops...",
                                xhr.responseJSON.message,
                                "error"
                            );
                        }
                        else if (xhr.status == 406) {
                            Swal.fire(
                                "Oops...",
                                xhr.responseJSON.message,
                                "error"
                            );
                        } else {
                            Swal.fire(
                                "Oops...",
                                "Algo deu errado!",
                                "error"
                            );
                        }
                    });
            });

        });

        $('body').on('click', '.btn-padroes-de-mensagens', function () {

            var canal_id = $(this).data('canal-id');
            var msg_categ = $(this).data('msg-categ');
            var emp_id = $(this).data('emp-id');
            $('#btnSalvarMensagem').attr('data-emp-id', emp_id);
            $('#is_edit').val("1")
            $('#canal_id').desabilitar();
            $('#msg_categ').desabilitar();

            Pace.restart();
            Pace.track(function () {
                var token = $('meta[name="csrf-token"]').attr(
                    "content"
                );
                var url = "/config-sistema-multmais/edit-padroes-de-mensagens/" + emp_id;
                console.log(url)
                $.ajax({
                    header: {
                        "X-CSRF-TOKEN": token,
                    },
                    url: url,
                    type: "get",
                    data: { emp_id: emp_id, canal_id: canal_id, msg_categ: msg_categ },
                })
                    .done(function (response) {
                        console.log(response);
                        const myDiv = document.getElementById('modalMensagem');
                        const inputElements = myDiv.querySelectorAll('input, select, textarea,file');

                        inputElements.forEach(input => {

                            $('#' + input.name).val(response.data[input.name]);

                            $('#' + input.name).trigger("change");
                            $('#' + input.name).trigger("keyup");
                            $('#' + input.name).removeClass('is-invalid');
                        });

                        $("#emp_id").val(response.data.emp_id);

                        $('#modalMensagemLabel').html('Editar Padrão de Mensagem');

                        $("#modalMensagem").modal('show');

                    })
                    .fail(function (xhr, status, error) {

                        if (xhr.status === 401 || xhr.responseJSON.message === "CSRF token mismatch.") {
                            Swal.fire({
                                title: "Erro",
                                text:
                                    "Sua sessão expirou, é preciso fazer o login novamente.",
                                icon: "error",
                                showCancelButton: false,
                                allowOutsideClick: false,
                            }).then(function (result) {
                                $.limparBloqueioSairDaTela();
                                location.reload();
                            });
                        } else if (xhr.status == 400) {
                            Swal.fire(
                                "Oops...",
                                xhr.responseJSON.message,
                                "error"
                            );
                        } else if (xhr.status == 422) {
                            Swal.fire(
                                "Oops...",
                                xhr.responseJSON.message,
                                "error"
                            );
                        }
                        else if (xhr.status == 406) {
                            Swal.fire(
                                "Oops...",
                                xhr.responseJSON.message,
                                "error"
                            );
                        } else {
                            Swal.fire(
                                "Oops...",
                                "Algo deu errado!",
                                "error"
                            );
                        }
                    });
            });

        });

        $('body').on('click', '.btn-alias-db', function () {

            var tab_name = $(this).data('tab-name');
            var emp_id = $(this).data('emp-id');
            $('#btnSalvarAlias').attr('data-emp-id', emp_id);
            $('#is_edit').val("1")
            $('#emp_tab_name').desabilitar();

            Pace.restart();
            Pace.track(function () {
                var token = $('meta[name="csrf-token"]').attr(
                    "content"
                );
                var url = "/config-sistema-multmais/edit-alias/" + emp_id;
                console.log(url)
                $.ajax({
                    header: {
                        "X-CSRF-TOKEN": token,
                    },
                    url: url,
                    type: "get",
                    data: { emp_id: emp_id, tab_name: tab_name },
                })
                    .done(function (response) {
                        console.log(response);

                        $("#emp_tab_alias").val(response.data.emp_tab_alias);

                        $('#emp_tab_name').val(response.data.emp_tab_name);
                        $('#emp_tab_name').trigger('change');
                        $("#emp_id").val(response.data.emp_id);

                        $('#modalAlias').modal('show');
                        $('#modalAliasLabel').html('Editar Alias');

                    })
                    .fail(function (xhr, status, error) {

                        if (xhr.status === 401 || xhr.responseJSON.message === "CSRF token mismatch.") {
                            Swal.fire({
                                title: "Erro",
                                text:
                                    "Sua sessão expirou, é preciso fazer o login novamente.",
                                icon: "error",
                                showCancelButton: false,
                                allowOutsideClick: false,
                            }).then(function (result) {
                                $.limparBloqueioSairDaTela();
                                location.reload();
                            });
                        } else if (xhr.status == 400) {
                            Swal.fire(
                                "Oops...",
                                xhr.responseJSON.message,
                                "error"
                            );
                        } else if (xhr.status == 422) {
                            Swal.fire(
                                "Oops...",
                                xhr.responseJSON.message,
                                "error"
                            );
                        }
                        else if (xhr.status == 406) {
                            Swal.fire(
                                "Oops...",
                                xhr.responseJSON.message,
                                "error"
                            );
                        } else {
                            Swal.fire(
                                "Oops...",
                                "Algo deu errado!",
                                "error"
                            );
                        }
                    });
            });

        });

        $('body').on('click', '.btn-dados-mestre', function () {

            var id = $(this).data('id');
            var name = $(this).data('name');
            $('#is_edit').val("1");
            $('#t_name').val(name);
            $('#v_id').val(id);
            $('#formTbDm .modal-body').html('');
            Pace.restart();
            Pace.track(function () {
                var token = $('meta[name="csrf-token"]').attr(
                    "content"
                );
                var url = "/config-sistema-multmais/edit-dados-mestre";
                console.log(url)
                $.ajax({
                    header: {
                        "X-CSRF-TOKEN": token,
                    },
                    url: url,
                    type: "get",
                    data: { id: id, name: name },
                })
                    .done(function (response) {
                        console.log(response);

                        $('#formTbDm .modal-body').append(response.form);
                        $('#modalTbDm').modal('show');
                        $('#modalTbDmLabel').html('Editar TBDM');

                    })
                    .fail(function (xhr, status, error) {

                        if (xhr.status === 401 || xhr.responseJSON.message === "CSRF token mismatch.") {
                            Swal.fire({
                                title: "Erro",
                                text:
                                    "Sua sessão expirou, é preciso fazer o login novamente.",
                                icon: "error",
                                showCancelButton: false,
                                allowOutsideClick: false,
                            }).then(function (result) {
                                $.limparBloqueioSairDaTela();
                                location.reload();
                            });
                        } else if (xhr.status == 400) {
                            Swal.fire(
                                "Oops...",
                                xhr.responseJSON.message,
                                "error"
                            );
                        } else if (xhr.status == 422) {
                            Swal.fire(
                                "Oops...",
                                xhr.responseJSON.message,
                                "error"
                            );
                        }
                        else if (xhr.status == 406) {
                            Swal.fire(
                                "Oops...",
                                xhr.responseJSON.message,
                                "error"
                            );
                        } else {
                            Swal.fire(
                                "Oops...",
                                "Algo deu errado!",
                                "error"
                            );
                        }
                    });
            });

        });

        $('body').on('click', '.btn-apis-db', function () {

            var emp_id = $(this).data('emp-id');
            $('#btnSalvarAPI').attr('data-emp-id', emp_id);
            var fornec = $(this).data('fornec');
            var grupo = $(this).data('grupo');
            var subgrp = $(this).data('subgrp');
            $('#is_edit').val("1")
            $('#bc_fornec_api').desabilitar();
            $('#api_grupo_api').desabilitar();
            $('#api_subgrp_api').desabilitar();

            Pace.restart();
            Pace.track(function () {
                var token = $('meta[name="csrf-token"]').attr(
                    "content"
                );
                var url = "/config-sistema-multmais/edit-apis/" + emp_id;
                console.log(url)
                $.ajax({
                    header: {
                        "X-CSRF-TOKEN": token,
                    },
                    url: url,
                    type: "get",
                    data: { emp_id: emp_id, bc_fornec: fornec, api_grupo: grupo, api_subgrp: subgrp },
                })
                    .done(function (response) {
                        console.log(response);
                        $("#bc_fornec_api").val(response.data.bc_fornec);
                        $("#bc_fornec_api").trigger("change");
                        $("#api_grupo_api").val(response.data.api_grupo);
                        $("#api_grupo_api").trigger("change");
                        $("#api_subgrp_api").val(response.data.api_subgrp);
                        $("#api_subgrp_api").trigger("change");
                        $("#api_emp_endpoint").val(response.data.api_emp_endpoint);
                        $("#api_emp_mtdo").val(response.data.api_emp_mtdo);
                        $("#api_emp_mtdo").trigger('change');
                        $("#api_emp_token").val(response.data.api_emp_token);
                        $("#api_emp_tpde").val(response.data.api_emp_tpde);
                        $("#api_emp_tpda").val(response.data.api_emp_tpda);
                        $("#api_emp_user").val(response.data.api_emp_user);
                        $("#api_emp_pass").val(response.data.api_emp_pass);
                        $("#api_emp_key").val(response.data.api_emp_key);
                        $("#emp_id").val(response.data.emp_id);

                        $('#modalCriarConexaoAPILabel').html('Editar Parâmetros de Conexão');

                        $('#modalCriarConexaoAPI').modal('show');
                    })
                    .fail(function (xhr, status, error) {
                        if (xhr.status === 401 || xhr.responseJSON.message === "CSRF token mismatch.") {
                            Swal.fire({
                                title: "Erro",
                                text:
                                    "Sua sessão expirou, é preciso fazer o login novamente.",
                                icon: "error",
                                showCancelButton: false,
                                allowOutsideClick: false,
                            }).then(function (result) {
                                $.limparBloqueioSairDaTela();
                                location.reload();
                            });
                        } else if (xhr.status == 400) {
                            Swal.fire(
                                "Oops...",
                                xhr.responseJSON.message,
                                "error"
                            );
                        } else if (xhr.status == 422) {
                            Swal.fire(
                                "Oops...",
                                xhr.responseJSON.message,
                                "error"
                            );
                        }
                        else if (xhr.status == 406) {
                            Swal.fire(
                                "Oops...",
                                xhr.responseJSON.message,
                                "error"
                            );
                        } else {
                            Swal.fire(
                                "Oops...",
                                "Algo deu errado!",
                                "error"
                            );
                        }
                    });
            });

        });

        $('body').on('click', '.btn-conexao-db', function () {

            $('#is_edit').val("1");
            var emp_id = $(this).data('emp-id');
            $('#btnSalvarConexaoDB').attr('data-emp-id', emp_id);
            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            });
            Pace.restart();
            Pace.track(function () {

                var url = "/config-sistema-multmais/edit-conexoes-bc-emp/" + emp_id;
                console.log(url)
                $.ajax({
                    url: url,
                    type: "get",
                    data: { emp_id: emp_id },
                })
                    .done(function (response) {
                        console.log(response);
                        $("#bc_emp_ident").val(response.data.bc_emp_ident);
                        $("#bc_emp_host").val(response.data.bc_emp_host);
                        $("#bc_emp_porta").val(response.data.bc_emp_porta);
                        $("#bc_emp_nome").val(response.data.bc_emp_nome);
                        $("#bc_emp_user").val(response.data.bc_emp_user);
                        $("#bc_emp_pass").val(response.data.bc_emp_pass);
                        $("#bc_emp_token").val(response.data.bc_emp_token);
                        $("#bc_emp_sslce").val(response.data.bc_emp_sslce);
                        $("#bc_emp_sslmo").val(response.data.bc_emp_sslmo);
                        $("#bc_emp_sslky").val(response.data.bc_emp_sslky);
                        $("#bc_emp_sslca").val(response.data.bc_emp_sslca);
                        $("#bc_emp_toconex").val(response.data.bc_emp_toconex);
                        $("#bc_emp_tocons").val(response.data.bc_emp_tocons);
                        $("#bc_emp_pooling").val(response.data.bc_emp_pooling);
                        $("#bc_emp_charset").val(response.data.bc_emp_charset);
                        $("#bc_emp_tzone").val(response.data.bc_emp_tzone);
                        $("#bc_emp_appname").val(response.data.bc_emp_appname);
                        $("#bc_emp_keepalv").val(response.data.bc_emp_keepalv);
                        $("#bc_emp_compress").val(response.data.bc_emp_compress);
                        $("#bc_emp_readonly").val(response.data.bc_emp_readonly);
                        $("#bc_fornec").val(response.data.bc_fornec);
                        $("#bc_fornec").trigger("change");
                        $("#emp_id").val(response.data.emp_id);

                        $('#modalConexaoDBLabel').html('Editar Conexão de banco de dados');

                        $("#modalConexaoDB").modal('show');
                    })
                    .fail(function (xhr, status, error) {

                        if (xhr.status === 401 || xhr.responseJSON.message === "CSRF token mismatch.") {
                            Swal.fire({
                                title: "Erro",
                                text:
                                    "Sua sessão expirou, é preciso fazer o login novamente.",
                                icon: "error",
                                showCancelButton: false,
                                allowOutsideClick: false,
                            }).then(function (result) {
                                $.limparBloqueioSairDaTela();
                                location.reload();
                            });
                        } else if (xhr.status == 400) {
                            Swal.fire(
                                "Oops...",
                                xhr.responseJSON.message,
                                "error"
                            );
                        } else if (xhr.status == 422) {
                            Swal.fire(
                                "Oops...",
                                xhr.responseJSON.message,
                                "error"
                            );
                        }
                        else if (xhr.status == 406) {
                            Swal.fire(
                                "Oops...",
                                xhr.responseJSON.message,
                                "error"
                            );
                        } else {
                            Swal.fire(
                                "Oops...",
                                "Algo deu errado!",
                                "error"
                            );
                        }
                    });
            });

        });

        $('body').on('click', '.btn-tp-plano', function () {

            $('#is_edit').val("1");
            var emp_id = $(this).data('emp-id');
            $('#btnSalvarPadroesPlano').attr('data-emp-id', emp_id);
            var tp_plano = $(this).data('tp-plano');
            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            });
            Pace.restart();
            Pace.track(function () {

                var url = "/config-sistema-multmais/edit-padroes-de-planos/" + emp_id;
                console.log(url)
                $.ajax({
                    url: url,
                    type: "get",
                    data: { emp_id: emp_id, tp_plano: tp_plano },
                })
                    .done(function (response) {
                        console.log(response);
                        const myDiv = document.getElementById('modalPadroesPlano');
                        const inputElements = myDiv.querySelectorAll('input, select, textarea,file');

                        inputElements.forEach(input => {

                            if (isNumeric(response.data[input.name])) {
                                $('#' + input.name).val($.toMoneySimples(response.data[input.name]));
                            } else {
                                $('#' + input.name).val(response.data[input.name]);
                            }

                            if (response.data[input.name] === 'x') {
                                $('#' + input.name).prop("checked", true);
                            }

                            $('#' + input.name).trigger("change");
                            $('#' + input.name).trigger("keyup");
                            $('#' + input.name).habilitar();
                            $('#' + input.name).removeClass('is-invalid');
                        });

                        $("#emp_id").val(response.data.emp_id);

                        $('#modalPadroesPlanoLabel').html('Editar Padrão de Planos');

                        $("#modalPadroesPlano").modal('show');
                    })
                    .fail(function (xhr, status, error) {

                        if (xhr.status === 401 || xhr.responseJSON.message === "CSRF token mismatch.") {
                            Swal.fire({
                                title: "Erro",
                                text:
                                    "Sua sessão expirou, é preciso fazer o login novamente.",
                                icon: "error",
                                showCancelButton: false,
                                allowOutsideClick: false,
                            }).then(function (result) {
                                $.limparBloqueioSairDaTela();
                                location.reload();
                            });
                        } else if (xhr.status == 400) {
                            Swal.fire(
                                "Oops...",
                                xhr.responseJSON.message,
                                "error"
                            );
                        } else if (xhr.status == 422) {
                            Swal.fire(
                                "Oops...",
                                xhr.responseJSON.message,
                                "error"
                            );
                        }
                        else if (xhr.status == 406) {
                            Swal.fire(
                                "Oops...",
                                xhr.responseJSON.message,
                                "error"
                            );
                        } else {
                            Swal.fire(
                                "Oops...",
                                "Algo deu errado!",
                                "error"
                            );
                        }
                    });
            });

        });

        $('body').on('click', '.btn-white-label', function () {

            try {

                $('#is_edit').val("1");
                var emp_id = $(this).data('emp-id');
                $('#btnSalvarWl').attr('data-emp-id', emp_id);
                $.ajaxSetup({
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                    },
                });
                Pace.restart();
                Pace.track(function () {

                    var url = "/config-sistema-multmais/edit-white-label/" + emp_id;
                    console.log(url)
                    $.ajax({
                        url: url,
                        type: "get",
                        data: { emp_id: emp_id },
                    })
                        .done(function (response) {
                            console.log(response);
                            const myDiv = document.getElementById('modalWl');
                            const inputElements = myDiv.querySelectorAll('input, select, textarea, file');

                            inputElements.forEach(input => {
                                console.log(input.name, response.data[input.name])
                                if (input.type === "file") {
                                    $('#' + input.name + '_label').html(response.data[input.name]);
                                } else {
                                    $('#' + input.name).val(response.data[input.name]);
                                }

                                $('#' + input.name).trigger("change");
                                $('#' + input.name).trigger("keyup");
                                $('#' + input.name).habilitar();
                                $('#' + input.name).removeClass('is-invalid');
                            });

                            $("#emp_id").val(response.data.emp_id);

                            $('#modalWlLabel').html('Editar Padrão de Planos');

                            $("#modalWl").modal('show');
                        })
                        .fail(function (xhr, status, error) {

                            if (xhr.status === 401 || xhr.responseJSON.message === "CSRF token mismatch.") {
                                Swal.fire({
                                    title: "Erro",
                                    text:
                                        "Sua sessão expirou, é preciso fazer o login novamente.",
                                    icon: "error",
                                    showCancelButton: false,
                                    allowOutsideClick: false,
                                }).then(function (result) {
                                    $.limparBloqueioSairDaTela();
                                    location.reload();
                                });
                            } else if (xhr.status == 400) {
                                Swal.fire(
                                    "Oops...",
                                    xhr.responseJSON.message,
                                    "error"
                                );
                            } else if (xhr.status == 422) {
                                Swal.fire(
                                    "Oops...",
                                    xhr.responseJSON.message,
                                    "error"
                                );
                            }
                            else if (xhr.status == 406) {
                                Swal.fire(
                                    "Oops...",
                                    xhr.responseJSON.message,
                                    "error"
                                );
                            } else {
                                Swal.fire(
                                    "Oops...",
                                    "Algo deu errado!",
                                    "error"
                                );
                            }
                        });
                });
            } catch (error) {
                console.error(error)
            }

        });

        $('body').on('click', '#btnSalvarMensagem', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            var isEdit = $("#is_edit").val();
            var url = "/config-sistema-multmais/update-padroes-de-mensagens";
            if (isEdit === "0") {
                url = "/config-sistema-multmais/store-padroes-de-mensagens";
            }
            //formId, btnSubmit, btnPesquisar, URL, Modal
            gridmultmaisjs.submitForm('formMensagem', this, 'btnPesquisarPdMsg', url, "modalMensagem");
        });

        $('body').on('click', '#btnSalvarConexaoDB', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            var isEdit = $("#is_edit").val();
            var url = "/config-sistema-multmais/update-conexoes-bc-emp";
            if (isEdit === "0") {
                url = "/config-sistema-multmais/store-conexoes-bc-emp";
            }
            //formId, btnSubmit, btnPesquisar, URL, Modal
            gridmultmaisjs.submitForm('formConexaoDB', this, 'btnPesquisarFbd', url, "modalConexaoDB");
        });

        $('body').on('click', '#btnSalvarAlias', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            var isEdit = $("#is_edit").val();
            var url = "/config-sistema-multmais/update-alias";
            if (isEdit === "0") {
                url = "/config-sistema-multmais/store-alias";
            }
            //formId, btnSubmit, btnPesquisar, URL, Modal
            gridmultmaisjs.submitForm('formAlias', this, 'btnPesquisarAlias', url, "modalAlias");
        });

        $('body').on('click', '#btnSalvarAPI', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            var isEdit = $("#is_edit").val();
            var url = "/config-sistema-multmais/update-apis";
            if (isEdit == "0") {
                url = "/config-sistema-multmais/store-apis";
            }

            //formId, btnSubmit, btnPesquisar, URL, Modal
            gridmultmaisjs.submitForm('formAPI', this, 'btnPesquisarFapi', url, 'modalCriarConexaoAPI');
        });

        $('body').on('click', '#btnSalvarPadroesPlano', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            var isEdit = $("#is_edit").val();
            var url = "/config-sistema-multmais/update-padroes-de-planos";
            if (isEdit == "0") {
                url = "/config-sistema-multmais/store-padroes-de-planos";
            }

            //formId, btnSubmit, btnPesquisar, URL, Modal
            gridmultmaisjs.submitForm('formPdPlan', this, 'btnPesquisarTpPlano', url, 'modalPadroesPlano');

        });

        $('body').on('click', '#btnSalvarTbDm', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            var isEdit = $("#is_edit").val();
            var url = "/config-sistema-multmais/update-dados-mestre";
            if (isEdit == "0") {
                url = "/config-sistema-multmais/store-dados-mestre";
            }

            //formId, btnSubmit, btnPesquisar, URL, Modal
            gridmultmaisjs.submitForm('formTbDm', this, 'btnPesquisarTbdm', url, 'modalTbDm');

        });

        $('body').on('click', '#btnSalvarWl', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            var isEdit = $("#is_edit").val();
            var url = "/config-sistema-multmais/update-white-label";
            if (isEdit == "0") {
                url = "/config-sistema-multmais/store-white-label";
            }

            //formId, btnSubmit, btnPesquisar, URL, Modal
            gridmultmaisjs.submitForm('formWl', this, 'btnPesquisarWl', url, 'modalWl');

        });

        $('body').on('click', '#btnSalvarWorkFlow', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            var isEdit = $("#is_edit").val();
            var url = "/config-sistema-multmais/update-work-flow";
            if (isEdit == "0") {
                url = "/config-sistema-multmais/store-work-flow";
            }

            //formId, btnSubmit, btnPesquisar, URL, Modal
            gridmultmaisjs.submitForm('formWorkFlow', this, 'btnPesquisarWf', url, 'modalWorkFlow');

        });

        var colunasConexaoDB = [
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            },
            {
                data: 'fornecedor',
                name: 'fornecedor',
                autoWidth: true
            },
            {
                data: 'empresa',
                name: 'empresa',
                autoWidth: true
            },
            {
                data: 'empresa_sts',
                name: 'empresa_sts'
            }
        ];

        var colunasConfigConexaoDB = [{
            width: "auto",
            targets: 0
        },
        {
            width: "auto",
            targets: 1
        },
        {
            width: "auto",
            targets: 2
        },
        {
            width: "auto",
            targets: 3
        }
        ];

        var colunasAlias = [
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            },
            {
                data: 'emp_tab_name',
                name: 'emp_tab_name',
                autoWidth: true
            },
            {
                data: 'emp_tab_alias',
                name: 'emp_tab_alias',
                autoWidth: true
            }
        ];

        var colunasConfigAlias = [{
            width: "auto",
            targets: 0
        },
        {
            width: "auto",
            targets: 1
        },
        {
            width: "auto",
            targets: 2
        }
        ];

        var colunasApis = [
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            },
            {
                data: 'bc_fornec',
                name: 'bc_fornec',
                autoWidth: true
            },
            {
                data: 'api_grupo',
                name: 'api_grupo',
                autoWidth: true
            },
            {
                data: 'api_subgrp',
                name: 'api_subgrp',
                autoWidth: true
            }
        ];

        var colunasConfigApis = [{
            width: "auto",
            targets: 0
        },
        {
            width: "auto",
            targets: 1
        },
        {
            width: "auto",
            targets: 2
        }
        ];

        var colunasTpPlan = [
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            },
            {
                data: 'tp_plano',
                name: 'tp_plano',
                autoWidth: true
            }
        ];

        var colunasConfigTpPlan = [{
            width: "auto",
            targets: 0
        },
        {
            width: "auto",
            targets: 1
        }
        ];

        var colunasWl = [
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            },
            {
                data: 'empresa',
                name: 'empresa',
                autoWidth: true
            }
        ];

        var colunasConfigWl = [{
            width: "auto",
            targets: 0
        },
        {
            width: "auto",
            targets: 1
        }
        ];

        var colunasPdMsg = [
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            },
            {
                data: 'canal',
                name: 'canal',
                autoWidth: true
            },
            {
                data: 'categoria',
                name: 'categoria',
                autoWidth: true
            }
        ];

        var colunasWf = [
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            },
            {
                data: 'tabela',
                name: 'tabela',
                autoWidth: true
            },
            {
                data: 'campo',
                name: 'campo',
                autoWidth: true
            },
            {
                data: 'user',
                name: 'user',
                autoWidth: true
            },
            {
                data: 'empresa',
                name: 'empresa',
                autoWidth: true
            }
        ];
    });
});
