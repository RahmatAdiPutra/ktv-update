(function (w) {
    'use strict';

    var $ = w.jQuery;
    var baseUrl = $('base').attr('href');
    var $formPlaylistSong = $('#form-playlist-song');
    var $formPlaylistModal = $('#form-playlist-modal');
    let data = {};

    var optionsNotif = {
        style: 'bar',
        message: '',
        position: 'top',
        type: 'success',
        timeout: 4000,
        showClose: true
    };

    var dataTableOptionsPlaylist = {
        ajax: {
            url: baseUrl + 'web/playlist/data'
        },
        order: [
            [1, 'asc']
        ],
        dom: '<"toolbar">frtip',
        columns: [
            {
                orderable: false,
                mRender: function (data, type, row) {
                    return `
                        <a href='#' data-id='${row.id}' id='delete-playlist' data-target='#modalConfirm' data-toggle='modal'>
                        <i class='fa fa-trash-o' aria-hidden='true' style='font-size:24px;color:red'></i>
                        </a>
                    `;
                }
            },
            {
                data: 'name',
                name: 'name'
            },
            {
                orderable: false,
                data: 'category.name',
                name: 'category'
            },
            {
                orderable: false,
                data: 'songs.length',
                name: 'category'
            }
        ]
    };

    var dataTableOptionsSong = {
        ajax: {
            url: baseUrl + 'web/song/data'
        },
        order: [
            [1, 'asc']
        ],
        columns: [
            {
                orderable: false,
                mRender: function (data, type, row) {
                    return `
                        <i class='fa fa-plus' aria-hidden='true' style='font-size:24px;'></i>
                    `;
                }
            },
            {
                data: 'title',
                name: 'title'
            },
            {
                data: 'artist_label',
                name: 'artists'
            }
        ]
    };

    var dataTableOptionsPlaylistSong = {
        bPaginate: false,
        ordering: false,
        bInfo: false,
        searching: false,
        rowId: 'id',
        oLanguage: {
            sZeroRecords: false,
            sEmptyTable: false
        },
        columns: [
            {
                orderable: false,
                mRender: function (data, type, row) {
                    return `
                        <i class='fa fa-trash-o' aria-hidden='true' style='font-size:24px;color:red'></i>
                    `;
                }
            },
            {
                data: 'title',
                name: 'title'
            },
            {
                data: 'artist_label',
                name: 'artists'
            }
        ]
    }

    var tablePlaylist = $('#tablePlaylist').DataTable(
        $.extend(true, window.dataTableDefaultOptions, dataTableOptionsPlaylist)
    );

    var tableSong = $('#tableSong').DataTable(
        $.extend(true, window.dataTableDefaultOptions1, dataTableOptionsSong)
    );

    var tablePlaylistSong = $('#tablePlaylistSong').DataTable(dataTableOptionsPlaylistSong);

    clearFormPlaylist()

    $('#tablePlaylist_wrapper div.toolbar').html(
        `<button class="btn btn-complete btn-cons" id='add-playlist' data-target='#modalFormPlaylist' data-toggle='modal'>
            New Playlist
        </button>`
    );

    $formPlaylistModal.on('submit', savePlaylist);
    $formPlaylistSong.on('submit', savePlaylistSong);

    $('#add-playlist').on('click', openModalPlaylist);
    $('#tablePlaylist tbody').on('click', 'td', selectPlaylist);
    $('#tablePlaylist tbody').on('click', '#delete-playlist', deletePlaylist);
    $('#tableSong tbody').on('click', 'td', addSongToPlaylist);
    $('#tablePlaylistSong tbody').on('click', 'td', deleteSongFromPlaylist);
    $('#modalConfirm').on('click', '#confirm', confirm);
    $('#clear-form-playlist').on('click', clearFormPlaylist);

    $('#tablePlaylistSong tbody').sortable();

    $('#save-playlist-song').on('click', function() {
        if (data.internal) {
            $formPlaylistSong.find('button').trigger('click');
        }
    });

    $('#modalPriviewVideo').on('hidden.bs.modal', function () {
        $('video').attr('src', '');
    });

    $('#add-to-playlist').on('click', function() {
        add(data.song);
        $('#modalPriviewVideo').modal('hide');
    });

    function selectPlaylist(event) {
        event.preventDefault();
        var tb = tablePlaylist.cell( this ).index();
        if (tb.column === 0) {
            return;
        }
        clearFormPlaylist();
        data.internal = tablePlaylist.row(tb.row).data();
        $formPlaylistSong.find('#id').val(data.internal.id);
        $formPlaylistSong.find('#name').val(data.internal.name);
        selectCategory(data.internal.playlist_category_id);
        getPlaylist(data.internal.songs);
    }

    function getPlaylist(songs) {
        data.playlists = songs;
        tablePlaylistSong.clear().rows.add(data.playlists).draw();
    }

    function addSongToPlaylist(event) {
        event.preventDefault();
        if (typeof data.internal === 'undefined') {return;}
        var tb = tableSong.cell( this ).index();
        data.song = tableSong.row(tb.row).data();
        if (tb.column === 0) {
            add(data.song);
        }
        if (tb.column === 1) {
            $('video').attr('src', `${KTV_SERVER}${data.song.file_path}`);
            $('#modalPriviewVideo').modal('show');
        }
        if (tb.column === 2) {
            tableSong.search( data.song.artist_label ).draw();
        }
    }

    function add(songs) {
        data.playlists.push(songs);
        tablePlaylistSong.clear().rows.add(data.playlists).draw();
    }

    function deleteSongFromPlaylist(event) {
        event.preventDefault();
        var tb = tablePlaylistSong.cell( this ).index();
        if (tb.column === 0) {
            tablePlaylistSong.row( tb.row ).remove().draw();
        }
    }

    function openModalPlaylist(event) {
        event.preventDefault();
        $formPlaylistModal.find('#playlist_category_id').select2({
            placeholder: 'Select a category',
            data: allData.playlist_category
        });
    }

    function savePlaylist(event) {
        event.preventDefault();
        var formData = new FormData($formPlaylistModal[0]);
        formData.append('name',$formPlaylistModal.find('#name').val());
        formData.append('playlist_category_id',$formPlaylistModal.find('#playlist_category_id').val());
        $.ajax({
            method: 'POST',
            dataType: 'json',
            url: baseUrl + 'web/playlist',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                $('.modal').modal('hide');
                optionsNotif.message = response.payloads.message;
                $('.notif').pgNotification(optionsNotif).show();
                tablePlaylist.ajax.reload(null, false);
                clearFormPlaylist();
            },
            error: function (response) {}
        });
    }

    function savePlaylistSong(event) {
        event.preventDefault();
        var songs = $('#tablePlaylistSong tbody').sortable('toArray');
        // data.playlists = tablePlaylistSong.rows().data().toArray();
        // console.log(songs);
        // console.log(data.playlists);
        // return false;
        data.formData = {
            id: $formPlaylistSong.find('#id').val(),
            name: $formPlaylistSong.find('#name').val(),
            playlist_category_id: $formPlaylistSong.find('#playlist_category_id').val(),
            songs: songs
        };
        $.ajax({
            method: 'POST',
            dataType: 'json',
            url: baseUrl + 'web/playlist',
            data: data.formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                $('.modal').modal('hide');
                optionsNotif.message = response.payloads.message;
                $('.notif').pgNotification(optionsNotif).show();
                tablePlaylist.ajax.reload(null, false);
                clearFormPlaylist();
            },
            error: function (response) {}
        });
    }

    function deletePlaylist(event) {
        event.preventDefault();
        data.id = $(this).attr('data-id');
    }

    function confirm(event) {
        event.preventDefault();
        $.ajax({
            method: 'DELETE',
            dataType: 'json',
            url: baseUrl + 'web/playlist/' + data.id,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                $('.modal').modal('hide');
                optionsNotif.message = response.payloads.message;
                $('.notif').pgNotification(optionsNotif).show();
                tablePlaylist.ajax.reload(null, false);
                clearFormPlaylist();
            },
            error: function (response) {}
        });
    }

    function clearFormPlaylist() {
        data = {};
        $($('#tablePlaylistSong tbody').children()).remove();
        $formPlaylistModal.find('#name').val('');
        $formPlaylistModal.find('#playlist_category_id').val('').trigger('change');
        $formPlaylistSong.find('#name').val('');
        $formPlaylistSong.find('#playlist_category_id').val('').trigger('change');
    }

    function selectCategory(id) {
        $('#playlist_category_id').select2({
            placeholder: 'Select a category',
            data: allData.playlist_category
        });
        $('#playlist_category_id').val(id).trigger('change');
    }
})(window);
