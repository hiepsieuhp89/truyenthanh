<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeviceInfo extends Model
{
    public function device()
    {
        return $this->belongsTo(Device::class, 'deviceCode','deviceCode');
    }
}
