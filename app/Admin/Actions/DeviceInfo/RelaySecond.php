<?php

namespace App\Admin\Actions\DeviceInfo;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class RelaySecond extends RowAction
{
    public $name = 'Relay 2';

    public function handle(Model $model)
    {
       // $model ...
       $model->relay2 = 1 - $model->relay2;

       if ($model->relay2 == 1) {
           $this->setRelay($model->deviceCode,'relay2on');
       } else {
           $this->setRelay($model->deviceCode,'relay2off');
       }

       $model->save();

       // return a new html to the front end after saving
       $html = ($model->relay2 == 1) ? "Tắt" : "Bật";

       return $this->response()->html($html);


        // return $this->response()->success('Success message.')->refresh();
    }

    public function display($relay)
    {
        return ($relay == 1) ? "Tắt" : "Bật";
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

        // return $response;
    }   

}