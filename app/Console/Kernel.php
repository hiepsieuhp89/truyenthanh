<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Carbon\Carbon;
use App\DeviceInfo;
use App\Document;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();

        $schedule->call(function () {
            $curl = curl_init();

            $dataRequest = "eyJEYXRhVHlwZSI6MjAsIkRhdGEiOiJHRVRfQUxMX0RFVklDRV9TVEFUVVMifQ==";
            
            curl_setopt_array($curl, array(
              CURLOPT_URL => "http://103.130.213.161:906/".$dataRequest,
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
            $response = json_decode($response,true);

            if(isset($response['DataType']) && $response['DataType'] == 5){
              $device_data = array_map(function($arr){
                return [$arr['DeviceID'], $arr["DeviceData"]["Data"]["PlayURL"]];
              }, $response["Data"]);

              foreach ($device_data as $active_device) {

                  DeviceInfo::where('deviceCode',$active_device[0])->update([
                      'status' => 1,
                      'turn_off_time' => null,
                      'is_playing' => $active_device[1],
                  ]);

              }

                        DeviceInfo::whereNotIn('deviceCode',array_column($device_data, 0))->update([
                            'status' => 0,
                            'is_playing' => ''
                        ]);
                        DeviceInfo::whereNotIn('deviceCode',array_column($device_data, 0))->where('turn_off_time',null)->update([
                            'turn_off_time' => Carbon::now('Asia/Ho_Chi_Minh'),
                        ]);

            }
        })->everyMinute();

        //Program::where('volumeBooster','<',5)->update(['volumeBooster' => 10]);
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
