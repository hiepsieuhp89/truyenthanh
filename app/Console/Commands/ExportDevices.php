<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Admin\Controllers\FeatureController;

class ExportDevices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exportdevices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Xuất tình trạng thiết bị ra Excel';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        (new FeatureController())->exportDeviceInfo();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        (new FeatureController())->exportDeviceInfo();
    }
}
