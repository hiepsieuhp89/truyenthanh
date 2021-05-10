<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    public function creator()
    {
        return $this->belongsTo(Admin::class,'creatorId');
    }
}
