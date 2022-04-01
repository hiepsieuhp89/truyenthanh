<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Carbon\Carbon;
use App\DeviceInfo;
use App\Statistic;

class UpdateDevicesStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(){
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

        if (isset($response['DataType']) && $response['DataType'] == 5) {

            $device_data = array_map(function ($arr) {
                return [
                    $arr['DeviceID'], 
                    $arr["DeviceData"]["Data"]["AudioOutState"], 
                    $arr["DeviceData"]["Data"]["Volume"]
                ];
            }, $response["Data"]);

            foreach ($device_data as $active_device) {

                DeviceInfo::where('deviceCode', $active_device[0])->update([
                    'is_playing' => $active_device[1],
                    'volume' => $active_device[2],
                ]);
            }

            //Thiết bị mới bật
            DeviceInfo::whereIn('deviceCode', array_column($device_data, 0))->where('status',0)->update([
                'status' => 1,
                'turn_on_time' => Carbon::now('Asia/Ho_Chi_Minh'),
            ]);
            //Thiết bị mới tắt
            DeviceInfo::whereNotIn('deviceCode', array_column($device_data, 0))->where('status',1)->update([
                'status' => 0,
                'turn_off_time' => Carbon::now('Asia/Ho_Chi_Minh'),
                'is_playing' => ''
            ]);
            //foreach(DeviceInf)

            // DeviceInfo::whereNotIn('deviceCode', array_column($device_data, 0))->where('turn_off_time', null)->update([
            //     'turn_off_time' => Carbon::now('Asia/Ho_Chi_Minh'),
            // ]);
        }
    }
}
