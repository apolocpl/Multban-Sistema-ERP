/*!
 * MtSoft v1.0.0 (https://MtSoft.io)
 * Copyright 2012-2025 MtSoft <https://mtsoft.com.br>
 */
var userid = $('meta[name="userid"]').attr("content");
var useremail = $('meta[name="useremail"]').attr("content");
var websocket = $('meta[name="websocket"]').attr("content");
var logged = $('meta[name="logged"]').attr("content") === "1";
var type = $('meta[name="type"]').attr("content");
var imagem = $('meta[name="imagem"]').attr("content");
var socket = null;
var namespace;
var pedido_item = new Array();
var columns_grade = new Array();
var rede_item = new Array();
var adddataform = null;
var listaDatatableXML = new Array();
var listaDatatableVendas = new Array();
var listaDatatableRelation = new Array();
var listaDatatableErrors = new Array();
var selectedItensGrid = new Array();
var chaveID;
var timeOut;
var clicodDB;
// socket.emit("init", {
//     type: type,
//     logged: logged,
//     userid: userid,
//     useremail: useremail,
//     imagem: imagem,
// });

if (websocket != null) {
    socket = io(websocket)
    connect()
}

function connect() {
    socket.on("connect_error", function (msg) {
        console.log(msg)
        toastr.error("Conection lost...");
    });

    socket.on("users online", function (count) {
        $(".navbar-badge").text(count);
    });

    socket.on("notify", function (type, message) {
        notify(type, message);
    });
}


function notify(type, message) {
    if (type === "error") {
        toastr.error(message);
    } else if (type === "success") {
        toastr.success(message);
    } else if (type === "warning") {
        toastr.warning(message);
    }
}



(function ($) {
    "use strict";

    var emp_or_cli = document.URL.split("/")[3];

    var rua;
    var bairro;
    var cidade;
    var estado;
    var pais;

        console.log('emp_or_cli', emp_or_cli);
    switch (emp_or_cli) {
        case 'cliente':
            rua = $("input[name='cliente_end']");
            bairro = $("input[name='cliente_endbair']");
            cidade = $("#cliente_endcid");
            estado = $("#cliente_endest");
            pais = $("#cliente_endpais");
            break;
        case 'empresa':
            rua = $("input[name='emp_end']");
            bairro = $("input[name='emp_endbair']");
            cidade = $("#emp_endcid");
            estado = $("#emp_endest");
            pais = $("#emp_endpais");
            break;

        default:
            break;
    }

    namespace = {
        setarPaisEstadoCidade: function (codigoIBGE) {
            var url = "/empresa/cidade-estado-pais";
            var parametro = { parametro: codigoIBGE };
            $.get(url, parametro, function (item) {
                console.log('item', item)
                pais.select2("trigger", "select", {
                    data: item.pais,
                });
                estado.select2("trigger", "select", {
                    data: item.estado,
                });
                cidade.select2("trigger", "select", {
                    data: item.cidade,
                });

                $("#emp_endnum").focus();
            });
        },
        comboBoxSelectTags: function (id, url, parametros, modal = null, campo = "") {

            var elemento = $("#" + id);
            elemento.select2({
                dropdownParent: modal ? $('#' + modal) : $('body'),
                tags: true,
                allowClear: true,
                ajax: {
                    url: url,
                    delay: 500,
                    type: "GET",
                    data: function (params) {
                        return $.extend(
                            {
                                parametro: params.term,
                                campo: campo,
                            },
                            $.isFunction(parametros) ? parametros() : {}
                        );
                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        if (XMLHttpRequest.status == 401) {
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
                        }
                    },
                    processResults: function (data, params) {
                        return {
                            results: $.map(data, function (item) {
                                return item; // { id: item.id, text: item.text, adicional : item.adicional };
                            }),
                        };
                    },
                    cache: true,
                },
                escapeMarkup: function (markup) {
                    return markup;
                },
                minimumInputLength: 2,
            });
        },
        comboBoxSelect: function (id, url, ident = 'id', pad = 5, parametros, modal = null) {
            var elemento = $("#" + id);
            elemento.select2({
                dropdownParent: modal ? $('#' + modal) : $('body'),
                allowClear: true,
                ajax: {
                    url: url,
                    delay: 500,
                    type: "GET",
                    data: function (params) {
                        return $.extend(
                            {
                                idempresa: $("#idempresa").val(),
                                parametro: params.term,
                                pais: $("#emp_endpais").val(),
                                estado: $("#emp_endest").val(),
                            },
                            $.isFunction(parametros) ? parametros() : {}
                        );
                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        if (XMLHttpRequest.status == 401) {
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
                        }
                    },
                    processResults: function (data, params) {
                        return {
                            results: $.map(data, function (item) {
                                //console.log('item',item[ident])
                                //item.id = item.id.toString().padStart(5, '0') + ' - ' + item.text
                                if (Number.isInteger(item[ident]))
                                    item[ident] = item[ident].toString().padStart(pad, '0')
                                item.text = item[ident] + ' - ' + item.text
                                return item;
                            }),
                        };
                    },
                    cache: true,
                },
                escapeMarkup: function (markup) {
                    return markup;
                },
                minimumInputLength: 2,
            });
        },
        scrollNav: function () {
            $(window).scrollTop(0);
            $(window).scroll(function () {
                var posicao = $(this).scrollTop();
                var nav = $(".nav-opcoes");
                var rowtop = $("#row-top");

                if (posicao >= 126) {
                    //nav.fadeOut('fast')
                    nav.css({ top: "0px", position: "fixed" }).fadeIn('slow');
                    rowtop.css({ "margin-top": "60px" }).fadeIn('slow');
                }

                if (posicao < 126) {
                    nav.removeAttr("style");
                    rowtop.removeAttr("style");
                }

            });
        },
        gridDataTable: function (
            colunas,
            colunasConfiguracao,
            colunaFixa,
            selectStyle,
            url,
            valores,
            formId
        ) {
            {
                var dataTable = $("#gridtemplate")
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
                        "footerCallback": function (row, data, start, end, display) {
                            if (valores.totaliza) {
                                var api = this.api(), data;

                                // converting to interger to find total
                                var intVal = function (i) {
                                    return typeof i === 'string' ?
                                        i.replace(/[\R$,]/g, '') * 1 :
                                        typeof i === 'number' ?
                                            i : 0;
                                };

                                if (valores.qtd > 0) {
                                    // computing column Total of the complete result
                                    var qtdTotal = api
                                        .column(valores.qtd)
                                        .data()
                                        .reduce(function (a, b) {
                                            return intVal(a) + intVal(b);
                                        }, 0);
                                }

                                if (valores.vlr > 0) {
                                    var valorTotal = api
                                        .column(valores.vlr)
                                        .data()
                                        .reduce(function (a, b) {
                                            return intVal(a) + intVal(b);
                                        }, 0);
                                }
                                // Update footer by showing the total with the reference of the column index
                                $(api.column(0).footer()).html('Total: ');
                                $(api.column(valores.qtd).footer()).html(qtdTotal);
                                $(api.column(valores.vlr).footer()).html($.toMoney(valorTotal));
                            }
                        },
                        ajax: {
                            url: "/" + url + "/obtergridpesquisa",
                            type: 'POST',
                            data: function (d) {
                                const myDiv = document.getElementById(formId);
                                const inputElements = myDiv.querySelectorAll('input, select, textarea, file');
                                var token = $('meta[name="csrf-token"]').attr("content");
                                var formData = new FormData();
                                formData.append("_token", token);
                                inputElements.forEach(input => {
                                    console.log(input.name, input.value);
                                    formData.append(input.name, input.value);
                                });
                                return formData;
                            },
                            contentType: false,
                            processData: false,
                            // data: {
                            //     //Empresa
                            //     cod_franqueadora: $("#cod_franqueadora option:selected").val(),
                            //     empresa_id: $("#empresa_id option:selected").val(),
                            //     empresa_cnpj: $("#empresa_cnpj").val(),
                            //     nome_fantasia: $("#nome_fantasia option:selected").val(),
                            //     //Cliente
                            //     nome_cliente: $("#nome_cliente").val(),
                            //     cpf_cnpj: $("#cpf_cnpj").val(),
                            //     status: $("#status option:selected").val(),
                            //     tipocliente: $("#tipocliente option:selected").val(),
                            // },
                            error: function (XMLHttpRequest, textStatus, errorThrown) {
                                if (XMLHttpRequest.status == 401) {
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
                                }
                            },
                        },
                        columns: colunas,
                        columnDefs: colunasConfiguracao,
                        fixedColumns: colunaFixa,
                        select: selectStyle,
                        order: [[1, "desc"]],
                    });
                new $.fn.dataTable.FixedHeader(dataTable);
            }
        },
        swalDelete: function (id, urlDelete) {
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
                        var url = "/" + urlDelete + "/" + id;
                        console.log(url)
                        $.ajax({
                            header: {
                                "X-CSRF-TOKEN": token,
                            },
                            url: url,
                            type: "post",
                            data: { id: id, _method: "delete", _token: token },
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
                                        $("#btnPesquisar").trigger('click');
                                    }
                                });
                            })
                            .fail(function (xhr, status, error) {
                                console.log('xhr status:', xhr.status);
                                console.log('error:', error);
                                console.log('status:', status);

                                if (xhr.status == 403) {
                                    Swal.fire(
                                        "Oops...",
                                        "Você não tem permissão para excluir, contate o administrador!",
                                        "error"
                                    );
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
        swalActive: function (id, urlActive) {
            Swal.fire({
                title: "Ativar?",
                text: "Deseja realmente ativar?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sim, ativar!",
                showLoaderOnConfirm: true,
                preConfirm: function () {
                    return new Promise(function (resolve) {
                        var token = $('meta[name="csrf-token"]').attr(
                            "content"
                        );
                        var url = "/" + urlActive + "/active/" + id;
                        console.log(url)
                        $.ajax({
                            header: {
                                "X-CSRF-TOKEN": token,
                            },
                            url: url,
                            type: "post",
                            data: { id: id, _method: "post", _token: token },
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
                                        $("#btnPesquisar").trigger('click');
                                    }
                                });
                            })
                            .fail(function (xhr, status, error) {
                                console.log('xhr status:', xhr.status);
                                console.log('error:', error);
                                console.log('status:', status);

                                if (xhr.status == 403) {
                                    Swal.fire(
                                        "Oops...",
                                        "Você não tem permissão para ativar, contate o administrador!",
                                        "error"
                                    );
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
                                        "Algo deu errado ao tentar ativar!",
                                        "error"
                                    );
                                }
                            });
                    });
                },
                allowOutsideClick: false,
            });
        },
        swalInactive: function (id, urlInactive) {
            Swal.fire({
                title: "Inativar?",
                text: "Deseja realmente inativar?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sim, inativar!",
                showLoaderOnConfirm: true,
                preConfirm: function () {
                    return new Promise(function (resolve) {
                        var token = $('meta[name="csrf-token"]').attr(
                            "content"
                        );
                        var url = "/" + urlInactive + "/inactive/" + id;
                        console.log(url)
                        $.ajax({
                            header: {
                                "X-CSRF-TOKEN": token,
                            },
                            url: url,
                            type: "post",
                            data: { id: id, _method: "post", _token: token },
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
                                        $("#btnPesquisar").trigger('click');
                                    }
                                });
                            })
                            .fail(function (xhr, status, error) {
                                console.log('xhr status:', xhr.status);
                                console.log('error:', error);
                                console.log('status:', status);

                                if (xhr.status == 403) {
                                    Swal.fire(
                                        "Oops...",
                                        "Você não tem permissão para inativar, contate o administrador!",
                                        "error"
                                    );
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
                                        "Algo deu errado ao tentar inativar!",
                                        "error"
                                    );
                                }
                            });
                    });
                },
                allowOutsideClick: false,
            });
        },
        getCNPJOnClick: function (id) {
            try {
                var cnpj = $("#" + id).val().replace(/\D/g, "");
                console.log('cnpj', cnpj)
                if (cnpj != "") {
                    var validacnpj = /^[0-9]{14}$/;

                    console.log('validacnpj', validacnpj)
                    if (validacnpj.test(cnpj)) {
                        $.loading();
                        var url = "//receitaws.com.br/v1/cnpj/" + cnpj;
                        const settings = {
                            async: true,
                            crossDomain: true,
                            url: '//receitaws.com.br/v1/cnpj/' + cnpj,
                            method: 'GET',
                            headers: {
                                Accept: 'application/json'
                            }
                        };

                        $.ajax(settings).done(function (response) {
                            console.log(response);
                        });

                    } else {
                        limpa_formulario_cep();
                        Swal.fire(
                            "Oops...",
                            "Formato de CEP inválido.",
                            "error"
                        );
                    }
                } else {
                    Swal.fire(
                        "Oops...",
                        "O CNPJ não pode ficar em branco.",
                        "error"
                    );
                }
            } catch (error) {
                $.removeLoading();
                console.log(error); // Logs the error
            }
        },
        cepOnClick: function (id) {
            var cep = $("#" + id).val().replace(/\D/g, "");
            console.log('cep', cep)
            if (cep != "") {
                var validacep = /^[0-9]{8}$/;

                if (validacep.test(cep)) {
                    rua.val("...");
                    bairro.val("...");
                    cidade.val("...");

                    Pace.restart();
                    Pace.track(function () {
                        var url = "//viacep.com.br/ws/" + cep + "/json/?callback=?";
                        $.getJSON(url, function (dados) {
                            if (!("erro" in dados)) {
                                rua.val(dados.logradouro);
                                rua.trigger('change');
                                bairro.val(dados.bairro);
                                bairro.trigger('change');
                                ns.setarPaisEstadoCidade(dados.ibge);
                            } else {
                                limpa_formulario_cep();
                                Swal.fire(
                                    "Oops...",
                                    "CEP não encontrado.",
                                    "error"
                                );

                            }
                        });
                    });

                } else {
                    limpa_formulario_cep();
                    Swal.fire(
                        "Oops...",
                        "Formato de CEP inválido.",
                        "error"
                    );
                }
            } else limpa_formulario_cep();
        },
        cepOnClickS: function (id) {
            rua = $("input[name='cliente_end_s']");
            bairro = $("input[name='cliente_endbair_s']");
            cidade = $("#cliente_endcid_s");
            estado = $("#cliente_endest_s");
            pais = $("#cliente_endpais_s");
            var cep = $("#" + id).val().replace(/\D/g, "");
            console.log('cep', cep)
            if (cep != "") {
                var validacep = /^[0-9]{8}$/;

                if (validacep.test(cep)) {
                    rua.val("...");
                    bairro.val("...");
                    cidade.val("...");

                    Pace.restart();
                    Pace.track(function () {
                        var url = "//viacep.com.br/ws/" + cep + "/json/?callback=?";
                        $.getJSON(url, function (dados) {
                            if (!("erro" in dados)) {
                                rua.val(dados.logradouro);
                                bairro.val(dados.bairro);
                                ns.setarPaisEstadoCidade(dados.ibge);
                            } else {
                                limpa_formulario_cep();
                                Swal.fire(
                                    "Oops...",
                                    "CEP não encontrado.",
                                    "error"
                                );

                            }
                        });
                    });

                } else {
                    limpa_formulario_cep();
                    Swal.fire(
                        "Oops...",
                        "Formato de CEP inválido.",
                        "error"
                    );
                }
            } else limpa_formulario_cep();
        },
        visualizarUpdate: function () {
            var visualizar = document.URL.split("/")[5] == "visualizar";

            if (visualizar) {
                $("#btnSalvar").attr("disabled", "disabled");
                $("form :input").prop("disabled", true);
                setTimeout(() => {
                    $("#deleteAll").prop("disabled", true);
                    $(".DeleteItem").prop("disabled", true);
                    $("#btnCancelar").prop("disabled", false);
                }, 250);
                $("#formPrincipal").attr("action", "#");
                $("#formPrincipal").attr("method", "");
                window.onbeforeunload = function () {
                    return null;
                };
                setTimeout(() => {
                    $.limparBloqueioSairDaTela();

                }, 850);
            }
        },
        iniciarlizarMascaras: function () {
            $(".date").mask("00/00/0000");
            $(".cep").mask("00000-000");
            $(".phone").mask("0000-0000");
            $(".placa").mask("AAA-0000");
            $(".date_time").mask("00/00/0000 00:00:00");
            $(".cell_with_ddd").mask("(00) 00000-0000");
            $(".phone_with_ddd").mask("(00) 0000-0000");
            $(".cpf").mask("000.000.000-00", { reverse: true });
            $(".cnpj").mask("00.000.000/0000-00", { reverse: true });
            $(".ie").mask("000.000.000.000");
            //$(".money").mask("#.##0,00", { reverse: true });
            //$(".trescasasdecimais").mask("#.##0,000", { reverse: true });
            //$(".quatrodecimais").mask("#.####0,0000", { reverse: true });
            // $(".porcentagem").mask("##0,00%", { reverse: true });
            //$(".porcentagem").formatCurrency($.extend({ colorize: false, roundToDecimalPlace: 2, negativeFormat: '-%s%n' }, $.formatCurrency.regions['pt-BR-PER']));

            //$(".money").formatCurrency($.extend({ colorize: false, roundToDecimalPlace: 2, negativeFormat: '-%s%n' }, $.formatCurrency.regions['pt-BR']));
            $(".trescasasdecimais").formatCurrency($.extend({ colorize: false, roundToDecimalPlace: 3, negativeFormat: '-%s%n' }, $.formatCurrency.regions['pt-BR-QTD']));
            $(".quatrodecimais").formatCurrency($.extend({ colorize: false, roundToDecimalPlace: 4, negativeFormat: '-%s%n' }, $.formatCurrency.regions['pt-BR-QTD']));
        },
        inicializarEventos: function () {
            // $("#formPrincipal").validate();


            // $('body').css({
            //     'zoom': '80%',
            //     'transform-origin': 'bottom center' // Ensures scaling originates from the top-left corner
            // });
        },
        selectAll: function (e) {
            e.preventDefault();
            clearTimeout(timeOut);
            timeOut = setTimeout(function () {

                $.each(selectedItensGrid, function (key, value) {
                    console.log('selectAll selectedItensGrid', value);
                    if (value.selected) {
                        $("#dt-checkboxes-grid-" + value.id).trigger("click");
                    }
                });

            }, 250);

        }
    };



    var obterIdsSelecionadoGridItens = function () {
        var lista_de_pedidos = $("#gridtemplate").DataTable();
        var idArray = lista_de_pedidos.rows({ selected: true }).data().toArray();
        //console.log('idArray', idArray)

        return idArray;
    };

    $('body').on('click', '#delete_grid_id', function (e) {
        var id = $(this).data('id');
        var url = $(this).data('url');
        ns.swalDelete(id, url);
        e.preventDefault();
    });

    $('body').on('click', '#active_grid_id', function (e) {
        var id = $(this).data('id');
        var url = $(this).data('url');
        ns.swalActive(id, url);
        e.preventDefault();
    });

    $('body').on('click', '#inactive_grid_id', function (e) {
        var id = $(this).data('id');
        var url = $(this).data('url');
        ns.swalInactive(id, url);
        e.preventDefault();
    });

    $('body').on("keypress", ".money, .porcentagem, .trescasasdecimais, .quatrodecimais", function (e) {
        switch (e.key) {
            case "1":
            case "2":
            case "3":
            case "4":
            case "5":
            case "6":
            case "7":
            case "8":
            case "9":
            case "0":
            case "Backspace":
            case "Enter":
                return true;
                break;
            case ",":
                if ($(this).val().indexOf(",") == -1) {
                    return true;
                }
                else {
                    return false;
                }
                break;

            case ".":
                if ($(this).val().indexOf(".") == -1) {
                    return true;
                }
                else {
                    return false;
                }
                break;

            default:
                return false;
        }
    });

    $("body").on("keypress change", "input[type='number']", function (e) {
        return /^-?[0-9]*$/.test(this.value + e.key);
    });

    function formataDecimalCurrency(i) {
        var v = i.value.replace(/\D/g, '');
        v = (v / 100).toFixed(2) + '';
        v = v.replace(".", ",");
        v = v.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
        i.value = v;
    }


    function formataDecimalPercent(i) {
        var v = i.value.replace(/\D/g, '');
        v = (v / 100).toFixed(2) + '%';
        v = v.replace(".", ",");
        v = v.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
        i.value = v;
    }

    function validateEmail(email) {
        var emailReg = /^[\w+.]+@\w+\.\w{2,}(?:\.\w{2})?$/i;
        if (emailReg.test(email) || email === '') {
            return true;
        } else {
            return false;
        }
    }

    $('body').on("blur", ".cell_with_ddd", function (event) {
        var id = $(this).attr('id');

        var phone = $(this).val().replace(/[\s~`!@#$%^&*()_+\-={[}\]|\\:;"'<,>.?/]+/g, '');

        if (phone.toString().length > 0 && phone.toString().length < 11) {

            $("#" + id).addClass("is-invalid");
            $("#" + id + "Error").html("Celular inválido.");
        }

    });

    $('body').on("blur", "input[type='email']", function (event) {
        var id = $(this).attr('id');
        if (!validateEmail($(this).val())) {
            $("#" + id).addClass("is-invalid");
            $("#" + id + "Error").html("Email inválido.");
        }
    });

    $('body').on("keyup", ".money", function (event) {
        formataDecimalCurrency(this);
    });

    $('body').on("keyup", ".porcentagem", function (event) {
        formataDecimalPercent(this);
    });

    $('body').on("blur", ".trescasasdecimais", function (event) {
        var valor = $(this).val().replace(".", "").replace(",", ".");

        if (isNaN(valor)) {
            $(this).val("0,000")
        }
        $(this).formatCurrency($.extend({ colorize: false, roundToDecimalPlace: 3, negativeFormat: '-%s%n' }, $.formatCurrency.regions['pt-BR-QTD']));
    });

    $('body').on("blur", ".quatrodecimais", function (event) {
        var valor = $(this).val().replace(".", "").replace(",", ".");

        if (isNaN(valor)) {
            $(this).val("0,0000")
        }
        $(this).formatCurrency($.extend({ colorize: false, roundToDecimalPlace: 4, negativeFormat: '-%s%n' }, $.formatCurrency.regions['pt-BR-QTD']));
    });

    $("body").on("change", "#selecionatodosGrid", function (e) {

        e.preventDefault();
        var isChecked = $(this).prop('checked')

        var parametro = obterIdsSelecionadoGridItens();

        if (parametro.length == 0) {
            $.each(selectedItensGrid, function (key, value) {
                value.selected = false;
            });
        }

        $.each(parametro, function (key, value) {

            var item = { 'id': parseInt(value.id), 'selected': isChecked };

            $("#dt-checkboxes-grid-" + value.id).trigger("click");
            var ids = _.map(selectedItensGrid, 'id');

            if (!_.includes(ids, item.id)) {
                selectedItensGrid.push(item);
            } else {
                var index = _.findIndex(selectedItensGrid, {
                    id: item.id
                });
                selectedItensGrid[index].selected = isChecked;
            }

        });

    });

    $("body").on("change", ".check-list-grid", function (e) {

        e.preventDefault();
        var id = $(this).attr("data-id");
        var item = { 'id': parseInt(id), 'selected': $(this).prop('checked') };

        var ids = _.map(selectedItensGrid, 'id');

        if (!_.includes(ids, item.id)) {
            selectedItensGrid.push(item);
        } else {
            var index = _.findIndex(selectedItensGrid, {
                id: item.id
            });
            selectedItensGrid[index].selected = $(this).prop('checked');
        }

        console.log('change selectedItensGrid', selectedItensGrid)
    });

    $(".alert-dismissible")
        .fadeTo(10000, 500)
        .slideUp(500, function () {
            $(".alert-dismissible").alert("close");
        });

    $("body").on("click", "#btnSalvar", function () {

        var visualizar = document.URL.split("/")[5] == "visualizar";
        if (visualizar) {
            Swal.fire(
                "Oops...",
                "Você não pode executar está ação.",
                "error"
            );
            return;
        }

        $(this).text("Salvando...");
        $(this).desabilitar();

        $.loading();
        $("#formPrincipal").submit();
    });

    $("#formPrincipal").on("submit", function (e) {
        //debugger;

        try {
            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            });

            e.preventDefault();

            var url = $(this).attr("action");
            var formData = new FormData(document.getElementById("formPrincipal"));

            if (pedido_item.length > 0) {
                createFormData(formData, 'itens', pedido_item);
            }

            if (rede_item.length > 0) {
                createFormData(formData, 'rede_item', rede_item);
            }

            var html =
                '<div class="alert alert-danger alert-dismissible" id="message" style="display: none">' +
                '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>' +
                '<ul id="errors"></ul></div>';

            $.ajax({
                type: "POST",
                url: url,
                data: formData,
                cache: false,
                dataType: "json",
                contentType: false,
                processData: false,
                success: function (data) {
                    $.limparBloqueioSairDaTela();
                    $.removeLoading();
                    Swal.fire("Sucesso!", data.message, "success");
                    $("#btnSalvar").html('<i class="icon fas fa-save"></i> Salvar');
                    $('#btnSalvar').habilitar();

                },
                error: function (xhr, status, error) {
                    $.removeLoading();
                    console.log(xhr.responseJSON)
                    $(".errors").html(html);
                    $("#message").css("display", "block");
                    $("#errors").empty();
                    var abaComErro = [];
                    if (xhr.status == 422) {

                        let errors = xhr.responseJSON.message;
                        Object.keys(errors).forEach(async function (key, value) {
                            console.log('key', key);
                            $("#" + key).addClass("is-invalid");

                            $("#" + key + "Error").html(errors[key][0].toString()
                                .replaceAll('"Dados Gerais"', '')
                                .replaceAll('"Endereço"', '')
                                .replaceAll('"Contatos"', '')
                                .replaceAll('"Multcard"', '')
                                .replaceAll('"Antecipação"', '')
                                .replaceAll('"Rebate"', '')
                                .replaceAll('"Cobranças"', '')
                                .replaceAll('"Dados Adicionais"', '')
                                .replaceAll('"Senhas"', '')
                            );

                            $("#" + key)
                                .closest(".form-group")
                                .find(".select2-selection")
                                .css("border-color", "#dc3545")
                                .addClass("text-danger");

                            var ids = _.map(abaComErro, 'id');
                            var msgError = errors[key][0]
                            const baseError = msgError.split('"')
                            var item = {
                                id: baseError.slice(-2, -1)[0]
                            };

                            if (!_.includes(ids, item.id)) {
                                if (item.id)
                                    abaComErro.push(item);
                            }
                        });

                        if (xhr.responseJSON.message_type) {
                            Swal.fire("Erro", xhr.responseJSON.message_type, "error");
                        } else {
                            $.each(abaComErro, function (key, item) {
                            console.log(item);
                            $("#errors").append("<li>" + item + "</li>");
                            toastr.error('Existem campos obrigatórios na aba "' + item.id + '" que não foram preenchidos.');
                        });
                        }


                    } else {
                        toastr.error(xhr.responseJSON.message);
                    }

                    $("#btnSalvar").html('<i class="icon fas fa-save"></i> Salvar');
                    $("#btnSalvar").habilitar();

                },
            });
        } catch (error) {
            toastr.error(error);
        }

    });

    var limpa_formulario_cep = function () {
        rua.val("");
        bairro.val("");
        cidade.val("");
    };

    var setarFocus = function (elemento) {
        if (elemento.is(":focus")) return;
        else {
            elemento.focus().select();
            setTimeout(function () {
                setarFocus(elemento);
            }, 100);
        }
    };

    var createFormData = function (formData, key, data) {
        if (data === Object(data) || Array.isArray(data)) {
            for (var i in data) {
                createFormData(formData, key + '[' + i + ']', data[i]);
            }
        } else {
            formData.append(key, data);
        }
    }

    Date.prototype.addDays = function (days) {
        const date = new Date(this.valueOf());
        date.setDate(date.getDate() + days);
        return date;
    };

    jQuery.validarErrorGrid = function (retorno) {
        var mensagem = retorno["error"];
        if ($.isNotNullAndNotEmpty(mensagem)) {
            $("#divErros").show();
            $("#divErros").html(
                '<div class="callout callout-danger bg-red disabled color-palette">' +
                '    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>' +
                "    <h4>Alerta!</h4>" +
                '    <div id="erros" hidden data="{!! json_encode(array_keys($errors->default->messages())) !!}"></div>' +
                "    <ul>" +
                "               <li>" +
                mensagem +
                "</li>" +
                "    </ul>" +
                "</div>"
            );
            var gridTemplate = $("#gridtemplate");
            var item = gridTemplate.obterLinhaGridItem();
            //gridTemplate.getKendoGrid().dataSource.read({ id: item.id });
            return true;
        } else {
            $("#divErros").hide();
            return false;
        }
    };


    jQuery.bloquearSairDaTela = function () {
        setTimeout(function () {
            window.onbeforeunload = function () {
                return "É possivel que as alterações feitas não sejam salvas.";
            };
        }, 500);
    };

    jQuery.limparBloqueioSairDaTela = function () {
        window.onbeforeunload = function () {
            return null;
        };
    };

    jQuery.isNotNullAndNotEmpty = function (texto) {
        return texto != null && texto != undefined && texto != "";
    };

    jQuery.FormularioAlterar = function () {
        return $("input[name='cod']").val() > 0;
    };

    jQuery.gridTemplateValido = function () {
        return (
            $("#gridtemplate").length > 0 &&
            $.isFunction($("#gridtemplate").getKendoGrid) &&
            $("#gridtemplate").getKendoGrid() != undefined
        );
    };

    jQuery.fn.tryFocus = function () {
        var elemento = $(this);
        setarFocus(elemento);
    };

    jQuery.fn.isNullOrEmpty = function () {
        var elemento = $(this);
        return (
            elemento.val() == null ||
            elemento.val() == undefined ||
            elemento.val() == ""
        );
    };

    jQuery.isNullOrEmpty = function () {
        var elemento = $(this);
        return (
            elemento.val() == null ||
            elemento.val() == undefined ||
            elemento.val() == ""
        );
    };

    jQuery.fn.selectOpen = function () {
        $(this).select2("open");
    };

    jQuery.fn.isNotNullAndNotEmpty = function () {
        var elemento = $(this);
        return $.isNotNullAndNotEmpty(elemento.val());
    };

    jQuery.fn.toMoney = function () {
        var elemento = $(this);
        return $.toMoney(elemento.val())
    };

    jQuery.toMoney = function (texto) {
        texto = $.isNotNullAndNotEmpty(texto) ? texto + "" : "0";
        return $.fn.dataTable.render
            .number(".", ",", 2, "R$ ")
            .display(texto.replace(",", "."));
    };

    $.toMoneyVenda = function (valor) {
        return $.toMoneyVendaSimples(valor, true);
    };

    $.tratarValor = function (valor) {
        if (valor == undefined) return 0;

        var retorno = valor.replace("%", "").replace(".", "").replace(",", ".");
        if (retorno < 0) return 0;

        return parseFloat(retorno);
    };

    $.toMoneyVendaSimples = function (valor, gerarTexto) {
        if (valor == undefined) return 0.0;

        if ($.isNumeric(valor)) {
            if (valor == "0.00") return 0.0;

            var novoValor = (valor + "").split(".");
            if (novoValor.length == 2 && (valor + "").split(".")[1].length >= 2)
                return $.toMoneySimples(novoValor);
            else if (
                novoValor.length == 2 &&
                (valor + "").split(".")[1].length == 1
            )
                return $.toMoneySimples(novoValor + "0");

            valor = valor + "".replace(".", ",") + ".00";
        }

        var resultado = "";
        var numeros = valor.split(",");
        var quantidadeSplit = numeros.length;

        $.each(numeros, function (index, elemento) {
            if (quantidadeSplit == index + 1)
                resultado += elemento.replace(".", ",") + ".";
            else resultado += elemento + ".";
        });

        return (
            (gerarTexto ? "R$" : "") +
            resultado.substring(0, resultado.length - 1)
        );
    };

    $.toMoneySimples = function (texto, casasDecimais) {
        texto = $.isNotNullAndNotEmpty(texto) ? texto + "" : "0";
        return $.fn.dataTable.render
            .number(
                ".",
                ",",
                casasDecimais == undefined ? 2 : casasDecimais,
                ""
            )
            .display(texto.replace(",", "."));
    };

    jQuery.executarChamadaAjax = async function (e, funcaoChamada) {
        e.preventDefault();
        $.loading();
        await funcaoChamada();
        //setTimeout(function () {
        $.removeLoading();
        //}, 100);
    };

    jQuery.callbackAjaxGridWithID = function (e, callback, id) {
        e.preventDefault();
        var grid = $("#" + id);
        var linha = grid.obterLinhaGridItemWithID(id);
        //console.log('linha', linha);

        if (linha == null) {
            Swal.fire("Alerta!", "Selecione um registro", "error");
        } else {
            Pace.restart();
            Pace.track(function () {
                callback();
            });

            // $.loading();
            //await funcaoChamada();
            //setTimeout(function () {
            // $.removeLoading();
            //}, 100);
        }
    };

    jQuery.executarChamadaAjaxGrid = function (e, funcaoChamada) {
        e.preventDefault();
        var grid = $("#gridtemplate");
        var linha = grid.obterLinhaGridItem();
        //console.log(linha);

        if (linha == null) {
            Swal.fire("Alerta!", "Selecione um registro", "error");
        } else {
            Pace.restart();
            Pace.track(function () {

                funcaoChamada();
            });

            // $.loading();
            //await funcaoChamada();
            //setTimeout(function () {
            // $.removeLoading();
            //}, 100);
        }
    };

    jQuery.minimizarEBloquearMenuLateral = function () {
        $("body").addClass("sidebar-collapse");
        $("body").removeClass("fixed");
    };

    jQuery.fn.obterLinhaGridItemWithID = function (id) {
        var grid = $("#" + id)
            .DataTable()
            .row({ selected: true })
            .data();
        return grid;
    };

    jQuery.fn.obterLinhaGridItem = function () {
        var grid = $("#gridtemplate")
            .DataTable()
            .row({ selected: true })
            .data();
        return grid;
    };

    jQuery.fn.obterLinhaGridItemId = function () {
        return $(this).obterLinhaGridItem().id;
    };

    jQuery.loading = function () {
        $(".se-pre-con").show();
    };

    jQuery.removeLoading = function () {
        $(".se-pre-con").hide();
    };

    jQuery.getIdEmpresa = function () {
        var comboEmpresa = $("#idempresa :selected");
        var idEmpresa = 0;

        if (comboEmpresa.length > 0)
            idEmpresa = $("#idempresa :selected").attr("id");
        else throw new userException("idEmpresa não selecionado");

        return idEmpresa;
    };

    jQuery.fn.desabilitar = function () {
        $(this).attr("disabled", "disabled");
    };

    jQuery.fn.habilitar = function () {
        $(this).removeAttr("disabled");
    };

    toastr.options = {
        closeButton: true,
        debug: false,
        newestOnTop: true,
        progressBar: true,
        positionClass: "toast-top-center",
        preventDuplicates: false,
        onclick: null,
        showDuration: "300",
        hideDuration: "1000",
        timeOut: "10000",
        extendedTimeOut: "1000",
        showEasing: "swing",
        hideEasing: "linear",
        showMethod: "fadeIn",
        hideMethod: "fadeOut",
    };

    var overlay = $(
        '<div class="overlay"><div class="fa fa-refresh fa-spin"></div></div>'
    );
    jQuery.fn.startLoad = function () {
        $(this).append(overlay);
    };

    jQuery.fn.removeLoad = function () {
        $(this).find(overlay).remove();
    };

    jQuery.toastrMsg = function (data) {
        switch (data.type) {
            case "success":
                toastr.success(data.message, data.title);
                break;
            case "info":
                toastr.info(data.message, data.title);
                break;
            case "warning":
                toastr.warning(data.message, data.title);
                break;
            case "error":
                toastr.error(data.message, data.title);
                break;
        }
    };

    // $("#showModalProduto").on("click", function () {
    //     Pace.restart();
    //     Pace.track(function () {
    //         socket.emit("chama guiche", {
    //             idvenda: $(".IncOrDecToCart").val(),
    //         });
    //     });
    // });

    window.ns = namespace;
})(this.jQuery);

$(document).ready(function () {
    $(document).on('select2:open', () => {
        document.querySelector('.select2-search__field').focus();
    });



    ns.scrollNav();
    ns.inicializarEventos();
    ns.iniciarlizarMascaras();
    $.fn.select2.defaults.set("language", "pt-BR");
    $(".select2").select2();

    $('body').on('change', '.select2', function () {
        var key = $(this).attr('data-select2-id');
        $("#" + key + "Error").html('');
        $("#" + key)
            .closest(".form-group")
            .find(".select2-selection")
            .css("border-color", "#ced4da")
            .removeClass("text-danger");
    });

    $("body").on("keyup change input", "textarea, input[type='text'],input[type='password'],input[type='email'],input[type='number']", function (e) {
        var id = $(this).attr('name');
        if ($(this).hasClass('is-invalid'))
            $('#' + id + 'Error').html("");

        $(this).removeClass('is-invalid');

    });

    ns.visualizarUpdate();
});
