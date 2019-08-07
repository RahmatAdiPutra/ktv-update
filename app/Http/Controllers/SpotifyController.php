<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SpotifyController extends Controller
{
    public function token()
    {
        $client = new \GuzzleHttp\Client();
        $client_id = 'f5f024123d8d48179ecbafb06fccf234';
        $client_secret = '022829eec5954957a462ccf57f7d41e6';
        $grant_type = 'client_credentials';
        $authorization = 'Basic '.base64_encode($client_id.':'.$client_secret);
        $response = $client->request('POST', 'https://accounts.spotify.com/api/token', [
            'headers' => [
                'Authorization' => $authorization
            ],
            'form_params' => [
                'grant_type' => $grant_type
            ]
        ])->getBody()->getContents();
        return json_decode($response, true);
    }

    public function search(Request $request)
    {
        $client = new \GuzzleHttp\Client();
        $token = $this->token(); //json_decode(json_encode($this->token()), true);
        $authorization = 'Bearer '.$token['access_token'];
        $query = $request->q;
        $type = $request->type;
        $market = $request->market;
        $limit = $request->limit;
        $offset = $request->offset;
        $response = $client->request('GET', 'https://api.spotify.com/v1/search', [
            'headers' => [
                'Authorization' => $authorization
            ],
            'query' => [
                'query' => $query,
                'type' => $type,
                'limit' => $limit,
                'offset' => $offset
            ]
        ])->getBody()->getContents();;
        return json_decode($response, true);
    }

    public function artist($id)
    {
        $client = new \GuzzleHttp\Client();
        $token = $this->token();
        $authorization = 'Bearer '.$token['access_token'];
        $response = $client->request('GET', 'https://api.spotify.com/v1/artists/'.$id, [
            'headers' => [
                'Authorization' => $authorization
            ]
        ])->getBody()->getContents();
        return json_decode($response, true);
    }

    public function artistTopTrack($id)
    {
        $client = new \GuzzleHttp\Client();
        $token = $this->token();
        $authorization = 'Bearer '.$token['access_token'];
        $response = $client->request('GET', 'https://api.spotify.com/v1/artists/'.$id.'/top-tracks', [
            'headers' => [
                'Authorization' => $authorization
            ],
            'query' => [
                'country' => 'ID'
            ]
        ])->getBody()->getContents();
        return json_decode($response, true);
    }

    public function artistAlbum($id)
    {
        $client = new \GuzzleHttp\Client();
        $token = $this->token();
        $authorization = 'Bearer '.$token['access_token'];
        $response = $client->request('GET', 'https://api.spotify.com/v1/artists/'.$id.'/albums', [
            'headers' => [
                'Authorization' => $authorization
            ]
        ])->getBody()->getContents();
        return json_decode($response, true);
    }

    public function album($id)
    {
        $client = new \GuzzleHttp\Client();
        $token = $this->token();
        $authorization = 'Bearer '.$token['access_token'];
        $response = $client->request('GET', 'https://api.spotify.com/v1/albums/'.$id, [
            'headers' => [
                'Authorization' => $authorization
            ]
        ])->getBody()->getContents();
        return json_decode($response, true);
    }

    public function albumTrack($id)
    {
        $client = new \GuzzleHttp\Client();
        $token = $this->token();
        $authorization = 'Bearer '.$token['access_token'];
        $response = $client->request('GET', 'https://api.spotify.com/v1/albums/'.$id.'/tracks', [
            'headers' => [
                'Authorization' => $authorization
            ]
        ])->getBody()->getContents();
        return json_decode($response, true);
    }

    public function track($id)
    {
        $client = new \GuzzleHttp\Client();
        $token = $this->token();
        $authorization = 'Bearer '.$token['access_token'];
        $response = $client->request('GET', 'https://api.spotify.com/v1/tracks/'.$id, [
            'headers' => [
                'Authorization' => $authorization
            ]
        ])->getBody()->getContents();
        return json_decode($response, true);
    }

    public function filterTrack($track, $singer)
    {
        $data = [];
        foreach ($track['items'] as $k => $v) {
            // $data[] = $v['album']['images'];
            // $data[] = $v['album']['artists'];
            foreach ($v['album']['artists'] as $ka => $va) {
                $data['artists'][] = $va['name'];
            }
            foreach ($v['album']['images'] as $ki => $vi) {
                $data['images'][] = $vi['url'];
            }
        }
        return $data;
    }
}
