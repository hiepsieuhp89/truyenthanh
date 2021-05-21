<?php

namespace App\Admin\Actions\Program;

use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;

class BatchPlayAll extends BatchAction
{
    public $name = 'Phát tất cả';

    public function handle(Collection $collection)
    {
        foreach ($collection as $model) {
            // ...
        }

        return $this->response()->success('Success message...')->refresh();
    }

}