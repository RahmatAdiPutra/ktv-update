<?php
namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use App\Events\RoomClosed;
use App\Events\RoomOpened;
use App\Events\RoomMoved;
use App\Events\RoomStatusChanged;
use App\Events\Calling;
use App\Events\Notify;
use Illuminate\Notifications\Notifiable;
use App\Notifications\RoomNotification;

class Room extends Model
{
    use Notifiable;

    protected $retries = 3;

    protected $fillable = [
        'room_type_id',
        'room_status_id',
        'name',
        'guest_name',
        'tokens',
        'active_session_id',
        'ip_address'
    ];

    public function stations()
    {
        return $this->belongsToMany(Room::class, 'operator_station_has_room');
    }

    public function type()
    {
        return $this->belongsTo(RoomType::class, 'room_type_id')->select('id', 'name');
    }

    public function status()
    {
        return $this->belongsTo(RoomStatus::class, 'room_status_id');
    }

    public function sessions()
    {
        return $this->hasMany(RoomSession::class);
    }

    public function activeSession()
    {
        return $this->belongsTo(RoomSession::class)
            ->select('room_sessions.id', 'room_id', 'guest_name', 'opened_at', 'hour_duration', 'room_session_types.name as session_type', 'timer_countdown')
            ->join('room_session_types', 'room_session_type_id', '=', 'room_session_types.id');
    }

    public function open($guestName, $sessionTypeId, $hourDuration = null)
    {
        // cek apakah saat ini room kosong?
        if ($this->activeSession !== null) {
            // throw error
            throw new \Exception('Anda tidak bisa menggunakan room ' . $this->name . ' karena saat ini sedang digunakan oleh tamu ' . $this->activeSession->guest_name . '.');
        }
        
        // apakah session type yang dipilih menggunakan timer countdown?
        $sessionType = RoomSessionType::findOrFail($sessionTypeId);
        if (true === $sessionType->timer_countdown) {
            if (is_numeric($hourDuration)) {
                $hourDuration = intval($hourDuration);
            } else {
                $hourDuration = 1;
            }
        } else {
            // tidak menggunakan timer
            $hourDuration = null;
        }
        
        DB::transaction(function () use ($guestName, $sessionTypeId, $hourDuration) {
            // prepare session data
            $session = new RoomSession([
                'area_id' => $this->area_id,
                'room_session_type_id' => $sessionTypeId,
                'room_type_id' => $this->room_type_id,
                'guest_name' => $guestName,
                'opened_by' => auth()->id(),
                'opened_at' => date('Y-m-d H:i:s'),
                'hour_duration' => $hourDuration
            ]);
            
            // save relation
            $this->sessions()->save($session);
            
            // update room status ke "occupied"
            $this->room_status_id = Setting::get('roomStatusOccupiedId');
            $this->guest_name = $guestName;
            // generate token
            $this->token = $this->generateToken();
            $this->active_session_id = $session->id;
            $this->save();
        }, $this->retries);
        
        // broadcast ke room app
        event(new RoomOpened(self::find($this->id)));

        $this->notify(new RoomNotification($this, $guestName." Check in"));

        event(new Notify(self::unreadNotify()));
    }

    public function close()
    {
        // untuk bisa di close, room harus sedang terisi (punya aktif session)
        if ($this->activeSession === null) {
            // throw error
            throw new \Exception('Room ' . $this->name . ' sedang kosong.');
        }
        
        // calculate hour duration
        $openedAt = strtotime($this->activeSession->opened_at);
        $closedAt = time();
        $hoursDuration = ceil(($closedAt - $openedAt) / 3600); // dibulatkan keatas, contoh: 1 jam lewat 1 menit akan dibulankan jadi 2 jam
        $guestName = $this->guest_name;
                                                               
        // current token (dipakai untuk close room channel)
        $token = $this->token;
        
        DB::transaction(function () use ($hoursDuration) {
            // mark response semua call
            $this->callResponded('waiter');
            $this->callResponded('operator');
            
            // update room session
            $this->activeSession->hour_duration = $hoursDuration;
            $this->activeSession->closed_at = date('Y-m-d H:i:s');
            $this->activeSession->closed_by = auth()->id();
            $this->activeSession->save();
            
            // update room status ke "vacant"
            $this->room_status_id = Setting::get('roomStatusVacantId');
            
            $this->guest_name = null;
            
            // reset active session
            $this->token = null;
            $this->active_session_id = null;
            
            // update room
            $this->save();
        }, $this->retries);
        
        // broadcast ke room app
        event(new RoomClosed($this, $token));

        $this->notify(new RoomNotification($this, $guestName." Check out"));

        event(new Notify(self::unreadNotify()));
    }

    public function reserve($guestName)
    {
        if (false === empty($this->active_session_id)) {
            throw new \Exception("Room {$this->name} tidak bisa di booking kerena sedang digunakan.");
        }
        
        DB::transaction(function () use ($guestName) {
            $this->guest_name = $guestName;
            $this->room_status_id = Setting::get('roomStatusReserveId');
            $this->save();
        });

        $this->notify(new RoomNotification($this, "Reserved ".$this->guest_name));

        event(new Notify(self::unreadNotify()));
        
        return true;
    }

    public function moveTo($newRoomId)
    {
        if ($newRoomId == $this->id) {
            // room yang baru dan yang lama sama, jadi tidak perlu di proses
            return true;
        }
        
        $newRoom = Room::find($newRoomId);
        // cek apakah room yang baru punya active_session?
        if ($newRoom->active_session_id) {
            throw new \Exception("Room {$newRoom->name} tidak bisa menerima pindahan karena sedang digunakan {$newRoom->activeSession->guest_name}");
        }
        
        DB::transaction(function () use ($newRoom) {
            // 1. mark response semua call
            $this->callResponded('waiter');
            $this->callResponded('operator');
            
            // 2. status & copy session ke new room
            $newRoom->room_status_id = Setting::get('roomStatusOccupiedId');
            $newRoom->token = $this->token;
            $newRoom->active_session_id = $this->active_session_id;
            $newRoom->guest_name = $this->guest_name;
            
            // 3. update pointer session
            RoomSession::whereId($this->active_session_id)->update([
                'room_id' => $newRoom->id,
                'area_id' => $newRoom->area_id,
                'room_type_id' => $newRoom->room_type_id
            ]);
            
            // 4. reset old room
            $this->room_status_id = Setting::get('roomStatusVacantId');
            $this->token = $this->active_session_id = $this->guest_name = null;
            $this->save();
            
            // 5. save new room
            $newRoom->save();
            
            // 6. catat di history
            SessionMovedHistory::create([
                'room_session_id' => $newRoom->active_session_id,
                'to_room_id' => $newRoom->id,
                'from_room_id' => $this->id,
                'moved_by' => auth()->id(),
                'moved_at' => date('Y-m-d H:i:s')
            ]);
        }, $this->retries);
        
        // broadcast ke room status
        event(new RoomMoved($this, $newRoom));

        $this->notify(new RoomNotification($this, " move to ".$newRoom->name));

        event(new Notify(self::unreadNotify()));
    }

    public function calling($who)
    {
        if (false === in_array($who, [
            'operator',
            'waiter'
        ])) {
            throw new \Exception('Mau panggil siapa?');
        }
        
        $callData = new RoomCall([
            'room_session_id' => $this->active_session_id,
            'room_id' => $this->id,
            'call_type' => $who
        ]);
        
        $call = $this->activeSession->calls()->save($callData);
        
        // fire event
        event(new Calling($call));

        $this->notify(new RoomNotification($this, "Calling ".$who));

        event(new Notify(self::unreadNotify()));
    }

    public function callResponded($responseFrom)
    {
        if (false === in_array($responseFrom, [
            'operator',
            'waiter'
        ])) {
            throw new \Exception('Mau respon panggilan yang mana?');
        }
        
        // cari panggilan yang belum di response
        $unresponseCalls = RoomCall::where([
            'room_id' => $this->id,
            'room_session_id' => $this->active_session_id,
            'call_type' => $responseFrom
        ])->whereNull('responded_at')->first();
        
        // apakah ada yang belum di response?
        if ($unresponseCalls) {
            // tandai semua panggilan sebagai telah di response
            RoomCall::where([
                'room_id' => $this->id,
                'room_session_id' => $this->active_session_id,
                'call_type' => $responseFrom
            ])->whereNull('responded_at')->update([
                'responded_at' => date('Y-m-d H:i:s')
            ]);
            
            // fire event
            event(new Calling($unresponseCalls));
        }
        return true;
    }

    public function changeStatus($newStatusId)
    {
        // perubahan status room TIDAK BERLAKU untuk status "occupied" (terisi)
        // perubahan DARI dan KE status occupied harus menggunakan open() & close() method.
        $occupiedStatusId = Setting::get('roomStatusOccupiedId');
        if ($this->room_status_id == $occupiedStatusId || $newStatusId == $occupiedStatusId) {
            throw new \Exception('Anda tidak bisa merubah room status ke Occupied.');
        }
        
        DB::transaction(function () use ($newStatusId) {
            $this->room_status_id = $newStatusId;
            $this->save();
        }, $this->retries);
        
        // broadcast ke room status
        event(new RoomStatusChanged());

        $message = self::with('status')->find($this->id)->status->name == "Vacant" ? "Available" : self::with('status')->find($this->id)->status->name;
        
        $this->notify(new RoomNotification($this, $message));

        event(new Notify(self::unreadNotify()));
    }

    public static function getAllRoomStatus()
    {
        $roomsRaw = self::with('status', 'activeSession')->select('id', 'name', 'guest_name', 'room_status_id', 'active_session_id')->get();
        $data = [];
        
        foreach ($roomsRaw as $room) {
            if ($room->activeSession) {
                $activeSession = [
                    'hourDuration' => $room->activeSession->hour_duration,
                    'openedAt' => $room->activeSession->opened_at,
                    'sessionType' => $room->activeSession->session_type,
                    'isTimerCountdown' => $room->activeSession->timer_countdown
                ];
            } else {
                $activeSession = null;
            }
            
            $data[] = [
                'id' => $room->id,
                'name' => $room->name,
                'guestName' => $room->guest_name,
                'activeSession' => $activeSession,
                'status' => [
                    'label' => $room->status->name,
                    'color' => $room->status->color
                ]
            ];
        }
        
        return $data;
    }

    public static function isTokenValid($ip, $roomKey, $token = null)
    {
        $room = self::where([
            'ip_address' => $ip,
            'activation_key' => $roomKey
        ])->first();
        
        if (true === empty($room)) {
            return abort(403, 'Room tidak valid! Hubungi software developer untuk informasi lebih lanjut.');
        } elseif (false === empty($token) && $token !== $room->token) {
            return abort(404, 'Token tidak valid!');
        }
        
        return $room;
    }

    public function generateToken()
    {
        $tokenLenght = Setting::get('sessionTokenCharLength', 9);
        $chars = str_repeat('23456789ABCDEFGHJKLMNPRSTUVWXYZ', 4);
        $token = substr(str_shuffle($chars), 0, $tokenLenght);
        
        return $token;
    }

    public function unreadNotify()
    {
        $notify = [];
        $room = self::all();
        foreach ($room as $k => $v) {
            $notify['unread'][] = $room[$k]->unreadNotifications;
        }
        $notify['data']['unread'] = collect($notify['unread'])->flatten()->sortByDesc('updated_at')->values()->all();
        $notify['data']['count'] = count($notify['data']['unread']);
        return $notify['data'];
    }

    public function markAsReadNotify($id)
    {
        if ($id) {
            $this->unreadNotifications->where('id', $id)->markAsRead();
        } else {
            $all = self::all();
            foreach ($all as $k => $v) {
                $all[$k]->unreadNotifications->markAsRead();
            }
        }

        event(new Notify(self::unreadNotify()));
    }
}