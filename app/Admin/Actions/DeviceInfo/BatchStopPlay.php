<?php

namespace App\Admin\Actions\DeviceInfo;

use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;
use App\Api;

class BatchStopPlay extends BatchAction
{
    use Api;
    public $name = 'Dừng phát';

    public function handle(Collection $collection)
    {
        $deviceCode = array_map(function($model){
            return $model['deviceCode'];
        }, $collection->toArray());

        $this->stopPlay($deviceCode);
        // foreach ($collection as $model) {

        //     $this->stopPlay($model->deviceCode);

        // }
        return $this->response()->success('Dừng phát thành công')->refresh();
    }

}