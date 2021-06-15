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
    public function getSchedule($deviceCode)
    {
        $schedules = Schedule::where('deviceCode', $deviceCode)->get();
        $return = '';
        foreach ($schedules as $schedule) {
            $return .= $schedule->get_schedule_of_device();
            if ($schedule != $schedules[count($schedules) - 1])
                $return .= ',';
        }
        return $return;
    }
    public function deleteSchedule($model)
    {
        Schedule::where('program_id', $model->id)->delete();
    }
    public function getFileDuration($songName, $replay_delay = 30)
    {
        try {

            if (env('APP_ENV') == 'local') $ffprobe = FFProbe::create(['ffmpeg.binaries' => 'D:\ffmpeg\bin\ffmpeg.exe', 'ffprobe.binaries' => 'D:\ffmpeg\bin\ffprobe.exe']);

            else $ffprobe = FFProbe::create();

            $file_duration = $ffprobe->format($songName)->get('duration');

            $file_duration += $replay_delay; //đợi 30 giây mỗi lần lặp

            return $file_duration;
        } catch (Exception $e) {

            return $this->getFileDuration($songName, $replay_delay);
        }
    }
    public function getDevicesStatus()
    {

        $curl = curl_init();

        $dataRequest = "eyJEYXRhVHlwZSI6MjAsIkRhdGEiOiJHRVRfQUxMX0RFVklDRV9TVEFUVVMifQ==";

        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://103.130.213.161:906/" . $dataRequest,
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
    public function setPlayFM($type, $deviceCode, $data)
    {
        $deviceCode = explode(",", $deviceCode);

        $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[';

        foreach ($deviceCode as $device) {
            $dataRequest .= '{\"DeviceID\":\"' . trim($device) . '\",\"CommandSend\":\"{\\\\\"Data\\\\\":\\\\\"' . $data . '\\\\\",\\\\\"PacketType\\\\\":11}\"},';
        }

        $dataRequest .= ']}"}';

        $this->curl_to_server($dataRequest);
    }
    public function setPlaySchedule($program_id, $type, $deviceCode, $startDate, $endDate, $startTime, $songName, $replay_times, $replay_delay = 30)
    {
        $devices = explode(',', $deviceCode);

        $startT = new Carbon($startDate . ' ' . $startTime); //tạo định dạng ngày tháng

        if ($type != 3 && $type != 2) {//ko tiep song, ko fm
            $file_duration = $this->getFileDuration(config('filesystems.disks.upload.path') . $songName, $replay_delay);
        }
        $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[';
        if ($type == 1 || $type == 4 || $type == 5) { // nếu là phát phương tiện

            foreach ($devices as $device) { //set từng thiết bị

                $startT = new Carbon($startDate . ' ' . $startTime);

                $dataRequest .= '{\"DeviceID\":\"' . trim($device) . '\",\"CommandSend\":\"{\\\\\"PacketType\\\\\":2,\\\\\"Data\\\\\":\\\\\"{\\\\\\\\\\\\\"PlayList\\\\\\\\\\\\\":[';

                for ($i = 0; $i < $replay_times; $i++) {

                    $start_time_of_the_loop_play = $startT->toTimeString();

                    $start_date_of_the_loop_play = $startDate;

                    $startT->addSeconds($file_duration);

                    $end_time_of_the_loop_play = $startT->toTimeString();

                    $end_date_of_the_loop_play = $endDate;

                    //insert new in schedule_table
                    Schedule::where('deviceCode', $device)
                        ->where('startDate', $start_date_of_the_loop_play)
                        ->where('time', $start_time_of_the_loop_play)
                        ->delete();

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
                $schedule = $this->getSchedule($device);

                $dataRequest .= $schedule;

                $dataRequest .= ']}\\\\\"}\"}';

                if ($device != $devices[count($devices) - 1])
                    $dataRequest .= ',';
            }
        }
        if ($type == 2){
            foreach ($devices as $device) { //set từng thiết bị

                $dataRequest .= '{\"DeviceID\":\"' . trim($device) . '\",\"CommandSend\":\"{\\\\\"PacketType\\\\\":2,\\\\\"Data\\\\\":\\\\\"{\\\\\\\\\\\\\"PlayList\\\\\\\\\\\\\":[';

                for ($i = 0; $i < $replay_times; $i++) {

                    //insert new in schedule_table
                    Schedule::where('deviceCode', $device)
                    ->where('startDate', $startDate)
                        ->where('time', $startTime)
                        ->delete();

                    $schedule = new Schedule();
                    $schedule->program_id = $program_id;
                    $schedule->deviceCode = $device;
                    $schedule->type = $type;
                    $schedule->fileVoice = $songName;
                    $schedule->startDate = $startDate;
                    $schedule->endDate = $endDate;
                    $schedule->time = $startTime;
                    $schedule->endtime = '23:59:00';
                    $schedule->save();
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
    public function resetSchedule($deviceCode, $type)
    {

        $devices = explode(',', $deviceCode);
        $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[';
        if ($type == 1 || $type == 4 || $type == 5) { // nếu là phát phương tiện

            foreach ($devices as $device) { //set từng thiết bị

                $dataRequest .= '{\"DeviceID\":\"' . trim($device) . '\",\"CommandSend\":\"{\\\\\"PacketType\\\\\":2,\\\\\"Data\\\\\":\\\\\"{\\\\\\\\\\\\\"PlayList\\\\\\\\\\\\\":[';

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
    public function sendFileToDevice($deviceCode, $songName)
    {
        $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[{\"DeviceID\":\"' . $deviceCode . '\",\"CommandSend\":\"{\\\\\"PacketType\\\\\":1,\\\\\"Data\\\\\":\\\\\"{\\\\\\\\\\\\\"URLlist\\\\\\\\\\\\\":[\\\\\\\\\\\\\"' . $songName . '\\\\\\\\\\\\\"]}\\\\\"}\"}]}"}';

        $this->curl_to_server($dataRequest);
    }

    public function playOnline($type, $deviceCode, $songName)
    {
        if($type != 2)
            $songName = config('filesystems.disks.upload.url') . $songName;
        $dataRequest = "";
        $deviceCode = explode(",", $deviceCode);

        $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[';

        if ($type == 1 || $type == 4 || $type == 5 || $type == 2) { // nếu khong phai phat fm
            foreach ($deviceCode as $device) {
                $dataRequest .= '{\"DeviceID\":\"' . trim($device) . '\",\"CommandSend\":\"{\\\\\"Data\\\\\":\\\\\"{\\\\\\\\\\\\\"PlayRepeatType\\\\\\\\\\\\\":1,\\\\\\\\\\\\\"PlayType\\\\\\\\\\\\\":2,\\\\\\\\\\\\\"SongName\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $songName . '\\\\\\\\\\\\\"}\\\\\",\\\\\"PacketType\\\\\":5}\"}';

                if($device != $deviceCode[count($deviceCode)-1])
                    $dataRequest .= ',';
            }
        }

        $dataRequest .= ']}"}';

        $this->curl_to_server($dataRequest);
    }
    public function curl_to_server($dataRequest)
    {
        if (env('APP_ENV') == 'local')
            dd($dataRequest);

        $request = base64_encode($dataRequest);

        // echo "request " . $request;
        $urlRequest = "http://103.130.213.161:906/" . $request;

        // admin_toastr('$urlRequest', 'info');
        // echo "XXX " . $urlRequest;
        Log::info('Play schedule ' . $urlRequest);

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
