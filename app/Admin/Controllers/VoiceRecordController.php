<?php

namespace App\Admin\Controllers;

use App\VoiceRecord;
use App\Admin\Extensions\RecordVoice;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class VoiceRecordController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'VoiceRecord';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new VoiceRecord());

        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        $grid->column('fileVoice', __('FileVoice'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

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
        $show->field('name', __('Name'));
        $show->field('fileVoice', __('FileVoice'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

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

        $form->saving(function($form){
            dd($form->fileVoice);
            $form->fileVoice = 'records/'.$form->getClientOriginalName();
            file_put_contents('uploads/' . $form->fileVoice, $form->fileVoice->getClientOriginalName());
        });
        return $form;
    }
}
