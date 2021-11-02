<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Device;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DevicesExport;

class FeatureController extends Controller
{
    public function exportDeviceInfo(){
        foreach (Device::select('deviceCode')->get() as $device) {
            Excel::store(new DevicesExport($device->deviceCode), $device->deviceCode . '.xlsx', 'export');
        }
        return true;
    }
}
