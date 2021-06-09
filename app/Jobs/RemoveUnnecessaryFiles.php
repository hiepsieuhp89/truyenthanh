<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Document;
use App\VoiceRecord;
use App\Program;

class RemoveUnnecessaryFiles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //Program::where('volumeBooster', '<', 5)->update(['volumeBooster' => 10]);

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
