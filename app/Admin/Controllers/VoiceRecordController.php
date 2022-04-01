<?php

namespace App\Admin\Controllers;

use Request;

use App\VoiceRecord;
use App\Admin\Extensions\RecordVoice;
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
    protected $title = 'Bản ghi âm';
    public $path = '/voice-records';

    public function index(Content $content)
    {
        // ->body(view('admin.chartjs',[
        //         'programs' => Program::select(DB::raw('type, COUNT(type) as types'))->groupby('type')->get()
        //         ]))
        if (Admin::user()->can('*') || Request::get('_scope_') == 'auth') {

            return $content
                ->title($this->title())
                ->description($this->description['index'] ?? trans('admin.list'))
                ->body($this->grid());
        }

        return redirect()->intended($this->path . '?_scope_=auth');
    }

    // public function show($id, Content $content)
    // {
    //     $record = VoiceRecord::where('id', $id)->first();

    //     if (Admin::user()->can('*') || Request::get('_scope_') == 'auth' || !isset($record->creatorId) ||       $record->creatorId == Admin::user()->id ) 

    //         return $content->title($this->title())
    //             ->description($this->description['show'] ?? trans('admin.show'))
    //             ->body($this->detail($id));
    //     abort(404);
    //     //return redirect()->intended($this->path);
    // }

    // public function edit($id, Content $content)
    // {
    //     $record = VoiceRecord::where('id', $id)->first();

    //     if (Admin::user()->can('*') || Request::get('_scope_') == 'auth' || !isset($record->creatorId) || $record->creatorId == Admin::user()->id) 

    //         return $content->title($this->title())
    //         ->description($this->description['edit'] ?? trans('admin.edit'))
    //         ->body($this->form()
    //             ->edit($id));

    //     //return redirect()->intended($this->path);
    //     abort(404);
    // }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new VoiceRecord());

        $grid->filter(function ($filter) {
            //$filter->expand();
            $filter->disableIdFilter();

            $filter->scope('auth', trans('Chương trình'))->where('creatorId', Admin::user()->id);
        });

        $grid->actions(function($actions){

            $actions->disableEdit();
            $actions->disableView();
            $actions->disableDelete();
            $actions->add(new Delete());
        });

        $grid->batchActions(function ($actions) {
            
            $actions->disableDelete();

            $actions->add(new BatchDelete());
        });
        $grid->model()->orderBy('id', 'DESC');
        $grid->column('id', __('Mã số'))->hide();
        $grid->column('name', __('Tên bản ghi'));
        $grid->column('fileVoice', __('File ghi âm'))->display(function ($fileVoice) {
            return "<audio controls><source src='" . config('filesystems.disks.upload.url') . $fileVoice . "' type='audio/wav'></audio>";
        });
        $grid->column('creatorId', __('Người tạo'))->display(function($creatorId){
            return isset($this->creator) ? $this->creator->name : ' ';
        });
        $grid->column('created_at', __('Thời gian tạo'))->sortable();
        $grid->column('updated_at', __('Thời gian cập nhật'))->hide();

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

        $form->text('name', __('Tên bản ghi'))->rules('required',['required'=>'Cần nhập tên bản ghi']);

        $form->record('fileVoice', __('File ghi âm'))->rules('required', ['required' => 'Chưa ghi âm']);

        $form->saving(function($form){

            file_put_contents(config('filesystems.disks.upload.path').'records/'.$form->fileVoice->getClientOriginalName(), file_get_contents($form->fileVoice));

            $form->fileVoice = 'records/' . $form->fileVoice->getClientOriginalName();

            $form->model()->creatorId = Admin::user()->id;

        });
        $form->disableViewCheck();
        $form->disableEditingCheck();
        $form->disableCreatingCheck();
        return $form;
    }
}
