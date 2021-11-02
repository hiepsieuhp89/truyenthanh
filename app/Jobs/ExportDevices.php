<?php

namespace App\Jobs;

use Carbon\Carbon;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Admin\Controllers\FeatureController;
class ExportDevices implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //(new FeatureController())->exportDeviceInfo();
        file_put_contents(config('filesystems.disks.export.path') . 'hello.txt', 'anh yeu em ' . Carbon::now());
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
    }
}
