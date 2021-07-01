<?php

namespace App\Providers;
use App\DeviceInfo;
use Exception;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

use App\Jobs\UpdateDevicesStatus;

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

        try{
            (new UpdateDevicesStatus())->handle();
        }catch(Exception $e){}
        // convert mp3 filevoice to .wav
        // foreach(Document::all() as $document){

        //   if(!is_numeric(strpos($document->fileVoice, '.wav'))){

        //     $fileName = substr($document->fileVoice,0,strpos($document->fileVoice, '.mp3'));
  
        //     if(file_exists(config('filesystems.disks.upload.path').$document->fileVoice)){

        //       $exec_to_convert_to_wav = 'ffmpeg -i '.config('filesystems.disks.upload.path').$document->fileVoice.' '.$fileName.'.wav';

        //       exec($exec_to_convert_to_wav);

        //       unlink(config('filesystems.disks.upload.path').$document->fileVoice);

        //       $document->fileVoice = $fileName.'.wav';

        //       $document->save();
        //     }
        //   }             
        // }
        // //
        // // put fileVoice to
        // foreach(Program::all() as $program){

        //   if(is_numeric($program->document_Id)){

        //       $d = Document::where('id',$program->document_Id)->first();

        //       if($d !== NULL){

        //         $program->fileVoice = $d->fileVoice;
                
        //         $program->save();

        //       }
        //     }
        // }
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
