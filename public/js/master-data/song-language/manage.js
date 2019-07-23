(function (w) {
    "use strict";

    var $ = w.jQuery;
    var $formSongLanguage = $("#form-song-language");
    var $modalFormSongLanguage = $('#modalFormSongLanguage');
    // var baseUrl = $("base").attr("href");
    var baseUrl = "http://localhost/1001/ktv/";
    var dataUrl = baseUrl + "master-data/song/language/data";

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
                        <a href="#" data-id="${row.id}" id="edit-song-language" data-target="#modalFormSongLanguage" data-toggle="modal">
                        <i class="fa fa-pencil-square-o" aria-hidden="true" style="font-size:24px"></i>
                        </a>
                        <a href="#" data-id="${row.id}" id="delete-song-language" data-target="#modalConfirm" data-toggle="modal">
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

    $('[data-target="#modalFormSongLanguage"]').on('click', clearFormSongLanguage);

    $('#modalConfirm').on('click', 'button', confirm);

    // handle save data
    $formSongLanguage.on("submit", saveSongLanguage);

    // handle edit data
    $('#detailedTable tbody').on('click', 'a[id="edit-song-language"]', editSongLanguage);

    // handle delete data
    $('#detailedTable tbody').on('click', 'a[id="delete-song-language"]', deleteSongLanguage);

    function editSongLanguage(evt) {
        var id = $(this).attr("data-id");
        $.ajax({
            method: "GET",
            dataType: "json",
            url: baseUrl + "master-data/song/language/" + id,
            success: function (response) {
                $modalFormSongLanguage.find('#id').val(response.payloads.id);
                $modalFormSongLanguage.find('#name').val(response.payloads.name);
            },
            error: function (response) {}
        });
        evt.preventDefault();
    }

    function saveSongLanguage(evt) {
        var formData = $formSongLanguage.serializeArray();
        $.ajax({
            method: "POST",
            dataType: "json",
            url: baseUrl + "master-data/song/language",
            data: formData,
            success: function (response) {
                $(".modal").modal("hide");
                table.ajax.url(dataUrl).load();
                clearFormSongLanguage();
                optionsNotif.message = response.payloads.message;
                $('.notif').pgNotification(optionsNotif).show();
            },
            error: function (response) {}
        });
        evt.preventDefault();
        return false;
    }

    function deleteSongLanguage(evt) {
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
                url: baseUrl + "master-data/song/language/" + id,
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

    function clearFormSongLanguage() {
        $modalFormSongLanguage.find('#id').val("");
        $modalFormSongLanguage.find('#name').val("");
    }
})(window);
