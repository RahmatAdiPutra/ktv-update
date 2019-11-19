<?php

namespace App\Http\Controllers\Web\TransactionData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomSession;

class RoomController extends Controller
{
    public function data(Request $request)
    {
        $draw = $request->get('draw', 1);
        $start = $request->get('start', 0);
        $limit = $request->get('length', 25);
        $query = Room::select('*')->with('status', 'activeSession');

        // build order
        $order = $request->get('order');
        $sortableColumns = [
            '1' => 'room_status_id',
            '2' => 'name',
            '3' => 'ip_address',
        ];

        if (isset($sortableColumns[$order[0]['column']])) {
            if (isset($sortableColumns[$order[0]['column']])) {
                $query->orderBy($sortableColumns[$order[0]['column']], $order[0]['dir']);
            }
        } else {
            $query->orderBy('name', 'desc');
        }

        $searchTerm = $request->get('search');
        if (empty($searchTerm['value']) === false) {
            $q = '%' . str_replace(' ', '%', trim($searchTerm['value'])) . '%';
            $query->where('name', 'like', $q);
        }

        // for get data total and last page,
        $paginate = $query->skip($start)
            ->paginate($limit)
            ->toArray();

        $paginateData['total'] = $paginate['total'];
        $paginateData['last_page'] = $paginate['last_page'];

        $paginateData['from'] = $start;
        $paginateData['to'] = $limit + ($start - 1);
        $paginateData['per_page'] = $limit;

        $paginateData['data'] = $query->skip($start)
            ->take($limit)
            ->get();

        return $this->responseSuccess($paginateData);
    }

    public function playlistRoom(RoomSession $session)
    {
        $data = [];
        $data['data'] = $session->load('songs');
        return $this->responseSuccess($data);
    }

    public function addSongToPlaylistRoom(Request $request)
    {
        $data = [];
        // $session->songs()->sync([
        //     ['song_id' => '32047']
        // ]);
        // $data['data'] = $session->load('songs');
        // return $this->responseSuccess($data);
        foreach ($request->all() as $k => $v) {
            $session = RoomSession::find($v['room_session_id']);
            $session->songs()->sync([
                [
                    'song_id' => $v['song_id'],
                    'is_played' => $v['is_played'],
                    'order_num' => $v['order_num'],
                    'count_play' => $v['count_play']
                ]
            ]);
        }
        return $data;
    }

    public function listToken(Request $request)
    {
        $query = Room::select('name as room', 'token');
        if ($request->room) {
            $query->where('name', 'like', '%'.$request->room);
        }
        $query = $query->get();

        return $this->responseSuccess($query);
    }
}
