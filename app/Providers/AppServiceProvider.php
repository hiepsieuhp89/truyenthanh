<?php

namespace App\Providers;

use App;
use App\Device;
use App\DeviceInfo;
use App\Document;
use App\Program;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

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
        Carbon::setLocale('vi');

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

          $active_device = array_column($response["Data"], "DeviceID");

                //dd(Carbon::now('Asia/Ho_Chi_Minh'));
                    DeviceInfo::whereIn('deviceCode',$active_device)->update([
                        'status' => 1,
                        'turn_off_time' => null,
                    ]);
                    DeviceInfo::whereNotIn('deviceCode',$active_device)->update([
                        'status' => 0,
                    ]);
                    DeviceInfo::whereNotIn('deviceCode',$active_device)->where('turn_off_time',null)->update([
                        'turn_off_time' => Carbon::now('Asia/Ho_Chi_Minh'),
                    ]);
        }
        // convert mp3 filevoice to .wav
        foreach(Document::all() as $document){

          if(!is_numeric(strpos($document->fileVoice, '.wav'))){

            $fileName = substr($document->fileVoice,0,strpos($document->fileVoice, '.mp3'));
  
            if(file_exists(config('filesystems.disks.upload.path').$document->fileVoice)){

              $exec_to_convert_to_wav = 'ffmpeg -i '.config('filesystems.disks.upload.path').$document->fileVoice.' '.$fileName.'.wav';

              exec($exec_to_convert_to_wav);

              unlink(config('filesystems.disks.upload.path').$document->fileVoice);

              $document->fileVoice = $fileName.'.wav';

              $document->save();
            }
          }             
        }
        //

        foreach(Program::all() as $program){

          if(is_numeric($program->document_Id)){

              $d = Document::where('id',$program->document_Id)->first();

              if($d !== NULL){

                $program->fileVoice = $d->fileVoice;
                
                $program->save();

              }
            }
        }

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
