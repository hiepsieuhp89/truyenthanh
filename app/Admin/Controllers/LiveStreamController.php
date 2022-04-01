<?php

namespace App\Admin\Controllers;

use App\LiveStreaming;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class LiveStreamController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'LiveStreaming';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new LiveStreaming());
        $grid->column('name', 'Tên chương trình');
        $grid->column('url', 'Đường dẫn');

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(LiveStreaming::findOrFail($id));



        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new LiveStreaming());
        $form->text('name','Tên chương trình');
        $form->text('url', 'Đường dẫn');



        return $form;
    }
}
