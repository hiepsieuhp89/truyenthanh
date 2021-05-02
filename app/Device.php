<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    public function area()
    {
        return $this->belongsTo(Area::class,'areaId');
    }
    
    public function DeviceInfo()
    {
        return $this->hasOne(DeviceInfo::class);
    }
}