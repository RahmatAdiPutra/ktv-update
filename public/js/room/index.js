(function (KTV, Socket) {
    'use strict';

    let activeSession;
    const $appWrapper = $('#ktv-app');

    const sessionEnd = () => {
        // disconect from session channel
        Socket.leave(`RoomSession.${activeSession.token}`);
        Socket.leave(`TvControl.${activeSession.token}`)
        // reset
        activeSession = null;

        sessionIdle();
    };

    const sessionStart = (data) => {
        activeSession = data;

        console.log(data);

        Socket.channel(`RoomSession.${activeSession.token}`)
            .listen('.myRoomSessionEnded', (data) => {
                console.log(data);
                sessionEnd();
            })
            .listen('.myRoomSessionPlaylistUpdated', (playlist) => {
                console.log(playlist);
            })
            .listen('.myRoomSessionKeypressed', (keyCode) => {
                console.log('Key pressed, key code = ', keyCode);
            });

        Socket.private(`TvControl.${activeSession.token}`)
            .listenForWhisper('typing', (e) => {
                alert('asdasd');
                console.log(e.name);
            });

        Socket.private(`TvControl.${activeSession.token}`).whisper('typing', {
            name: 'Avikaco'
        });

        console.log(`TvControl.${activeSession.token}`);

        $appWrapper.html('<div class="ktv-app-wrapper" id="app-song-browser">Session started, Guest Name: ' + data.guestName + '. Room token ' + data.token + '</div>');
    };

    const sessionIdle = () => {
        $appWrapper.html('<div class="ktv-app-wrapper" id="app-idle">Room are idle. Show promotion</div>');
    };

    /**
     * Setup public chennel,
     * channel ini hanya yang berhubungan dengan room saat ini (`KTV.roomChannel`),
     * tidak bisa menguping room lain.
     *
     * Event yang di listen oleh `myRoomSessionStarted`
     */
    Socket.channel(KTV.roomChannel)
        .listen('.myRoomSessionStarted', (data) => {
            // start listening channel on room session
            sessionStart(data);
        });

    if (KTV.token) {
        // saat di refresh, room sudah memiliki aktif session
        // ambil detail session

        fetch(KTV.baseApiUrl + '/room-session/' + KTV.token)
            .then(res => res.json())
            .then(json => sessionStart(json))
            .catch(e => console.log(e));
    } else {
        sessionIdle();
    }
})(KTV, Socket);
