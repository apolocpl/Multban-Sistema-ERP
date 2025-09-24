var listaTaxa = 0;

$(document).ready(function () {
    $('#emp_wl').on('change', function() {
        if ($(this).is(':checked')) {
            $('#comissionamento-multban-group').show();
        } else {
            $('#comissionamento-multban-group').hide();
        }
    });
});

$(function () {

    $("input[type='text']").trigger('keyup');

    ns.comboBoxSelect("emp_endpais", "/empresa/obter-pais", "pais");
    ns.comboBoxSelect("emp_endest", "/empresa/obter-estado", "estado");
    ns.comboBoxSelect("emp_endcid", "/empresa/obter-cidade", "cidade_ibge");
    ns.comboBoxSelect("rebate_emp", "/empresa/obter-empresas", "emp_id");
    ns.comboBoxSelect("royalties_emp", "/empresa/obter-empresas", "emp_id");
    ns.comboBoxSelect("comiss_emp", "/empresa/obter-empresas", "emp_id");

    $("#btnPesquisarCep").on("click", function () {
        ns.cepOnClick("emp_cep");
    });

    bsCustomFileInput.init();

    $("body").on("click", ".incluir_taxa_btn", function (e) {
        e.preventDefault();
        var categ = $(this).data('categ');
        var tipo = $(this).data('tipo');
        $("#taxa_" + categ + "")
            .append(`<div class="form-row" id="taxa_${categ}_${listaTaxa}">
                <div class="form-group col-md-2">
                    <input class="form-control form-control-sm" name="tax_categ_${categ}[${listaTaxa}][categ]" value="${tipo}" type="hidden">
                    <input class="form-control form-control-sm" placeholder="0" data-categ="${tipo}" data-tipo="${tipo}_parc_de" name="tax_categ_${categ}[${listaTaxa}][parc_de]" type="number">
                </div>
                <div class="form-group col-md-2">
                    <input class="form-control form-control-sm" placeholder="0" data-categ="${tipo}" data-tipo="${tipo}_parc_ate" name="tax_categ_${categ}[${listaTaxa}][parc_ate]" type="number">
                </div>
                <div class="form-group col-md-2">
                    <input class="form-control form-control-sm porcentagem" data-categ="${tipo}" data-tipo="${tipo}_taxa" placeholder="0,00" name="tax_categ_${categ}[${listaTaxa}][taxa]" type="text">
                </div>
                <div class="form-group col-md-2">
                    <button type="button" data-id="${listaTaxa}" data-categ="${categ}" class="btn btn-danger btn-sm remove_taxa"><i class="icon fas fa-trash"></i></button>
                </div>
            </div>`);

        listaTaxa++;

    });

    $("body").on("click", ".remove_taxa", function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        var categ = $(this).data('categ');

        $("#taxa_" + categ + "_" + id).remove();

    });

    $("body").on("keyup change", "input[type='number'], input[type='text']", function (e) {
        var tipo = $(this).data('tipo');
        var categ = $(this).data('categ');
        console.log('tipo', tipo)
        console.log('categ', categ)

        if (tipo) {

            $('#' + categ + 'Error').html("");
            $('#' + tipo + 'Error').html("");
        }

    });

    $("body").on("change", "#emp_frq, #emp_frq_n", function (e) {

        if ($(this).val() == "x") {
            $("#emp_frqmst").prop('disabled', true);
            $("#emp_frqmst").select2("trigger", "select", {
                data: { id: '', text: '' },
            });
        } else {
            $("#emp_frqmst").prop('disabled', false);
        }
    });

    $("body").on("change", "#lib_cnscore", function (e) {
        e.preventDefault();
        let checked = $(this).is(':checked');
        console.log(checked)
        if (checked) {
            setTimeout(() => {
                $('#lib_cnscore_coll').CardWidget('expand');
            }, 150);
        } else {
            setTimeout(() => {
                $('#lib_cnscore_coll').CardWidget('collapse');
            }, 150);
        }
    });

    $("body").on("change", "#blt_ctr", function (e) {
        e.preventDefault();
        let checked = $(this).is(':checked');
        console.log(checked)
        if (checked) {
            setTimeout(() => {
                $('#blt_ctr_coll').CardWidget('expand');
            }, 150);
        } else {
            setTimeout(() => {
                $('#blt_ctr_coll').CardWidget('collapse');
            }, 150);
        }
    });

    $("body").on("change", "#card_posctr", function (e) {
        e.preventDefault();
        let checked = $(this).is(':checked');

        if (checked) {
            setTimeout(() => {
                $('#card_posctr_coll').CardWidget('expand');
            }, 150);
        } else {
            setTimeout(() => {
                $('#card_posctr_coll').CardWidget('collapse');
            }, 150);
        }
    });

    $("body").on("change", "#cob_mltjr_atr", function (e) {
        e.preventDefault();
        let checked = $(this).is(':checked');

        if (checked) {
            setTimeout(() => {
                $('#cob_mltjr_atr_coll').CardWidget('expand');
            }, 150);
        } else {
            setTimeout(() => {
                $('#cob_mltjr_atr_coll').CardWidget('collapse');
            }, 150);
        }
    });

    $("body").on("change", "#parc_cjuros", function (e) {
        e.preventDefault();
        let checked = $(this).is(':checked');

        if (checked) {
            setTimeout(() => {
                $('#parc_cjuros_coll').CardWidget('expand');
            }, 150);
        } else {
            setTimeout(() => {
                $('#parc_cjuros_coll').CardWidget('collapse');
            }, 150);
        }
    });

    $("body").on("change", "#card_prectr", function (e) {
        e.preventDefault();
        let checked = $(this).is(':checked');

        if (checked) {
            setTimeout(() => {
                $('#card_prectr_coll').CardWidget('expand');
            }, 150);
        } else {
            setTimeout(() => {
                $('#card_prectr_coll').CardWidget('collapse');
            }, 150);
        }
    });

    $("body").on("change", "#card_giftctr", function (e) {
        e.preventDefault();
        let checked = $(this).is(':checked');

        if (checked) {
            setTimeout(() => {
                $('#card_giftctr_coll').CardWidget('expand');
            }, 150);
        } else {
            setTimeout(() => {
                $('#card_giftctr_coll').CardWidget('collapse');
            }, 150);
        }
    });

    $("body").on("change", "#card_fidctr", function (e) {
        e.preventDefault();
        let checked = $(this).is(':checked');

        if (checked) {
            setTimeout(() => {
                $('#card_fidctr_coll').CardWidget('expand');
            }, 150);
        } else {
            setTimeout(() => {
                $('#card_fidctr_coll').CardWidget('collapse');
            }, 150);
        }
    });

    $("body").on("change", "#antecip_ctr", function (e) {
        e.preventDefault();
        let checked = $(this).is(':checked');

        if (checked) {
            setTimeout(() => {
                $('#antecip_ctr_coll').CardWidget('expand');
            }, 150);
        } else {
            setTimeout(() => {
                $('#antecip_ctr_coll').CardWidget('collapse');
            }, 150);
        }
    });

    $("body").on("change", "#antecip_auto", function (e) {
        e.preventDefault();
        let checked = $(this).is(':checked');

        if (checked) {
            setTimeout(() => {
                $('#antecip_auto_coll').CardWidget('expand');
            }, 150);
        } else {
            setTimeout(() => {
                $('#antecip_auto_coll').CardWidget('collapse');
            }, 150);
        }
    });

    $("body").on("change", "#cobsrv_atv", function (e) {
        e.preventDefault();
        let checked = $(this).is(':checked');

        if (checked) {
            setTimeout(() => {
                $('#cobsrv_atv_coll').CardWidget('expand');
            }, 150);
        } else {
            setTimeout(() => {
                $('#cobsrv_atv_coll').CardWidget('collapse');
            }, 150);
        }
    });

    $("body").on("change", "#fndant_cdgbc", function (e) {
        let data = $(this).select2('data')[0];

        if (data.id) {
            $("#banco-fndant").show('slow');
        } else {
            $("#banco-fndant").hide('slow');
        }
    });

    $("body").on("change", "#emp_cdgbc", function (e) {
        let data = $(this).select2('data')[0];

        if (data.id) {
            $("#banco-principal").show('slow');
        } else {
            $("#banco-principal").hide('slow');
            $("#emp_agbcs").val('');
            $("#emp_ccbcs").val('');
            $("#emp_pixs").val('');
            $("#banco-principal").val('');
        }
    });

    $("body").on("change", "#emp_cdgbcs", function (e) {
        let data = $(this).select2('data')[0];
        if (data.id) {
            $("#banco-secundario").show('slow');

        } else {
            $("#banco-secundario").hide('slow');
        }
    });

    $("body").on("change", "#rebate_emp", function (e) {
        let data = $(this).select2('data')[0];

        if (data) {
            $("#tax_rebate").prop('disabled', false);
            $("#tax_rebate").focus();
        } else {
            $("#tax_rebate").prop('disabled', true);
            $("#tax_rebate").val("");
        }

    });

    $("body").on("change", "#royalties_emp", function (e) {
        let data = $(this).select2('data')[0];

        if (data) {
            $("#tax_royalties").prop('disabled', false);
            $("#tax_royalties").focus();
        } else {
            $("#tax_royalties").prop('disabled', true);
            $("#tax_royalties").val("");
        }
    });

    $("body").on("change", "#comiss_emp", function (e) {
        let data = $(this).select2('data')[0];

        if (data) {
            $("#tax_comiss").prop('disabled', false);
            $("#tax_comiss").focus();
        } else {
            $("#tax_comiss").prop('disabled', true);
            $("#tax_comiss").val("");
        }
    });

    $("body").on("change", "#emp_endpais", function (e) {
        $("#emp_endest").select2("trigger", "select", {
            data: { id: '', text: '' },
        });
        $("#emp_endcid").select2("trigger", "select", {
            data: { id: '', text: '' },
        });
    });

    $("body").on("change", "#emp_endest", function (e) {
        $("#emp_endcid").select2("trigger", "select", {
            data: { id: '', text: '' },
        });
    });

    $("body").on("change", "#emp_checkb", function (e) {
        e.preventDefault();
        let checked = $(this).is(':checked');
        console.log(checked)
        if (checked) {
            $("#emp_tpbolet").prop("disabled", false);
        } else {
            $("#emp_tpbolet").prop("disabled", true);
            $("#emp_tpbolet").select2("trigger", "select", {
                data: { id: '', text: '' },
            });
        }
    });

    $("body").on("change", "#emp_checkm", function (e) {
        e.preventDefault();
        let checked = $(this).is(':checked');
        console.log(checked)
        if (checked) {
            $("#tp_plano").prop("disabled", false);
        } else {
            $("#tp_plano").prop("disabled", true);
            $("#tp_plano").select2("trigger", "select", {
                data: { id: '', text: '' },
            });
        }
    });

    $("body").on("change", "#emp_checkc", function (e) {
        e.preventDefault();
        let checked = $(this).is(':checked');
        console.log(checked)
        if (checked) {
            $("#emp_adqrnt").prop("disabled", false);
        } else {
            $("#emp_adqrnt").prop("disabled", true);
            $("#emp_adqrnt").select2("trigger", "select", {
                data: { id: '', text: '' },
            });
        }
    });

    var searchClient = function () {
        var inserir = document.URL.split("/")[4] == "inserir";

        if (inserir) {
            $("#serachClient").hide();
            return false;
        }
    }

    $("body").on("keyup", "#qtde_cns_freem, #qtde_cns_cntrm", function (e) {
        e.preventDefault();
        QtdeTotaldeConsultasMensal();
    });

    var QtdeTotaldeConsultasMensal = function () {
        var consultasGratis = $('#qtde_cns_freem').val() ?? 0;
        var consultasContratadas = $('#qtde_cns_cntrm').val() ?? 0;
        var consultasPre = $('#qtde_cns_prem').val() ?? 0;

        var consultasUtilizadas = $('#qtde_cns_utlxm').val() ?? 0;

        var Totalconsultas = Number(consultasGratis) + Number(consultasContratadas) + Number(consultasPre);

        console.log(Totalconsultas);

        $('#qtde_cns_totm').val(Totalconsultas);
        $('#qtde_cns_dispm').val(Number(Totalconsultas) - Number(consultasUtilizadas));
    }

    $("#btnSearchEmpresa").on("click", function (e) {
        e.preventDefault();
        let data = $("#emp_id").select2('data')[0];
        console.log(data);
        if (data.id) {
            $("#emp_id").val(data.id);

            clearTimeout(timeOut);
            timeOut = setTimeout(() => {
                $("#btnCarregaEmpresa").trigger('click');
            }, 250);

        }
    });

    $("#btnCarregaEmpresa").on("click", function (e) {
        e.preventDefault();
        if (parseInt($("#emp_id").val()) > 0) {
            var url = "/empresa/" + $("#emp_id").val() + "/alterar"
            window.open(url, "_self");
        } else {
            Swal.fire(
                "Oops...",
                "Por favor, informe o Código da empresa..",
                "error"
            );
            return;
        }
    });

    searchClient();

    $("body").on("keyup change", "input[type='text']", function (e) {
        $(this).removeClass('is-invalid');
    });

    $(".alert-dismissible")
        .fadeTo(10000, 500)
        .slideUp(500, function () {
            $(".alert-dismissible").alert("close");
        });

});
