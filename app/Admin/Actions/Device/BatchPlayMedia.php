<?php

namespace App\Admin\Actions\Device;

use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;

class BatchPlayMedia extends BatchAction
{
    public $name = 'Phát audio ngay';

    public function handle(Collection $collection)
    {
        foreach ($collection as $model) {
            // ...
        }

        return $this->response()->success('Success message...')->refresh();
    }
    public function form()
	{
	    $type = [
	        1 => 'Advertising',
	        2 => 'Illegal',
	        3 => 'Fishing',
	    ];
	    
	    $this->checkbox('type', 'type')->options($type);
	    $this->textarea('reason', 'reason')->rules('required');
	}
}