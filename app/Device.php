<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Device;

class Device extends Model
{
    public function area()
    {
        return $this->belongsTo(Area::class,'areaId');
    }
    
    public function DeviceInfo()
    {
        return $this->hasOne(DeviceInfo::class,'deviceCode','deviceCode');
    }
    public function updateStatus(){

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

        whereIn('deviceCode',$active_device)->update(['status' => 1]);
        whereNotIn('deviceCode',$active_device)->update(['status' => 0]);

        DeviceInfo::whereIn('deviceCode',$active_device)->update(['status' => 1]);
        DeviceInfo::whereNotIn('deviceCode',$active_device)->update(['status' => 0]);
    }
}