<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Schedule extends Model
{
    public function device()
    {
        return $this->belongsTo(\App\Device::class, 'deviceCode', 'deviceCode');
    }
    public function program(){
        return $this->belongsTo(\App\Program::class, 'program_id');
    }
    public function get_schedule_of_device(){
        $today = Carbon::now('Asia/Ho_Chi_Minh')->toDateString();
        if($today > $this->endDate)
            return '';
        if($today > $this->startDate)
            $this->startDate = $today;
        //$playtype = 2;
        if($this->type == 2)
            $playtype = 2;
        else
            $playtype = 1;
        return '{\\\\\\\\\\\\\"SongName\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $this->fileVoice . '\\\\\\\\\\\\\",\\\\\\\\\\\\\"TimeStart\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $this->time . '\\\\\\\\\\\\\",\\\\\\\\\\\\\"TimeStop\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $this->endTime . '\\\\\\\\\\\\\",\\\\\\\\\\\\\"DateStart\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $this->startDate . '\\\\\\\\\\\\\",\\\\\\\\\\\\\"DateStop\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $this->endDate . '\\\\\\\\\\\\\",\\\\\\\\\\\\\"PlayType\\\\\\\\\\\\\":'.$playtype.',\\\\\\\\\\\\\"PlayRepeatType\\\\\\\\\\\\\":1}';
    }
}
