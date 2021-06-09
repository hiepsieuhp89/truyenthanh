<?php

namespace App\Admin\Actions\Program;

use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;
use Encore\Admin\Actions\Response;
use Illuminate\Support\Facades\DB;
use App\Api;
use App\Schedule;

class BatchDelete extends BatchAction
{
    use Api;
    public $name = 'Xóa';

    public function handle(Collection $collection)
    {
    	$trans = [
            'failed'    => trans('admin.delete_failed'),
            'succeeded' => trans('admin.delete_succeeded'),
        ];
        foreach ($collection as $model) {
            try {
	            DB::transaction(function () use ($model) {
                    
	                $model->delete();

                    $this->deleteSchedule($model);
                    $this->resetSchedule(implode(',', $model->devices), $model->type);

	                // if($model->type == 1 && isset($model->fileVoice)){
	                //     $file_path = 'uploads/'.$model->fileVoice;
	                //     if(file_exists($file_path))
	                //         unlink($file_path);
	                // }
	            });
	        } catch (\Exception $exception) {
	            return $this->response()->error("{$trans['failed']} : {$exception->getMessage()}");
	        }
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