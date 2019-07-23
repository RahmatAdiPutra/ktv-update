(function (w) {
    "use strict";

    var $ = w.jQuery;
    var $formRoomStatus = $("#form-room-status");
    var $modalFormRoomStatus = $('#modalFormRoomStatus');
    // var baseUrl = $("base").attr("href");
    var baseUrl = "http://localhost/1001/ktv/";
    var dataUrl = baseUrl + "master-data/room/status/data";

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
                data: "color",
                name: "color"
            },
            {
                orderable: false,
                mRender: function (data, type, row) {
                    return `
                        <a href="#" data-id="${row.id}" id="edit-room-status" data-target="#modalFormRoomStatus" data-toggle="modal">
                        <i class="fa fa-pencil-square-o" aria-hidden="true" style="font-size:24px"></i>
                        </a>
                        <a href="#" data-id="${row.id}" id="delete-room-status" data-target="#modalConfirm" data-toggle="modal">
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

    $('[data-target="#modalFormRoomStatus"]').on('click', clearFormRoomStatus);

    $('#modalConfirm').on('click', 'button', confirm);

    // handle save data
    $formRoomStatus.on("submit", saveRoomStatus);

    // handle edit data
    $('#detailedTable tbody').on('click', 'a[id="edit-room-status"]', editRoomStatus);

    // handle delete data
    $('#detailedTable tbody').on('click', 'a[id="delete-room-status"]', deleteRoomStatus);

    function editRoomStatus(evt) {
        var id = $(this).attr("data-id");
        $.ajax({
            method: "GET",
            dataType: "json",
            url: baseUrl + "master-data/room/status/" + id,
            success: function (response) {
                $modalFormRoomStatus.find('#id').val(response.payloads.id);
                $modalFormRoomStatus.find('#name').val(response.payloads.name);
                $modalFormRoomStatus.find('#color').val(response.payloads.color);
            },
            error: function (response) {}
        });
        evt.preventDefault();
    }

    function saveRoomStatus(evt) {
        var formData = $formRoomStatus.serializeArray();
        $.ajax({
            method: "POST",
            dataType: "json",
            url: baseUrl + "master-data/room/status",
            data: formData,
            success: function (response) {
                $(".modal").modal("hide");
                table.ajax.url(dataUrl).load();
                clearFormRoomStatus();
                optionsNotif.message = response.payloads.message;
                $('.notif').pgNotification(optionsNotif).show();
            },
            error: function (response) {}
        });
        evt.preventDefault();
        return false;
    }

    function deleteRoomStatus(evt) {
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
                url: baseUrl + "master-data/room/status/" + id,
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

    function clearFormRoomStatus() {
        $modalFormRoomStatus.find('#id').val("");
        $modalFormRoomStatus.find('#name').val("");
        $modalFormRoomStatus.find('#color').val("");
    }
})(window);
