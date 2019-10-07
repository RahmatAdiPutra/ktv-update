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
        $songMap = SongMap::select('*')->first();
        $pathinfo = pathinfo($songMap->file_name);
        $name =  $songMap->description . '#' . $songMap->singer . '#' . $songMap->language;

        // $search = File::glob('/home/cyber/public_html/new/*.*');
        // $search = File::glob('/home/cyber/public_html/*/A WHOLE NEW WORLD*');
        // $search = $this->searchFile('/home/*/*/*/', 'A WHOLE NEW WORLD*');
        // $search = $this->searchFile('/media/hdd2/new/Music/INDONESIA/', $name . '*');
        $search = $this->searchFile('/media/hdd2/new/Music/*/', $pathinfo['filename'] . '*');
        // $search = '';

        $song = Song::select('*')->where('file_path', 'like', '%'.$pathinfo['filename'].'%')->first();

        $filename = str_slug($songMap->description, '_');
        dd($search, $name, $filename, $pathinfo, $songMap->toArray(), $song->toArray());
        // return $this->responseSuccess($data);
    }

    public function searchFile($filepath, $filename) {
        // $search = File::glob('/home/cyber/public_html/new/*.*');
        // $search = File::glob('/home/cyber/public_html/*/A WHOLE NEW WORLD*');
        return File::glob($filepath.$filename);
    }
}
