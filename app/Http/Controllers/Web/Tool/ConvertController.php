<?php

namespace App\Http\Controllers\Web\Tool;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tool\Song;
use App\Models\Tool\SongMap;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ConvertController extends Controller
{
    public function index(Request $request)
    {
        $result = [];
        $filesInFolder = File::allFiles('/media/hdd2/new/Music/INDONESIA');
        // $filesInFolder = File::allFiles('/home/cyber/public_html/new');

        $format = "mp4";
        $basepath = "/media/hdd1/new/ind/";

        foreach($filesInFolder as $path)
        {
            $pathinfo = pathinfo($path);
            // $output = $basepath.$pathinfo['filename'].".".$format;
            $exp = explode('#', $pathinfo['filename']);
            $title = Str::title($exp[0]);
            if (count($exp) > 1) {
                $artist = Str::title($exp[1]);
            } else {
                $artist = '';
            }
            // $language = Str::title($exp[2]);
            // $audio_channel = Str::lower($exp[3]);
            $result[] = [
                'title' => $title,
                'artist' => $artist,
                // 'language' => $language,
                // 'audio_channel' => $audio_channel
            ];
        }
        return $result;
    }



    public function sample(Request $request)
    {
        /*
        1. ambil satu baris data tabel song_maps di database 192.168.70.64
        2. cek file di server 192.168.7.224
           - jika ada, lanjut step 3
           - jika tidak ada, kembali step 1 dan update flag_check=1
        3. cek kode file di tabel song database 192.168.7.226
           - jika ada, file_path update
           - jika tidak ada, tambah baru
        4. rename file di server 192.168.7.224
        */

        // 1
        // $songMap = SongMap::select('*')->first();
        // $songMap = SongMap::select('*')->inRandomOrder()->first();
        $songMap = SongMap::select('*')->where('language', 'INDONESIAN')->inRandomOrder()->first();

        // 2
        $basepath = '/media/hdd2/new/Music/INDONESIA/';
        $filename =  $songMap->description . '#' . $songMap->singer . '#' . $songMap->language;
        $pathinfo = pathinfo($songMap->file_name);
        $files = $this->searchFile($basepath, $filename . '*');
        dd($songMap->toArray(), $filename.'*', $files);
        if (!empty($files)) {

        } else {
            $files = $this->searchFile($basepath, $pathinfo['filename'] . '*');
            if (!empty($files)) {

            } else {
                return 'step 1';
            }
        }
        $pathinfonew = pathinfo($files[0]);
        $uri_parts = explode('/', $pathinfonew['dirname']);
        $uri_tail = end($uri_parts);

        // 3
        $song = Song::select('*')->where('file_path', 'like', '%'.$pathinfo['filename'].'%')->first();
        if (!empty($song)) {

        } else {
            return 'tambah baru';
        }

        // $filenamenew = str_slug($songMap->description, '_') . '-' . $songMap->singer . '-' . $songMap->language;;
        dd($songMap->toArray(), $files, $filename, $pathinfo, $uri_tail, $pathinfonew, $song->toArray());
        // return $this->responseSuccess($data);
    }

    public function searchFile($filepath, $filename) {
        // $file = File::glob('/home/cyber/public_html/new/*.*');
        // $file = File::glob('/home/cyber/public_html/*/A WHOLE NEW WORLD*');
        $file = File::glob($filepath.$filename);
        return $file;
    }
}
