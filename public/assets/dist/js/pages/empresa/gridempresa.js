$(document).ready(function () {
    $(function () {

        //Initialize Select2 Elements
        $('.select2').select2();
        ns.comboBoxSelectTags("empresa_id", "/empresa/obter-empresas", "emp_id");
        ns.comboBoxSelectTags("nome_fantasia", "/empresa/obter-empresas", "emp_id");
        ns.comboBoxSelectTags("nome_multmais", "/empresa/obter-empresas-nmult", "emp_id");
        ns.comboBoxSelect("cod_franqueadora", "/empresa/obter-empresas-franqueadoras", "emp_id");

        $('#cod_franqueadora').on('change', function() {
            var franqueadoraId = $(this).val();
            // Chame a função que popula o select de empresa, passando o cod_franqueadora
            ns.comboBoxSelectTags("empresa_id", "/empresa/obter-empresas?cod_franqueadora=" + franqueadoraId, "emp_id");
            ns.comboBoxSelectTags("nome_fantasia", "/empresa/obter-empresas?cod_franqueadora=" + franqueadoraId, "emp_id");
            ns.comboBoxSelectTags("nome_multmais", "/empresa/obter-empresas-nmult?cod_franqueadora=" + franqueadoraId, "emp_id");

        });

        $('#btnPesquisar').click(function () {
            var totaliza = {};
            totaliza.totaliza = false;
            $('#gridtemplate').DataTable().clear().destroy();
            ns.gridDataTable(colunas, colunasconfig, true, false, "empresa", totaliza, 'filtro-pesquisa');
        });


        var colunas = [
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            },
            {
                data: 'emp_id',
                name: 'emp_id'
            },
            {
                data: 'emp_nmult',
                name: 'emp_nmult',
                autoWidth: true
            },
            {
                data: 'emp_cnpj',
                name: 'emp_cnpj'
            },
            {
                data: 'emp_sts',
                name: 'emp_sts'
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
        }
        ];

        $(document).on('click', '#delete_grid_id', function (e) {
            var id = $(this).data('id');
            ns.swalDelete(id, 'empresa');
            e.preventDefault();
        });
    });
});
