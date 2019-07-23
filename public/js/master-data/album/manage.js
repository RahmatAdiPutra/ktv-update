(function (w) {
    "use strict";

    var $ = w.jQuery;
    var $formAlbum = $("#form-album");
    var $modalFormAlbum = $('#modalFormAlbum');
    // var baseUrl = $("base").attr("href");
    var baseUrl = "http://localhost/1001/ktv/";
    var dataUrl = baseUrl + "master-data/album/data";

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
                data: "release_date",
                name: "release_date",
                render: function (data, type, row) {
                    return moment(data).format('DD MMMM YYYY');
                }
            },
            {
                data: "cover_art",
                name: "cover_art"
            },
            {
                data: "code",
                name: "code"
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
                        <a href="#" data-id="${row.id}" id="edit-album" data-target="#modalFormAlbum" data-toggle="modal">
                        <i class="fa fa-pencil-square-o" aria-hidden="true" style="font-size:24px"></i>
                        </a>
                        <a href="#" data-id="${row.id}" id="delete-album" data-target="#modalConfirm" data-toggle="modal">
                        <i class="fa fa-trash-o" aria-hidden="true" style="font-size:24px;color:red"></i>
                        </a>
                    `;
                }
            },
        ]
    };

    $('#myDatepicker').datepicker({
        format: 'yyyy-mm-dd'
    });

    var table = $("#detailedTable").DataTable(
        $.extend(true, window.dataTableDefaultOptions, dataTableOptions)
    );

    $('[data-target="#modalFormAlbum"]').on('click', clearFormAlbum);

    $('#modalConfirm').on('click', 'button', confirm);

    // handle save data
    $formAlbum.on("submit", saveAlbum);

    // handle edit data
    $('#detailedTable tbody').on('click', 'a[id="edit-album"]', editAlbum);

    // handle delete data
    $('#detailedTable tbody').on('click', 'a[id="delete-album"]', deleteAlbum);

    function editAlbum(evt) {
        var id = $(this).attr("data-id");
        $.ajax({
            method: "GET",
            dataType: "json",
            url: baseUrl + "master-data/album/" + id,
            success: function (response) {
                $modalFormAlbum.find('#id').val(response.payloads.id);
                $modalFormAlbum.find('#title').val(response.payloads.title);
                $modalFormAlbum.find('#release_date').val(moment(response.payloads.release_date).format('YYYY-MM-DD'));
                $modalFormAlbum.find('#cover_art').val(response.payloads.cover_art);
                $modalFormAlbum.find('#code').val(response.payloads.code);
            },
            error: function (response) {}
        });
        evt.preventDefault();
    }

    function saveAlbum(evt) {
        var formData = $formAlbum.serializeArray();
        $.ajax({
            method: "POST",
            dataType: "json",
            url: baseUrl + "master-data/album",
            data: formData,
            success: function (response) {
                $(".modal").modal("hide");
                table.ajax.url(dataUrl).load();
                clearFormAlbum();
                optionsNotif.message = response.payloads.message;
                $('.notif').pgNotification(optionsNotif).show();
            },
            error: function (response) {}
        });
        evt.preventDefault();
        return false;
    }

    function deleteAlbum(evt) {
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
                url: baseUrl + "master-data/album/" + id,
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

    function clearFormAlbum() {
        $modalFormAlbum.find('#id').val("");
        $modalFormAlbum.find('#title').val("");
        $modalFormAlbum.find('#release_date').val("");
        $modalFormAlbum.find('#cover_art').val("");
        $modalFormAlbum.find('#code').val("");
    }
})(window);
