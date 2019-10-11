<?php

namespace App\Http\Controllers\Web\Tool;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Song;
use App\Models\SongGenre;
use App\Models\SongLanguage;
use App\Models\Tool\Song as ToolSong;
use App\Models\Tool\SongMap;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Models\Tool\Setting;

class ConvertController extends Controller
{
    

    public function index(Request $request)
    {
        /*
        1. function untuk handle :
           - membuat perintah rename file to original -> bentuk file shell script
           - membuat perintah rename file to newname -> bentuk file shell script
           - membuat data files -> bentuk file json
           - save data file
        2. function untuk hadle update 
        3. function untuk handle excute shell script
        */
        
        // $setup = [
        //     'development' => [
        //         'dirname' => '/home/cyber/public_html/new/',
        //         'path' => '/home/cyber/public_html/sh/'
        //     ],
        //     'production' => [
        //         'dirname' => '/media/hdd2/new/Music/INDONESIA/',
        //         'path' => '/home/aman/convert/'
        //     ],
        //     'basepath' => 'hdd1/new/ind/',
        //     'extension' => '.mp4',
        //     'genre' => 'pop',
        //     'lang' => 'indonesia',
        // ];

        // {"development":{"dirname":"/home/cyber/public_html/new/","path": "/home/cyber/public_html/sh/"},"production":{"dirname": "/media/hdd2/new/Music/INDONESIA/","path": "/home/aman/convert/"},"basepath":"hdd1/new/ind/","extension":".mp4","genre":"pop","lang":"indonesia"}

        // scp aman@192.168.7.224:/home/aman/convert/rename_new.sh /home/cyber/public_html/sh/
        // scp aman@192.168.7.224:/home/aman/convert/rename_original.sh /home/cyber/public_html/sh/

        // $setup = Setting::get('dropBox');
        $setup = json_decode(File::get(public_path('dropBox.json')), true);

        // $files = $this->files($setup);
        return $files = $this->update($setup);

        // File::put($setup[env('DROP_BOX')]['path'].'rename_new.sh', implode("\n", $files['rename_new']));
        // File::put($setup[env('DROP_BOX')]['path'].'rename_original.sh', implode("\n", $files['rename_original']));
        File::put($setup[env('DROP_BOX')]['path'].'convert_update.sh', implode("\n", $files['update']));
        // File::put(public_path('files/songs.json'), json_encode($files['songs']));

        return 'Done';
    }

    public function files($setup)
    {
        $song = new ToolSong();
        $genre = SongGenre::select('id', 'name')->where('name', 'like', '%'.$setup['genre'].'%')->first();
        $lang = SongLanguage::select('id', 'name')->where('name', 'like', '%'.$setup['lang'].'%')->first();

        if (!empty($genre) && !empty($lang)) {
            $data = [];
            $filesInFolder = File::allFiles($setup[env('DROP_BOX')]['dirname']);

            foreach($filesInFolder as $path)
            {
                $pathinfo = pathinfo($path);
                $exp = explode('#', $pathinfo['filename']);
                $title = preg_replace("/\s\((.*?)\)/", "", Str::title($exp[0])); // hilangin dalam kurung
                if (count($exp) > 1) {
                    $artist = Str::title($exp[1]);
                    $filename = Str::slug($title, '_') . '-' . Str::slug($artist, '_');
                } else {
                    $artist = '';
                    $filename = Str::slug($title, '_');
                }
                $data['original'][] = "mv \"$pathinfo[dirname]$filename$setup[extension]\" \"$pathinfo[dirname]/$pathinfo[basename]\"";
                $data['newname'][] = "mv \"$pathinfo[dirname]/$pathinfo[basename]\" \"$pathinfo[dirname]/$filename$setup[extension]\"";
                $data['songs'][] = [
                    'song_genre_id' => $genre->id,
                    'song_language_id' => $lang->id,
                    'title' => $title,
                    'artist_label' => $artist,
                    'file_path' => $setup['filepath'] . $filename . $setup['extension']
                ];
            }
            $song->insert($data['songs']);
            return $data;
        } else {
            return 'Not available';
        }
    }

    public function update($setup)
    {
        $data = [];
        $filesInFolder = File::allFiles($setup[env('DROP_BOX')]['dirname']);
        
        foreach($filesInFolder as $path)
        {
            $pathinfo = pathinfo($path);
            $result = File::exists($setup['basepath'].$pathinfo['filename'].$setup['extension']);
            if (!in_array($pathinfo['extension'], $setup['extension_allow'])) {
                if (!$result) {
                    $data['update'][] = 'cp "'.$pathinfo['dirname'].'/'.$pathinfo['basename'].'" "'.$setup['basepath'].$pathinfo['filename'].$setup['extension'].'"';
                }
            } else {
                if (!$result) {
                    $data['update'][] = 'ffmpeg -i "'.$pathinfo['dirname'].'/'.$pathinfo['basename'].'" "'.$setup['basepath'].$pathinfo['filename'].$setup['extension'].'"';
                }
            }
            // $data[] = $setup['extension_allow'];
        }
        return $data;
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

        // $file = File::glob('/home/cyber/public_html/new/*.*');
        // $file = File::glob('/home/cyber/public_html/*/A WHOLE NEW WORLD*');

        // 1
        // $songMap = SongMap::select('*')->first();
        // $songMap = SongMap::select('*')->inRandomOrder()->first();
        $songMap = SongMap::select('*')->where('language', 'INDONESIAN')->inRandomOrder()->first();

        // 2
        $basepath = '/media/hdd2/new/Music/INDONESIA/';
        $filename =  preg_replace("/\s\((.*?)\)/","",$songMap->description) . '#' . $songMap->singer;
        $filename1 =  preg_replace("/\s\((.*?)\)/","",$songMap->description);
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
        $file = File::glob($filepath);
        return $file;
    }

    public function sample1(Request $request)
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
}
