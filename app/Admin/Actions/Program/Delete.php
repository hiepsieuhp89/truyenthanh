<?php

namespace App\Admin\Actions\Program;

use Encore\Admin\Actions\Response;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Schedule;

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
                
                Schedule::wherein('deviceCode', $model->devices)
                ->where('fileVoice', config('filesystems.disks.upload.url') . $model->fileVoice)
                ->where('startDate',$model->startDate)
                ->where('time', $model->time)
                ->where('endDate', $model->endDate)
                ->delete();

                // if($model->type == 1 && isset($model->fileVoice)){
                //     $file_path = 'uploads/'.$model->fileVoice;
                //     if(file_exists($file_path))
                //         unlink($file_path);
                // }
            });
        } catch (\Exception $exception) {
            return $this->response()->error("{$trans['failed']} : {$exception->getMessage()}");
        }

        return $this->response()->success($trans['succeeded'])->refresh();
    }

	/**
     * @return void
     */
    public function dialog(){
    	$this->question(trans('Bạn có thực sự muốn xóa?'), '', ['confirmButtonColor' => '#d33','confirmButtonText'=>trans('Xóa')]);
    }
}