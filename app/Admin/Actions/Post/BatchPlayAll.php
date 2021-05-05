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
        // copy the data model for each row
        $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[';

        foreach ($collection as $model) {

            // if program is a media file
            if($model->type == 1){

                $songName = $model->fileVoice;

                $devices = $model->devices;

                foreach($devices as $device){
                  $dataRequest .= '{\"DeviceID\":\"'.$device.'\",\"CommandSend\":\"{\\\\\"Data\\\\\":\\\\\"{\\\\\\\\\\\\\"PlayRepeatType\\\\\\\\\\\\\":1,\\\\\\\\\\\\\"PlayType\\\\\\\\\\\\\":2,\\\\\\\\\\\\\"SongName\\\\\\\\\\\\\":\\\\\\\\\\\\\"'.$songName.'\\\\\\\\\\\\\"}\\\\\",\\\\\"PacketType\\\\\":5}\"},';
                }
            }
            else{
              $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[';
            }
        }
        $dataRequest .= ']}"}';

                $request = base64_encode($dataRequest);

                $urlRequest = "http://103.130.213.161:906/".$request;

                // Log::info('Phat ngay ' . $urlRequest);

                // curl_setopt_array($curl, array(
                //   CURLOPT_URL => $urlRequest,
                //   CURLOPT_RETURNTRANSFER => true,
                //   CURLOPT_ENCODING => "",
                //   CURLOPT_MAXREDIRS => 10,
                //   CURLOPT_CONNECTTIMEOUT => 20,
                //   CURLOPT_TIMEOUT => 30,
                //   CURLOPT_FOLLOWLOCATION => false,
                //   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                //   CURLOPT_CUSTOMREQUEST => "GET",
                // ));
                
                // $response = curl_exec($curl);

                // $err = curl_error($curl);
                
                // curl_close($curl);

        // return a success message of "copy success" and refresh the page
        return $this->response()->success($dataRequest)->refresh();
    }
}