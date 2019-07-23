<?php

namespace App\Http\Controllers\Web\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\NeoAuth\User;
use App\Models\Artist;
use App\Models\Song;

class StatisticController extends Controller
{
    public function test()
    {
        $data = [];
        $artist = Artist::select('updated_by')->with('updatedBy');
        $artist->whereNotNull('code');
        $artist = collect($artist->get()->toArray());
        $artist = $artist->pluck('updated_by')->countBy('user_name');

        $song = Song::select('updated_by')->with('updatedBy');
        $song->whereNotNull('code');
        $song = collect($song->get()->toArray());
        $song = $song->pluck('updated_by')->countBy('user_name');

        foreach ($artist as $k => $v) {
            $data['artist'][] = [
                'name' => $k,
                'point' => $v,
            ];
        }

        foreach ($song as $k => $v) {
            $data['song'][] = [
                'name' => $k,
                'point' => $v,
            ];
        }

        $data['artist'] = collect($data['artist'])->sortBy('point')->values()->all();
        $data['song'] = collect($data['song'])->sortBy('point')->values()->all();
        // $data['artist'] = collect($data['artist'])->sortByDesc('point')->values()->all();
        // $data['song'] = collect($data['song'])->sortByDesc('point')->values()->all();

        // $query = Artist::select('*')->with(['country', 'songs' => function($q) { return $q->limit(5); }]);
        // $query->whereIn('language_id', [1,2]);
        // $query->whereNull('flag_check');
        // $query = $query->inRandomOrder()->first();

        // $query = Artist::select('*')->whereIn('id', [2914])->with(['country', 'songs' => function($q) { return $q->limit(1); }])->get();

        // $query =  $query->inRandomOrder()->first();

        // $data['test'] = $query;

        return $data;
    }

    public function index()
    {
        return view('report.statistic.index');
    }

    public function data()
    {
        $data = [];

        $points = \DB::select("
            SELECT updated_by, artist, song, artist+song as total_point FROM (
                SELECT updated_by, SUM(point_artist) as artist, SUM(point_song) as song FROM (
                    SELECT updated_by,count(*) as point_artist, 0 as point_song FROM artists where flag_check=1 group by updated_by
                    UNION 
                    SELECT updated_by, 0 as point_artist, count(*) as point_song from songs where code IS NOT NULL GROUP BY updated_by
                ) t1 GROUP BY updated_by
            ) t2 ORDER BY total_point DESC
        ");

        $usersId = collect($points)->pluck('updated_by');
        $users = User::select('user_id','first_name','last_name')->whereIn('user_id', $usersId)->get()->keyBy('user_id');

        foreach($points as $p) {
            $data[] = [
                'name' => $users[$p->updated_by]->first_name,
                'artist' => $p->artist,
                'song' => $p->song,
                'total_point' => $p->total_point
            ];
        }

        return $this->responseSuccess($data);
    }
}
