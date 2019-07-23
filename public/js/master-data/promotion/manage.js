(function (w) {
    "use strict";

    var $ = w.jQuery;
    var $formPromotion = $("#form-promotion");
    var $modalFormPromotion = $('#modalFormPromotion');
    // var baseUrl = $("base").attr("href");
    var baseUrl = "http://localhost/1001/ktv/";
    var dataUrl = baseUrl + "master-data/promotion/data";

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
                data: "name",
                name: "name"
            },
            {
                data: "src",
                name: "src"
            },
            {
                data: 'start_date',
                name: 'start_date',
                render: function (data, type, row) {
                  return moment(data).format('DD MMMM YYYY');
                }
            },
            {
                data: 'end_date',
                name: 'end_date',
                render: function (data, type, row) {
                  return moment(data).format('DD MMMM YYYY');
                }
            },
            {
                orderable: false,
                mRender: function (data, type, row) {
                    return `
                        <a href="#" data-id="${row.id}" id="edit-promotion" data-target="#modalFormPromotion" data-toggle="modal">
                        <i class="fa fa-pencil-square-o" aria-hidden="true" style="font-size:24px"></i>
                        </a>
                        <a href="#" data-id="${row.id}" id="delete-promotion" data-target="#modalConfirm" data-toggle="modal">
                        <i class="fa fa-trash-o" aria-hidden="true" style="font-size:24px;color:red"></i>
                        </a>
                    `;
                }
            },
        ]
    };

    $('#datepicker-range').datepicker({
        format: 'yyyy-mm-dd'
    });

    var table = $("#detailedTable").DataTable(
        $.extend(true, window.dataTableDefaultOptions, dataTableOptions)
    );

    $('[data-target="#modalFormPromotion"]').on('click', clearFormPromotion);

    $('#modalConfirm').on('click', 'button', confirm);

    // handle save data
    $formPromotion.on("submit", savePromotion);

    // handle edit data
    $('#detailedTable tbody').on('click', 'a[id="edit-promotion"]', editPromotion);

    // handle delete data
    $('#detailedTable tbody').on('click', 'a[id="delete-promotion"]', deletePromotion);

    function editPromotion(evt) {
        var id = $(this).attr("data-id");
        $.ajax({
            method: "GET",
            dataType: "json",
            url: baseUrl + "master-data/promotion/" + id,
            success: function (response) {
                $modalFormPromotion.find('#id').val(response.payloads.id);
                $modalFormPromotion.find('#name').val(response.payloads.name);
                $modalFormPromotion.find('#src').val(response.payloads.src);
                $modalFormPromotion.find('#start_date').val(moment(response.payloads.start_date).format('YYYY-MM-DD'));
                $modalFormPromotion.find('#end_date').val(moment(response.payloads.end_date).format('YYYY-MM-DD'));
            },
            error: function (response) {}
        });
        evt.preventDefault();
    }

    function savePromotion(evt) {
        var formData = $formPromotion.serializeArray();
        $.ajax({
            method: "POST",
            dataType: "json",
            url: baseUrl + "master-data/promotion",
            data: formData,
            success: function (response) {
                $(".modal").modal("hide");
                table.ajax.url(dataUrl).load();
                clearFormPromotion();
                optionsNotif.message = response.payloads.message;
                $('.notif').pgNotification(optionsNotif).show();
            },
            error: function (response) {}
        });
        evt.preventDefault();
        return false;
    }

    function deletePromotion(evt) {
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
                url: baseUrl + "master-data/promotion/" + id,
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

    function clearFormPromotion() {
        $modalFormPromotion.find('#id').val("");
        $modalFormPromotion.find('#name').val("");
        $modalFormPromotion.find('#src').val("");
        $modalFormPromotion.find('#start_date').val("");
        $modalFormPromotion.find('#end_date').val("");
    }
})(window);
