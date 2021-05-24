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

class Api extends Model
{
    public function setPlayFM($type, $deviceCode, $data)
    {
        $curl = curl_init();

        $deviceCode = explode(",", $deviceCode);

        $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[';

        foreach ($deviceCode as $device)
        {
            $dataRequest .= '{\"DeviceID\":\"' . trim($device) . '\",\"CommandSend\":\"{\\\\\"Data\\\\\":\\\\\"' . $data . '\\\\\",\\\\\"PacketType\\\\\":11}\"},';
        }

        $dataRequest .= ']}"}';

        //{"DataType":4,"Data":"{\"CommandItem_Ts\":[{\"DeviceID\":\"'.$deviceCode.'\",\"CommandSend\":\"{\\\"Data\\\":\\\"'.$data.'\\\",\\\"PacketType\\\":11}\"}]}"}';
        if (env('APP_ENV') == 'local') dd($dataRequest);
        $request = base64_encode($dataRequest);

        // echo "request " . $request;
        $urlRequest = "http://103.130.213.161:906/" . $request;

        // admin_toastr('$urlRequest', 'info');
        // echo "XXX " . $urlRequest;
        Log::info($urlRequest);

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

        // if ($err) {
        //   echo "cURL Error #:" . $err;
        // } else {
        //   echo $response;
        // }
        
    }

    public function setPlaySchedule($type, $deviceCode, $startDate, $endDate, $startTime, $songName, $replay_times, $replay_delay = 30)
    {
        $curl = curl_init();

        $devices = explode(',', $deviceCode);

        $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[';

        $startT = new Carbon($startDate . ' ' . $startTime); //tạo định dạng ngày tháng

        if ($endDate == NULL || $endDate == ''){ // nếu đặt trong ngày

            if ($type == 1 || $type == 4){ // nếu là file phương tiện
                
                if (env('APP_ENV') == 'local') $ffprobe = FFProbe::create(['ffmpeg.binaries' => 'D:\ffmpeg\bin\ffmpeg.exe', 'ffprobe.binaries' => 'D:\ffmpeg\bin\ffprobe.exe']);
                else $ffprobe = FFProbe::create();

                $file_duration = $ffprobe->format($songName)->get('duration');

                $file_duration += $replay_delay; //đợi 30 giây mỗi lần lặp
                foreach ($devices as $device)
                {

                    $dataRequest .= '{\"DeviceID\":\"' . trim($device) . '\",\"CommandSend\":\"{\\\\\"PacketType\\\\\":2,\\\\\"Data\\\\\":\\\\\"{\\\\\\\\\\\\\"PlayList\\\\\\\\\\\\\":[';

                    $startT = new Carbon($startDate . ' ' . $startTime);

                    for ($i = 0;$i < $replay_times;$i++)
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
            if ($type == 3) {// nếu là đài FM hoặc tiếp sóng
                foreach($devices as $device){
                    $dataRequest .= '{\"DeviceID\":\"'.trim($device).'\",\"CommandSend\":\"{\\\\\"Data\\\\\":\\\\\"'.$data.'\\\\\",\\\\\"PacketType\\\\\":11}\"},';
                }
                $dataRequest .= ']}\\\\\"}\"}';
                if($device != $devices[count($devices) - 1]) $dataRequest .= ',';
            }
            
        }
        else{ // nếu đặt hàng ngày
            // $dates = [];
            // $period = new DatePeriod( // lấy danh sách ngày phát
            //     new DateTime($startDate),
            //     new DateInterval('P1D'),
            //     new DateTime($endDate)
            // );
            // $i = 0;
            // foreach ($period as $key => $value) {
            //     $dates[$i++] = $value->format('Y-m-d');
            // }
            // $dates[$i] = $endDate;
            if ($type == 1 || $type == 4)
            { // nếu là file phương tiện
                foreach ($devices as $device)
                { //set từng thiết bị
                    $dataRequest .= '{\"DeviceID\":\"' . trim($device) . '\",\"CommandSend\":\"{\\\\\"PacketType\\\\\":2,\\\\\"Data\\\\\":\\\\\"{\\\\\\\\\\\\\"PlayList\\\\\\\\\\\\\":[';

                    $startT = new Carbon($startDate . ' ' . $startTime);

                    for ($i = 0;$i < $replay_times;$i++)
                    {

                        $start_time_of_the_loop_play = $startT->toTimeString();

                        $start_date_of_the_loop_play = $startT->toDateString();

                        $startT->addSeconds($file_duration);

                        $end_time_of_the_loop_play = $startT->toTimeString();

                        $dataRequest .= '{\\\\\\\\\\\\\"SongName\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $songName . '\\\\\\\\\\\\\",\\\\\\\\\\\\\"TimeStart\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $start_time_of_the_loop_play . '\\\\\\\\\\\\\",\\\\\\\\\\\\\"TimeStop\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $end_time_of_the_loop_play . '\\\\\\\\\\\\\",\\\\\\\\\\\\\"DateStart\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $startDate . '\\\\\\\\\\\\\",\\\\\\\\\\\\\"DateStop\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $endDate . '\\\\\\\\\\\\\",\\\\\\\\\\\\\"PlayType\\\\\\\\\\\\\":2,\\\\\\\\\\\\\"PlayRepeatType\\\\\\\\\\\\\":1}';

                        if ($i < $replay_times - 1) $dataRequest .= ',';
                    }
                    $dataRequest .= ']}\\\\\"}\"}';

                    if ($device != $devices[count($devices) - 1]) $dataRequest .= ',';
                }
            }
        }

        $dataRequest .= ']}"}';

        if (env('APP_ENV') == 'local') dd($dataRequest);

        $request = base64_encode($dataRequest);

        // echo "request " . $request;
        $urlRequest = "http://103.130.213.161:906/" . $request;

        // admin_toastr('$urlRequest', 'info');
        // echo "XXX " . $urlRequest;
        Log::info('Play schedule ' . $urlRequest);

        //Log::info('Play schedule json' .$urlRequest);
        

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

        $response = json_decode($response);

        // if(is_numeric($response->DataType))
        //     return response()->success('Thành công')->refresh();
        // else
        //     return response()->fail('Không thành công')->refresh();
        // if ($err) {
        //   echo "cURL Error #:" . $err;
        // } else {
        //   echo $response;
        // }
        
    }

    public function sendFileToDevice($deviceCode, $songName)
    {
        $curl = curl_init();

        $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[{\"DeviceID\":\"' . $deviceCode . '\",\"CommandSend\":\"{\\\\\"PacketType\\\\\":1,\\\\\"Data\\\\\":\\\\\"{\\\\\\\\\\\\\"URLlist\\\\\\\\\\\\\":[\\\\\\\\\\\\\"' . $songName . '\\\\\\\\\\\\\"]}\\\\\"}\"}]}"}';

        $request = base64_encode($dataRequest);

        // echo "request " . $request;
        $urlRequest = "http://103.130.213.161:906/" . $request;

        Log::info('Send file ' . $urlRequest);

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
    }

    public function playOnline($type, $deviceCode, $songName)
    {
        $curl = curl_init();
        $dataRequest = "";
        $deviceCode = explode(",", $deviceCode);

        $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[';

        if ($type == 1 || $type == 4)
        { // nếu phát ngay file pt
            foreach ($deviceCode as $device)
            {

                $dataRequest .= '{\"DeviceID\":\"' . trim($device) . '\",\"CommandSend\":\"{\\\\\"Data\\\\\":\\\\\"{\\\\\\\\\\\\\"PlayRepeatType\\\\\\\\\\\\\":1,\\\\\\\\\\\\\"PlayType\\\\\\\\\\\\\":2,\\\\\\\\\\\\\"SongName\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $songName . '\\\\\\\\\\\\\"}\\\\\",\\\\\"PacketType\\\\\":5}\"},';
            }

        }
        else
        {

            $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[{\"DeviceID\":\"' . $deviceCode . '\",\"CommandSend\":\"{\\\\\"Data\\\\\":\\\\\"' . $songName . '\\\\\",\\\\\"PacketType\\\\\":11}\"}]}"}';
        }

        $dataRequest .= ']}"}';

        $request = base64_encode($dataRequest);
        // echo "request " . $request;
        $urlRequest = "http://103.130.213.161:906/" . $request;

        Log::info('Phat ngay ' . $urlRequest);

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
    }
}