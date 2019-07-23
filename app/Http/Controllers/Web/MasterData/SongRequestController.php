<?php

namespace App\Http\Controllers\Web\MasterData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\SongRequest;
use App\Http\Requests\Web\MasterData\SongRequestRequest;

class SongRequestController extends Controller
{
    public function index()
    {
        // return $this->seed(10);
        return view('master-data.song-request.index');
    }

    protected function seed($totalRow)
    {
        $faker = \Faker\Factory::create();

        for ($i = 0; $i < $totalRow; $i++) {
            SongRequest::create([
                'title' => $faker->name,
                'artist' => $faker->name,
                'processed' => $faker->randomDigit
            ]);
        }

        return 'OK';
    }

    public function data(Request $request)
    {
        $draw = $request->get('draw', 1);
        $start = $request->get('start', 0);
        $limit = $request->get('length', 25);
        $query = SongRequest::select('*');

        // build order
        $order = $request->get('order');
        $sortableColumns = [
            '1' => 'title',
            '2' => 'artist',
            '3' => 'processed'
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

    public function show(SongRequest $request)
    {
        return $this->responseSuccess($request);
    }

    public function post(SongRequestRequest $request)
    {
        try {
            if ($request->id) {
                $message = 'Song Request has been updated';
            } else {
                $message = 'Song Request has added';
            }

            $request->save($request->only(array_keys($request->rules())), $request->id);

            return $this->responseSuccess(['message' => $message]);
        } catch (\Exception $e) {
            return $this->responseSuccess(['message' => $e->getMessage()]);
        }
    }

    public function destroy(SongRequest $request)
    {
        $request->delete();
        return $this->responseSuccess(['message' => 'Song Request has been deleted']);
    }
}
