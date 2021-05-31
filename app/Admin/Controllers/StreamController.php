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



class VoiceRecordController extends AdminController
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
        $show = new Show(VoiceRecord::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Tên'));
        $show->field('fileVoice', __('FileVoice'));
        $show->field('creatorId', __('Người tạo'));
        $show->field('created_at', __('Thời gian tạo'));
        $show->field('updated_at', __('Thời gian cập nhật'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new VoiceRecord());

        $form->text('name', __('Tên bản ghi'));

        $form->record('fileVoice', __('File ghi âm'));

        $form->saving(function ($form) {

            file_put_contents(config('filesystems.disks.upload.path') . 'records/' . $form->fileVoice->getClientOriginalName(), file_get_contents($form->fileVoice));

            $form->fileVoice = 'records/' . $form->fileVoice->getClientOriginalName();

            $form->model()->creatorId = Admin::user()->id;
        });
        return $form;
    }
}
