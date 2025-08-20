$(function () {

    "use strict";
    ns.comboBoxSelect("cliente_endpais", "/empresa/obter-pais", "pais");
    ns.comboBoxSelect("cliente_endest", "/empresa/obter-estado", "estado");
    ns.comboBoxSelect("cliente_endcid", "/empresa/obter-cidade", "cidade_ibge");
    ns.comboBoxSelect("emp_id", "/empresa/obter-empresas", "emp_id", "", "", "modalCriarCartao");


    function isNumeric(n) {
        return !isNaN(parseFloat(n)) && isFinite(n);
    }

    window.clientejs = ({
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

                formData.append("cliente_id", $("#cliente_id").val());
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

                            if (xhr.responseJSON.message_type) {
                                Swal.fire("Erro", xhr.responseJSON.message_type, "error");
                            } else {
                                Swal.fire("Erro", "Existem um ou mais campos obrigatórios não preenchidos.", "error");
                            }

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
                    // dom: 'rt<"bottom"iflp>',
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
                    searching: true,
                    ordering: true,
                    info: true, autoWidth: true,
                    scrollX: true,

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
                        url: url,
                        type: 'POST',
                        data: function (d) {
                            console.log(d);
                            const myDiv = document.getElementById(formId);
                            const inputElements = myDiv.querySelectorAll('input, select, textarea,file');
                            var token = $('meta[name="csrf-token"]').attr("content");
                            var formData = new FormData();
                            formData.append("emp_id", $("#empresa_id option:selected").val());
                            formData.append("cliente_id", $("#cliente_id").val());
                            formData.append("_token", token);
                            inputElements.forEach(input => {
                                // formData.append(input.name, input.value);
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
            if (colunaFixa) {
                resizeHandler($('#' + id));
            }

        },
    });

    var observer = window.ResizeObserver ? new ResizeObserver(function (entries) {
        entries.forEach(function (entry) {
            $(entry.target).DataTable().columns.adjust();
        });
    }) : null;

    // Function to add a datatable to the ResizeObserver entries array
    var resizeHandler = function ($table) {
        if (observer)
            observer.observe($table[0]);
    };

    $('body').on('click', '#btnCriarCartao', function () {
        $("#is_edit").val("0");

        var modal = $(this).data('modal');
        $('#modalCriarCartaoLabel').html('Criar Novo Cartão');

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

    $('body').on('click', '#btnPesquisarWf', function () {
        $('#gridtemplate-wf').DataTable().clear().destroy();
        clientejs.gridDataTable(colunasWf, [], true, false, "obtergridpesquisa-work-flow", "gridtemplate-wf", "tabs-work-flow");
    });

    $('body').on('click', '#btnSalvarCartao', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        var isEdit = $("#is_edit").val();
        var url = "/cliente/update-card";
        if (isEdit === "0") {
            url = "/cliente/store-card";
        }
        //formId, btnSubmit, btnPesquisar, URL, Modal
        clientejs.submitForm('formCriarCartao', this, 'btnPesquisarPdMsg', url, "modalCriarCartao");
    });

    $('body').on('click', '.btn-edit', function () {

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

    $('#cliente_rendam, #cliente_rdam_s').trigger('keyup');

    $("#btnPesquisarCep").on("click", function () {
        ns.cepOnClick("cliente_cep");
    });

    $("#btnPesquisarCepS").on("click", function () {
        ns.cepOnClickS("cliente_cep_s");
    });

    $('body').on('click', '#btnCriarCartao', function () {
        $('#modalCriarCartao').modal('show');
    });


    $("#btnSearchClient").on("click", function (e) {
        e.preventDefault();
        let data = $("#clicod").select2('data')[0];
        console.log(data);
        if (data.id) {
            $("#cliente_cdg").val(data.id);

            clearTimeout(timeOut);
            timeOut = setTimeout(() => {
                $("#btnCarregaCliente").trigger('click');
            }, 100);

        }
    });

    $("#btnCarregaCliente").on("click", function (e) {
        e.preventDefault();
        if (parseInt($("#cliente_cdg").val()) > 0) {
            var url = "/cliente/" + $("#cliente_cdg").val() + "/alterar"
            window.open(url, "_self");
        } else {
            Swal.fire(
                "Oops...",
                "Por favor, informe o Código do cliente..",
                "error"
            );
            return;
        }
    });

    $("#cliente_tipo").on("change", function () {
        changeClienteTipo();
    });

    $("body").on("change", "#cliente_endpais", function (e) {
        e.preventDefault();
        verificaPais();

    });


    var setClienteTipo = function () {
        var inserir = document.URL.split("/")[4] == "inserir";
        //console.log('inserir', inserir)
        if (inserir) {

            $('#cliente_tipo').select2("trigger", "select", {
                data: {
                    "id": 1,
                    "text": "Pessoa Jurídica"
                },
            });
        }

    }

    var searchClient = function () {
        var inserir = document.URL.split("/")[4] == "inserir";
        //console.log('inserir', inserir)
        if (inserir) {
            $("#serachClient").hide();
            return false;
        }
    }

    var verificaPais = function () {
        var visualizar = document.URL.split("/")[5] == "visualizar";

        if (visualizar) {
            return false;
        }

        let data = $("#cliente_endpais").select2('data')[0];
        let estado = $("#cliente_endest").select2('data')[0];
        let cidade = $("#cliente_endcid").select2('data')[0];
        if (!data) {
            $('#cliente_endcid').prop("disabled", true);
            $('#cliente_endest').prop("disabled", true);
            $('#cliente_endcid').val('');
            $('#cliente_endcid').trigger('change');
            $('#cliente_endest').val('');
            $('#cliente_endest').trigger('change');
        } else {

            if (!estado) {
                $('#cliente_endest').val('');
                $('#cliente_endest').trigger('change');
            }

            if (!cidade) {
                $('#cliente_endcid').val('');
                $('#cliente_endcid').trigger('change');
            }
            $('#cliente_endcid').prop("disabled", false);
            $('#cliente_endest').prop("disabled", false);
        }
    }

    $('body').on('click', '#btnExcluir', function (e) {
        e.preventDefault();
        Pace.restart();
        Pace.track(function () {
            $('#user_sts').val('EX');
            $('#user_sts').trigger('change');
            $('#btnSalvar').trigger('click');
            $("#btnExcluir").prop('disabled', true);
        });
    });

    $('body').on('click', '#btnInativar', function (e) {
        console.log($(this).text())
        var status = $(this).text().trim();

        e.preventDefault();
        Pace.restart();
        Pace.track(function () {
            if (status === 'Ativar') {
                $('#user_sts').val('AT');

                $("#btnInativar").text("Inativar");
                $("#btnInativar").prepend('<i class="fa fa-check"></i> ');
            } else if (status === 'Inativar') {
                $('#user_sts').val('IN');
                $("#btnInativar").text("Ativar");
                $("#btnInativar").prepend('<i class="fa fa-ban"></i> ');

            }
            $("#btnExcluir").prop('disabled', false);

            $('#user_sts').trigger('change');
            $('#btnSalvar').trigger('click');

        });
    });

    var changeClienteTipo = function () {
        var labelCnpj = $("#labelcliente_doc");
        var cliente_doc = $("#cliente_doc");
        var clientetipo = $("#cliente_tipo").val();
        console.log(clientetipo);
        //Cliente Física
        if (clientetipo == 1) {
            labelCnpj.html("CPF:*");
            cliente_doc.mask("999.999.999-99");
            cliente_doc.attr("placeholder", "Digite o CPF");
            cliente_doc.val("");
        } else {
            //Cliente Jurídica
            labelCnpj.html("CNPJ:*");
            cliente_doc.mask("99.999.999/9999-99");
            cliente_doc.attr("placeholder", "Digite o CNPJ");
            cliente_doc.val("");
        }
    };

    var clienteTipo = function () {
        var labelCnpj = $("#labelcliente_doc");
        var cliente_doc = $("#cliente_doc");
        var clientetipo = $("#cliente_tipo").val();

        //Cliente Física
        if (clientetipo == 1) {
            labelCnpj.html("CPF:*");
            cliente_doc.mask("999.999.999-99");
            cliente_doc.attr("placeholder", "Digite o CPF");
        } else {
            //Cliente Jurídica
            labelCnpj.html("CNPJ:*");
            cliente_doc.mask("99.999.999/9999-99");
            cliente_doc.attr("placeholder", "Digite o CNPJ");
        }
    };

    clienteTipo();
    searchClient();
    setClienteTipo();

    var alterar = document.URL.split("/")[5] == "alterar";
    var colunasConfiguracao = [
        { width: 120, targets: 0 },
        { width: "auto", targets: 1 },
        { width: "auto", targets: 2 },
        { width: "auto", targets: 3 }
    ];
    if (alterar) {
        clientejs.gridDataTable([
            {
                data: 'action',
                name: 'action',
                "width": "25%"
            },
            {
                data: 'empresa',
                name: 'empresa'
            },
            {
                data: 'cliente_cardn',
                name: 'cliente_cardn',
                autoWidth: true
            },
            {
                data: 'cliente_cardcv',
                name: 'cliente_cardcv'
            },
            {
                data: 'card_sts',
                name: 'card_sts'
            },
            {
                data: 'card_tp',
                name: 'card_tp',
                searchable: false
            },
            {
                data: 'card_mod',
                name: 'card_mod',
                searchable: false
            },
            {
                data: 'card_categ',
                name: 'card_categ',
                searchable: false
            },
            {
                data: 'card_desc',
                name: 'card_desc',
                searchable: false
            },
            {
                data: 'card_saldo_vlr',
                name: 'card_saldo_vlr',
                searchable: false
            },
            {
                data: 'card_limite',
                name: 'card_limite',
                searchable: false
            },
            {
                data: 'card_saldo_pts',
                name: 'card_saldo_pts',
                searchable: false
            },
        ], colunasConfiguracao, true, false, "/cliente/get-obter-grid-pesquisa-card", "gridtemplate-cards", "formPrincipal");


    }


    $("body").on("keyup change", "input[type='text']", function (e) {
        $(this).removeClass('is-invalid');
    });
});
