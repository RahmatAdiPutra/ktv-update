(function (w) {
    "use strict";

    var $ = w.jQuery;
    var $formOperator = $("#form-operator");
    var $modalFormOperator = $('#modalFormOperator');
    // var baseUrl = $("base").attr("href");
    var baseUrl = "http://localhost/1001/ktv/";
    var dataUrl = baseUrl + "master-data/operator/data";

    var optionsNotif = {
        style: 'bar',
        message: '',
        position: 'top',
        type: 'success',
        timeout: 4000,
        showClose: true
    }

    var dataTableOptions = {
        ajax: {
            url: dataUrl
        },
        order: [
            [1, "asc"]
        ],
        columns: [
            {
                orderable: false,
                "render": function ( data, type, full, meta ) {
                    return  meta.row + 1;
                }
            },
            {
                data: "ip_address",
                name: "ip_address"
            },
            {
                data: "name",
                name: "name"
            },
            {
                orderable: false,
                mRender: function (data, type, row) {
                    return `
                        <a href="#" data-id="${row.id}" id="edit-operator" data-target="#modalFormOperator" data-toggle="modal">
                        <i class="fa fa-pencil-square-o" aria-hidden="true" style="font-size:24px"></i>
                        </a>
                        <a href="#" data-id="${row.id}" id="delete-operator" data-target="#modalConfirm" data-toggle="modal">
                        <i class="fa fa-trash-o" aria-hidden="true" style="font-size:24px;color:red"></i>
                        </a>
                    `;
                }
            },
        ]
    };

    var table = $("#detailedTable").DataTable(
        $.extend(true, window.dataTableDefaultOptions, dataTableOptions)
    );

    $('[data-target="#modalFormOperator"]').on('click', clearFormOperator);

    $('#modalConfirm').on('click', 'button', confirm);

    // handle save data
    $formOperator.on("submit", saveOperator);

    // handle edit data
    $('#detailedTable tbody').on('click', 'a[id="edit-operator"]', editOperator);

    // handle delete data
    $('#detailedTable tbody').on('click', 'a[id="delete-operator"]', deleteOperator);

    function editOperator(evt) {
        var id = $(this).attr("data-id");
        $.ajax({
            method: "GET",
            dataType: "json",
            url: baseUrl + "master-data/operator/" + id,
            success: function (response) {
                $modalFormOperator.find('#id').val(response.payloads.id);
                $modalFormOperator.find('#ip_address').val(response.payloads.ip_address);
                $modalFormOperator.find('#name').val(response.payloads.name);
            },
            error: function (response) {}
        });
        evt.preventDefault();
    }

    function saveOperator(evt) {
        var formData = $formOperator.serializeArray();
        $.ajax({
            method: "POST",
            dataType: "json",
            url: baseUrl + "master-data/operator",
            data: formData,
            success: function (response) {
                $(".modal").modal("hide");
                table.ajax.url(dataUrl).load();
                clearFormOperator();
                optionsNotif.message = response.payloads.message;
                $('.notif').pgNotification(optionsNotif).show();
            },
            error: function (response) {}
        });
        evt.preventDefault();
        return false;
    }

    function deleteOperator(evt) {
        var id = $(this).attr("data-id");
        $("#modalConfirm").data("id",id);
        evt.preventDefault();
    }

    function confirm(evt) {
        var id = $("#modalConfirm").data("id");
        if ($(this).text() == 'Yes') {
            $.ajax({
                method: "DELETE",
                dataType: "json",
                url: baseUrl + "master-data/operator/" + id,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    $(".modal").modal("hide");
                    table.ajax.url(dataUrl).load();
                    optionsNotif.message = response.payloads.message;
                    $('.notif').pgNotification(optionsNotif).show();
                },
                error: function (response) {}
            });
        }
        evt.preventDefault();
        return false;
    }

    function clearFormOperator() {
        $modalFormOperator.find('#id').val("");
        $modalFormOperator.find('#ip_address').val("");
        $modalFormOperator.find('#name').val("");
    }
})(window);
