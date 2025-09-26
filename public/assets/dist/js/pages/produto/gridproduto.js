$(document).ready(function () {
    $(function () {

        //Initialize Select2 Elements
        $('.select2').select2();
        ns.comboBoxSelectTags("empresa_id", "/empresa/obter-empresas", "emp_id");
        ns.comboBoxSelectTags("produto_dmf", "/produto/obter-descricao-produto", "produto_id");

        $('#produto_dmf').on('select2:select', function (e) {
            var data = e.params.data;
            $('#produto_dmf_id').val(data.id); // Aqui você utiliza o ID do produto selecionado
        });

        $('#btnPesquisar').click(function () {
            if (!$('#empresa_id').val()) {
                toastr.error('Selecione uma Empresa antes de pesquisar.', 'Campo obrigatório');
                $('#empresa_id').focus();
                return false; // Impede a execução do restante do código
            }
            var totaliza = {};
            totaliza.totaliza = false;
            $('#gridtemplate').DataTable().clear().destroy();
            ns.gridDataTable(colunas, colunasconfig, true, false, "produto", totaliza, 'filtro-pesquisa');
        });

        var colunas = [
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            },
            {
                data: 'produto_id',
                name: 'produto_id'
            },
            {
                data: 'produto_tipo',
                name: 'produto_tipo'
            },
            {
                data: 'produto_dc',
                name: 'produto_dc'
            },
            {
                data: 'produto_dm',
                name: 'produto_dm'
            },
            {
                data: 'produto_sts',
                name: 'produto_sts'
            }
        ];

        var colunasconfig = [{
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
            },
            {
                width: "auto",
                targets: 4
            },
            {
                width: "auto",
                targets: 5
            }
        ];

        $(document).on('click', '#delete_grid_id', function (e) {
            var id = $(this).data('id');
            ns.swalDelete(id, 'produto');
            e.preventDefault();
        });

    });
});
