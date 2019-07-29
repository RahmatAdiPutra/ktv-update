(function (w) {
    "use strict";

    var $ = w.jQuery;
    var $formSong = $("#form-song");
    var baseUrl = $("base").attr("href");
    let data = {};

    var optionsNotif = {
        style: 'bar',
        message: '',
        position: 'top',
        type: 'success',
        timeout: 4000,
        showClose: true
    }

    $("#simple-app").on("click", getArtist).trigger("click");
    $('#form-song div').on('change', 'input', getArtist);
    $('tbody').on('click', 'tr', checkedArtist);
    $('#next').on('click', next);
    $('#skip').on('click', skip);

    function getData() {
        data = {};
        $formSong.find('#artist').val('');
        getArtist();
    }

    function getArtist() {
        var id = typeof data.internal == 'undefined' ? '' : data.internal.id;
        var artist = $formSong.find('#artist').val();
        var formData = {
            id:id,
            artist:artist
        };
        $($('#spotifyTable tbody').children()).remove();
        $.ajax({
            method: "GET",
            dataType: "json",
            url: baseUrl + "web/artist/spotify",
            data: formData,
            success: function (response) {
                data.songs = [];
                data.internal = response.payloads.artists;
                data.spotify = response.payloads.spotify.artists.items;
                $.each(data.internal.songs, function(k, v) { 
                    data.songs.push(v.title);
                });
                artist = artist == '' ? data.internal.name : artist;
                $formSong.find('#artist').val(artist);
                $('#artist-name').html(data.internal.name);
                $('#song-name').html(data.songs.join(', '));
                data.spotify.forEach((v,k) => {
                    var image = v.images.length ? v.images[0].url : ''
                    $('tbody').append(`
                        <tr id="${k}">
                            <td class="align-middle text-center"><input type="radio" value="${v.name}" name="artist" id="${k}"></td>
                            <td class="align-middle text-center">${v.name}</td>
                            <td class="align-middle text-center"><img src="${image}" widht=350 height=350></td>
                            <td class="align-middle text-center">${v.popularity}</td>
                            <td class="align-middle text-center">${v.followers.total}</td>
                            <td class="align-middle text-center">${v.genres.join(', ')}</td>
                        </tr>
                    `);
                });
            },
            error: function (response) {}
        });
    }

    function checkedArtist() {
        var id = $(this).attr('id');
        data.spotify.forEach((v,k) => {
            if (k == id) {
                $('input:radio[name=artist][id='+k+']').prop('checked', true);
            } else {
                $('input:radio[name=artist][id='+k+']').prop('checked', false);
            }
        });
    }

    function next() {
        var artist = $("input[name='artist']:checked");
        if (artist.length) {
            var formData = new FormData();
            var spotify = data.spotify[artist.attr('id')];
            var name = artist.val();
            var image = spotify.images.length ? spotify.images[0].url : '';
            var uri = spotify.uri;
            var popularity = spotify.popularity;
            formData.append('id', data.internal.id);
            formData.append('name', name);
            formData.append('url_image', image);
            formData.append('code',uri);
            formData.append('popularity',popularity);
            $.ajax({
                method: "POST",
                dataType: "json",
                url: baseUrl + "web/artist",
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                contentType: false,
                processData: false,
                success: function (response) {
                    getData();
                },
                error: function (response) {}
            });
        } else {
            optionsNotif.message = 'Not selected';
            $('.notif').pgNotification(optionsNotif).show();
        }
    }

    function skip() {
        $.ajax({
            method: "POST",
            dataType: "json",
            url: baseUrl + "web/artist",
            data: {
                id: data.internal.id
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                getData();
            },
            error: function (response) {}
        });
    }
})(window);
