<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Statistic extends Model
{
    protected $table = "statistics";

    public function device(){
        return $this->belongsTo(Device::class, 'deviceCode','deviceCode');
    }
}
