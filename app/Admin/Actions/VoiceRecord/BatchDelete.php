<?php

namespace App\Admin\Actions\VoiceRecord;

use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;
use Encore\Admin\Actions\Response;
use Illuminate\Support\Facades\DB;

class BatchDelete extends BatchAction
{
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

                    $file_path = 'uploads/' . $model->fileVoice;
                    
                    if (file_exists($file_path))
                        unlink($file_path);
                    
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
    public function dialog()
    {
        $this->question(trans('Bạn có thực sự muốn xóa?'), '', ['confirmButtonColor' => '#d33', 'confirmButtonText' => trans('Xóa')]);
    }

}