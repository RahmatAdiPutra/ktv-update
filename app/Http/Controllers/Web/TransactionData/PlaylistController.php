<?php

namespace App\Http\Controllers\Web\TransactionData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\TransactionData\PlaylistRequest;
use App\Models\Playlist;
use App\Models\PlaylistCategory;
use App\Models\Song;

class PlaylistController extends Controller
{
    protected function layoutBase(Request $request)
    {
        $data = [];

        $data['playlist_category'] = PlaylistCategory::select('id', 'name as text')->orderBy('name')->get();

        $data['all'] = $data;

        return $data;
    }
    
    public function index(Request $request)
    {
        $data = $this->layoutBase($request);
        return view('transaction-data.playlist.index', $data);
    }

    public function data(Request $request)
    {
        $draw = $request->get('draw', 1);
        $start = $request->get('start', 0);
        $limit = $request->get('length', 25);
        $query = Playlist::select('*')->with('songs', 'category');

        // build order
        $order = $request->get('order');
        $sortableColumns = [
            '1' => 'name',
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

    public function post(PlaylistRequest $request)
    {
        // dd($request->all());
        try {
            $message = \DB::transaction(function() use ($request) {
                if ($request->id) {
                    $songs = [];
                    $playlist = Playlist::find($request->id);
                    if (!empty($request->songs)) {
                        foreach ($request->songs as $index => $songId) {
                            $song = Song::find($songId);
                            $songs[] =  [
                                'song_id' => $song->id,
                                'song_genre_id' => $song->song_genre_id,
                                'song_language_id' => $song->song_language_id,
                                'order_num' => $index+1,
                                'playlist_category_id' => $playlist->playlist_category_id
                            ];
                        }
                    }
                    $playlist->songs()->sync($songs);
                    $message = 'Playlist has been updated';
                } else {
                    $message = 'Playlist has added';
                }

                $request->save($request->only(array_keys($request->rules())), $request->id);

                return $message;
            });
            return $this->responseSuccess(['message' => $message]);
        } catch (\Exception $e) {
            return $this->responseSuccess(['message' => $e->getMessage()]);
        }
    }

    public function destroy(Playlist $playlist)
    {
        $playlist->delete();
        return $this->responseSuccess(['message' => 'Playlist has been deleted']);
    }
}
