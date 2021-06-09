<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Schedule extends Model
{
    public function get_schedule_of_device(){
        $today = Carbon::now('Asia/Ho_Chi_Minh')->toDateString();
        if($today > $this->startDate && $today <= $this->endDate)
            $this->startDate = $today;

        return '{\\\\\\\\\\\\\"SongName\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $this->fileVoice . '\\\\\\\\\\\\\",\\\\\\\\\\\\\"TimeStart\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $this->time . '\\\\\\\\\\\\\",\\\\\\\\\\\\\"TimeStop\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $this->endTime . '\\\\\\\\\\\\\",\\\\\\\\\\\\\"DateStart\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $this->startDate . '\\\\\\\\\\\\\",\\\\\\\\\\\\\"DateStop\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $this->endDate . '\\\\\\\\\\\\\",\\\\\\\\\\\\\"PlayType\\\\\\\\\\\\\":1,\\\\\\\\\\\\\"PlayRepeatType\\\\\\\\\\\\\":1}';
    }
}
