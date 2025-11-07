
ns.comboBoxSelect("cliente_endpais", "/empresa/obter-pais", "pais");
ns.comboBoxSelect("emp_id", "/empresa/obter-empresas", "emp_id", "", "", "modalCriarCartao");
var gridprotocolo;

(function () {
    const estadoSelector = '#cliente_endest';
    const cidadeSelector = '#cliente_endcid';
    let isSyncingCity = false;

    $(function () {
        const $estado = $(estadoSelector);
        const $cidade = $(cidadeSelector);

        if (!$estado.length || !$cidade.length) {
            return;
        }

        const placeholderEstado = ($estado.data('placeholder') || $estado.find('option[value=""]').first().text() || 'Selecione').trim();
        const placeholderCidade = ($cidade.data('placeholder') || $cidade.find('option[value=""]').first().text() || 'Selecione').trim();

        const cidadeOptions = [];
        const cidadeMap = {};

        $cidade.find('option').each(function () {
            const $option = $(this);
            const value = $option.val();
            const text = $option.text();
            const estado = $option.data('estado') || '';

            cidadeOptions.push({ value, text, estado });

            if (value) {
                cidadeMap[value] = { value, text, estado };
            }
        });

        function rebuildCidadeOptions(stateValue, preserveCity) {
            isSyncingCity = true;
            const currentCity = preserveCity ? $cidade.val() : '';

            $cidade.empty();

            const placeholderOption = new Option(placeholderCidade, '', false, false);
            $(placeholderOption).attr('data-estado', '');
            $cidade.append(placeholderOption);

            cidadeOptions.forEach(function (option) {
                if (!option.value) {
                    return;
                }

                if (!stateValue || option.estado === stateValue) {
                    const shouldSelect = preserveCity && currentCity === option.value;
                    const optionElement = new Option(option.text, option.value, false, shouldSelect);
                    $(optionElement).attr('data-estado', option.estado);
                    $cidade.append(optionElement);
                }
            });

            const hasCurrentCity = currentCity && $cidade.find('option[value="' + currentCity + '"]').length > 0;
            const newValue = preserveCity && hasCurrentCity ? currentCity : '';

            $cidade.val(newValue).trigger('change.select2');
            isSyncingCity = false;
        }

        $estado.select2({
            placeholder: placeholderEstado,
            allowClear: true,
            width: 'resolve',
        });

        $cidade.select2({
            placeholder: placeholderCidade,
            allowClear: true,
            width: 'resolve',
        });

        $estado.on('change', function (event, data) {
            const preserveCity = data && data.preserveCity === true;
            rebuildCidadeOptions($(this).val(), preserveCity);
        });

        $cidade.on('change', function () {
            if (isSyncingCity) {
                return;
            }

            const selectedCity = $(this).val();

            if (!selectedCity) {
                return;
            }

            const cityInfo = cidadeMap[selectedCity];

            if (cityInfo && cityInfo.estado && $estado.val() !== cityInfo.estado) {
                $estado.val(cityInfo.estado).trigger('change', { preserveCity: true });
            }
        });

        const initialState = $estado.val();

        if (initialState) {
            rebuildCidadeOptions(initialState, true);
        } else {
            const initialCity = $cidade.val();

            if (initialCity) {
                const cityInfo = cidadeMap[initialCity];

                if (cityInfo && cityInfo.estado) {
                    $estado.val(cityInfo.estado).trigger('change', { preserveCity: true });
                } else {
                    rebuildCidadeOptions('', true);
                }
            } else {
                rebuildCidadeOptions('', false);
            }
        }
    });
})();

function isNumeric(n) {
    return !isNaN(parseFloat(n)) && isFinite(n);
}

var colunas = [

    {
        data: 'protocolo',
        name: 'protocolo'
    },
    {
        data: 'protocolo_tp',
        name: 'protocolo_tp',
    },
    {
        data: 'medico',
        name: 'medico',
    },
    {
        data: 'anexo',
        name: 'anexo',
    }
];

var colunasConfiguracao = [

];
$(function () {
    "use strict";

    const DATE_ONLY_REGEX = /^\d{4}-\d{2}-\d{2}$/;
    const DATETIME_REGEX = /^(\d{4})-(\d{2})-(\d{2})[T\s](\d{2}):(\d{2})(?::(\d{2}))?/;

    function normalizeDateValue(value) {
        if (typeof value !== 'string') {
            return value;
        }

        if (DATE_ONLY_REGEX.test(value)) {
            const [year, month, day] = value.split('-');
            return `${day}/${month}/${year}`;
        }

        const match = value.match(DATETIME_REGEX);
        if (match) {
            const [, year, month, day, hour, minute, second] = match;
            const time = `${hour}:${minute}${second ? `:${second}` : ''}`;
            return `${day}/${month}/${year} ${time}`;
        }

        return value;
    }

    function normalizeFieldValue(input, value) {
        if (value === null || value === undefined || value === '') {
            return value ?? '';
        }

        const element = input;
        const type = (element.type || '').toLowerCase();
        const dataFormat = (element.getAttribute('data-format') || '').toLowerCase();

        if (type === 'date' || dataFormat === 'date') {
            return normalizeDateValue(value);
        }

        if (type === 'datetime-local' || dataFormat === 'datetime' || dataFormat === 'datetime-local') {
            return normalizeDateValue(value);
        }

        return value;
    }

    window.clientejs = ({
        submitFormPrt: function (formId, btnSubmit, btnPesquisar, url, modal) {
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
                    formData.append("emp_id", $("#empresa_id").val());
                } else {
                    formData.append("emp_id", $(btnSubmit).data('emp-id'));
                }

                formData.append("cliente_id", $("#cliente_id").val());
                inputElements.forEach(input => {
                    const fieldName = input.name;
                    if (!fieldName) {
                        return;
                    }

                    if (input.type === 'checkbox') {
                        formData.append(fieldName, input.checked ? 'x' : '');
                        return;
                    }

                    if (input.type === 'file') {
                        const file = input.files[0];
                        if (file) {
                            formData.append(fieldName, file, file.name);
                        }
                        return;
                    }

                    const value = $(input).val();

                    if (Array.isArray(value)) {
                        value.forEach(item => formData.append(fieldName + '[]', normalizeFieldValue(input, item ?? '')));
                    } else {
                        formData.append(fieldName, normalizeFieldValue(input, value ?? ''));
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

        loadDatatablePrt: function (dados) {
            gridprotocolo = $('#gridprotocolo').DataTable();
            gridprotocolo.clear().destroy();

            gridprotocolo = $('#gridprotocolo').DataTable({
                data: dados,
                columns: colunas,
                rowId: "protocolo",
                columnDefs: colunasConfiguracao,
                fixedColumns: false,
                info: false,
                searching: false,
                select: {
                    style: 'single'
                },
                lengthChange: false,
                "pageLength": 100,
                lengthMenu: [
                    [10, 50, 100, -1],
                    [10, 50, 100, 'Todos']
                ],
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
                    sLengthMenu: "_MENU_",
                    sLoadingRecords: "Carregando...",
                    sProcessing: "Processando...",
                    sZeroRecords: "Nenhum registro encontrado",
                    sSearch: "",
                    sSearchPlaceholder: "Pesquisar...",
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
                order: [[2, "asc"]],
            });

            gridprotocolo.on('select.dt', function (e, dt, type, indexes) {
                if (type === 'row') {
                    var data = gridprotocolo.rows(indexes).data().toArray();

                    $('#texto_anm').text(data[0].texto_anm);
                    $('#texto_anm').summernote('code', data[0].texto_anm);
                    $('#texto_prt').text(data[0].texto_prt);
                    $('#texto_prt').summernote('code', data[0].texto_prt);
                    $('#texto_prv').text(data[0].texto_prv);
                    $('#texto_prv').summernote('code', data[0].texto_prv);
                    $('#texto_rec').text(data[0].texto_rec);
                    $('#texto_rec').summernote('code', data[0].texto_rec);
                    $('#texto_exm').text(data[0].texto_exm);
                    $('#texto_exm').summernote('code', data[0].texto_exm);
                    $('#texto_atd').text(data[0].texto_atd);
                    $('#texto_atd').summernote('code', data[0].texto_atd);
                    //Receituário
                    $('#rec_medico_nome').html(data[0].medico);
                    $('#rec_crm_medico').html(data[0].crm_medico);
                    //Exames
                    $('#exa_medico_nome').html(data[0].medico);
                    $('#exa_crm_medico').html(data[0].crm_medico);
                    //Atestado
                    $('#atd_medico_nome').html(data[0].medico);
                    $('#atd_crm_medico').html(data[0].crm_medico);

                    $('#listaFotosAnexadas').empty();
                    $('#listaDocsAnexados').empty();

                    var html = '<div class="row">';
                    $.each(data[0].images, function (index, file) {
                        html += `
                            <div class="col-md-1">
                                <a href="/storage/${file.replace('thumbnails', 'images')}" data-toggle="lightbox" data-title="Foto ${index + 1}" data-gallery="gallery">
                                    <img src="/storage/${file}" class="img-fluid mb-2" alt="Foto ${index + 1}"/>
                                </a>
                            </div>
                        `;

                        if ((index + 1) % 10 === 0 && index + 1 !== data[0].images.length) {
                            html += '</div><div class="row">';
                        }
                    });
                    $('#listaFotosAnexadas').html(html);

                    html = '<div class="row">';
                    $.each(data[0].docs, function (index, file) {
                        html += `<div class="col-md-1 p-2">
                                <a href="/storage/${file}" target="_blank" rel="noopener noreferrer">`;

                                if(file.includes('pdf')) {
                                    html += `<i class="fas fa-file-pdf text-secondary" style="font-size: 90px;"></i>`;
                                }else if(file.includes('doc')) {
                                    html += `<i class="fas fa-file-word text-secondary" style="font-size: 90px;"></i>`;
                                }else if(file.includes('xls')) {
                                    html += `<i class="fas fa-file-excel text-secondary" style="font-size: 90px;"></i>`;
                                }else if(file.includes('ppt')) {
                                    html += `<i class="fas fa-file-powerpoint text-secondary" style="font-size: 90px;"></i>`;
                                }else if(file.includes('jpg') || file.includes('jpeg') || file.includes('png')) {
                                    html += `<i class="fas fa-file-image text-secondary" style="font-size: 90px;"></i>`;
                                }else if(file.includes('txt')) {
                                    html += `<i class="fas fa-file-alt text-secondary" style="font-size: 90px;"></i>`;
                                }else {
                                    html += `<i class="fas fa-file text-secondary" style="font-size: 90px;"></i>`;
                                }

                               html += `</a></div>`;

                        if ((index + 1) % 10 === 0 && index + 1 !== data[0].docs.length) {
                            html += '</div><div class="row">';
                        }
                    });
                    $('#listaDocsAnexados').html(html);

                    $('#tabs-anamnese-tab').trigger('click');
                }
            });

            gridprotocolo.on('deselect.dt', function (e, dt, type, indexes) {
                if (type === 'row') {
                    $('#texto_anm').text('');
                    $('#texto_anm').summernote('code', '');
                    $('#texto_prt').text('');
                    $('#texto_prt').summernote('code', '');
                    $('#texto_prv').text('');
                    $('#texto_prv').summernote('code', '');
                    $('#texto_rec').text('');
                    $('#texto_rec').summernote('code', '');
                    $('#texto_exm').text('');
                    $('#texto_exm').summernote('code', '');
                    $('#texto_atd').text('');
                    $('#texto_atd').summernote('code', '');

                    //Receituário
                    $('#rec_medico_nome').html('');
                    $('#rec_crm_medico').html('');
                    //Exames
                    $('#exa_medico_nome').html('');
                    $('#exa_crm_medico').html('');
                    //Atestado
                    $('#atd_medico_nome').html('');
                    $('#atd_crm_medico').html('');


                    $('#listaFotosAnexadas').empty();
                    $('#listaDocsAnexados').empty();

                    $('#tabs-anamnese-tab').trigger('click');

                }
            });

        },
        loadDatatablePrtAjax: function () {

            gridprotocolo = $('#gridprotocolo').DataTable();
            gridprotocolo.clear().destroy();
            var url = "/cliente/obtergridpesquisa-protocolo";

            gridprotocolo = $("#gridprotocolo")
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

                    columns: colunas,
                    rowId: "protocolo",
                    columnDefs: colunasConfiguracao,
                    fixedColumns: false,
                    info: false,
                    searching: false,
                    select: true,
                    lengthChange: false,
                    "pageLength": 100,
                    lengthMenu: [
                        [10, 50, 100, -1],
                        [10, 50, 100, 'Todos']
                    ],
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
                        sLengthMenu: "_MENU_",
                        sLoadingRecords: "Carregando...",
                        sProcessing: "Processando...",
                        sZeroRecords: "Nenhum registro encontrado",
                        sSearch: "",
                        sSearchPlaceholder: "Pesquisar...",
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
                    order: [[2, "asc"]],
                    ajax: {
                        url: url,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: function (d) {
                            console.log(d);
                            const myDiv = document.getElementById('filtro-prontuario');
                            const inputElements = myDiv.querySelectorAll('input, select, textarea,file');
                            var token = $('meta[name="csrf-token"]').attr("content");
                            var formData = new FormData();
                            formData.append("emp_id", $("#empresa_id").val());
                            formData.append("cliente_id", $("#cliente_id").val());
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
                        error: function (xhr, status, error) {
                            Swal.fire(xhr.responseJSON.title, xhr.responseJSON.message, xhr.responseJSON.type);
                            $('.dataTables_empty').text('Nenhum registro encontrado');

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
                        },
                        processData: false, // Essential for FormData
                        contentType: false,  // Essential for FormData
                        serverSide: true,
                        responsive: false,
                    },
                });

            gridprotocolo.on('select.dt', function (e, dt, type, indexes) {
                if (type === 'row') {
                    var data = gridprotocolo.rows({ selected: true }).data().toArray();
                    console.log('Selected rows:', data);

                    $('#texto_anm').text(data[0].texto_anm);
                    $('#texto_anm').summernote('code', data[0].texto_anm);
                    $('#texto_prt').text(data[0].texto_prt);
                    $('#texto_prt').summernote('code', data[0].texto_prt);
                    $('#texto_prv').text(data[0].texto_prv);
                    $('#texto_prv').summernote('code', data[0].texto_prv);
                    $('#texto_rec').text(data[0].texto_rec);
                    $('#texto_rec').summernote('code', data[0].texto_rec);
                    $('#texto_exm').text(data[0].texto_exm);
                    $('#texto_exm').summernote('code', data[0].texto_exm);
                    $('#texto_atd').text(data[0].texto_atd);
                    $('#texto_atd').summernote('code', data[0].texto_atd);

                    //Receituário
                    $('#rec_medico_nome').html(data[0].medico);
                    $('#rec_crm_medico').html(data[0].crm_medico);
                    //Exames
                    $('#exa_medico_nome').html(data[0].medico);
                    $('#exa_crm_medico').html(data[0].crm_medico);
                    //Atestado
                    $('#atd_medico_nome').html(data[0].medico);
                    $('#atd_crm_medico').html(data[0].crm_medico);
                    $('#listaFotosAnexadas').empty();
                    $('#listaDocsAnexados').empty();
                    $('#tabs-anamnese-tab').trigger('click');

                    var html = '<div class="row">';
                    $.each(data[0].images, function (index, file) {
                        html += `
                            <div class="col-md-1">
                                <a href="/storage/${file.replace('thumbnails', 'images')}" data-toggle="lightbox" data-title="Foto ${index + 1}" data-gallery="gallery">
                                    <img src="/storage/${file}" class="img-fluid mb-2" alt="Foto ${index + 1}"/>
                                </a>
                            </div>
                        `;

                        if ((index + 1) % 10 === 0 && index + 1 !== data[0].images.length) {
                            html += '</div><div class="row">';
                        }
                    });
                    $('#listaFotosAnexadas').html(html);

                    html = '<div class="row">';
                    $.each(data[0].docs, function (index, file) {
                        html += `<div class="col-md-1 p-2">
                                <a href="/storage/${file}" target="_blank" rel="noopener noreferrer">`;

                                if(file.includes('pdf')) {
                                    html += `<i class="fas fa-file-pdf text-secondary" style="font-size: 90px;"></i>`;
                                }else if(file.includes('doc')) {
                                    html += `<i class="fas fa-file-word text-secondary" style="font-size: 90px;"></i>`;
                                }else if(file.includes('xls')) {
                                    html += `<i class="fas fa-file-excel text-secondary" style="font-size: 90px;"></i>`;
                                }else if(file.includes('ppt')) {
                                    html += `<i class="fas fa-file-powerpoint text-secondary" style="font-size: 90px;"></i>`;
                                }else if(file.includes('jpg') || file.includes('jpeg') || file.includes('png')) {
                                    html += `<i class="fas fa-file-image text-secondary" style="font-size: 90px;"></i>`;
                                }else if(file.includes('txt')) {
                                    html += `<i class="fas fa-file-alt text-secondary" style="font-size: 90px;"></i>`;
                                }else {
                                    html += `<i class="fas fa-file text-secondary" style="font-size: 90px;"></i>`;
                                }

                               html += `</a></div>`;

                        if ((index + 1) % 10 === 0 && index + 1 !== data[0].docs.length) {
                            html += '</div><div class="row">';
                        }
                    });
                    $('#listaDocsAnexados').html(html);

                    $('#tabs-anamnese-tab').trigger('click');
                }
            });

            gridprotocolo.on('deselect.dt', function (e, dt, type, indexes) {
                if (type === 'row') {
                    $('#texto_anm').text('');
                    $('#texto_anm').summernote('code', '');
                    $('#texto_prt').text('');
                    $('#texto_prt').summernote('code', '');
                    $('#texto_prv').text('');
                    $('#texto_prv').summernote('code', '');
                    $('#texto_rec').text('');
                    $('#texto_rec').summernote('code', '');
                    $('#texto_exm').text('');
                    $('#texto_exm').summernote('code', '');
                    $('#texto_atd').text('');
                    $('#texto_atd').summernote('code', '');

                    //Receituário
                    $('#rec_medico_nome').html('');
                    $('#rec_crm_medico').html('');
                    //Exames
                    $('#exa_medico_nome').html('');
                    $('#exa_crm_medico').html('');
                    //Atestado
                    $('#atd_medico_nome').html('');
                    $('#atd_crm_medico').html('');

                    $('#listaFotosAnexadas').empty();
                    $('#listaDocsAnexados').empty();
                    $('#tabs-anamnese-tab').trigger('click');
                }
            });

        },
        submitForm: function (formId, btnSubmit, btnPesquisar, url, modal) {
            $(btnSubmit).html("<span class=\"spinner-border spinner-border-sm\" role=\"status\" aria-hidden=\"true\"></span> Salvando...");
            $(btnSubmit).desabilitar();
            $.loading();
            try {


                $.ajaxSetup({
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                    },
                });

                const myDiv = document.getElementById(formId);
                const inputElements = myDiv.querySelectorAll('input, select, textarea, file');

                var formData = new FormData();
                formData.append("emp_id", $("#empresa_id").val());

                formData.append("cliente_id", $("#cliente_id").val());
                inputElements.forEach(input => {
                    const fieldName = input.name;
                    if (!fieldName) {
                        return;
                    }

                    if (input.type === 'checkbox') {
                        formData.append(fieldName, input.checked ? 'x' : '');
                        return;
                    }

                    if (input.type === 'file') {
                        const file = input.files[0];
                        if (file) {
                            formData.append(fieldName, file, file.name);
                        }
                        return;
                    }

                    const value = $(input).val();

                    if (Array.isArray(value)) {
                        value.forEach(item => formData.append(fieldName + '[]', normalizeFieldValue(input, item ?? '')));
                    } else {
                        formData.append(fieldName, normalizeFieldValue(input, value ?? ''));
                    }
                });

                // Append files from Dropzone queue manually
                myDropzoneDocs.getFilesWithStatus(Dropzone.ADDED).forEach(function (file) {
                    formData.append("fileDoc[]", file); // Or a specific field name
                });
                myDropzone.getFilesWithStatus(Dropzone.ADDED).forEach(function (file) {
                    formData.append("fotoUpload[]", file); // Or a specific field name
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
                        if (data.redirect_url) {
                            window.location.href = data.redirect_url;
                        } else {
                            $.removeLoading();
                            $(btnSubmit).html('<i class="icon fas fa-save"></i> Salvar');
                            $(btnSubmit).habilitar();
                            $(btnSubmit).attr('data-emp-id', '');
                            $("#" + btnPesquisar).trigger('click');
                            if (modal) {

                                $("#" + modal).modal('hide');
                            }

                            myDropzone.removeAllFiles(true);
                            myDropzoneDocs.removeAllFiles(true);

                            Swal.fire({
                                title: data.title,
                                text: data.text,
                                icon: data.type,
                                showCancelButton: false,
                                allowOutsideClick: false,
                            }).then(function (result) {
                                $.limparBloqueioSairDaTela();
                                location.reload();
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        $.removeLoading();

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

            console.log('gridDataTable', url, id, formId);

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
                    info: true,
                    autoWidth: true,
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
                            formData.append("emp_id", $("#empresa_id").val());
                            formData.append("cliente_id", $("#cliente_id").val());
                            formData.append("_token", token);
                            inputElements.forEach(input => {
                                //formData.append(input.name, input.value);
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
            if (input.name !== 'emp_id') {
                $('#' + input.name).val("");
                $('#' + input.name).prop("checked", false);
                $('#' + input.name).trigger("change");
                $('#' + input.name).habilitar();
                $('#' + input.name).removeClass('is-invalid');
            }
        });
    });

    $('body').on('click', '#btnPesquisarProtocolo', function () {
        $('#gridprotocolo').DataTable().clear().destroy();
        clientejs.loadDatatablePrtAjax();
    });



    $('body').on('click', '#btnAdicionarMed', function () {
        var currentContent = $('#texto_rec').summernote('code');
        var textoToAdd = $('#rec_detalhes_posologia').val();
        if (textoToAdd.trim() === "") {
            return;
        }
        $('#texto_rec').summernote('code', currentContent + textoToAdd + '<br>');

        $('#rec_detalhes_posologia').val("");
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



    $('body').on('click', '#btnSalvarPrt', function (e) {

        e.preventDefault();
        e.stopImmediatePropagation();

        var grid = $("#gridprotocolo");
        var linha = grid.obterLinhaGridItemWithID('gridprotocolo');

        var url = "/cliente/store-prontuario";
        if (linha != null) {
            url = "/cliente/update-prontuario/" + linha.protocolo;
        }

        //formId, btnSubmit, btnPesquisar, URL, Modal
        clientejs.submitForm('formProntuario', this, 'btnPesquisarProtocolo', url, "");

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
            $('#cliente_sts').val('EX');
            $('#cliente_sts').trigger('change');
            $('#btnSalvar').trigger('click');
            $("#btnExcluir").prop('disabled', true);
        });
    });

    function updateClienteStatusButton(status) {
        var $btn = $('#btnInativar');
        if (!$btn.length) {
            return;
        }

        if (status === 'IN' || status === 'EX') {
            $btn.html('<i class="fa fa-check"></i> Ativar');
        } else {
            $btn.html('<i class="fa fa-ban"></i> Inativar');
        }
    }

    $(function () {
        updateClienteStatusButton($('#cliente_sts').val());
        $('#cliente_sts').on('change', function () {
            updateClienteStatusButton($(this).val());
        });
    });

    $('body').on('click', '#btnInativar', function (e) {
        var currentStatus = $('#cliente_sts').val();

        e.preventDefault();
        Pace.restart();
        Pace.track(function () {
            var nextStatus = currentStatus === 'AT' ? 'IN' : 'AT';

            if (currentStatus === 'EX') {
                nextStatus = 'AT';
            }

            $('#cliente_sts').val(nextStatus);
            updateClienteStatusButton(nextStatus);
            $("#btnExcluir").prop('disabled', false);

            $('#cliente_sts').trigger('change');
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

    // DropzoneJS Demo Code Start
    Dropzone.autoDiscover = false

    // Get the template HTML and remove it from the doumenthe template HTML and remove it from the doument
    var previewNode = document.querySelector("#template")
    previewNode.id = ""
    var previewTemplate = previewNode.parentNode.innerHTML
    previewNode.parentNode.removeChild(previewNode)

    var myDropzone = new Dropzone("#dropzone-fotos", { // Make the whole body a dropzone
        url: "#", // Set the url
        //paramName: "fotoUpload",
        thumbnailWidth: 80,
        thumbnailHeight: 80,
        parallelUploads: 10,
        maxFilesize: 5, // MB
        maxFiles: 20,
        previewTemplate: previewTemplate,
        autoQueue: false, // Make sure the files aren't queued until manually added
        autoProcessQueue: false,
        previewsContainer: "#previews", // Define the container to display the previews
        clickable: ".fileinput-button-fotos", // Define the element that should be used as click trigger to select files.
        acceptedFiles: "image/*",
        autoDiscover: false
    })

    myDropzone.on("addedfile", function (file) {
        // Hookup the start button
        //file.previewElement.querySelector(".start").onclick = function () { myDropzone.enqueueFile(file) }
    })

    // Update the total progress bar
    myDropzone.on("totaluploadprogress", function (progress) {
        document.querySelector("#total-progress .progress-bar").style.width = progress + "%"
    })

    myDropzone.on("sending", function (file) {
        // Show the total progress bar when upload starts
        document.querySelector("#total-progress").style.opacity = "1"
        // And disable the start button
        file.previewElement.querySelector(".start").setAttribute("disabled", "disabled")
    })

    // Hide the total progress bar when nothing's uploading anymore
    myDropzone.on("queuecomplete", function (progress) {
        document.querySelector("#total-progress").style.opacity = "0"
    })

    // Setup the buttons for all transfers
    // The "add files" button doesn't need to be setup because the config
    // `clickable` has already been specified.
    // document.querySelector("#actions .start").onclick = function () {
    //     myDropzone.enqueueFiles(myDropzone.getFilesWithStatus(Dropzone.ADDED))
    // }
    document.querySelector("#actions .cancel").onclick = function () {
        myDropzone.removeAllFiles(true)
    }
    // DropzoneJS Fotos Code End

    /////////////////// start DropzoneJS Documentos //////////////////


    // DropzoneJS Demo Code Start
    Dropzone.autoDiscover = false
    // Get the template HTML and remove it from the doumenthe template HTML and remove it from the doument
    var previewNode = document.querySelector("#template-documentos")
    previewNode.id = ""
    var previewTemplate = previewNode.parentNode.innerHTML
    previewNode.parentNode.removeChild(previewNode)

    var myDropzoneDocs = new Dropzone("#dropzone-documentos", { // Make the whole body a dropzone
        url: "#", // Set the url
        thumbnailWidth: 80,
        thumbnailHeight: 80,
        parallelUploads: 20,
        maxFilesize: 5, // MB
        maxFiles: 20,
        previewTemplate: previewTemplate,
        autoQueue: false, // Make sure the files aren't queued until manually added
        autoProcessQueue: false,
        previewsContainer: "#previews-documentos", // Define the container to display the previews
        clickable: ".fileinput-button-documentos", // Define the element that should be used as click trigger to select files.
        acceptedFiles: ".pdf,.xls,.xlsx,.doc,.docx,.ppt,.pptx,.txt",
        autoDiscover: false
    });

    myDropzoneDocs.on("maxfilesexceeded", function (file) {
        Swal.fire({
            title: 'Erro',
            text: "Só é permitido selecinar até 10 arquivos.",
            icon: 'error'
        });
    });

    myDropzoneDocs.on("addedfile", function (file) {
        // Hookup the start button
        //file.previewElement.querySelector(".start").onclick = function () { myDropzoneDocs.enqueueFile(file) }
    })

    // Update the total progress bar
    myDropzoneDocs.on("totaluploadprogress", function (progress) {
        document.querySelector("#total-progress-documentos .progress-bar").style.width = progress + "%"
    })

    myDropzoneDocs.on("sending", function (file) {
        // Show the total progress bar when upload starts
        document.querySelector("#total-progress-documentos").style.opacity = "1"
        // And disable the start button
        file.previewElement.querySelector(".start").setAttribute("disabled", "disabled")
    })

    // Hide the total progress bar when nothing's uploading anymore
    myDropzoneDocs.on("queuecomplete", function (progress) {
        document.querySelector("#total-progress-documentos").style.opacity = "0"
    })

    // Setup the buttons for all transfers
    // The "add files" button doesn't need to be setup because the config
    // `clickable` has already been specified.
    // document.querySelector("#actions .start").onclick = function () {
    //     myDropzoneDocs.enqueueFiles(myDropzoneDocs.getFilesWithStatus(Dropzone.ADDED))
    // }
    document.querySelector("#actions-documentos .cancel").onclick = function () {
        myDropzoneDocs.removeAllFiles(true)
    }
    // DropzoneJS Demo Code End
});
