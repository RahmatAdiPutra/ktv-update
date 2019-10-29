<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

class YoutubeController extends Controller
{
    public function test(Request $request)
    {
        // $request->refresh_token = '1//0gSEJkb0fHAiHCgYIARAAGBASNwF-L9Ir7vzyfkWB0Lw1h6jpQ2I6V6C7xkjdm1yzOVRtCnX5RRMOQcuG0-vKUj0JIgmJAT6JehQ';
        // $request->part = 'snippet';
        // $request->order = 'viewCount';
        // $request->type = 'video';
        // $request->q = 'i follow rivers';
        // $request->videoDefinition = 'high';
        // $request->maxResults = '1';
        // $request->videoEmbeddable = true;

        #channelname
        #judul
        #videoid
        #image

        // 2754

        // return $this->search($request);

        // $search = collect(['api' => Str::lower('coba')]);
        // $search = $search->merge(['data' => 'req']);
        // return $this->responseSuccess($search);
        // dd(now(),now()->addHours(24));
    }

    public function index(Request $request)
    {
        return view('youtube.index');
    }

    public function video(Request $request)
    {
        $request->refresh_token = '1//0gSEJkb0fHAiHCgYIARAAGBASNwF-L9Ir7vzyfkWB0Lw1h6jpQ2I6V6C7xkjdm1yzOVRtCnX5RRMOQcuG0-vKUj0JIgmJAT6JehQ';
        $request->part = 'snippet';
        $request->order = 'viewCount';
        $request->type = 'video';
        // $request->q = 'i follow rivers';
        $request->videoDefinition = 'high';
        $request->maxResults = '5';
        $request->videoEmbeddable = true;

        try {
            if (Cache::has(Str::lower($request->q))) {
                $search = collect(['api' => 'From Cache']);
                $search = $search->merge(json_decode(Cache::get(Str::lower($request->q)), true));
            } else {
                $search = collect(['api' => 'From Youtube']);
                $search = $search->merge($this->search($request));
            }
            return $this->responseSuccess($search);
        } catch (\Exception $e) {
            return $this->responseSuccess($e->getMessage());
        }

    }
    
    public function authorizeCode()
    {
        $client_id = 'client_id=887630562006-d0p9q62133bkdf475kvug4uit96u6vpr.apps.googleusercontent.com&';
        $redirect_uri = 'redirect_uri=http://localhost/playground/laravel/simple-app/public/youtube/token&';
        $response_type = 'response_type=code&';
        $scope = 'scope=https://www.googleapis.com/auth/youtube';
        return redirect('https://accounts.google.com/o/oauth2/v2/auth?'.$client_id.$redirect_uri.$response_type.$scope);
    }

    public function token(Request $request)
    {
        try {
            $client = new \GuzzleHttp\Client();
            $code = $request->code;
            $client_id = '887630562006-d0p9q62133bkdf475kvug4uit96u6vpr.apps.googleusercontent.com';
            $client_secret = 'fZdDG6zugjXtRkoiu_Tz-zXs';
            $grant_type = 'authorization_code';
            $redirect_uri = 'http://localhost/playground/laravel/simple-app/public/youtube/token';
            $response = $client->request('POST', 'https://oauth2.googleapis.com/token', [
                'form_params' => [
                    'code' => $code,
                    'client_id' => $client_id,
                    'client_secret' => $client_secret,
                    'grant_type' => $grant_type,
                    'redirect_uri' => $redirect_uri
                ]
            ])->getBody()->getContents();
            return json_decode($response, true);
        } catch (\Exception $e) {
            return $this->responseSuccess($e->getMessage());
        }
    }

    public function refreshToken(Request $request)
    {
        $client = new \GuzzleHttp\Client();
        $refresh_token = $request->refresh_token;
        $client_id = '887630562006-d0p9q62133bkdf475kvug4uit96u6vpr.apps.googleusercontent.com';
        $client_secret = 'fZdDG6zugjXtRkoiu_Tz-zXs';
        $grant_type = 'refresh_token';
        $response = $client->request('POST', 'https://oauth2.googleapis.com/token', [
            'form_params' => [
                'refresh_token' => $refresh_token,
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'grant_type' => $grant_type
            ]
        ])->getBody()->getContents();
        return json_decode($response, true);
    }

    public function search(Request $request)
    {
        $client = new \GuzzleHttp\Client();
        $token = $this->refreshToken($request); //json_decode(json_encode($this->token()), true);
        $authorization = 'Bearer '.$token['access_token'];
        $part = $request->part;
        $order = $request->order;
        $type = $request->type;
        $q = $request->q;
        $videoDefinition = $request->videoDefinition;
        $maxResults = $request->maxResults;
        $videoEmbeddable = $request->videoEmbeddable;
        $response = $client->request('GET', 'https://www.googleapis.com/youtube/v3/search', [
            'headers' => [
                'Authorization' => $authorization
            ],
            'query' => [
                'part' => $part,
                'order' => $order,
                'type' => $type,
                'q' => $q,
                'videoDefinition' => $videoDefinition,
                'maxResults' => $maxResults,
                // 'videoEmbeddable' => $videoEmbeddable
            ]
        ])->getBody()->getContents();
        Cache::put(Str::lower($q), $response, now()->addHours(24));
        return json_decode($response, true);
    }
}
