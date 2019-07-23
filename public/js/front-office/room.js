(function(Laravel, Echo) {
    "use strict";
    "esversion: 6";

    let roomStatuses = {};
    let roomAction = {};
    let roomTimer = {};
    let roomTimeOut = {};
    const roomActionLabel = {
        1: "tersedia untuk digunakan",
        4: "sedang perbaikan",
        5: "sedang dibersihkan"
    };
    const $modalStatusConfirmation = $("#modal-room-change-status");
    const $modalMoveRoom = $("#modal-room-move");
    const $modalMoveRoomLists = $modalMoveRoom.find("[name='to']");
    const $modalRoomOpenSession = $("#modal-room-open");
    const $modalRoomOpenGuestname = $modalRoomOpenSession.find(
        "input[name=guestname]"
    );
    const $modalRoomOpenSessionType = $modalRoomOpenSession.find(
        "select[name=type]"
    );
    const $modalRoomOpenDurationContainer = $modalRoomOpenSession.find(
        "#duration"
    );
    const $modalRoomOpenDuration = $modalRoomOpenSession.find(
        "input[name=duration]"
    );
    const $modalRoomCloseSession = $("#modal-room-close");
    const $modalCallWaiter = $("#modal-room-waiter");
    const $modalCallWaiterBtn = $modalCallWaiter.find(".btn-complete");
    const $modalRoomReserve = $("#modal-room-reserve");

    let errorCatcher = res => {
        console.log("errorCatcher", res.responseJSON);
    };

    Echo.private("room")
        .listen("RoomStatusChanged", rooms => {
            console.log("event RoomStatusChanged", rooms);
            roomData(rooms);
        })
        .listen("Calling", room => {
            console.log("Calling room", room);
            if (room.to !== "waiter") {
                // hanya listening panggilan untuk waiter
                return;
            }

            if (room.isResponded === false) {
                // belum di response oleh FO, buka modal
                $modalCallWaiter.find(".room-name-modal").html(room.name);
                $modalCallWaiter.modal("show");
                $modalCallWaiterBtn.data({
                    id: room.id,
                    to: room.to
                });
            } else {
                // sudah di response, tutup window
                $modalCallWaiter.modal("hide");
            }
        }).listen("Notify", room =>{
            notify(room[0]);
        });

    $modalRoomOpenSessionType.on("change", () => {
        const $selectedOption = $modalRoomOpenSessionType.find(
            "[value=" + $modalRoomOpenSessionType.val() + "]"
        );

        if ($selectedOption.data("countdown")) {
            $modalRoomOpenDurationContainer.removeClass("disabled");
            $modalRoomOpenDuration.attr("disabled", false);
            $modalRoomOpenDuration.val(1);
        } else {
            $modalRoomOpenDurationContainer.addClass("disabled");
            $modalRoomOpenDuration.attr("disabled", true);
            $modalRoomOpenDuration.val("");
        }
    });

    $modalRoomCloseSession.find(".btn-complete").on("click", e => {
        e.preventDefault();

        $.post(Laravel.baseUrl + "/room/" + roomAction.roomId + "/close")
            .then(res => console.log(res))
            .fail(errorCatcher);
    });

    $modalStatusConfirmation.find(".btn-complete").on("click", e => {
        e.preventDefault();

        $.post(
            Laravel.baseUrl + "/room/" + roomAction.roomId + "/change-status",
            {
                status: roomAction.status
            }
        )
            .then(res => console.log(res))
            .fail(errorCatcher);
    });

    $modalCallWaiterBtn.on("click", e => {
        const data = $modalCallWaiterBtn.data();
        console.log(data);
        e.preventDefault();

        $.post(Laravel.baseUrl + "/room/" + data.id + "/call-responded", {
            from: data.to
        });
        $modalCallWaiter.modal("hide");
    });

    $("#room-refresh-data")
        .on("click", e => {
            e.preventDefault();

            $.getJSON(Laravel.baseUrl + "/room/all-status", rooms =>
                roomData(rooms)
            );

            $.getJSON(Laravel.baseUrl + "/room/unread-notify", rooms =>
                notify(rooms)
            );
        })
        .trigger("click");

    // btn open room action
    $(".room-btn-action-open").on("click", function(e) {
        const $this = $(this);

        roomAction = {
            roomId: $this.data("room"),
            roomName: $this.data("room-name")
        };
        $modalRoomOpenSession
            .find(".room-name-modal")
            .html(roomAction.roomName);
        $modalRoomOpenGuestname.val("");
        $modalRoomOpenSessionType.trigger("change");
        $modalRoomOpenSession.modal("show");

        e.preventDefault();
    });
    $modalRoomOpenSession.find(".btn-complete").on("click", e => {
        e.preventDefault();

        $.post(Laravel.baseUrl + "/room/" + roomAction.roomId + "/open", {
            guestName: $modalRoomOpenGuestname.val(),
            type: $modalRoomOpenSessionType.val(),
            duration: $modalRoomOpenDuration.val() || 0
        })
            .then(res => console.log(res))
            .fail(errorCatcher);
        $modalRoomOpenSession.modal("hide");
    });

    // btn close room action
    $(".room-btn-action-stop").on("click", function(e) {
        const $this = $(this);

        roomAction = {
            roomId: $this.data("room"),
            roomName: $this.data("room-name")
        };

        $modalRoomCloseSession
            .find(".room-name-modal")
            .html(roomAction.roomName);
        $modalRoomCloseSession.modal("show");

        e.preventDefault();
        return false;
    });

    // btn move room action
    $(".room-btn-action-move").on("click", function(e) {
        const $this = $(this);
        let i, exists;
        let html = "";

        e.preventDefault();

        roomAction = {
            roomId: $this.data("room"),
            roomName: $this.data("room-name"),
            status: $this.data("room-status")
        };

        roomStatuses.forEach(function(r) {
            if (!r.activeSession) {
                exists = true;
                html += "<option value='" + r.id + "'>" + r.name + "</options>";
            }
        });
        $modalMoveRoomLists.html(html).trigger("change.select2");

        if (exists !== true) {
            $("#bg-master-lightest")
                .pgNotification({
                    style: "flip",
                    message: "Tidak ada room yang kosong!",
                    position: "top-right",
                    timeout: 2000,
                    type: "danger"
                })
                .show();
            return;
        }

        console.log($this.data("room"), $this.data("room-name"));
        $modalMoveRoom.find(".room-name-modal").html(roomAction.roomName);
        $modalMoveRoom.modal("show");

        return false;
    });
    $modalMoveRoom.find(".btn-complete").on("click", e => {
        e.preventDefault();

        $.post(Laravel.baseUrl + "/room/" + roomAction.roomId + "/move", {
            toRoom: $modalMoveRoomLists.val()
        })
            .then(res => console.log(res))
            .fail(errorCatcher);

        $modalMoveRoom.modal("hide");
    });

    // btn reserve room action
    $(".room-btn-action-reserve").on("click", function(e) {
        var $this = $(this);

        e.preventDefault();

        roomAction = {
            roomId: $this.data("room"),
            roomName: $this.data("room-name")
        };

        $modalRoomReserve.find("[name=guestname]").val("");
        $modalRoomReserve.find(".room-name-modal").html(roomAction.roomName);
        $modalRoomReserve.modal("show");

        return false;
    });
    $modalRoomReserve.find(".btn-complete").on("click", e => {
        const guestName = $modalRoomReserve.find("[name=guestname]").val();

        e.preventDefault();

        if (!guestName) {
            // kalo guestname kosong ga bisa di submit
            return;
        }

        $.post(Laravel.baseUrl + "/room/" + roomAction.roomId + "/reserve", {
            guestName: guestName
        })
            .then(res => console.log(res))
            .fail(errorCatcher);

        $modalRoomReserve.modal("hide");
    });

    // btn status room action
    $(".room-btn-action-status").on("click", function(e) {
        var $this = $(this);
        roomAction = {
            roomId: $this.data("room"),
            roomName: $this.data("room-name"),
            status: $this.data("room-status")
        };

        $modalStatusConfirmation
            .find(".room-status-modal")
            .html(roomActionLabel[roomAction.status]);
        $modalStatusConfirmation
            .find(".room-name-modal")
            .html(roomAction.roomName);
        $modalStatusConfirmation.modal("show");

        e.preventDefault();
    });

    $("#filter").on("click", "div", function(e) {
        e.preventDefault();
        var room = $("#bg-master-lightest").children().children().children();
        var colorShowAll = $($("#filter").children()[0]).data('color');
        var colorFilter = $(this).data('color');
        if (colorFilter == colorShowAll) {
            $(room).show();
        } else {
            $.each(room, function (index, value) {
                var color = $(value).children().data('color');
                if (colorFilter == color) {
                    $(value).show();
                } else {
                    $(value).hide();
                }
            });
        }
        return false;
    });

    $(".read-notify").on("click", "div", function(e) {
        var read = $(this).data("read");
        var notifyId = $(this).data("notify-id");
        var roomId = $(this).data("room-id");
        if (read) {
            $.post(Laravel.baseUrl + "/room/read-notify", {
                notifyId: notifyId,
                roomId: roomId
            })
                .then(res => console.log(res))
                .fail(errorCatcher);
        }
    });

    function roomData(rooms) {
        let i;

        roomStatuses = rooms;

        $.each(roomTimer, function (index, value) {
            stopTimer(index);
        });

        $.each(roomTimeOut, function (index, value) {
            clearTimeout(value);
        });

        for (i = 0; i < rooms.length; i++) {
            let room = rooms[i];
            let $room = $("#room" + room.id);
            let $roomGuestName = $room.find(".room-guest-name");

            if (room.guestName) {
                $roomGuestName
                    .html(room.guestName)
                    .parent()
                    .removeClass("invisible");
            } else {
                $roomGuestName.parent().addClass("invisible");
            }

            if (room.activeSession) {
                $room
                    .find(".room-btn-action-show-onactive")
                    .removeClass("d-none");
                $room.find(".room-btn-action-hide-onactive").addClass("d-none");
                $room
                    .find(".room-session-type")
                    .html("[" + room.activeSession.sessionType + "]");

                counterTimer(room);
                startTimer(room);
            } else {
                $room
                    .find(".room-btn-action-hide-onactive")
                    .removeClass("d-none");
                $room.find(".room-btn-action-show-onactive").addClass("d-none");
                $room.find(".room-session-type").html("");

                $("#room-" + room.id + "-timer").html("-- : --");
            }

            $room.find(".room-status").html(room.status.label);
            $room.attr(
                "class",
                "card card-default room" +
                    (room.status.color ? " bg-" + room.status.color : "")
            );
            $room.data("color",(room.status.color ? "bg-" + room.status.color : "bg-info-lighter"));

            if ($("#room-" + room.id + "-timer").text() == "TIME OUT") {
                timeOutStart(room.id);
            } else {
                $room.removeClass("animated infinite shake slower");
            }
        }
    }

    function notify(room) {
        $(".heading").empty();
        if (room.count) {
            $(".bubble").show();
            $(".notification-footer .read-all").show();
            $(".notification-footer .empty").hide();
            $(".bubble").html(`${room.count}`);
            $.each(room.unread, function (index, value) {
                $(".heading").append(`
                    <div class="d-flex flex-row justify-content-between bg-complete-lighter m-1 cursor" data-read="true" data-notify-id="${value.id}" data-room-id="${value.notifiable_id}">
                        <div class="pl-2">${value.data.room.name} ${value.data.room.message}</div>
                        <div class="time">${notifyTime(value.updated_at)}</div>
                    </div>
                `);
            });
        } else {
            $(".bubble").hide();
            $(".notification-footer .read-all").hide();
            $(".notification-footer .empty").show();
        }
    }

    function notifyTime(dateTime) {
        var time;
        
        var now = new Date().getTime();
    
        var date = new Date(dateTime).getTime();
    
        var distance = now - date;
    
        var monthsYear = 12;
        var daysMonth = 31;
        var daysWeek = 7;
        
        var days = Math.floor(distance / (1000 * 60 * 60 * 24));
        var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((distance % (1000 * 60)) / 1000);
    
        if (days == 0 && hours == 0 && minutes == 0 && seconds <= 60) {
            time = seconds + " seconds ago"
        } else if (days == 0 && hours == 0 && minutes <= 60 && seconds <= 60) {
            time = minutes + " minutes ago";
        } else if (days == 0 && hours <= 24 && minutes <= 60 && seconds <= 60) {
            time = hours + " hours ago";
        } else {
            if (now >= date) {
                if (days < 7) {
                    time = days + " days ago";
                } else if (days >= daysWeek && days <= daysMonth) {
                    time = (days / daysWeek).toFixed()  + " weeks ago";
                } else if ((days / daysMonth).toFixed() <= monthsYear) {
                    time = (days / daysMonth).toFixed()  + " months ago";
                } else {
                    time = dateTime;
                }
            } else {
                time = dateTime;
            }
        }
        
        return time;
    }

    function stopTimer(room) {
        clearInterval(roomTimer[room]);
    }

    function startTimer(room) {
        roomTimer["interval-room-"+room.id] = setInterval(function() { counterTimer(room); }, 60000);
    }

    function counterTimer(room) {
        var now, countDownDate, distance;
        
        now = new Date().getTime();
        
        if (room.activeSession.isTimerCountdown) {
            countDownDate = new Date(room.activeSession.openedAt).getTime() + (1000 * 60 * 60 * room.activeSession.hourDuration);
            distance = countDownDate - now;
        } else {
            countDownDate = new Date(room.activeSession.openedAt).getTime();
            distance = now - countDownDate;
        }

        var hours = Math.floor(distance / (1000 * 60 * 60));
        var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));

        if (hours < "10") { hours = "0" + hours; }
        if (minutes < "10") { minutes = "0" + minutes; }
            
        $("#room-" + room.id + "-timer").html(hours + ":" + minutes);

        if (distance < 0) {
            $("#room-" + room.id + "-timer").html("TIME OUT");
            stopTimer("interval-room-"+room.id);
        }
    }

    function timeOutStart(roomId) {
        $("#room"+roomId).addClass("animated infinite shake slower");
        roomTimeOut["timeOutStop"+roomId] = setTimeout(function(){ timeOutStop(roomId); }, 3000);
    }

    function timeOutStop(roomId) {
        $("#room"+roomId).removeClass("animated infinite shake slower");
        roomTimeOut["timeOutStart"+roomId] = setTimeout(function(){ timeOutStart(roomId); }, 3000);
    }
})(window.Laravel, window.Echo);
