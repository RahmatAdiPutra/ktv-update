<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SongGenre;
use App\Models\SongLanguage;
use App\Models\Tool\Song as ToolSong;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Convert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $signature = 'convert:mp4';
    protected $signature = 'convert:mp4 {test? : Testing}'; // argument
    // protected $signature = 'convert:mp4 {--T|test= : Testing}'; // option

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert video to mp4';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            // $this->output->writeln($this->option('test'));
            // $this->output->writeln($this->argument('test'));

            $setup = json_decode(File::get(public_path('dropBox.json')), true);
            $convert = $this->choice('Choice convert ?', $setup['file-sh'], 'newname');
            $this->output->writeln('Run <info>'.$convert.'<info>');
            $this->line('Run....');
            shell_exec('sh '.$setup[env('DROP_BOX')]['path'].$convert.'.sh');
            // shell_exec('sh /home/cyber/public_html/sh/newname.sh');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return;
        }
    }

    public function file($setup)
    {
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
            $data['original'][] = 'mv "'.$pathinfo['dirname'].'/'.$filename.$setup['extension'].'" "'.$pathinfo['dirname'].'/'.preg_replace("/\`/", "\`", $pathinfo['basename']).'"';
            $data['newname'][] = 'mv "'.$pathinfo['dirname'].'/'.preg_replace("/\`/", "\`", $pathinfo['basename']).'" "'.$pathinfo['dirname'].'/'.$filename.$setup['extension'].'"';
        }
        return $data;
    }

    public function song($setup)
    {
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
            $data[] = [
                'title' => $title,
                'artist_label' => $artist,
                'file_path' => $setup['filepath'] . $filename . $setup['extension']
            ];
        }
        return $data;
    }

    public function save($setup, $songs)
    {
        $genre = SongGenre::select('id', 'name')->where('name', 'like', '%'.$setup['genre'].'%')->first();
        $lang = SongLanguage::select('id', 'name')->where('name', 'like', '%'.$setup['lang'].'%')->first();

        if (!empty($genre) && !empty($lang)) {
            $data = [];
            foreach($songs as $field)
            {
                // $data['song'][] = [
                //     'song_genre_id' => $genre->id,
                //     'song_language_id' => $lang->id,
                //     'title' => $field['title'],
                //     'artist_label' => $field['artist_label'],
                //     'file_path' => $field['file_path']
                // ];
                DB::transaction(function () use ($genre, $lang, $field) {
                    $song = new ToolSong();
                    $song->song_genre_id = $genre->id;
                    $song->song_language_id = $lang->id;
                    $song->title = $field['title'];
                    $song->artist_label = $field['artist_label'];
                    $song->file_path = $field['file_path'];
                    $song->save();
                }, 3);
            }
            return $data;
        } else {
            return 'Not available';
        }
    }

    public function convert($setup)
    {
        $data = [];
        $filesInFolder = File::allFiles($setup[env('DROP_BOX')]['dirname']);
        
        foreach($filesInFolder as $path)
        {
            $pathinfo = pathinfo($path);
            $result = File::exists($setup['basepath'].$pathinfo['filename'].$setup['extension']);
            if (in_array(pathinfo($path, PATHINFO_EXTENSION), $setup['extension-allow'])) {
                if (!$result) {
                    $data['convert'][] = 'cp "'.$pathinfo['dirname'].'/'.preg_replace("/\`/", "\`", $pathinfo['basename']).'" "'.$setup['basepath'].preg_replace("/\`/", "\`", $pathinfo['filename']).$setup['extension'].'"';
                }
            } else {
                if (!$result) {
                    $data['convert'][] = 'ffmpeg -i "'.$pathinfo['dirname'].'/'.preg_replace("/\`/", "\`", $pathinfo['basename']).'" "'.$setup['basepath'].preg_replace("/\`/", "\`", $pathinfo['filename']).$setup['extension'].'"';
                }
            }
        }
        return $data;
    }
}
