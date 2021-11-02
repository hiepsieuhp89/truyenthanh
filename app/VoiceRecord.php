<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VoiceRecord extends Model
{
    public function creator()
    {
        //he
        return $this->belongsTo(Admin::class, 'creatorId');
    }
}
