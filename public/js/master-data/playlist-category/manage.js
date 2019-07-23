(function (w) {
    "use strict";

    var $ = w.jQuery;
    var $formPlaylistCategory = $("#form-playlist-category");
    var $modalFormPlaylistCategory = $('#modalFormPlaylistCategory');
    // var baseUrl = $("base").attr("href");
    var baseUrl = "http://localhost/1001/ktv/";
    var dataUrl = baseUrl + "master-data/playlist-category/data";

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
                orderable: false,
                mRender: function (data, type, row) {
                    return `
                        <a href="#" data-id="${row.id}" id="edit-playlist-category" data-target="#modalFormPlaylistCategory" data-toggle="modal">
                        <i class="fa fa-pencil-square-o" aria-hidden="true" style="font-size:24px"></i>
                        </a>
                        <a href="#" data-id="${row.id}" id="delete-playlist-category" data-target="#modalConfirm" data-toggle="modal">
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

    $('[data-target="#modalFormPlaylistCategory"]').on('click', clearFormPlaylistCategory);

    $('#modalConfirm').on('click', 'button', confirm);

    // handle save data
    $formPlaylistCategory.on("submit", savePlaylistCategory);

    // handle edit data
    $('#detailedTable tbody').on('click', 'a[id="edit-playlist-category"]', editPlaylistCategory);

    // handle delete data
    $('#detailedTable tbody').on('click', 'a[id="delete-playlist-category"]', deletePlaylistCategory);

    function editPlaylistCategory(evt) {
        var id = $(this).attr("data-id");
        $.ajax({
            method: "GET",
            dataType: "json",
            url: baseUrl + "master-data/playlist-category/" + id,
            success: function (response) {
                $modalFormPlaylistCategory.find('#id').val(response.payloads.id);
                $modalFormPlaylistCategory.find('#name').val(response.payloads.name);
            },
            error: function (response) {}
        });
        evt.preventDefault();
    }

    function savePlaylistCategory(evt) {
        var formData = $formPlaylistCategory.serializeArray();
        $.ajax({
            method: "POST",
            dataType: "json",
            url: baseUrl + "master-data/playlist-category",
            data: formData,
            success: function (response) {
                $(".modal").modal("hide");
                table.ajax.url(dataUrl).load();
                clearFormPlaylistCategory();
                optionsNotif.message = response.payloads.message;
                $('.notif').pgNotification(optionsNotif).show();
            },
            error: function (response) {}
        });
        evt.preventDefault();
        return false;
    }

    function deletePlaylistCategory(evt) {
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
                url: baseUrl + "master-data/playlist-category/" + id,
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

    function clearFormPlaylistCategory() {
        $modalFormPlaylistCategory.find('#id').val("");
        $modalFormPlaylistCategory.find('#name').val("");
    }
})(window);
