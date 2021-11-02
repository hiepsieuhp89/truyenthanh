<?php

namespace App\Admin\Actions\DeviceInfo;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Api;

class StopPlay extends RowAction
{

    use Api;

    public $name = 'Dừng phát';

    public function handle(Model $model)
    {
        $this->stopPlay([$model->deviceCode]);

        return $this->response()->success('Dừng phát thành công')->refresh();
    }

    public function display($stop)
    {
        return  '<span><i class="fas fa-stop-circle"></i> Dừng phát</span>';
    }
}