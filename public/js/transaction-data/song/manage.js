(function (w) {
    "use strict";

    var $ = w.jQuery;
    var $formSong = $("#form-song");
    var $video = $('#video');
    var baseUrl = $("base").attr("href");
    var dataUrl = baseUrl + "web/song/data";
    let data = {};

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
                data: "artist_label",
                name: "artists"
            },
            {
                orderable: false,
                mRender: function (data, type, row) {
                    return `
                        ${row.cover_art ? '<i class="fa fa-check" aria-hidden="true" style="font-size:24px"></i>' : ''}
                    `;
                }
            }
        ]
    };

    var table = $("#detailedTable").DataTable(
        $.extend(true, window.dataTableDefaultOptions, dataTableOptions)
    );

    $('#detailedTable tbody').on('click', 'tr', selectSong);
    $('#spotifyTable tbody').on('click', 'tr', checkedSong);
    $('#form-song div').on('change', 'input', getSpotify);
    $('#save-song').on("click", saveSong);
    $('#jumppage').on('change', jumpToPage);

    function jumpToPage(evt) {
        evt.preventDefault();
        var info = table.page.info();
        var page = $('#jumppage').val();
        if (page > 0 && page <= info.pages) {
            page = page - 1;
            table.page(parseInt(page)).draw(false);
        }
    }

    function selectSong(evt) {
        evt.preventDefault();
        clearForm();
        data.internal = table.row(this).data();
        $('video').attr('src', `${KTV_SERVER}${data.internal.file_path}`);
        $formSong.find('#title').val(data.internal.title);
        $formSong.find('#artist').val(data.internal.artist_label);
        selectGenre(data.internal.song_genre_id);
        $formSong.find('#title_non_latin').val(data.internal.title_non_latin);
        getSpotify();
    }

    function getSpotify() {
        var title = $formSong.find('#title').val();
        var latin = $formSong.find('#title_non_latin').val();
        var artist = $formSong.find('#artist').val();
        var formData = {
            title:latin ? latin : title,
            artist:artist,
        };
        $($('#spotifyTable tbody').children()).remove();
        $.ajax({
            method: "GET",
            dataType: "json",
            url: baseUrl + "web/song/spotify",
            data: formData,
            success: function (response) {
                data.spotify = response.tracks ? response.tracks.items : response.payloads.tracks ? response.payloads.tracks.items : [];
                data.spotify.forEach((v,k) => {
                    var artist = [];
                    var image = v.album.images.length ? v.album.images[0].url : ''
                    v.artists.forEach((va,ka) => {
                        artist.push(va.name);
                    });
                    $('#spotifyTable tbody').append(`
                        <tr id="${k}">
                            <td class="align-middle text-center"><input type="radio" value="${v.name}" name="song" id="${k}"></td>
                            <td class="align-middle text-center">${v.name}</td>
                            <td class="align-middle text-center">${artist.join(', ')}</td>
                            <td class="align-middle text-center"><img src="${image}" widht=200 height=200></td>
                            <td class="align-middle text-center">${v.popularity}</td>
                        </tr>
                    `);
                });
            },
            error: function (response) {}
        });
    }

    function checkedSong() {
        var id = $(this).attr('id');
        data.spotify.forEach((v,k) => {
            if (k == id) {
                $('input:radio[name=song][id='+k+']').prop('checked', true);
            } else {
                $('input:radio[name=song][id='+k+']').prop('checked', false);
            }
        });
    }

    function saveSong(evt) {
        evt.preventDefault();
        if (typeof data.internal == 'undefined') { return false; }
        var formData = new FormData();
        var song = $("input[name='song']:checked");
        var song_genre_id = $formSong.find('#song_genre_id').val();
        formData.append('id', data.internal.id);
        if (song.length) {
            var artist = [];
            var spotify = data.spotify[song.attr('id')];
            var release_year = spotify.album.release_date ? new Date(spotify.album.release_date) : '';
            spotify.artists.forEach((va,ka) => {
                artist.push(va.name);
            });
            var image = spotify.album.images.length ? spotify.album.images[0].url : '';
            formData.append('title', spotify.name);
            formData.append('artist_label', artist.join(', '));
            formData.append('song_genre_id',song_genre_id);
            formData.append('url_image', image);
            formData.append('code',spotify.uri);
            formData.append('release_year',release_year.getFullYear());
        } else {
            var name = $formSong.find('#title').val();
            var artist_label = $formSong.find('#artist').val();
            formData.append('title', capitalizeWords(name));
            formData.append('artist_label', capitalizeWords(artist_label));
            formData.append('song_genre_id',song_genre_id);
        }

        $.ajax({
            method: "POST",
            dataType: "json",
            url: baseUrl + "web/song",
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            contentType: false,
            processData: false,
            success: function (response) {
                optionsNotif.message = response.payloads.message;
                $('.notif').pgNotification(optionsNotif).show();
                table.ajax.reload(null, false);
                $($('#spotifyTable tbody').children()).remove();
                clearForm();
            },
            error: function (response) {}
        });
    }

    function clearForm() {
        data = {};
        $formSong.find('#title').val('');
        $formSong.find('#artist').val('');
        selectGenre();
    }

    function selectGenre(val) {
        var data = []
        dataSong.genres.map(function(item, i) {
            data[i] = {
                id : item.id,
                text : item.name
            }
        });
        $('#song_genre_id').select2({
            placeholder: "Select a genre",
            data: data
        });
        $('#song_genre_id').val(val).trigger('change');
    }

    function capitalizeWords(str) {
        return str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
    }
})(window);
