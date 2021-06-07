<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    public function get_schedule_of_device(){
        return '{\\\\\\\\\\\\\"SongName\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $this->fileVoice . '\\\\\\\\\\\\\",\\\\\\\\\\\\\"TimeStart\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $this->time . '\\\\\\\\\\\\\",\\\\\\\\\\\\\"TimeStop\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $this->endTime . '\\\\\\\\\\\\\",\\\\\\\\\\\\\"DateStart\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $this->startDate . '\\\\\\\\\\\\\",\\\\\\\\\\\\\"DateStop\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $this->endDate . '\\\\\\\\\\\\\",\\\\\\\\\\\\\"PlayType\\\\\\\\\\\\\":1,\\\\\\\\\\\\\"PlayRepeatType\\\\\\\\\\\\\":1},';
    }
}
