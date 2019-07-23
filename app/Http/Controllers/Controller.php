<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function responseSuccess($payloads = null, $code = 200)
    {
        $response = [
            'error' => false
        ];
        
        if (is_null($payloads) === false) {
            $response['payloads'] = $payloads;
        }
        
        return response($response, $code);
    }
}
