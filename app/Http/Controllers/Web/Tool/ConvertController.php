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
    public function sample(Request $request)
    {
        $draft = '/home/aman/convert/test.sql';
        $newname = '/home/aman/convert/newname.sh';
        $original = '/home/aman/convert/original.sh';
        $dirname = '/media/hdd2/new/Music/BARAT';

        // $draft = '/home/cyber/Workdir/test.sql';
        // $newname = '/home/cyber/Workdir/newname.sh';
        // $original = '/home/cyber/Workdir/original.sh';
        // $dirname = '/home/cyber/public_html/new';

        $basepath = "hdd1/new/eng/";

        $rename = $this->renameFile($dirname);
        $query = $this->getQuery($dirname, $basepath);

        File::put($draft, implode("\n", $query['songs']));
        File::put($newname, implode("\n", $rename['newname']));
        File::put($original, implode("\n", $rename['original']));

        return 'Sukses';
    }

    public function getQuery($dirname, $basepath)
    {
        $data = [];
        $filesInFolder = File::allFiles($dirname);
        $date = date('Y-m-d H:i:s');

        foreach($filesInFolder as $path)
        {
            $pathinfo = pathinfo($path);
            $exp = explode('#', $pathinfo['filename']);
            $title = Str::title($exp[0]);
            if (count($exp) <= 1) {
                $artist = '';
                $filename = Str::slug($title, '_');
            } else if (count($exp) <= 2) {
                $artist = Str::title($exp[1]);
                $filename = Str::slug($title, '_') . '-' . Str::slug($artist, '_');
            } else if (count($exp) <= 3) {
                $artist = Str::title($exp[1]);
                $lang = Str::title($exp[2]);
                $filename = Str::slug($title, '_') . '-' . Str::slug($artist, '_') . '-' . Str::slug($lang, '_');
            } else {
                $artist = Str::title($exp[1]);
                $lang = Str::title($exp[2]);
                $channel = Str::title($exp[3]);
                $filename = Str::slug($title, '_') . '-' . Str::slug($artist, '_') . '-' . Str::slug($lang, '_') . '-' . Str::slug($channel, '_');
            }
            $file_path = $basepath . $filename . ".mp4";
            $data['songs'][] = "INSERT INTO `ktv_v1`.`songs` (`song_genre_id`, `song_language_id`, `title`, `artist_label`, `file_path`, `created_at` , `updated_at`) VALUES (4, 1, \"$title\", \"$artist\", \"$file_path\", \"$date\", \"$date\");";
        }
        return $data;
    }

    public function renameFile($dirname)
    {
        $data = [];
        $filesInFolder = File::allFiles($dirname);

        foreach($filesInFolder as $path)
        {
            $pathinfo = pathinfo($path);
            $exp = explode('#', $pathinfo['filename']);
            $title = Str::title($exp[0]);
            if (count($exp) <= 1) {
                $artist = '';
                $filename = Str::slug($title, '_');
            } else if (count($exp) <= 2) {
                $artist = Str::title($exp[1]);
                $filename = Str::slug($title, '_') . '-' . Str::slug($artist, '_');
            } else if (count($exp) <= 3) {
                $artist = Str::title($exp[1]);
                $lang = Str::title($exp[2]);
                $filename = Str::slug($title, '_') . '-' . Str::slug($artist, '_') . '-' . Str::slug($lang, '_');
            } else {
                $artist = Str::title($exp[1]);
                $lang = Str::title($exp[2]);
                $channel = Str::title($exp[3]);
                $filename = Str::slug($title, '_') . '-' . Str::slug($artist, '_') . '-' . Str::slug($lang, '_') . '-' . Str::slug($channel, '_');
            }
            $data['newname'][] = "mv \"$dirname/$pathinfo[filename].$pathinfo[extension]\" \"$dirname/$filename.$pathinfo[extension]\"";
            $data['original'][] = "mv \"$dirname/$filename.$pathinfo[extension]\" \"$dirname/$pathinfo[filename].$pathinfo[extension]\"";
        }
        return $data;
    }

    public function index(Request $request)
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
        $filename =  preg_replace("/\((.*?)\)/","",$songMap->description) . '#' . $songMap->singer;
        $filename1 =  preg_replace("/\((.*?)\)/","",$songMap->description);
        $files = $this->searchFile($basepath . $filename . '*');
        $files1 = $this->searchFile($basepath . $filename1 . '*');
        $pathinfo = pathinfo($songMap->file_name);
        dd($songMap->toArray(), $filename, $filename1, $files, $files1);
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

    public function searchFile($filepath) {
        // $file = File::glob('/home/cyber/public_html/new/*.*');
        // $file = File::glob('/home/cyber/public_html/*/A WHOLE NEW WORLD*');
        $file = File::glob($filepath);
        return $file;
    }
}
