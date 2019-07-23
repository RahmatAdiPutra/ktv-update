(function (w) {
    "use strict";

    var $ = w.jQuery;
    var $formSongRequest = $("#form-song-request");
    var $modalFormSongRequest = $('#modalFormSongRequest');
    // var baseUrl = $("base").attr("href");
    var baseUrl = "http://localhost/1001/ktv/";
    var dataUrl = baseUrl + "master-data/song/request/data";

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
                data: "title",
                name: "title"
            },
            {
                data: "artist",
                name: "artist"
            },
            {
                data: "processed",
                name: "processed"
            },
            {
                data: 'updated_at',
                name: 'updated_at',
                orderable: false,
                render: function (data, type, row) {
                  return moment(data).format('DD MMMM YYYY');
                }
            },
            {
                orderable: false,
                mRender: function (data, type, row) {
                    return `
                        <a href="#" data-id="${row.id}" id="edit-song-request" data-target="#modalFormSongRequest" data-toggle="modal">
                        <i class="fa fa-pencil-square-o" aria-hidden="true" style="font-size:24px"></i>
                        </a>
                        <a href="#" data-id="${row.id}" id="delete-song-request" data-target="#modalConfirm" data-toggle="modal">
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

    $('[data-target="#modalFormSongRequest"]').on('click', clearFormSongRequest);

    $('#modalConfirm').on('click', 'button', confirm);

    // handle save data
    $formSongRequest.on("submit", saveSongRequest);

    // handle edit data
    $('#detailedTable tbody').on('click', 'a[id="edit-song-request"]', editSongRequest);

    // handle delete data
    $('#detailedTable tbody').on('click', 'a[id="delete-song-request"]', deleteSongRequest);

    function editSongRequest(evt) {
        var id = $(this).attr("data-id");
        $.ajax({
            method: "GET",
            dataType: "json",
            url: baseUrl + "master-data/song/request/" + id,
            success: function (response) {
                $modalFormSongRequest.find('#id').val(response.payloads.id);
                $modalFormSongRequest.find('#title').val(response.payloads.title);
                $modalFormSongRequest.find('#artist').val(response.payloads.artist);
                $modalFormSongRequest.find('#processed').val(response.payloads.processed);
            },
            error: function (response) {}
        });
        evt.preventDefault();
    }

    function saveSongRequest(evt) {
        var formData = $formSongRequest.serializeArray();
        $.ajax({
            method: "POST",
            dataType: "json",
            url: baseUrl + "master-data/song/request",
            data: formData,
            success: function (response) {
                $(".modal").modal("hide");
                table.ajax.url(dataUrl).load();
                clearFormSongRequest();
                optionsNotif.message = response.payloads.message;
                $('.notif').pgNotification(optionsNotif).show();
            },
            error: function (response) {}
        });
        evt.preventDefault();
        return false;
    }

    function deleteSongRequest(evt) {
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
                url: baseUrl + "master-data/song/request/" + id,
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

    function clearFormSongRequest() {
        $modalFormSongRequest.find('#id').val("");
        $modalFormSongRequest.find('#title').val("");
        $modalFormSongRequest.find('#artist').val("");
        $modalFormSongRequest.find('#processed').val("");
    }
})(window);
