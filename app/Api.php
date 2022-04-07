<?php

namespace App;

use Illuminate\Support\Facades\Log;

use FFMpeg\FFProbe;
use App\Schedule;
use Carbon\Carbon;
use DateTime;
use DatePeriod;
use DateInterval;
use Exception;

use Encore\Admin\Facades\Admin;

// use Illuminate\Database\Eloquent\Model;
// use FFMpeg\FFMpeg;
// use Request;
// use Helper;
// use DB;
// use DateTime;
// use DatePeriod;
// use DateInterval;
// use App\Program;
// use App\Area;
// use App\Device;
// use App\DeviceInfo;

// use App\Document;

trait Api
{
    public function daysFilter($startDate, $endDate, $days){

        if(!$days[count($days)-1]) unset($days[count($days)-1]);

        $period = new DatePeriod(
            new DateTime($startDate),
            new DateInterval('P1D'),
            new DateTime($endDate)
        );
        $dates = [];

        foreach($period as $value){

            if(in_array((new Carbon($value))->dayOfWeek, $days))
                array_push($dates, $value->format('Y-m-d'));

        }
        return $dates;
    }
    public function caculateEndTime($startDate, $startTime, $replay_times, $replay_delay, $duration){

            $startT = new Carbon($startDate . ' ' . $startTime);

            $timerange = $replay_times * ($duration + $replay_delay) - $replay_delay;

            $endT = $startT->addSeconds($timerange);

            if($startT->toDateString() < $endT->toDateString())
                $endT = new Carbon($startT->toDateString().' '.'23:59:59');

            return $endT;
    }
    public function findDuplicateSchedule($program_id, $devices, $startDate,$endDate,$startTime, $endTime){
        return Schedule::where('program_id','<>', $program_id)->wherein('deviceCode',$devices)
            ->where(function($query) use ($startDate, $endDate){

                    $query->where(function ($query2) use ($startDate) {

                        $query2->where('startDate', '<=', $startDate)
                        ->where('endDate', '>=', $startDate);
                    })

                    ->orwhere(function ($query3) use ($startDate, $endDate) {

                        $query3->where('startDate', '>=', $startDate)
                        ->where('startDate', '<=', $endDate);
                    });

            })->where(function ($query) use ($startTime, $endTime) {
                    $query->where('time', $startTime)
                    ->orwhere(function ($query1) use ($startTime) {
                        $query1->where('time', '<', $startTime)
                        ->where('endTime', '>', $startTime);
                    })
                    ->orwhere(function ($query2) use ($startTime, $endTime) {
                            $query2->where('time', '>', $startTime)
                            ->where('time', '<', $endTime);
                    });
            })->first();
        // return Program::where('id','<>', $program_id)->where('status',2)->where(function($query) use ($devices){ 
        //         foreach($devices as $device){
        //             $query->where('devices','like','%'.$device.'%');
        //         };
        //     })->where(function($query) use ($startDate, $endDate){

        //                 $query->where(function ($query2) use ($startDate) {

        //                     $query2->where('startDate', '<=', $startDate)
        //                     ->where('endDate', '>=', $startDate);
        //                 })

        //                 ->orwhere(function ($query3) use ($startDate, $endDate) {

        //                     $query3->where('startDate', '>=', $startDate)
        //                     ->where('startDate', '<=', $endDate);
        //                 });

        //         })->where(function ($query) use ($startTime, $endTime) {
        //                 $query->where('time', $startTime)
        //                 ->orwhere(function ($query1) use ($startTime) {
        //                     $query1->where('time', '<', $startTime)
        //                     ->where('endTime', '>', $startTime);
        //                 })
        //                 ->orwhere(function ($query2) use ($startTime, $endTime) {
        //                         $query2->where('time', '>', $startTime)
        //                         ->where('time', '<', $endTime);
        //                 });
        //         })->first();
    }
    /**
     * A call api to set schedule of one or more device
     * @var program_id is index of program record that contain the schedule
     * @var type is integer to know type play, play file, stream, documents,...
     * @var deviceCode is array of devices that are needed to stop play
     * @var startDate is the day start the schedule
     * @var endDate is the day end the schedule
     * @var startTime is the time start the schedule
     * @var endTime is the time end the schedule
     * @var songName is url of media file
     * @var replay_times is replay program times
     * @var replay_delay is interval each replay
     * @return curl_response
     */
    public function checkPlaySchedule($program_id, $type, $mode, $deviceCode, $startDate, $endDate, $startTime, $songName, $replay_times, $replay_delay, $duration, $days = null)
    {
        $endDate = $endDate ? $endDate : $startDate;
        $devices = $deviceCode;

        $startT = new Carbon($startDate . ' ' . $startTime); //tạo định dạng ngày tháng

        if ($type == 1 || $type == 4 || $type == 5) { // nếu là phát phương tiện(media, record, text to speech)

            $findDuplicateSchedule = null;

            //if not weekly schedule
            if($mode != 3){

                $endTime = $this->caculateEndTime(
                    $startDate, 
                    $startTime, 
                    $replay_times,
                    $replay_delay, 
                    $this->getFileDuration($songName)
                )->toTimeString();

                //dd($startDate.' '.$endDate.' '.$startTime.' '.$endTime);

                $findDuplicateSchedule = $this->findDuplicateSchedule($program_id, $devices,$startDate,$endDate,$startTime,$endTime);
            }
            else{// if weekly schedule

                $dates = $this->daysFilter($startDate, $endDate, $days);
                //dd($dates);
                foreach($dates as $date){

                    $end = $this->caculateEndTime(
                        $date, 
                        $startTime, 
                        $replay_times,
                        $replay_delay, 
                        $this->getFileDuration($songName)
                    );

                    $endDate = $end->toDateString();
                    $endTime = $end->toTimeString();

                    //dd($date.' '.$endDate.' '.$startTime.' '.$endTime);

                    $findDuplicateSchedule = $this->findDuplicateSchedule($program_id, $devices, $date, $endDate, $startTime, $endTime);

                    if ($findDuplicateSchedule != null) break;
                    
                }
            }
            if ($findDuplicateSchedule)
                return ['program'=> $findDuplicateSchedule];
        }
        if ($type == 2){ // phát tiếp sóng (livestreaming)
            $endTime = $this->caculateEndTime(
                $startDate, 
                $startTime, 
                $replay_times,
                $replay_delay, 
                $duration * 60
            )->toTimeString();

            $findDuplicateSchedule = $this->findDuplicateSchedule($program_id, $devices,$startDate,$endDate,$startTime,$endTime);

            if ($findDuplicateSchedule)
                return ['program'=> $findDuplicateSchedule];
        }
    }
    /**
     * A call api to set get all schedule of a device
     * 
     * @var deviceCode is device code that need to get schedule
     * @return string
     */
    public function getSchedule($deviceCode)
    {
        $schedules = Schedule::where('deviceCode', $deviceCode)->get();
        $return = '';
        foreach ($schedules as $schedule) {
            $sch = $schedule->get_schedule_of_device();  
            $return .= $sch;
            if ($sch != '' && $schedule != $schedules[count($schedules) - 1])
                $return .= ',';
        }
        return $return;
    }
    public function deleteSchedule($model)
    {
        Schedule::where('program_id', $model->id)->delete();
        return true;
    }
    /**
     * get duration of file
     * @var songName is url path of media file needed to get duration
     * return double number
     */
    public function getFileDuration($songName)
    {
        try {

            if (env('APP_ENV') == 'local') 
                $ffprobe = FFProbe::create([
                    'ffmpeg.binaries' => 'F:\ffmpeg\ffmpeg.exe', 
                    'ffprobe.binaries' => 'F:\ffmpeg\ffprobe.exe'
                ]);

            else 
                $ffprobe = FFProbe::create();

            $file_duration = $ffprobe->format($songName)->get('duration');

            return $file_duration;

        } catch (Exception $e) {

            return false;
        }
    }
    /**
     * A call api to get all online devices status
     * return json string
     */
    public function getDevicesStatus()
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://103.130.213.161:906/eyJEYXRhVHlwZSI6MjAsIkRhdGEiOiJHRVRfQUxMX0RFVklDRV9TVEFUVVMifQ==",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        $response = str_replace(':"{', ":{", $response);
        $response = str_replace(':"[{', ":[{", $response);
        $response = str_replace('"}"', "}", $response);
        $response = str_replace('"{"', "{", $response);
        $response = str_replace(']"}', "]}", $response);
        $response = json_decode($response, true);

        return $response;
    }
    /**
     * A call api to set schedule of one or more device
     * @var type is integer to know type play, play file, stream, documents,...
     * @var deviceCode is array of devices that are needed to stop play
     * @var data is radio frequency 
     */
    public function setPlayFM($type, $deviceCode, $data)
    {
    
        $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[';

        $dataRequest .= implode(',', array_map(function ($device) use ($data) {
            return '{\"DeviceID\":\"' . trim($device) . '\",\"CommandSend\":\"{\\\\\"Data\\\\\":\\\\\"' . $data . '\\\\\",\\\\\"PacketType\\\\\":11}\"}';
        }, $deviceCode));
        
        $dataRequest .= ']}"}';

        $this->curl_to_server($dataRequest);
    }
    /**
     * A call api to set schedule of one or more device
     * @var program_id is index of program record that contain the schedule
     * @var type is integer to know type play, play file, stream, documents,...
     * @var deviceCode is array of devices that are needed to stop play
     * @var startDate is the day start the schedule
     * @var endDate is the day end the schedule
     * @var startTime is the time start the schedule
     * @var endTime is the time end the schedule
     * @var songName is url of media file
     * @var replay_times is replay program times
     * @var replay_delay is interval each replay
     * @return curl_response
     */
    public function setPlaySchedule($program_id, $type, $deviceCode, $startDate, $endDate, $startTime, $endtime, $songName, $replay_times, $replay_delay, $duration = 60)
    {
        $endDate = $endDate ? $endDate : $startDate;

        $devices = $deviceCode;

        $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[';

        if ($type == 1 || $type == 4 || $type == 5) { // nếu là phát phương tiện

            //Get duration of media file
            $file_duration = $this->getFileDuration(config('filesystems.disks.upload.path') . $songName);
            //get each device
            foreach ($devices as $device) {

                $startT = new Carbon($startDate . ' ' . $startTime);

                $dataRequest .= '{\"DeviceID\":\"' . trim($device) . '\",\"CommandSend\":\"{\\\\\"PacketType\\\\\":2,\\\\\"Data\\\\\":\\\\\"{\\\\\\\\\\\\\"PlayList\\\\\\\\\\\\\":[';
                
                for ($i = 0; $i < $replay_times; $i++) {

                    $start_time_of_the_loop_play = $startT->toTimeString();

                    $start_date_of_the_loop_play = $startDate;

                    $endT = $startT->addSeconds($file_duration);

                    if($startT->toDateString() < $endT->toDateString())
                        $endT = new Carbon($startT->toDateString().' '.'23:59:59');

                    $end_time_of_the_loop_play = $endT->toTimeString();

                    //Nếu nhập endtime hoặc không cho time kết thúc là 0h
                    if($endtime && $end_time_of_the_loop_play > $endtime)
                        break;

                    $end_date_of_the_loop_play = $endDate;

                    $startT->addSeconds($replay_delay);

                    $schedule = new Schedule();
                    $schedule->program_id = $program_id;
                    $schedule->deviceCode = $device;
                    $schedule->type = $type;
                    $schedule->fileVoice = config('filesystems.disks.upload.url') . $songName;
                    $schedule->startDate = $start_date_of_the_loop_play;
                    $schedule->endDate = $end_date_of_the_loop_play;
                    $schedule->time = $start_time_of_the_loop_play;
                    $schedule->endtime = $end_time_of_the_loop_play;
                    $schedule->save();
                }
                Program::find($program_id)->update(['endTime' => $end_time_of_the_loop_play]);
                $schedule = $this->getSchedule($device);

                $dataRequest .= $schedule;

                $dataRequest .= ']}\\\\\"}\"}';

                if ($device != $devices[count($devices) - 1])
                    $dataRequest .= ',';
            }
        }
        if ($type == 2){// phát tiếp sóng

            foreach ($devices as $device) { //set từng thiết bị

                $startT = new Carbon($startDate . ' ' . $startTime);

                $dataRequest .= '{\"DeviceID\":\"' . trim($device) . '\",\"CommandSend\":\"{\\\\\"PacketType\\\\\":2,\\\\\"Data\\\\\":\\\\\"{\\\\\\\\\\\\\"PlayList\\\\\\\\\\\\\":[';

                for ($i = 0; $i < $replay_times; $i++) {

                    $start_time_of_the_loop_play = $startT->toTimeString();

                    $start_date_of_the_loop_play = $startDate;

                    $endT = $startT->addMinutes($duration);

                    if($startDate < $endT->toDateString()){

                        $endT = new Carbon($startT->toDateString().' '.'23:59:59');

                        $startT = new Carbon($startDate . ' ' . '23:59:59');
                    }

                    $end_time_of_the_loop_play = $endT->toTimeString();

                    //Nếu nhập endtime hoặc không cho time kết thúc là 0h
                    if(($endtime) && ($end_time_of_the_loop_play > $endtime))
                    break;

                    $end_date_of_the_loop_play = $endDate;

                    $startT->addSeconds($replay_delay);

                    $schedule = new Schedule();
                    $schedule->program_id = $program_id;
                    $schedule->deviceCode = $device;
                    $schedule->type = $type;
                    $schedule->fileVoice = $songName;
                    $schedule->startDate = $start_date_of_the_loop_play;
                    $schedule->endDate = $end_date_of_the_loop_play;
                    $schedule->time = $start_time_of_the_loop_play;
                    $schedule->endTime = $end_time_of_the_loop_play;
                    $schedule->save();

                    if($end_time_of_the_loop_play == '23:59:59')
                        break;
                }

                //get array schedule
                $schedule = $this->getSchedule($device);

                $dataRequest .= $schedule;

                $dataRequest .= ']}\\\\\"}\"}';

                if ($device != $devices[count($devices) - 1])
                    $dataRequest .= ',';
            }
        }
        $dataRequest .= ']}"}';

        $this->curl_to_server($dataRequest);
    }
    /**
     * A call api to set schedule of one or more device
     * @var program_id is index of program record that contain the schedule
     * @var type is integer to know type play, play file, stream, documents,...
     * @var deviceCode is array of devices that are needed to stop play
     * @var startDate is the day start the schedule
     * @var endDate is the day end the schedule
     * @var startTime is the time start the schedule
     * @var days in a week
     * @var songName is url of media file
     * @var replay_times is replay program times
     * @var replay_delay is interval each replay
     * @return curl_response
     */
    public function setPlayWeekSchedule($program_id, $type, $deviceCode, $startDate, $endDate, $startTime, $endtime, $days, $songName, $replay_times, $replay_delay, $duration = 60){

        $dates = $this->daysFilter($startDate, $endDate, $days);

        $endDate = $endDate ? $endDate : $startDate;

        $devices = $deviceCode;

        $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[';

        if ($type == 1 || $type == 4 || $type == 5) { // nếu là phát phương tiện

            //Get duration of media file
            $file_duration = $this->getFileDuration(config('filesystems.disks.upload.path') . $songName);

            //get each device
            foreach ($devices as $device) {

                $dataRequest .= '{\"DeviceID\":\"' . trim($device) . '\",\"CommandSend\":\"{\\\\\"PacketType\\\\\":2,\\\\\"Data\\\\\":\\\\\"{\\\\\\\\\\\\\"PlayList\\\\\\\\\\\\\":[';

                $end_time_of_the_loop_play = null;

                //each day play
                foreach($dates as $date){

                    $startT = new Carbon($date . ' ' . $startTime);
                    
                    //Duyệt mỗi lần phát
                    for ($i = 0; $i < $replay_times; $i++) {

                        $start_time_of_the_loop_play = $startT->toTimeString();

                        $start_date_of_the_loop_play = $startT->toDateString();

                        $endT = $startT->addSeconds($file_duration);

                        // set the time stop is end of day
                        if($startT->toDateString() < $endT->toDateString())

                        $endT = new Carbon($startT->toDateString().' '.'23:59:59');

                        $end_time_of_the_loop_play = $endT->toTimeString();

                        $end_date_of_the_loop_play = $endT->toDateString();

                        //Nếu nhập endtime hoặc không cho time kết thúc là 0h
                        if(($endtime) && ($end_time_of_the_loop_play > $endtime))
                            break;

                        //Thêm replay delay giữa mỗi lần phát
                        $startT->addSeconds($replay_delay);

                        $schedule = new Schedule();
                        $schedule->program_id = $program_id;
                        $schedule->deviceCode = $device;
                        $schedule->type = $type;
                        $schedule->fileVoice = config('filesystems.disks.upload.url') . $songName;
                        $schedule->startDate = $start_date_of_the_loop_play;
                        $schedule->endDate = $end_date_of_the_loop_play;
                        $schedule->time = $start_time_of_the_loop_play;
                        $schedule->endtime = $end_time_of_the_loop_play;
                        $schedule->save();
                    }
                }
                Program::find($program_id)->update(['endTime' => $end_time_of_the_loop_play]);

                $schedule = $this->getSchedule($device);

                $dataRequest .= $schedule;

                $dataRequest .= ']}\\\\\"}\"}';

                if ($device != $devices[count($devices) - 1])
                    $dataRequest .= ',';
            }
        }
        if ($type == 2){// phát tiếp sóng

            foreach ($devices as $device) { //set từng thiết bị

                $dataRequest .= '{\"DeviceID\":\"' . trim($device) . '\",\"CommandSend\":\"{\\\\\"PacketType\\\\\":2,\\\\\"Data\\\\\":\\\\\"{\\\\\\\\\\\\\"PlayList\\\\\\\\\\\\\":[';

                foreach($dates as $date){

                    $startT = new Carbon($date . ' ' . $startTime);

                    for ($i = 0; $i < $replay_times; $i++) {

                        $start_time_of_the_loop_play = $startT->toTimeString();

                        $start_date_of_the_loop_play = $startT->toDateString();

                        

                        $endT = $startT->addMinutes($duration);

                        if($startT->toDateString() < $endT->toDateString())
                            $endT = new Carbon($startT->toDateString().' '.'23:59:59');

                        $end_time_of_the_loop_play = $endT->toTimeString();

                        $end_date_of_the_loop_play = $endT->toDateString();

                        $startT->addSeconds($replay_delay);

                        //Nếu nhập endtime hoặc không cho time kết thúc là 0h
                        if(($endtime) && ($end_time_of_the_loop_play > $endtime))
                            break;

                        $schedule = new Schedule();
                        $schedule->program_id = $program_id;
                        $schedule->deviceCode = $device;
                        $schedule->type = $type;
                        $schedule->fileVoice = $songName;
                        $schedule->startDate = $start_date_of_the_loop_play;
                        $schedule->endDate = $end_date_of_the_loop_play;
                        $schedule->time = $start_time_of_the_loop_play;
                        $schedule->endTime = $end_time_of_the_loop_play;
                        $schedule->save();

                        if($end_time_of_the_loop_play == '23:59:59')
                            break;
                    }
                }
                $schedule = $this->getSchedule($device);

                $dataRequest .= $schedule;

                $dataRequest .= ']}\\\\\"}\"}';

                if ($device != $devices[count($devices) - 1])
                    $dataRequest .= ',';
            }
        }
        $dataRequest .= ']}"}';

        $this->curl_to_server($dataRequest);
    }
    /**
     * A call api to re call schedule of one or more device
     * 
     * @var type is integer to know type play, play file, stream, documents,...
     * @var deviceCode is array of devices that are needed to stop play
     * @return curl_response
     */
    public function resetSchedule($deviceCode, $type)
    {
        $devices = $deviceCode;
        $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[';
        if ($type == 1 || $type == 4 || $type == 5 || $type == 2) { // nếu là phát phương tiện

            //foreach ($devices as $device) { //set từng thiết bị

                $dataRequest .= implode(',', array_map(function ($device){

                    return '{\"DeviceID\":\"' . trim($device) . '\",\"CommandSend\":\"{\\\\\"PacketType\\\\\":2,\\\\\"Data\\\\\":\\\\\"{\\\\\\\\\\\\\"PlayList\\\\\\\\\\\\\":['.$this->getSchedule($device). ']}\\\\\"}\"}';

                }, $devices));
        }
        $dataRequest .= ']}"}';

        return $this->curl_to_server($dataRequest);
    }
    /**
     * A call api to play now a program
     * 
     * @var type is integer to know type play, play file, stream, documents,...
     * @var deviceCode is string of devices array that are needed to stop play
     * @var songName is url of media play
     * @return curl_response
     */
    public function playOnline($type, $deviceCode, $songName, $duration = 30)
    {
        $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[';

        //not play stream, so can send file to devices
        if($type != 2){
            //$timeStop = null;
            $songName = config('filesystems.disks.upload.url') . $songName;

            //send file to devices before playing
            //$this->sendFileToDevice($deviceCode, $songName);
        }

        //if play stream
        else{
            $timeStop = Carbon::now()->addMinutes($duration)->toTimeString();
        }
        //generate string
        $dataRequest .= implode(',',array_map(function($device) use ($songName){
            return
            '{\"DeviceID\":\"' . trim($device) . '\",\"CommandSend\":\"{\\\\\"Data\\\\\":\\\\\"{\\\\\\\\\\\\\"PlayRepeatType\\\\\\\\\\\\\":1,\\\\\\\\\\\\\"PlayType\\\\\\\\\\\\\":2,\\\\\\\\\\\\\"SongName\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $songName . '\\\\\\\\\\\\\"}\\\\\",\\\\\"PacketType\\\\\":5}\"}';
        },$deviceCode));

        $dataRequest .= ']}"}';

        //Stop the current play before play this program
        $this->stopPlay($deviceCode);

        return $this->curl_to_server($dataRequest);
    }
    /**
     * A call api to stop play of one or more devices
     * 
     * @var deviceCode is array of devices that are needed to stop play
     * @return curl_response
     */
    public function stopPlay($deviceCode)
    {
        $curl = curl_init();

        $deviceCode = implode(",", array_map(function($value){
            return '{\"DeviceID\":\"' . $value . '\",\"CommandSend\":\"{\\\\\"Data\\\\\":\\\\\"Stop play music\\\\\\",\\\\\"PacketType\\\\\":7}\"}';
        },$deviceCode));

        $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":['.$deviceCode.']}"}';

        return $this->curl_to_server($dataRequest);
    }
    /**
     * A call api to send files from server to devices
     * 
     * @var devices is array of devices
     * @var file
     * @return curl_response
     */
    public function sendFileToDevice($devices, $file){

        $devices = array_map(function($device) use ($file){
            return '{\"DeviceID\":\"'.$device.'\",\"CommandSend\":\"{\\\"PacketType\\\":1,\\\"Data\\\":\\\"{\\\\\\\"URLlist\\\\\\\":[\\\\\\\"'.$file.'\\\\\\\"]}\\\"}\"}';
        },$devices);

        $devices = implode(',',$devices);

        $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":['.$devices.']}"}';

        return $this->curl_to_server($dataRequest);
    }
    /**
     * A void to call api
     * 
     * @var dataRequest
     * @return curl_response
     */
    public function curl_to_server($dataRequest)
    {
        if (env('APP_ENV') == 'local')
            dd($dataRequest);

        $request = base64_encode($dataRequest);

        $urlRequest = "http://103.130.213.161:906/" . $request;

        // admin_toastr('$urlRequest', 'info');
        // echo "XXX " . $urlRequest;
        // Log::info('send ' . $urlRequest);

        //Log::info('Play schedule json' .$urlRequest);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $urlRequest,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }
}
