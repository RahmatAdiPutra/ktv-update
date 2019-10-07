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

        // $search = File::glob('/home/cyber/public_html/new/*.*');
        // $search = File::glob('/home/cyber/public_html/new/CALL YOU MINE#THE CHAINSMOKERS FT BEBE REXHA#BARAT#LEFT.mp4');
        // return $search = $this->searchFile('/home/cyber/public_html', 'CALL YOU MINE#THE CHAINSMOKERS FT BEBE REXHA#BARAT#LEFT.mp4');

        $song = Song::select('*')->where('file_path', 'like', '%'.$pathinfo['filename'].'%')->first();

        $filename = str_slug($songMap->description, '_');
        dd($filename, $songMap->toArray(), $song->toArray());
        // return $this->responseSuccess($data);
    }

    public function searchFile($filepath, $filename) {
        $manuals = [];
        $filesInFolder = File::allFiles($filepath);

        foreach($filesInFolder as $path)
        {
            $data = pathinfo($path);
            $manuals[] = $data;
        }
        return $manuals;
    }
}
