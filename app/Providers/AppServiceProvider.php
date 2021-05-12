<?php

namespace App\Providers;

use App;
use App\Device;
use App\DeviceInfo;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Session;

use Encore\Admin\Config\Config;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
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

        $active_device = array_column($response["Data"], "DeviceID");

        DeviceInfo::whereIn('deviceCode',$active_device)->update(['status' => 1]);
        DeviceInfo::whereNotIn('deviceCode',$active_device)->update(['status' => 0]);
        Device::whereIn('deviceCode',$active_device)->update(['status' => 1]);
        Device::whereNotIn('deviceCode',$active_device)->update(['status' => 0]);
        
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
