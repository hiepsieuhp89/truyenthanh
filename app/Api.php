<?php

namespace App;

use Illuminate\Support\Facades\Log;

use FFMpeg\FFProbe;
use App\Schedule;
use Carbon\Carbon;
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
    public function caculateEndTime($startDate, $startTime, $replay_times, $replay_delay, $duration){

            $startT = new Carbon($startDate . ' ' . $startTime);

            $timerange = $replay_times * ($duration + $replay_delay) - $replay_delay;

            $endT = $startT->addSeconds($timerange);

            if($startT->toDateString() < $endT->toDateString())
                $endT = new Carbon($startT->toDateString().' '.'23:59:59');

            return $endT;
    }
    public function findDuplicateSchedule($program_id, $devices,$startDate,$endDate,$startTime,$endTime){

        return Program::where('id','<>', $program_id)->where('status',2)->where(function($query) use ($devices){ 
                foreach($devices as $device){
                    $query->orwhere('devices','like','%'.$device.'%');
                };
            })->where(function($query) use ($startDate, $endDate){

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
    public function checkPlaySchedule($program_id, $type, $deviceCode, $startDate, $endDate, $startTime, $songName, $replay_times, $replay_delay, $duration)
    {
        $devices = explode(',', $deviceCode);

        $startT = new Carbon($startDate . ' ' . $startTime); //tạo định dạng ngày tháng

        if ($type == 1 || $type == 4 || $type == 5) { // nếu là phát phương tiện

            //Get duration of media file

            $endTime = $this->caculateEndTime(
                $startDate, 
                $startTime, 
                $replay_times,
                $replay_delay, 
                $this->getFileDuration($songName)
            )->toTimeString();

            $findDuplicateSchedule = $this->findDuplicateSchedule($program_id, $devices,$startDate,$endDate,$startTime,$endTime);

            if ($findDuplicateSchedule)
                return ['program'=> $findDuplicateSchedule];

            //get each device
            // foreach ($devices as $device) {

            //     $startT = new Carbon($startDate . ' ' . $startTime);
                
            //     for ($i = 0; $i < $replay_times; $i++) {

            //         $start_time_of_the_loop_play = $startT->toTimeString();

            //         $start_date_of_the_loop_play = $startDate;

            //         $startT->addSeconds($file_duration + $replay_delay);

            //         $end_time_of_the_loop_play = $startT->toTimeString();

            //         $end_date_of_the_loop_play = $endDate;

            //         $findDuplicateSchedule = Schedule::where('deviceCode', $device)

            //         ->where('program_id','<>', $program_id)

            //         ->where(function($query) use ($start_date_of_the_loop_play, $end_date_of_the_loop_play){
            //             $query->where('startDate', $start_date_of_the_loop_play)

            //             ->orwhere(function ($query2) use ($start_date_of_the_loop_play) {
            //                 $query2->where('startDate', '<', $start_date_of_the_loop_play)
            //                 ->where('endDate', '>', $start_date_of_the_loop_play);
            //             })

            //             ->orwhere(function ($query3) use ($start_date_of_the_loop_play, $end_date_of_the_loop_play) {
            //                 $query3->where('startDate', '>', $start_date_of_the_loop_play)
            //                 ->where('startDate', '<', $end_date_of_the_loop_play);
            //             });

            //         })->where(function ($query) use ($start_time_of_the_loop_play, $end_time_of_the_loop_play) {
            //             $query->where('time', $start_time_of_the_loop_play)
            //             ->orwhere(function ($query1) use ($start_time_of_the_loop_play) {
            //                 $query1->where('time', '<', $start_time_of_the_loop_play)
            //                 ->where('endTime', '>', $start_time_of_the_loop_play);
            //             })
            //             ->orwhere(function ($query2) use ($start_time_of_the_loop_play, $end_time_of_the_loop_play) {
            //                     $query2->where('time', '>', $start_time_of_the_loop_play)
            //                     ->where('time', '<', $end_time_of_the_loop_play);
            //             });
            //         })
            //         ->first();

            //         if ($findDuplicateSchedule)
            //             return ['program'=> $findDuplicateSchedule];
            //     }
            // }
        }
        if ($type == 2){// phát tiếp sóng

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

            // foreach ($devices as $device) { //set từng thiết bị
            //     for ($i = 0; $i < $replay_times; $i++) {

            //         $start_time_of_the_loop_play = $startT->toTimeString();

            //         $start_date_of_the_loop_play = $startDate;

            //         $startT->addMinutes($duration);

            //         $end_time_of_the_loop_play = $startT->toTimeString();

            //         $end_date_of_the_loop_play = $endDate;

            //         $findDuplicateSchedule = Schedule::where('deviceCode', $device)

            //         ->where('program_id','<>', $program_id)

            //         ->where(function($query) use ($start_date_of_the_loop_play, $end_date_of_the_loop_play){
            //             $query->where('startDate', $start_date_of_the_loop_play)

            //             ->orwhere(function ($query2) use ($start_date_of_the_loop_play) {
            //                 $query2->where('startDate', '<', $start_date_of_the_loop_play)
            //                 ->where('endDate', '>', $start_date_of_the_loop_play);
            //             })

            //             ->orwhere(function ($query3) use ($start_date_of_the_loop_play, $end_date_of_the_loop_play) {
            //                 $query3->where('startDate', '>', $start_date_of_the_loop_play)
            //                 ->where('startDate', '<', $end_date_of_the_loop_play);
            //             });

            //         })->where(function ($query) use ($start_time_of_the_loop_play, $end_time_of_the_loop_play) {
            //             $query->where('time', $start_time_of_the_loop_play)
            //             ->orwhere(function ($query1) use ($start_time_of_the_loop_play) {
            //                 $query1->where('time', '<', $start_time_of_the_loop_play)
            //                 ->where('endTime', '>', $start_time_of_the_loop_play);
            //             })
            //             ->orwhere(function ($query2) use ($start_time_of_the_loop_play, $end_time_of_the_loop_play) {
            //                     $query2->where('time', '>', $start_time_of_the_loop_play)
            //                     ->where('time', '<', $end_time_of_the_loop_play);
            //             });
            //         })
            //         ->first();

            //         if ($findDuplicateSchedule)
            //             return ['program'=> $findDuplicateSchedule];
            //     }
            // }
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
    }
    /**
     * get duration of file
     * @var songName is url path of media file needed to get duration
     * return double number
     */
    public function getFileDuration($songName)
    {
        try {

            if (env('APP_ENV') == 'local') $ffprobe = FFProbe::create(['ffmpeg.binaries' => 'D:\ffmpeg\bin\ffmpeg.exe', 'ffprobe.binaries' => 'D:\ffmpeg\bin\ffprobe.exe']);

            else $ffprobe = FFProbe::create();

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
        $deviceCode = explode(",", $deviceCode);

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
    public function setPlaySchedule($program_id, $type, $deviceCode, $startDate, $endDate, $startTime, $songName, $replay_times, $replay_delay, $duration = 60)
    {
        $devices = explode(',', $deviceCode);

        $startT = new Carbon($startDate . ' ' . $startTime); //tạo định dạng ngày tháng

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

                    if($startDate< $endT->toDateString()){

                        $endT = new Carbon($startT->toDateString().' '.'23:59:59');

                        $startT = new Carbon($startDate . ' ' . '23:59:59');
                    }

                    $end_time_of_the_loop_play = $endT->toTimeString();

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

        $devices = explode(',', $deviceCode);
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
    public function playOnline($type, $deviceCode, $songName, $duration = null)
    {
    
        $dataRequest = "";

        $deviceCode = explode(",", $deviceCode);

        $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[';

        //not play stream, so can send file to devices
        if($type != 2){

            $playtype = 1;
            $songName = config('filesystems.disks.upload.url') . $songName;

            //send files to devices before playing
            $this->sendFileToDevice($deviceCode, $songName);
        }
        //play stream
        else
            $playtype = 2;

        if ($type == 1 || $type == 4 || $type == 5 || $type == 2) { // nếu khong phai phat fm

            $dataRequest .= implode(',',array_map(function($device) use ($songName, $playtype){
                return
                '{\"DeviceID\":\"' . trim($device) . '\",\"CommandSend\":\"{\\\\\"Data\\\\\":\\\\\"{\\\\\\\\\\\\\"PlayRepeatType\\\\\\\\\\\\\":1,\\\\\\\\\\\\\"PlayType\\\\\\\\\\\\\":'.$playtype.',\\\\\\\\\\\\\"SongName\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $songName . '\\\\\\\\\\\\\"}\\\\\",\\\\\"PacketType\\\\\":5}\"}';
            },$deviceCode));

        }

        $dataRequest .= ']}"}';

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

        // echo "request " . $request;
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

        return json_decode($response);
    }
}
