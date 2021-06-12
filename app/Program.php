<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\VoiceRecord;

class Program extends Model
{
    public function getDaysAttribute($value)
    {
        return explode(',', $value);
    }
    public function setDaysAttribute($value)
    {
        $this->attributes['days'] = implode(',', $value);
    }

    public function getDevicesAttribute($value)
    {
        return explode(',', $value);
    }

    public function setDevicesAttribute($value)
    {
        $this->attributes['devices'] = implode(',', $value);
    }
    public function document()
    {
        return $this->belongsTo(Document::class,'document_Id');
    }
    public function record()
    {
        return $this->belongsTo(VoiceRecord::class, 'record_Id');
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class,'creatorId');
    }

    public function approver()
    {
        return $this->belongsTo(Admin::class,'approvedId');
    }
    public function setFileVoiceAttribute($fileVoice)
    {
        $this->attributes['fileVoice'] = trim(trim($fileVoice,'["'),'"]');
    }
    // public function setVolumeBoosterAttribute($pictures)
    // {
    //     if (is_array($pictures)) {
    //         $this->attributes['pictures'] = json_encode($pictures);
    //     }
    // }
    public function getVolumeBoosterAttribute($bo)
    {
        return (float)$bo/10;
    }

    // public function getFileVoiceAttribute($fileVoice)
    // {
    //     return json_decode($fileVoice, true)
    //     return explode(',',trim(trim(json_decode($fileVoice, true),'['),']'));
    // }
}