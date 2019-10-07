<?php

namespace App\Http\Controllers\Web\Tool;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tool\SongMap;

class ConvertController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->get('start', 0);
        $limit = $request->get('length', 25);
        $query = SongMap::select('*');
        $paginate = $query->skip($start)
            ->paginate($limit)
            ->toArray();
        return $this->responseSuccess($paginate);
    }
}
