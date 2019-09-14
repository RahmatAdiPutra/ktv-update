(function (w) {
    "use strict";

    var $ = w.jQuery;
    var $formSong = $("#form-song");
    var $formSongModal = $("#form-song-modal");
    var $modalFormSong = $('#modalFormSong');
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
            },
            {
                orderable: false,
                mRender: function (data, type, row) {
                    if (window.ALLOW_EDIT) {
                        return `
                            <a href="#" data-id="${row.id}" id="edit-song" data-target="#modalFormSong" data-toggle="modal">
                            <i class="fa fa-pencil-square-o" aria-hidden="true" style="font-size:24px"></i>
                            </a>
                            <a href="#" data-id="${row.id}" id="delete-song" data-target="#modalConfirm" data-toggle="modal">
                            <i class="fa fa-trash-o" aria-hidden="true" style="font-size:24px;color:red"></i>
                            </a>
                        `;
                    } else {
                        return ``;
                    }
                }
            }
        ]
    };

    var table = $("#detailedTable").DataTable(
        $.extend(true, window.dataTableDefaultOptions, dataTableOptions)
    );

    selectLanguage();
    selectCheckUpdated();

    if (window.ALLOW_EDIT) {
        $('#artist_id').select2({
            placeholder: "Select a artist",
            data: dataSong.artists
        });
    }

    $formSongModal.on("submit", saveSongModal);

    $('#detailedTable tbody').on('click', 'tr', selectSong);
    $('#detailedTable tbody').on('click', 'a[id="edit-song"]', editSong);
    $('#detailedTable tbody').on('click', 'a[id="delete-song"]', deleteSong);
    $('#spotifyTable tbody').on('click', 'tr', checkedSong);
    $('#form-song div').on('change', 'input', getSpotify);
    $('#save-song').on("click", saveSong);
    $('#modalConfirm').on('click', 'button', confirm);
    $('#jumppage').on('change', jumpToPage);
    $('#language_id').on('change', getFilter);
    $('#check_updated').on('change', getFilter);

    function jumpToPage(evt) {
        evt.preventDefault();
        var info = table.page.info();
        var page = $('#jumppage').val();
        if (page > 0 && page <= info.pages) {
            page = page - 1;
            table.page(parseInt(page)).draw(false);
        }
    }

    function getFilter(evt) {
        evt.preventDefault();
        var lang = $('#language_id').val();
        var check = $('#check_updated').val();
        if (check == 1) {
            table.ajax.url(dataUrl + '?lang=' + lang + '&checkNotNull=' + check).load();
        } else if (check == 2) {
            table.ajax.url(dataUrl + '?lang=' + lang + '&checkNullCover=' + check).load();
        } else if (check == 3) {
            table.ajax.url(dataUrl + '?lang=' + lang + '&checkNull=' + check).load();
        } else {
            table.ajax.url(dataUrl + '?lang=' + lang).load();
        }
    }

    function selectSong(evt) {
        evt.preventDefault();
        clearForm();
        clearFormSong();
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
        $.ajax({
            method: "GET",
            dataType: "json",
            url: baseUrl + "web/song/spotify",
            data: formData,
            success: function (response) {
                $($('#spotifyTable tbody').children()).remove();
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

    function editSong(evt) {
        var id = $(this).attr("data-id");
        $.ajax({
            method: "GET",
            dataType: "json",
            url: baseUrl + "web/song/" + id,
            success: function (response) {
                var selected = [];
                $modalFormSong.find('#id').val(response.payloads.id);
                selectGenreForm(response.payloads.song_genre_id);
                selectLanguageForm(response.payloads.song_language_id);
                $modalFormSong.find('#title').val(response.payloads.title);
                $modalFormSong.find('#title_non_latin').val(response.payloads.title_non_latin);
                $modalFormSong.find('#artist_lab').val(response.payloads.artist_label);
                selectType(response.payloads.type);
                $modalFormSong.find('#volume').val(response.payloads.volume);
                selectAudio(response.payloads.audio_channel);
                if(typeof response.payloads.artists === 'object' && response.payloads.artists.length > 0) {
                    response.payloads.artists.forEach((v) => {
                        selected.push(v.id);
                    });
                    setTimeout(function(){ selectArtist(selected); }, 3000);
                }
            },
            error: function (response) {}
        });
        evt.preventDefault();
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

    function saveSongModal(evt) {
        var formData = new FormData($formSongModal[0]);
        formData.append('id',$('#id').val());
        formData.append('song_genre_id',$('#genre_id').val());
        formData.append('song_language_id',$('#song_language_id').val());
        formData.append('title',$('#title').val());
        formData.append('title_non_latin',$('#title_non_latin').val());
        formData.append('artist_id',$('#artist_id').val());
        formData.append('artist_label',$('#artist_lab').val());
        formData.append('type',$('#type').val());
        formData.append('volume',$('#volume').val());
        formData.append('audio_channel',$('#audio_channel').val());
        $.ajax({
            method: "POST",
            dataType: "json",
            url: baseUrl + "web/song",
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                $(".modal").modal("hide");
                optionsNotif.message = response.payloads.message;
                $('.notif').pgNotification(optionsNotif).show();
                table.ajax.reload(null, false);
                $($('#spotifyTable tbody').children()).remove();
                clearFormSong();
            },
            error: function (response) {}
        });
        evt.preventDefault();
        return false;
    }

    function deleteSong(evt) {
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
                url: baseUrl + "web/song/" + id,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    $(".modal").modal("hide");
                    optionsNotif.message = response.payloads.message;
                    $('.notif').pgNotification(optionsNotif).show();
                    table.ajax.reload(null, false);
                    $($('#spotifyTable tbody').children()).remove();
                    clearForm();
                    clearFormSong();
                },
                error: function (response) {}
            });
        }
        evt.preventDefault();
        return false;
    }

    function clearForm() {
        data = {};
        $formSong.find('#title').val('');
        $formSong.find('#artist').val('');
        $formSong.find('#title_non_latin').val('');
        $formSong.find('#song_genre_id').val("").trigger('change');
    }

    function clearFormSong() {
        $modalFormSong.find('#id').val("");
        $modalFormSong.find('#genre_id').val("").trigger('change');
        $modalFormSong.find('#song_language_id').val("").trigger('change');
        $modalFormSong.find('#title').val("");
        $modalFormSong.find('#title_non_latin').val("");
        $modalFormSong.find('#artist_id').val("").trigger('change');
        $modalFormSong.find('#artist_lab').val("");
        $modalFormSong.find('#type').val("").trigger('change');
        $modalFormSong.find('#volume').val("");
        $modalFormSong.find('#audio_channel').val("").trigger('change');
    }

    function selectArtist(artists) {
        $('#artist_id').val(artists).trigger('change');
    }

    function selectGenre(val) {
        $('#song_genre_id').select2({
            placeholder: "Select a genre",
            data: dataSong.genres
        });
        $('#song_genre_id').val(val).trigger('change');
    }

    function selectGenreForm(val) {
        $('#genre_id').select2({
            placeholder: "Select a genre",
            data: dataSong.genres
        });
        $('#genre_id').val(val).trigger('change');
    }

    function selectLanguageForm(val) {
        $('#song_language_id').select2({
            placeholder: "Select a language",
            data: dataSong.languages
        });
        $('#song_language_id').val(val).trigger('change');
    }

    function selectType(val) {
        $('#type').select2({
            data: dataSong.type
        });
        val ? $('#type').val(val).trigger('change') : '';
    }

    function selectAudio(val) {
        $('#audio_channel').select2({
            data: dataSong.audio
        });
        val ? $('#audio_channel').val(val).trigger('change') : '';
    }

    function selectLanguage() {
        var data = []
        dataSong.languages.map(function(item, i) {
            data[i + 1] = {
                id : item.id,
                text : item.text
            }
        });
        data[0] = {
            id: '',
            text: "All"
        }
        $('#language_id').select2({
            data: data
        });
    }

    function selectCheckUpdated() {
        var data = [
            {
                id: "",
                text: "All"
            },
            {
                id: 1,
                text: "Updated"
            },
            {
                id: 2,
                text: "Updated Not Cover"
            },
            {
                id: 3,
                text: "Not Updated"
            }
        ]
        $('#check_updated').select2({
            data: data
        });
    }

    function capitalizeWords(str) {
        return str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
    }
})(window);
