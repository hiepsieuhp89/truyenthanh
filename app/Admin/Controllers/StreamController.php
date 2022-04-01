<?php

namespace App\Admin\Controllers;

use Request;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;

use App\Admin\Actions\VoiceRecord\Delete;
use App\Admin\Actions\VoiceRecord\BatchDelete;



class StreamController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Phát trực tiếp';

    public function index(Content $content)
    {
         return $content
                ->title($this->title())
                ->description($this->description['index'] ?? trans('admin.list'))
                ->body($this->grid());

    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        if (strpos((Request::all())['url'], 'hls/') > 0)
            $url = env('STREAM_WATCHING_URL') . substr((Request::all())['url'], strpos((Request::all())['url'], 'hls/') + 4);
        else
            $url = (Request::all())['url'];
            
        return view('stream',['url'=>$url]);
    }
}
