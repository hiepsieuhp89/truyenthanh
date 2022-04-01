<?php

namespace App\Admin\Actions\VoiceRecord;

use Encore\Admin\Actions\Response;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Delete extends RowAction
{
    public $name = 'Xóa';

    public function handle(Model $model)
    {
    	$trans = [
	        'failed'    => trans('admin.delete_failed'),
	        'succeeded' => trans('admin.delete_succeeded'),
	    ];
	    try {
            DB::transaction(function () use ($model) {
                $model->delete();

                $file_path = config('filesystems.disks.upload.path').$model->fileVoice;
                
                if(file_exists($file_path))
                    unlink($file_path);
                
            });
        } catch (\Exception $exception) {
            return $this->response()->error("{$trans['failed']} : {$exception->getMessage()}");
        }
        return $this->response()->success($trans['succeeded'])->refresh();
    }

    public function dialog(){
    	$this->question(trans('Bạn có thực sự muốn xóa?'), '', ['confirmButtonColor' => '#d33','confirmButtonText'=>trans('Xóa')]);
    }

}