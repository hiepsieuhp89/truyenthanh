<?php

namespace App\Admin\Actions\DeviceInfo;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class StopPlay extends RowAction
{
    public $name = 'Dừng phát';

    public function handle(Model $model)
    {
        $stt = $this->stopPlay($model->deviceCode);

        return $this->response()->success($stt)->refresh();
    }

    public function display($stop)
    {
        return  "Dừng phát";
    }

    protected function stopPlay($deviceCode) 
    {
        $curl = curl_init();
        
        $dataRequest = 

        '{"DataType":4,"Data":"{\"CommandItem_Ts\":[{\"DeviceID\":\"'.$deviceCode.'\",\"CommandSend\":\"{\\\\\"Data\\\\\":\\\\\"Stop play music\\\\\\",\\\\\"PacketType\\\\\":7}\"}]}"}';
       
        $request = base64_encode($dataRequest);

        $urlRequest = "http://103.130.213.161:906/".$request;

        Log::info($urlRequest);

        curl_setopt_array($curl, array(
          CURLOPT_URL => $urlRequest,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_CONNECTTIMEOUT => 10,
          CURLOPT_TIMEOUT => 10,
          CURLOPT_FOLLOWLOCATION => false,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);

        return $dataRequest;
    }   

}