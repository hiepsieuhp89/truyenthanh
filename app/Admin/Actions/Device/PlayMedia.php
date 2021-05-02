<?php

namespace App\Admin\Actions\Device;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class PlayMedia extends RowAction
{
    public $name = 'Phát audio ngay';

    public function handle(Model $model,Request $request)
    {
        // $model ...

        return $this->response()->success('Success message.')->refresh();
    }
    public function form()
	{
	    $this->file('fileVoice', 'Chọn file')->uniqueName()->required();
	}

}