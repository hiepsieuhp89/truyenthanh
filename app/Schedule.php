<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Schedule extends Model
{
    public function get_schedule_of_device(){
        $today = Carbon::now('Asia/Ho_Chi_Minh')->toDateString();
        if($today > $this->endDate)
            return false;
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
