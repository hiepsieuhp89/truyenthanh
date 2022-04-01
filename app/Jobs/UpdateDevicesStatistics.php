<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Carbon\Carbon;
use App\Statistic;
use App\DeviceInfo;

class UpdateDevicesStatistics implements ShouldQueue
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
    public function handle()
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

        if (isset($response['DataType']) && $response['DataType'] == 5) {

            $device_data = array_map(function ($arr) {
                return [
                    $arr['DeviceID'], 
                    $arr["DeviceData"]["Data"]["AudioOutState"], 
                    $arr["DeviceData"]["Data"]["Fan1Status"], 
                    $arr["DeviceData"]["Data"]["PlayURL"], 
                    $arr["DeviceData"]["Data"]["RadioFrequency"], 
                    $arr["DeviceData"]["Data"]["Volume"]
                ];
            }, $response["Data"]);
            
            // các thiết bị kết nối
            foreach ($device_data as $active_device) {

                $statistic = Statistic::where('deviceCode', $active_device[0])->orderby('id','DESC')->first();

                if(isset($statistic) && $statistic->status == 1 && $statistic->audio_out_state == $active_device[1] && $statistic->fan_status == $active_device[2] && $statistic->play_url == $active_device[3] &&  $statistic->radio_frequency == $active_device[4] && $statistic->volume == $active_device[5]){

                    $statistic->updated_at = Carbon::now();
                    $statistic->save();
                    
                }
                else{
                    $statistic = new Statistic();
                    $statistic->status = 1;
                    $statistic->deviceCode = $active_device[0];
                    $statistic->audio_out_state = $active_device[1];
                    $statistic->fan_status = $active_device[2];
                    $statistic->play_url = $active_device[3];
                    $statistic->radio_frequency = $active_device[4];
                    $statistic->volume = $active_device[5];
                    $statistic->save();
                }
            }
            // các tb ko kết nối
            foreach(DeviceInfo::WhereNotIn('deviceCode', array_map(function($values){return $values[0];},$device_data))->get() as $device){
                
                // lấy record gần nhất của tb
                $stat = Statistic::where('deviceCode', $device->deviceCode)->first();

                // nếu có record và trạng thái cũ là đang bật (= 1)
                if(!isset($stat) || $stat->status ){
                    $statistic = new Statistic();
                    $statistic->status = 0;
                    $statistic->deviceCode = $device->deviceCode;
                    $statistic->play_url = '';
                    $statistic->radio_frequency = '';
                    $statistic->save();
                }
                else{
                    $stat->updated_at = Carbon::now();
                    $stat->save();
                }
            }
        }
    }
}
