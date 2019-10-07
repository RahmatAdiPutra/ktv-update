<?php

namespace App\Http\Controllers\Web\Tool;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Song;
use App\Models\Tool\SongMap;
use Illuminate\Support\Facades\File;

class ConvertController extends Controller
{
    public function index(Request $request)
    {
        /*
        1. ambil satu baris data file video di tabel song_maps dari database 192.168.70.64
        2. cek file video di server 192.168.7.224
           - jika ada, lanjut step 3
           - jika tidak ada, kembali step 1
        3. cek kode file di tabel song dari database 192.168.7.226
           - jika ada, data diganti
           - jika tidak ada, tambah baru
        4. rename file video di server 192.168.7.224
        */

        // 1
        $songMap = SongMap::select('*')->first();

        // 2
        $basepath = '/media/hdd2/new/Music/*/';
        $filename =  $songMap->description . '#' . $songMap->singer . '#' . $songMap->language;
        $pathinfo = pathinfo($songMap->file_name);

        // $search = '';
        // $search = $this->searchFile('/home/*/*/*/', 'A WHOLE NEW WORLD*');
        $search = $this->searchFile($basepath, $filename . '*');
        if (!empty($search)) {

        } else {
            $search = $this->searchFile($basepath, $pathinfo['filename'] . '*');
            if (!empty($search)) {

            } else {
                return 'step 1';
            }
        }

        // 3
        $song = Song::select('*')->where('file_path', 'like', '%'.$pathinfo['filename'].'%')->first();

        // $filename = str_slug($songMap->description, '_');
        dd($songMap->toArray(), $search, $filename, $pathinfo, $song->toArray());
        // return $this->responseSuccess($data);
    }

    public function searchFile($filepath, $filename) {
        // $file = File::glob('/home/cyber/public_html/new/*.*');
        // $file = File::glob('/home/cyber/public_html/*/A WHOLE NEW WORLD*');
        $file = File::glob($filepath.$filename);
        return $file;
    }
}
