<?php

namespace App\Admin\Actions\Device;

use Encore\Admin\Actions\Response;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\DeviceInfo;

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

            	$deviceinfo = DeviceInfo::where('deviceCode',$model->deviceCode)->get();

            	if(count($deviceinfo) > 0){

            		foreach($deviceinfo as $value){

                        $value->delete();

                    }

            	}
            	
                $model->delete();
            });
        } catch (\Exception $exception) {
            return $this->response()->error("{$trans['failed']} : {$exception->getMessage()}");
        }

        return $this->response()->success($trans['succeeded'])->refresh();
    }
    public function dialog()
    {
        $this->question(trans('admin.delete_confirm'), '', ['confirmButtonColor' => '#d33']);
    }
}