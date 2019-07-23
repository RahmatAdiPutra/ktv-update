<?php

namespace App\Http\Controllers\Web\MasterData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OperatorStation;
use App\Http\Requests\Web\MasterData\OperatorStationRequest;

class OperatorStationController extends Controller
{
    public function index()
    {
        // return $this->seed(10);
        return view('master-data.operator-station.index');
    }

    protected function seed($totalRow)
    {
        $faker = \Faker\Factory::create();

        for ($i = 0; $i < $totalRow; $i++) {
            OperatorStation::create([
                'ip_address' => $faker->ipv4,
                'name' => $faker->name
            ]);
        }

        return 'OK';
    }

    public function data(Request $request)
    {
        $draw = $request->get('draw', 1);
        $start = $request->get('start', 0);
        $limit = $request->get('length', 25);
        $query = OperatorStation::select('*');

        // build order
        $order = $request->get('order');
        $sortableColumns = [
            '1' => 'ip_address',
            '2' => 'name',
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

    public function show(OperatorStation $operator)
    {
        return $this->responseSuccess($operator);
    }

    public function post(OperatorStationRequest $request)
    {
        try {
            if ($request->id) {
                $message = 'Operator has been updated';
            } else {
                $message = 'Operator has added';
            }

            $request->save($request->only(array_keys($request->rules())), $request->id);

            return $this->responseSuccess(['message' => $message]);
        } catch (\Exception $e) {
            return $this->responseSuccess(['message' => $e->getMessage()]);
        }
    }

    public function destroy(OperatorStation $operator)
    {
        $operator->delete();
        return $this->responseSuccess(['message' => 'Operator has been deleted']);
    }
}
