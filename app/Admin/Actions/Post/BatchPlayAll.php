<?php

namespace App\Admin\Actions\Post;

use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;

class BatchPlayAll extends BatchAction
{
    public $name = '';
    function __construct(){
      $this->name = trans('admin.play_all');
    }
    public function handle(Collection $collection)
    {
        $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[';
        foreach ($collection as $model) {
          $dataRequest .= '{\"DeviceID\":\"'.$model->deviceCode.'\",\"CommandSend\":\"{\\\"Data\\\":\\\"Play music\\\",\\\"PacketType\\\":7}'.',';
        }
        $dataRequest .= '\"}]}"}';

        $curl = curl_init();
        
          $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[{\"DeviceID\":\"'.$deviceCode.'\",\"CommandSend\":\"{\\\"Data\\\":\\\"Play music\\\",\\\"PacketType\\\":7}\"}]}"}';
         
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
        
        return $this->response()->success(trans('admin.play_all_succeeded'))->refresh();
    }
	protected function Play($deviceCode) 
	{
		$curl = curl_init();
        
        $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[{\"DeviceID\":\"'.$deviceCode.'\",\"CommandSend\":\"{\\\"Data\\\":\\\"Play music\\\",\\\"PacketType\\\":7}\"}]}"}';
       
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

        //return $response;
    }
}