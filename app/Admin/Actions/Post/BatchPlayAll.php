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

        foreach ($collection as $model) {// cho moi chuong trinh

            $devices = $model->devices;// lay cac thiet bi cua chuong trinh

            // if program is a media file
            if($model->type == 1){

                $songName = $model->fileVoice;

                if($model->mode == 4){ // phat ngay

                    foreach($devices as $device){

                      $dataRequest .= '{\"DeviceID\":\"'.trim($device).'\",\"CommandSend\":\"{\\\\\"Data\\\\\":\\\\\"{\\\\\\\\\\\\\"PlayRepeatType\\\\\\\\\\\\\":1,\\\\\\\\\\\\\"PlayType\\\\\\\\\\\\\":2,\\\\\\\\\\\\\"SongName\\\\\\\\\\\\\":\\\\\\\\\\\\\"'.$songName.'\\\\\\\\\\\\\"}\\\\\",\\\\\"PacketType\\\\\":5}\"},';

                    }
                }

                else{ // theo lich

                    $startTime = $model->time ? $model->time : '00:00:00';
                    $startDate = $model->startDate ? $model->startDate : '';
                    $endDate = $model->endDate ? $model->endDate : '3000-05-10';

                    foreach($devices as $device){

                        $dataRequest .= '{\"DeviceID\":\"'.trim($device).'\",\"CommandSend\":\"{\\\\\"PacketType\\\\\":2,\\\\\"Data\\\\\":\\\\\"{\\\\\\\\\\\\\"PlayList\\\\\\\\\\\\\":[{\\\\\\\\\\\\\"SongName\\\\\\\\\\\\\":\\\\\\\\\\\\\"'.$songName.'\\\\\\\\\\\\\",\\\\\\\\\\\\\"TimeStart\\\\\\\\\\\\\":\\\\\\\\\\\\\"'.$startTime.'\\\\\\\\\\\\\",\\\\\\\\\\\\\"TimeStop\\\\\\\\\\\\\":\\\\\\\\\\\\\"00:00:00\\\\\\\\\\\\\",\\\\\\\\\\\\\"DateStart\\\\\\\\\\\\\":\\\\\\\\\\\\\"'.$startDate.'\\\\\\\\\\\\\",\\\\\\\\\\\\\"DateStop\\\\\\\\\\\\\":\\\\\\\\\\\\\"'.$endDate.'\\\\\\\\\\\\\",\\\\\\\\\\\\\"PlayType\\\\\\\\\\\\\":1,\\\\\\\\\\\\\"PlayRepeatType\\\\\\\\\\\\\":1}]}\\\\\"}\"},';

                    }
                }
                
            }
            //neu la dai Fm hoac tiep song
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
        return $this->response()->success('Thành công')->refresh();
    }
}