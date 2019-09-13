<?php

namespace App\Http\Controllers\Web\TransactionData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\SpotifyController;
use App\Models\Artist;
use App\Http\Requests\Web\TransactionData\ArtistRequest;
use Illuminate\Support\Facades\Auth;

class ArtistController extends Controller
{
    public function test(Request $request)
    {
        $data = [];
        $spotify = new SpotifyController();
        // $request->q = 'ZZ WARD';
        $request->type = 'artist';
        $search = $spotify->search($request);

        foreach ($search['artists']['items'] as $kS => $vS) {
            $data[$vS['id']] = $vS['name'];
        }
        // $artistAlbum = $spotify->artistAlbum($search['artists']['items'][0]['id']);
        // $artistTopTrack = $spotify->artistTopTrack($search['artists']['items'][0]['id']);
        // $albumTrack = $spotify->albumTrack("4l3fOJbOwczGU265TtMCrw");
        // $data['search'] = $search;
        // $data['artistAlbum'] = $artistAlbum;
        // $data['artistTopTrack'] = $artistTopTrack;
        // $data['albumTrack'] = $albumTrack;

        return $data;
    }

    public function spotify(Request $request)
    {
        $data = [];
        $spotify = new SpotifyController();
        
        if ($request->id) {
            $artist = Artist::with(['country', 'songs' => function($q) { return $q->limit(5); }])->find($request->id);
            $q = $request->artist;
        } else {
            $artist = Artist::select('*')->with(['country', 'songs' => function($q) { return $q->limit(5); }]);
            $artist->whereIn('language_id', [1,2]);
            $artist->whereNull('flag_check');
            $artist = $artist->inRandomOrder()->first();
            $q = $artist->name;
        }

        $request->q = $q;
        $request->type = 'artist';
        $search = $spotify->search($request);
        $data['artists'] = $artist;
        $data['spotify'] = $search;
        return $this->responseSuccess($data);
    }

    protected function layoutBase(Request $request)
    {
        $data = [];

        return $data;
    }
    
    public function index(Request $request)
    {
        $data = $this->layoutBase($request);
        return view('transaction-data.artist.index', $data);
    }

    public function data(Request $request)
    {
        $draw = $request->get('draw', 1);
        $start = $request->get('start', 0);
        $limit = $request->get('length', 25);
        $query = Artist::select('*')->with('songs', 'albums', 'category', 'updatedBy');

        // build order
        $order = $request->get('order');
        $sortableColumns = [
            '1' => 'name',
            '2' => 'name_non_latin'
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

        return $this->responseSuccess($paginate);
    }

    public function show(Artist $artist)
    {
        $artist->load('songs', 'albums', 'category', 'country', 'updatedBy');
        return $this->responseSuccess($artist);
    }

    public function post(ArtistRequest $request)
    {
        // dd($request->all());
        try {
            if ($request->url_image) {
                $url = $request->url_image;
                $info = pathinfo($url);
                $filename = 'uploads/artists/'.$info['filename'].'.jpg';
                $file = file_get_contents($url);
                $save = file_put_contents(Setting::get('pathImage').$filename, $file);
                $flag_check = 1;
            } else {
                $filename = null;
                $flag_check = 0;
            }

            $request->merge([
                'photo' => $filename,
                'flag_check' => $flag_check,
                'updated_by' => Auth::user()->user_id
            ]);

            if ($request->id) {
                $message = 'Artist has been updated';
            } else {
                $message = 'Artist has added';
            }

            $request->save($request->only(array_keys($request->rules())), $request->id);

            return $this->responseSuccess(['message' => $message]);
        } catch (\Exception $e) {
            return $this->responseSuccess(['message' => $e->getMessage()]);
        }
    }

    public function destroy(Artist $artist)
    {
        
        if ($artist->photo) {
            unlink(Setting::get('pathImage').$artist->photo);
        }
        $artist->delete();
        return $this->responseSuccess(['message' => 'Artist has been deleted']);
    }
}
