(function (w) {
    'use strict';

    var $ = w.jQuery;
    var baseUrl = $('base').attr('href');
    var data = {};

    $('#search').on('change', getYoutube);

    function getYoutube() {
        data.query = $('#search').val();
        $.ajax({
            method: "GET",
            dataType: "json",
            url: baseUrl + "youtube/video",
            data: {q : data.query},
            success: function (response) {
                console.log(response.payloads);
                data.videos = [];
                data.htmlVideo = [];
                response.payloads.items.forEach((v, k) => {
                    data.videos.push({
                        channel: v.snippet.channelTitle,
                        title: v.snippet.title,
                        thumb: v.snippet.thumbnails.high.url,
                        video: v.id.videoId
                    });
                    // data.htmlVideo.push(`<iframe width="250" height="250" src="https://www.youtube.com/embed/${v.id.videoId}"></iframe>`);
                    data.htmlVideo.push(`
                        <div class="w-20 m-2 text-center">
                            <a href="https://www.youtube.com/embed/${v.id.videoId}" target="_blank">
                                <img width="200" height="200" src="${v.snippet.thumbnails.high.url}"></img>
                            </a>
                            <div>${v.snippet.channelTitle} | ${v.snippet.title}</div>
                        </div>
                    `);
                });
                // console.log(data);
                $('.result').html(data.htmlVideo.join(' '));
            },
            error: function (response) {}
        });
    }
})(window);