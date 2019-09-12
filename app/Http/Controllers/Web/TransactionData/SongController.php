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
use Carbon\Carbon;
use App\Models\Setting;

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

        $data['languages'] = SongLanguage::select('id', 'name')->orderBy('name')->get();

        $data['genres'] = SongGenre::select('id', 'name')->orderBy('name')->get();

        $data['type'] = ["karaoke","mp3","video"];

        $data['audio'] = ["left","right","none"];

        $data['all'] = $data;

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
            $query->orWhere('title_non_latin', 'like', $q);
            $query->orWhere('artist_label', 'like', $q);
        }

        $lang = $request->get('lang');
        if (empty($lang) === false) {
            $query->where('song_language_id', $lang);
        }

        $checkNull = $request->get('checkNull');
        if (empty($checkNull) === false) {
            $query->whereNull('cover_art');
            $query->whereNull('updated_by');
        }

        $checkNullCover = $request->get('checkNullCover');
        if (empty($checkNullCover) === false) {
            $query->whereNull('cover_art');
            $query->whereNotNull('updated_by');
        }

        $checkNotNull = $request->get('checkNotNull');
        if (empty($checkNotNull) === false) {
            $query->whereNotNull('cover_art');
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
            $song = Song::find($request->id);

            if ($song->updated_by) {
                if ($song->cover_art) {
                    $updatedBy = $song->updated_by;
                } else {
                    $updatedBy = Auth::user()->user_id;
                }
            } else {
                $updatedBy = Auth::user()->user_id;
            }

            // $updatedBy = Auth::user()->user_id;

            if ($request->url_image) {
                $url = $request->url_image;
                $info = pathinfo($url);
                $filename = 'uploads/songs/'.$info['filename'].'.jpg';
                $file = file_get_contents($url);
                $save = file_put_contents(Setting::get('pathImage').$filename, $file);
            } else {
                $filename = $song->cover_art;
            }

            $request->merge([
                'cover_art' => $filename,
                'updated_by' => $updatedBy
            ]);
            
            if ($request->id) {
                $message = 'Song has been updated';
            } else {
                $message = 'Song has added';
            }

            $data = $request->only(array_keys($request->rules()));
            // dd($data);

            // TODO cek lagu punya non latin? 
            if($song->title_non_latin) {

                // buang semua non alphabet
                $judulLagu = preg_replace('/[a-z0-9 \/,\-\*\.\'\"\(\)\%\!\?]/i', '',$data['title']);
                
                if(empty($judulLagu) === false) {
                    $data['title_non_latin'] = $data['title'];
                    unset($data['title']);
                }
                // dd($judulLagu, $data);
            }

            $request->save($data, $request->id);

            return $this->responseSuccess(['message' => $message]);
        } catch (\Exception $e) {
            return $this->responseSuccess(['message' => $e->getMessage()]);
        }
    }

    public function destroy(Song $song)
    {
        unlink(Setting::get('pathImage').$song->cover_art);
        $song->delete();
        return $this->responseSuccess(['message' => 'Song has been deleted']);
    }

    public function getSpotifyTrack($code)
    {
        $spotify = new SpotifyController();
        $explode = explode(":", $code);
        $id = array_pop($explode);
        $search = $spotify->track($id);
        return $search;
    }

    public function autoUpdateCoverArt()
    {
        $song = Song::select('id', 'code');
        $song->whereNotNull('code');
        $song->whereNull('cover_art');
        $song = $song->inRandomOrder()->first();

        if ($song) {
            $spotifyTrack = $this->getSpotifyTrack($song->code);

            $url_image = count($spotifyTrack['album']['images']) ? $spotifyTrack['album']['images'][0]['url'] : '';

            if ($url_image) {
                $url = $url_image;
                $info = pathinfo($url);
                $filename = 'uploads/songs/'.$info['filename'].'.jpg';
                $file = file_get_contents($url);
                $save = file_put_contents($filename, $file);

                $update = Song::find($song->id);
                $update->cover_art = $filename;
                $update->save();

                return 'Updated';
            }
        } else {
            return 'Finish';
        }
    }

    public function autoUpdateReleaseYear()
    {
        $song = Song::select('id', 'code');
        $song->whereNotNull('code');
        $song->whereNull('release_year');
        $song = $song->inRandomOrder()->first();

        if ($song) {
            $spotifyTrack = $this->getSpotifyTrack($song->code);

            $release_date = $spotifyTrack['album']['release_date'];

            if ($release_date) {
                $date = new Carbon($release_date);
                $update = Song::find($song->id);
                $update->release_year = $date->year;
                $update->save();

                echo 'Updated '.$update->id;
                echo "<meta http-equiv='refresh' content='0;url=http://localhost:8000/web/song/test'>";
            }
        } else {
            echo 'Finish';
        }
    }
}
