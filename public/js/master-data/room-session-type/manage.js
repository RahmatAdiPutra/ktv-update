(function (w) {
    "use strict";

    var $ = w.jQuery;
    var $formRoomSessionType = $("#form-room-session-type");
    var $modalFormRoomSessionType = $('#modalFormRoomSessionType');
    // var baseUrl = $("base").attr("href");
    var baseUrl = "http://localhost/1001/ktv/";
    var dataUrl = baseUrl + "master-data/room/session-type/data";

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
                data: "timer_countdown",
                name: "timer_countdown"
            },
            {
                data: "count_song_played",
                name: "count_song_played"
            },
            {
                orderable: false,
                mRender: function (data, type, row) {
                    return `
                        <a href="#" data-id="${row.id}" id="edit-room-session-type" data-target="#modalFormRoomSessionType" data-toggle="modal">
                        <i class="fa fa-pencil-square-o" aria-hidden="true" style="font-size:24px"></i>
                        </a>
                        <a href="#" data-id="${row.id}" id="delete-room-session-type" data-target="#modalConfirm" data-toggle="modal">
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

    $('[data-target="#modalFormRoomSessionType"]').on('click', clearFormRoomSessionType);

    $('#modalConfirm').on('click', 'button', confirm);

    // handle save data
    $formRoomSessionType.on("submit", saveRoomSessionType);

    // handle edit data
    $('#detailedTable tbody').on('click', 'a[id="edit-room-session-type"]', editRoomSessionType);

    // handle delete data
    $('#detailedTable tbody').on('click', 'a[id="delete-room-session-type"]', deleteRoomSessionType);

    function editRoomSessionType(evt) {
        var id = $(this).attr("data-id");
        $.ajax({
            method: "GET",
            dataType: "json",
            url: baseUrl + "master-data/room/session-type/" + id,
            success: function (response) {
                $modalFormRoomSessionType.find('#id').val(response.payloads.id);
                $modalFormRoomSessionType.find('#name').val(response.payloads.name);
                $modalFormRoomSessionType.find('#timer_countdown').val(response.payloads.timer_countdown);
                $modalFormRoomSessionType.find('#count_song_played').val(response.payloads.count_song_played);
            },
            error: function (response) {}
        });
        evt.preventDefault();
    }

    function saveRoomSessionType(evt) {
        var formData = $formRoomSessionType.serializeArray();
        $.ajax({
            method: "POST",
            dataType: "json",
            url: baseUrl + "master-data/room/session-type",
            data: formData,
            success: function (response) {
                $(".modal").modal("hide");
                table.ajax.url(dataUrl).load();
                clearFormRoomSessionType();
                optionsNotif.message = response.payloads.message;
                $('.notif').pgNotification(optionsNotif).show();
            },
            error: function (response) {}
        });
        evt.preventDefault();
        return false;
    }

    function deleteRoomSessionType(evt) {
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
                url: baseUrl + "master-data/room/session-type/" + id,
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

    function clearFormRoomSessionType() {
        $modalFormRoomSessionType.find('#id').val("");
        $modalFormRoomSessionType.find('#name').val("");
        $modalFormRoomSessionType.find('#timer_countdown').val("");
        $modalFormRoomSessionType.find('#count_song_played').val("");
    }
})(window);
