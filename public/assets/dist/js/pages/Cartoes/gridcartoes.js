$(document).ready(function () {

    // Abrir modal para novo registro
    $(document).on('click', '#btnCriarPrograma', function(e) {
        if (!$('#empresa_id').val()) {
            toastr.error('Selecione uma Empresa antes de criar um novo programa.', 'Campo obrigatório');
            $('#empresa_id').focus();
            return false;
        }
        // Resetar o formulário e campos do modal
        $('#formCriarPrograma')[0].reset();
        $('#formCriarPrograma .select2').val(null).trigger('change');
        $('#prgpts_id').val('');
        $('#modalCriarPrograma').modal('show');
    });

    // Abrir modal para edição
    $(document).on('click', '.btn-editar-programa', function() {
        var id = $(this).data('id');
        // Resetar o formulário antes de preencher
        $('#formCriarPrograma')[0].reset();
        $('#formCriarPrograma .select2').val(null).trigger('change');
        $('#prgpts_id').val('');
        $('#modalCriarPrograma').modal('show');

        $.get('/programa-de-pontos/' + id + '/visualizar', function(data) {
            $('#prgpts_id').val(id);
            $('#card_categ_modal').val(data.card_categ).trigger('change');
            $('#prgpts_valor').val(data.prgpts_valor);
            $('#prgpts_eq').val(data.prgpts_eq);
            $('#prgpts_sc').prop('checked', data.prgpts_sc === 'S');
            $('#prgpts_sts').val(data.prgpts_sts).trigger('change');
        });
    });

    // Inicialização dos componentes
    $('.select2').select2();
    ns.comboBoxSelectTags("empresa_id", "/empresa/obter-empresas", "emp_id");

    $('#prg_valor').mask('#.##0,00', {reverse: true});

    $('#inputPesquisa').on('keyup', function(e){
        if(e.keyCode == 13){
            $("#btnPesquisar").trigger("click");
        }
    });

    $(".alert-dismissible")
        .fadeTo(10000, 500)
        .slideUp(500, function() {
            $(".alert-dismissible").alert("close");
        });

    var colunas = [
        { data: 'action', name: 'action', orderable: false, searchable: false },
        { data: 'card_categ', name: 'card_categ' },
        { data: 'prgpts_valor', name: 'prgpts_valor' },
        { data: 'prgpts_eq', name: 'prgpts_eq' },
        { data: 'prgpts_sc', name: 'prgpts_sc' },
        { data: 'prgpts_sts', name: 'prgpts_sts' }
    ];

    var colunasconfig = [
        { width: "auto", targets: 0 },
        { width: "auto", targets: 1 },
        { width: "auto", targets: 2 },
        { width: "auto", targets: 3 },
        { width: "auto", targets: 4 },
        { width: "auto", targets: 5 }
    ];

    // Pesquisa no grid
    $('#btnPesquisar').click(function () {
        if (!$('#empresa_id').val()) {
            toastr.error('Selecione uma Empresa antes de pesquisar.', 'Campo obrigatório');
            $('#empresa_id').focus();
            return false;
        }
        var totaliza = {};
        totaliza.totaliza = false;
        $('#gridtemplate').DataTable().clear().destroy();
        ns.gridDataTable(colunas, colunasconfig, true, false, "programa-de-pontos", totaliza, 'filtro-pesquisa');
    });

    // Salvar registro (novo ou edição)
    $('#btnSalvarPrograma').on('click', function() {
        var id = $('#prgpts_id').val();
        var url = id ? '/programa-de-pontos/' + id + '/alterar' : '/programa-de-pontos/inserir';
        var method = id ? 'PATCH' : 'POST';

        $.ajax({
            url: url,
            type: method,
            data: {
                prgpts_id: id,
                empresa_id: $('#empresa_id').val(),
                card_categ: $('#card_categ_modal').val(),
                prgpts_valor: $('#prgpts_valor').val(),
                prgpts_eq: $('#prgpts_eq').val(),
                prgpts_sc: $('#prgpts_sc').is(':checked') ? 'X' : '',
                prgpts_sts: $('#prgpts_sts').val(),
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#modalCriarPrograma').modal('hide');
                toastr.success('Registro salvo com sucesso!');
                if ($.fn.DataTable.isDataTable('#gridtemplate')) {
                    $('#gridtemplate').DataTable().ajax.reload();
                } else {
                    $('#card_categ').val('');
                    $('#btnPesquisar').trigger('click');
                }
            },
            error: function(xhr) {
                let msg = 'Erro ao salvar registro!';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire('Atenção', msg, 'warning');
            }
        });
    });

    $('#modalCriarPrograma').on('shown.bs.modal', function () {
        $('#prgpts_valor').mask('#.##0,00', {reverse: true});
        $('#prgpts_eq').mask('#.##0,00', {reverse: true});
    });

});
