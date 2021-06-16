<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Document;
use App\VoiceRecord;
use App\Program;

class RemoveUnnecessaryFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ruf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove unnecessary files';

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
        $files = scandir(config('filesystems.disks.upload.path') . 'files/');
        foreach ($files as $file) {
            if (strlen($file) > 3 && count(Program::where('fileVoice', 'files/' . $file)->get()) == 0)
                unlink(config('filesystems.disks.upload.path') . 'files/' . $file);
        }

        $voices = scandir(config('filesystems.disks.upload.path') . 'voices/');
        foreach ($voices as $voice) {
            if (strlen($voice) > 3 && count(Document::where('fileVoice', 'voices/' . $voice)->get()) == 0)
                unlink(config('filesystems.disks.upload.path') . 'voices/' . $voice);
        }

        $records = scandir(config('filesystems.disks.upload.path') . 'records/');
        foreach ($records as $file) {
            if (strlen($file) > 6 && count(VoiceRecord::where('fileVoice', 'records/' . $file)->get()) == 0)
                unlink(config('filesystems.disks.upload.path') . 'records/' . $file);
        }
    }
}
