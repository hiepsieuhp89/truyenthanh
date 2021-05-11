<?php

namespace App\Admin\Actions\DeviceInfo;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class RelayFirst extends RowAction
{
    public $name = 'Relay 1';

    public function handle(Model $model)
    {
         // $model ...
       $model->relay1 = 1 - $model->relay1;

       if (
        ($model->relay1 == 1 && $this->setRelay($model->deviceCode,'relay1on')) || 
        ($model->relay1 == 0 && $this->setRelay($model->deviceCode,'relay1off'))) {

          $model->save();

          return $this->response()->success('Set Relay thành công')->refresh();
       } 
    }
    public function display($relay)
    {
        return ($relay == 0) ? "Tắt" : "Bật";
    }

    protected function setRelay($deviceCode, $status) 
    {
        $curl = curl_init();
        $dataRequest = "";

        $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[{\"DeviceID\":\"'.$deviceCode.'\",\"CommandSend\":\"{\\\\\"Data\\\\\":\\\\\"'.$status.'\\\\\",\\\\\"PacketType\\\\\":10}\"}]}"}';
       
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

        return 1;
    }   
}