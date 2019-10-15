<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Web\Tool\ConvertController;
use Illuminate\Support\Facades\File;

class Convert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert:mp4';
    // protected $signature = 'convert:mp4 {test? : Testing}'; // argument
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
            $convert = new ConvertController();
            $renameFile = $convert->file($setup);

            // $this->output->writeln('Run job rename file');
            // $this->rename($renameFile['newname']);

            // $this->output->writeln('Run job save data');
            // $convert->save($setup, $convert->song($setup));

            $this->output->writeln('Run job make shell script convert to mp4');
            $convertFile = $convert->convert($setup);
            File::put($setup[env('DROP_BOX')]['path']['script'].$setup['script']['name']['convert'].'_'.$setup['lang'].$setup['script']['extension'], implode("\n", $convertFile['convert']));

            // $script = $this->choice('Execute script ?', $setup['script']['name'], 'newname');
            // $this->output->writeln('Run job rename file use <info>'.$script.'<info>');
            // $this->rename($renameFile[$script]);
            // $filepath = $setup[env('DROP_BOX')]['path']['script'].$script.'_'.$setup['lang'].$setup['script']['extension'];
            // shell_exec('sh '.$filepath);
            $this->line('Done....');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return;
        }
    }

    public function test()
    {
        return 'aaaaaa';
    }

    public function rename($commands)
    {
        foreach($commands as $command)
        {
            shell_exec($command);
        }
    }
}
