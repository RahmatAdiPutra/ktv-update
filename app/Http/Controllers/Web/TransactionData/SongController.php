<?php

namespace App\Http\Controllers\Web\TransactionData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\SpotifyController;
use App\Models\Song;
use App\Http\Requests\Web\TransactionData\SongRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\SongGenre;
use App\Models\SongLanguage;
use App\Models\Artist;
use App\Models\ArtistCategory;
use App\Models\Album;

class SongController extends Controller
{
    public function test(Request $request)
    {
        $spotify = new SpotifyController();
        // $request->q = 'Nella Hip Hop Koplo';
        // $request->type = 'album,artist,track';
        $request->q = 'sellow';
        $request->type = 'track';
        // $request->market = 'ID';
        $request->limit = 20;
        // $request->offset = 0;
        $search = $spotify->search($request);
        // $artist = $spotify->artistAlbum($search['artists']['items'][0]['id']);
        // $artistTrack = $spotify->artistTopTrack($search['artists']['items'][0]['id']);
        // $album = $spotify->albumTrack("4l3fOJbOwczGU265TtMCrw");
        // return $this->seed($search['artists']['items'][0]);
        return $search;
        // return $artist;
        // return $album;
        // return $artistTrack;

        // $singer = 'NIRWANA';
        // return $spotify->filterTrack($search['tracks'],$singer);
    }

    public function spotify(Request $request)
    {
        $spotify = new SpotifyController();
        $q = $request->title.' '.'artist:"'.$request->artist.'"';
        $request->q = $q;
        $request->type = 'track';
        $request->limit = 20;

        try {
            $search = $spotify->search($request);
            if (count($search['tracks']['items'])) {
                return $search;
            }
            $q = $request->title;
            $request->q = $q;
            $search = $spotify->search($request);
            return $this->responseSuccess($search);
        } catch (\Exception $e) {
            return $this->responseSuccess(['message' => $e->getMessage()]);
        }
    }


    protected function layoutBase(Request $request)
    {
        $data = [];

        return $data;
    }

    public function index(Request $request)
    {
        $data = $this->layoutBase($request);
        return view('transaction-data.song.index', $data);
    }

    public function data(Request $request)
    {
        $draw = $request->get('draw', 1);
        $start = $request->get('start', 0);
        $limit = $request->get('length', 25);
        $query = Song::select('*')->with('language', 'genre', 'artists', 'albums', 'updatedBy');

        // build order
        $order = $request->get('order');
        $sortableColumns = [
            '1' => 'title',
            '2' => 'artist_label',
        ];

        if (isset($sortableColumns[$order[0]['column']])) {
            if (isset($sortableColumns[$order[0]['column']])) {
                $query->orderBy($sortableColumns[$order[0]['column']], $order[0]['dir']);
            }
        } else {
            $query->orderBy('title', 'desc');
        }

        $searchTerm = $request->get('search');
        if (empty($searchTerm['value']) === false) {
            $q = '%' . str_replace(' ', '%', trim($searchTerm['value'])) . '%';
            $query->where('title', 'like', $q);
            $query->orWhere('artist_label', 'like', $q);
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

    public function show(Song $song)
    {
        $song->load('language', 'genre', 'albums', 'artists', 'updatedBy');
        return $this->responseSuccess($song);
    }

    public function post(SongRequest $request)
    {
        // dd($request->all());
        try {
            if ($request->url_image) {
                $url = $request->url_image;
                $info = pathinfo($url);
                $filename = 'uploads/songs/'.$info['filename'].'.jpg';
                $file = file_get_contents($url);
                $save = file_put_contents($filename, $file);
            } else {
                $filename = null;
            }

            $request->merge([
                'cover_art' => $filename,
                'updated_by' => Auth::user()->user_id
            ]);
            
            if ($request->id) {
                $message = 'Song has been updated';
            } else {
                $message = 'Song has added';
            }

            $request->save($request->only(array_keys($request->rules())), $request->id);

            return $this->responseSuccess(['message' => $message]);
        } catch (\Exception $e) {
            return $this->responseSuccess(['message' => $e->getMessage()]);
        }
    }
}
