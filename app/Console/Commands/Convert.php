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
                            {--R|flag-newpath : Flag newpath use for options [-a, -r]}
                            {--s|save : Second step run job save data}
                            {--S|flag-save : Flag save use for options [-a, -s]}
                            {--c|convert : Third step run job convert to mp4}
                            {--m|make : Create shell script for rename file to new name and original name and convert to mp4 (for backup)}
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
            $setup['base'] = $setup[env('DROP_BOX')]['base'];
            $setup['flag']['save'] = $this->option('flag-save');
            $setup['flag']['newpath'] = $this->option('flag-newpath');

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
                    $this->output->writeln('Create shell script');
                    $this->file($convert, $setup);
                } else if ($this->option('execute')) {
                    $this->output->writeln('Run shell script');
                    $script = $this->choice('Choose script for run ?', $setup['script']['name'], 'newname');
                    $filepath = $setup['base']['scriptpath'].$script.'_'.$setup['lang'].$setup['script']['extension'];
                    $this->output->writeln('Run <info>'.$filepath.'<info>');
                    $this->executeScript($filepath);
                } else {
                    $this->output->writeln('Run need a option {-a | -r | -s | -c | -m | -e}');
                    return;
                }
            }
            $this->line('Done....');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return;
        }
    }

    public function save($convert, $setup)
    {
        $convert->save($setup, $convert->song($setup));
    }

    public function file($convert, $setup)
    {
        $convertFile = $convert->convert($setup);
        $renameFile = $convert->file($setup);
        
        $filepath_convert = $setup['base']['scriptpath'].$setup['script']['name']['convert'].'_'.$setup['lang'].$setup['script']['extension'];
        $filepath_original = $setup['base']['scriptpath'].$setup['script']['name']['original'].'_'.$setup['lang'].$setup['script']['extension'];
        $filepath_newname = $setup['base']['scriptpath'].$setup['script']['name']['newname'].'_'.$setup['lang'].$setup['script']['extension'];

        $commands_convert = implode("\n", $convertFile['convert']);
        $commands_original = implode("\n", $renameFile['original']);
        $commands_newname = implode("\n", $renameFile['newname']);

        $this->make($filepath_convert, $commands_convert);
        $this->make($filepath_original, $commands_original);
        $this->make($filepath_newname, $commands_newname);
    }

    public function make($filepath, $commands)
    {
        File::put($filepath, $commands);
    }

    public function rename($convert, $setup)
    {
        $renameFile = $convert->file($setup);
        $commands = $renameFile['newname'];
        
        $this->executeCommand($commands);
    }

    public function convert($convert, $setup)
    {
        $convertFile = $convert->convert($setup);
        $commands = $convertFile['convert'];
        
        $this->executeCommand($commands);
    }

    public function executeCommand($commands)
    {
        foreach($commands as $command)
        {
            shell_exec($command);
        }
    }

    public function executeScript($filepath)
    {
        shell_exec('sh '.$filepath);
    }
}
