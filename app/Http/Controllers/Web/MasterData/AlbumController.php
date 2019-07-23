<?php

namespace App\Http\Controllers\Web\MasterData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Http\Requests\Web\MasterData\AlbumRequest;

class AlbumController extends Controller
{
    public function index()
    {
        // return $this->seed(10);
        return view('master-data.album.index');
    }

    protected function seed($totalRow)
    {
        $faker = \Faker\Factory::create();

        for ($i = 0; $i < $totalRow; $i++) {
            Album::create([
                'title' => $faker->word,
                'release_date' => $faker->date('Y-m-d', 'now'),
                'cover_art' => $faker->imageUrl(640, 480),
                'code' => $faker->isbn13
            ]);
        }

        return 'OK';
    }

    public function data(Request $request)
    {
        $draw = $request->get('draw', 1);
        $start = $request->get('start', 0);
        $limit = $request->get('length', 25);
        $query = Album::select('*')->with('songs');;

        // build order
        $order = $request->get('order');
        $sortableColumns = [
            '1' => 'title',
            '2' => 'release_date',
            '3' => 'cover_art',
            '4' => 'code'
        ];

        if (isset($sortableColumns[$order[0]['column']])) {
            if (isset($sortableColumns[$order[0]['column']])) {
                $query->orderBy($sortableColumns[$order[0]['column']], $order[0]['dir']);
            }
        } else {
            $query->orderBy('release_date', 'desc');
        }

        $searchTerm = $request->get('search');
        if (empty($searchTerm['value']) === false) {
            $q = '%' . str_replace(' ', '%', trim($searchTerm['value'])) . '%';
            $query->where('release_date', 'like', $q);
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

    public function show(Album $album)
    {
        $album->load('songs');
        return $this->responseSuccess($album);
    }

    public function post(AlbumRequest $request)
    {
        try {
            if ($request->id) {
                $message = 'Album has been updated';
            } else {
                $message = 'Album has added';
            }

            $request->save($request->only(array_keys($request->rules())), $request->id);

            return $this->responseSuccess(['message' => $message]);
        } catch (\Exception $e) {
            return $this->responseSuccess(['message' => $e->getMessage()]);
        }
    }

    public function destroy(Album $album)
    {
        $album->delete();
        return $this->responseSuccess(['message' => 'Album has been deleted']);
    }
}
