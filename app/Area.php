<?php

namespace App;

use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use AdminBuilder, ModelTree {
        ModelTree::boot as treeBoot;
    }

    // public function devices()
    // {
    //     return $this->hasMany(Device::class, 'areaId');
    // }
}
