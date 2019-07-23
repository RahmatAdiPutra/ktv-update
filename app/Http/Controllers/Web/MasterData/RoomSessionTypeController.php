<?php

namespace App\Http\Controllers\Web\MasterData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\RoomSessionType;
use App\Http\Requests\Web\MasterData\RoomSessionTypeRequest;

class RoomSessionTypeController extends Controller
{
    public function index()
    {
        // return $this->seed(10);
        return view('master-data.room-session-type.index');
    }

    protected function seed($totalRow)
    {
        $faker = \Faker\Factory::create();

        for ($i = 0; $i < $totalRow; $i++) {
            RoomSessionType::create([
                'name' => $faker->name,
                'timer_countdown' => $faker->time('H:i:s', 'now'),
                'count_song_played' => $faker->randomDigit
            ]);
        }

        return 'OK';
    }

    public function data(Request $request)
    {
        $draw = $request->get('draw', 1);
        $start = $request->get('start', 0);
        $limit = $request->get('length', 25);
        $query = RoomSessionType::select('*');

        // build order
        $order = $request->get('order');
        $sortableColumns = [
            '1' => 'name',
            '2' => 'timer_countdown',
            '3' => 'count_song_played'
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

    public function show(RoomSessionType $type)
    {
        return $this->responseSuccess($type);
    }

    public function post(RoomSessionTypeRequest $request)
    {
        try {
            if ($request->id) {
                $message = 'Room Session Type has been updated';
            } else {
                $message = 'Room Session Type has added';
            }

            $request->save($request->only(array_keys($request->rules())), $request->id);

            return $this->responseSuccess(['message' => $message]);
        } catch (\Exception $e) {
            return $this->responseSuccess(['message' => $e->getMessage()]);
        }
    }

    public function destroy(RoomSessionType $type)
    {
        $type->delete();
        return $this->responseSuccess(['message' => 'Room Session Type has been deleted']);
    }
}
