<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\Tool\Setting;
use App\Http\Controllers\Web\Tool\ConvertController;

class Convert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $signature = 'convert:mp4';
    // protected $signature = 'convert:mp4 {test? : Testing}'; // argument
    // protected $signature = 'convert:mp4 {--T|test : Testing}'; // option without value
    // protected $signature = 'convert:mp4 {--T|test= : Testing}'; // option with value

    protected $signature = 'convert:mp4
                            {--a|all-job : Run all jobs step by step}
                            {--r|rename : First step run job rename}
                            {--s|save : Second step run job save data}
                            {--c|convert : Third step run job convert to mp4 (in the form of a shell script file)}
                            {--m|make : Create shell script rename file to new name and original name (for backup name file)}
                            {--e|execute : Run shell script}';

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
            // $setup = Setting::get('dropBox');
            $setup = json_decode(File::get(public_path('dropBox.json')), true);

            $convert = new ConvertController();

            $jobs = collect([
                $this->option('all-job'),
                $this->option('rename'),
                $this->option('save'),
                $this->option('convert'),
                $this->option('make'),
                $this->option('execute')
            ]);
            
            if ($jobs->sum() > 1) {
                $this->output->writeln('Command too many options');
            } else {
                if ($this->option('all-job')) {
                    $this->output->writeln('Run all job step by step');
                    $this->file($convert, $setup);
                    $this->rename($convert, $setup);
                    $this->save($convert, $setup);
                    $this->convert($convert, $setup);
                } else if ($this->option('rename')) {
                    $this->output->writeln('Run job rename');
                    $this->rename($convert, $setup);
                } else if ($this->option('save')) {
                    $this->output->writeln('Run job save data');
                    $this->save($convert, $setup);
                } else if ($this->option('convert')) {
                    $this->output->writeln('Run job convert to mp4');
                    $this->convert($convert, $setup);
                } else if ($this->option('make')) {
                    $this->output->writeln('Create shell script rename file to new name and original name (for backup name file)');
                    $this->file($convert, $setup);
                } else if ($this->option('execute')) {
                    $this->output->writeln('Run shell script');
                    $script = $this->choice('Choose script for run ?', $setup['script']['name'], 'newname');
                    $filepath = $setup[env('DROP_BOX')]['path']['script'].$script.'_'.$setup['lang'].$setup['script']['extension'];
                    $this->output->writeln('Run <info>'.$filepath.'<info>');
                    $this->executeScript($filepath);
                } else {
                    // 
                }
            }
            $this->line('Done....');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return;
        }
    }

    public function file($convert, $setup)
    {
        $renameFile = $convert->file($setup);
        $filepath_original = $setup[env('DROP_BOX')]['path']['script'].$setup['script']['name']['original'].'_'.$setup['lang'].$setup['script']['extension'];
        $filepath_newname = $setup[env('DROP_BOX')]['path']['script'].$setup['script']['name']['newname'].'_'.$setup['lang'].$setup['script']['extension'];
        $commands_original = implode("\n", $renameFile['original']);
        $commands_newname = implode("\n", $renameFile['newname']);
        $this->make($filepath_original, $commands_original);
        $this->make($filepath_newname, $commands_newname);
    }

    public function rename($convert, $setup)
    {
        $renameFile = $convert->file($setup);
        $commands = $renameFile['newname'];
        
        foreach($commands as $command)
        {
            shell_exec($command);
        }
    }

    public function save($convert, $setup)
    {
        $convert->save($setup, $convert->song($setup));
    }

    public function convert($convert, $setup)
    {
        $convertFile = $convert->convert($setup);
        $filepath = $setup[env('DROP_BOX')]['path']['script'].$setup['script']['name']['convert'].'_'.$setup['lang'].$setup['script']['extension'];
        $commands = implode("\n", $convertFile['convert']);
        $this->make($filepath, $commands);
    }

    public function make($filepath, $commands)
    {
        File::put($filepath, $commands);
    }

    public function executeScript($filepath)
    {
        shell_exec('sh '.$filepath);
    }
}
