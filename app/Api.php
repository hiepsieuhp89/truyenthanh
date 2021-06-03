<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use Request;
use Helper;
use DB;
use DateTime;
use DatePeriod;
use DateInterval;
use Carbon\Carbon;

use App\Program;
use App\Area;
use App\Device;
use App\DeviceInfo;
use App\Document;

trait Api
{
    public function getDevicesStatus(){
        
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

        foreach ($deviceCode as $device)
        {
            $dataRequest .= '{\"DeviceID\":\"' . trim($device) . '\",\"CommandSend\":\"{\\\\\"Data\\\\\":\\\\\"' . $data . '\\\\\",\\\\\"PacketType\\\\\":11}\"},';
        }

        $dataRequest .= ']}"}';

        $this->curl_to_server($dataRequest);
        
    }

    public function setPlaySchedule($type, $deviceCode, $startDate, $endDate, $startTime, $songName, $replay_times, $replay_delay = 30)
    {
        $devices = explode(',', $deviceCode);

        $startT = new Carbon($startDate . ' ' . $startTime); //tạo định dạng ngày tháng

        if($type != 3){

        	if (env('APP_ENV') == 'local') $ffprobe = FFProbe::create(['ffmpeg.binaries' => 'D:\ffmpeg\bin\ffmpeg.exe', 'ffprobe.binaries' => 'D:\ffmpeg\bin\ffprobe.exe']);
        	
            else $ffprobe = FFProbe::create();

            $file_duration = $ffprobe->format($songName)->get('duration');

            $file_duration += $replay_delay; //đợi 30 giây mỗi lần lặp
        }

        if ($endDate == NULL || $endDate == ''){ // nếu đặt trong ngày

            $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[';
            
            if ($type == 1 || $type == 4 || $type == 5 || $type == 2){ // nếu là file phương tiện

                foreach ($devices as $device)// foreach add device
                {

                    $startT = new Carbon($startDate . ' ' . $startTime);

                    $dataRequest .= '{\"DeviceID\":\"' . trim($device) . '\",\"CommandSend\":\"{\\\\\"PacketType\\\\\":2,\\\\\"Data\\\\\":\\\\\"{\\\\\\\\\\\\\"PlayList\\\\\\\\\\\\\":[';

                    for ($i = 0;$i < $replay_times;$i++) // foreach add songname
                    {

                        $start_time_of_the_loop_play = $startT->toTimeString();

                        $start_date_of_the_loop_play = $startT->toDateString();

                        $startT->addSeconds($file_duration);

                        $end_time_of_the_loop_play = $startT->toTimeString();

                        $end_date_of_the_loop_play = $startT->toDateString();

                        $dataRequest .= '{\\\\\\\\\\\\\"SongName\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $songName . '\\\\\\\\\\\\\",\\\\\\\\\\\\\"TimeStart\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $start_time_of_the_loop_play . '\\\\\\\\\\\\\",\\\\\\\\\\\\\"TimeStop\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $end_time_of_the_loop_play . '\\\\\\\\\\\\\",\\\\\\\\\\\\\"DateStart\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $start_date_of_the_loop_play . '\\\\\\\\\\\\\",\\\\\\\\\\\\\"DateStop\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $end_date_of_the_loop_play . '\\\\\\\\\\\\\",\\\\\\\\\\\\\"PlayType\\\\\\\\\\\\\":2,\\\\\\\\\\\\\"PlayRepeatType\\\\\\\\\\\\\":1}';

                        if ($i < $replay_times - 1) $dataRequest .= ',';
                    }

                    $dataRequest .= ']}\\\\\"}\"}';

                    if ($device != $devices[count($devices) - 1]) $dataRequest .= ',';
                }
            }
            $dataRequest .= ']}"}';
    
            $this->curl_to_server($dataRequest);
        }
        else{ // nếu đặt hàng ngày

            $dates = [];

            $period = new DatePeriod( // lấy danh sách ngày phát
                new DateTime($startDate),
                new DateInterval('P1D'),
                new DateTime($endDate)
            );
            $i = 0;
            foreach ($period as $key => $value) {
                $dates[$i++] = $value->format('Y-m-d');
            }
            $dates[$i] = $endDate;

            if ($type == 1 || $type == 4 || $type == 5){ // nếu là phát phương tiện

                foreach($dates as $date){

                    //$time = new Carbon($date . ' ' . $startTime);

                    $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[';

                    foreach ($devices as $device) { //set từng thiết bị

                        $startT = new Carbon($date . ' ' . $startTime);

                        $dataRequest .= '{\"DeviceID\":\"' . trim($device) . '\",\"CommandSend\":\"{\\\\\"PacketType\\\\\":2,\\\\\"Data\\\\\":\\\\\"{\\\\\\\\\\\\\"PlayList\\\\\\\\\\\\\":[';  

                        for ($i = 0; $i < $replay_times; $i++) {

                            $start_time_of_the_loop_play = $startT->toTimeString();

                            $start_date_of_the_loop_play = $startT->toDateString();

                            $startT->addSeconds($file_duration);
                            
                            $end_time_of_the_loop_play = $startT->toTimeString();

                            $end_date_of_the_loop_play = $startT->toDateString();

                            $dataRequest .= '{\\\\\\\\\\\\\"SongName\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $songName . '\\\\\\\\\\\\\",\\\\\\\\\\\\\"TimeStart\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $start_time_of_the_loop_play . '\\\\\\\\\\\\\",\\\\\\\\\\\\\"TimeStop\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $end_time_of_the_loop_play . '\\\\\\\\\\\\\",\\\\\\\\\\\\\"DateStart\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $start_date_of_the_loop_play . '\\\\\\\\\\\\\",\\\\\\\\\\\\\"DateStop\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $end_date_of_the_loop_play . '\\\\\\\\\\\\\",\\\\\\\\\\\\\"PlayType\\\\\\\\\\\\\":2,\\\\\\\\\\\\\"PlayRepeatType\\\\\\\\\\\\\":1}';

                            if ($i < $replay_times - 1) $dataRequest .= ',';
                        }
                        $dataRequest .= ']}\\\\\"}\"}';

                        if ($device != $devices[count($devices) - 1]) $dataRequest .= ',';
                    }
                    
                    $dataRequest .= ']}"}';

                    $this->curl_to_server($dataRequest);
                }
            }
        }
       

        $this->curl_to_server($dataRequest);
    }

    public function sendFileToDevice($deviceCode, $songName)
    {

        $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[{\"DeviceID\":\"' . $deviceCode . '\",\"CommandSend\":\"{\\\\\"PacketType\\\\\":1,\\\\\"Data\\\\\":\\\\\"{\\\\\\\\\\\\\"URLlist\\\\\\\\\\\\\":[\\\\\\\\\\\\\"' . $songName . '\\\\\\\\\\\\\"]}\\\\\"}\"}]}"}';

        $this->curl_to_server($dataRequest);
    }

    public function playOnline($type, $deviceCode, $songName)
    {
        $dataRequest = "";
        $deviceCode = explode(",", $deviceCode);

        $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[';

        if ($type == 1 || $type == 4 || $type == 5 || $type == 2)
        { // nếu phát ngay file pt
            foreach ($deviceCode as $device)
            {

                $dataRequest .= '{\"DeviceID\":\"' . trim($device) . '\",\"CommandSend\":\"{\\\\\"Data\\\\\":\\\\\"{\\\\\\\\\\\\\"PlayRepeatType\\\\\\\\\\\\\":1,\\\\\\\\\\\\\"PlayType\\\\\\\\\\\\\":2,\\\\\\\\\\\\\"SongName\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $songName . '\\\\\\\\\\\\\"}\\\\\",\\\\\"PacketType\\\\\":5}\"},';
            }

        }

        $dataRequest .= ']}"}';

        $this->curl_to_server($dataRequest);
    }
    public function curl_to_server($dataRequest){
        
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

        $err = curl_error($curl);

        curl_close($curl);

        return json_decode($response);
    }
}