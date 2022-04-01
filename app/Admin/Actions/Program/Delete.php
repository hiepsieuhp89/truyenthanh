<?php

namespace App\Admin\Actions\Program;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Program;
use App\Api;

class Delete extends RowAction
{
    use Api;
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

                if($model->mode != 4){
                    $this->deleteSchedule($model);
                    $this->resetSchedule($model->devices, $model->type);
                }

                if($model->type == 1 && isset($model->fileVoice) && count(Program::where('fileVoice', $model->fileVoice)->get()) == 0){
                    $file_path = 'uploads/'.$model->fileVoice;
                    if(file_exists($file_path))
                        unlink($file_path);
                }
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