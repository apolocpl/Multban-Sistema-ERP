$(document).ready(function () {
    $(function () {

        //Initialize Select2 Elements
        $('.select2').select2();
        ns.comboBoxSelectTags("produto_dmf", "/produto/obter-descricao-produto", "produto_id");

        $('#produto_dmf').on('select2:select', function (e) {
            var data = e.params.data;
            $('#produto_dmf_id').val(data.id); // Aqui você utiliza o ID do produto selecionado
        });

    });
});
