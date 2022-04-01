<?php

namespace App\Admin\Controllers;

use App\Statistic;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

Use App\Exports\StatisticsExport;

class StatisticController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Thống kê';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Statistic());

        //export to excel

        // $grid->export(function ($export) {

        //     $export->filename('Statistics.csv');
        
        //     // $export->column('column_5', function ($value, $original) {
        //     //     return $value;
        //     // )};
        // });

        $grid->exporter(new StatisticsExport());

        $grid->disableCreateButton();

        $grid->actions(function ($actions)
        {
            $actions->disableDelete();
        });

        // $grid->batchActions(function ($batch)
        // {
        //     $batch->disableDelete();
        // });
        $grid->filter(function ($filter)
        {
            $filter->expand();
            $filter->disableIdFilter();

            $filter->like('device.name', 'Tên thiết bị');

            $filter->like('deviceCode', 'Mã thiết bị');

            $filter->between('created_at','Khoảng thời gian')->date();

        });
        $grid->model()->orderBy('id','DESC');
        $grid->column('id', __('Id'))->hide();
        $grid->column('status', __('Trạng thái'))->display(function($value){
            return $value?"Kết nối":"Không kết nối";
        })->sortable();
        $grid->column('deviceCode', __('Mã thiết bị'))->copyable();
        $grid->column('audio_out_state', __('Đang phát'))->display(function($value){
            return $value?"Đang phát":"";
        })->sortable();
        $grid->column('fan_status', __('Quạt'))->display(function($value){
            return $value?"Hoạt động":"";
        })->sortable();
        $grid->column('play_url', __('Đường dẫn'))->width(200);
        $grid->column('radio_frequency', __('Tần số Radio'))->display(function($value){
            return $value?$value:"";
        });
        $grid->column('volume', __('Âm lượng'));
        $grid->column('created_at', __('Thời gian từ'))->sortable();
        $grid->column('updated_at', __('Đến'))->sortable();
        return $grid;
    }
    // /**
    //  * Make a show builder.
    //  *
    //  * @param mixed $id
    //  * @return Show
    //  */
    // protected function detail($id)
    // {
    //     $show = new Show(Statistic::findOrFail($id));

    //     $show->field('id', __('Id'));
    //     $show->field('device_id', __('Device id'));
    //     $show->field('audio_out_state', __('Audio out state'));
    //     $show->field('fan_status', __('Fan status'));
    //     $show->field('play_url', __('Play url'));
    //     $show->field('radio_frequency', __('Radio frequency'));
    //     $show->field('volume', __('Volume'));
    //     $show->field('created_at', __('Created at'));
    //     $show->field('updated_at', __('Updated at'));

    //     return $show;
    // }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Statistic());

        $form->textarea('device_id', __('Device id'));
        $form->number('audio_out_state', __('Audio out state'));
        $form->number('fan_status', __('Fan status'));
        $form->textarea('play_url', __('Play url'));
        $form->textarea('radio_frequency', __('Radio frequency'));
        $form->number('volume', __('Volume'));

        return $form;
    }
}
