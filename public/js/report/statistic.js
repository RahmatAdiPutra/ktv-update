(function (w) {
    "use strict";

    var $ = w.jQuery;
    var baseUrl = $("base").attr("href");
    let data = {};

    $("#simple-app").on("click", getData).trigger("click");

    function getData() {
        $.ajax({
            method: "GET",
            dataType: "json",
            url: baseUrl + "web/statistic/data",
            success: function (response) {
                console.log(response);
                response.payloads.data.forEach((v,k) => {
                    $('#statisticTable tbody').append(`
                        <tr>
                            <td class="align-middle text-center">${k+1}</td>
                            <td class="align-middle text-center">${v.name}</td>
                            <td class="align-middle text-center">${v.artist}</td>
                            <td class="align-middle text-center">${v.song}</td>
                            <td class="align-middle text-center">${v.total_point}</td>
                        </tr>
                    `);
                });

                $('#statisticTable tfoot').append(`
                    <tr>
                        <th class="align-middle text-center" colspan="2">Total</th>
                        <th class="align-middle text-center">${response.payloads.total.artist}</th>
                        <th class="align-middle text-center">${response.payloads.total.song}</th>
                        <th class="align-middle text-center">${response.payloads.total.total_point}</th>
                    </tr>
                `);
            },
            error: function (response) {}
        });
    }
})(window);
