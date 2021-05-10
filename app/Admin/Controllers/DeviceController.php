<?php

namespace App\Admin\Controllers;

use Request;

use App\Device;
use App\DeviceInfo;
use App\Area;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Controllers\Dashboard;
use App\Admin\Actions\Device\PlayMedia;
use App\Admin\Actions\Device\BatchPlayMedia;
use Encore\Admin\Layout\Content;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Facades\Admin;

class DeviceController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Thiết bị';
    public $path = '/admin/devices';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    public function map(){
        $devices = Device::select('id','name','address','lat','lon','status')->get();
        return response()->view('map',['devices'=>$devices])->header('Content-Type', 'text/xml');
    }
    public function index(Content $content)
    {
        if(Admin::user()->can('*') || Request::get('_scope_') == 'auth'){

            return $content

                ->title($this->title())

                ->description($this->description['index'] ?? trans('admin.list'))

                ->body($this->grid());
           
        }

        return redirect()->intended($this->path.'?_scope_=auth');

        // if(Request::get('_scope_') == 'auth')
        //     return $content
        //         ->title($this->title())
        //         ->description($this->description['index'] ?? trans('admin.list'))
        //         ->body($this->grid());

        // return redirect()->intended($this->path.'?_scope_=auth');
    }
    protected function grid()
    {
        //dd((new Device)->where('areaId',7));
        $grid = new Grid((new Device));

        //$grid->disableCreateButton();       
        //$grid->disablePagination();        
        //$grid->disableBatchActions();    
        //$grid->disableExport();    
        //$grid->disableColumnSelector();  

        //$grid->filterRecord('`areaId` = 7');

        $grid->actions(function ($actions) {
            
            $actions->add(new PlayMedia);

            if (!Admin::user()->can('*')) {

                $actions->disableDelete();
            }
        });
        $grid->batchActions(function ($batch) {

            $batch->add(new BatchPlayMedia());

            if (!Admin::user()->can('*')) {

                $batch->disableDelete();

            }
        });
        $grid->filter(function($filter){

            $filter->scope('auth',trans('Thiết bị'))->wherein('areaId',explode(',',Admin::user()->areaId));

            $filter->expand();

            $filter->disableIdFilter();

            $filter->like('name', trans('Tên thiết bị'));

            $filter->like('deviceCode', trans('Mã thiết bị'));

            $filter->like('area.title', trans('Khu vực'));

            // $menuModel = new Area();
            // $filter->equal('areaId', trans('Cụm loa'))->select($menuModel::selectOptions());
        });

        $grid->column('id', trans('entity.id'));

        $grid->column('name', trans('Tên thiết bị'))->label()->style('font-size:16px;');  

        $grid->column('deviceCode', trans('Mã thiết bị')); 

        $grid->column('area.title', trans('Cụm loa'))->label(' label-primary')->style('font-size:16px;');   

        $grid->column('address', trans('Địa chỉ'))->label(' label-default')->style('font-size:16px;');

        $grid->column('lat', trans('Tọa độ lat'))->label(' label-info')->style('font-size:16px;')->hide();  

        $grid->column('lon', trans('Tọa độ lon'))->label(' label-info')->style('font-size:16px;')->hide();

        $grid->column('status', trans('Trạng thái'))->display(function($value){
            if($value == 1) return "Bật";
            return "Tắt";
        });    

        // $grid->column('payment_fee', trans('entity.gateway.payment_fee') . ' (%)');
        // $grid->column('transaction_fee', trans('entity.gateway.transaction_fee') . ' (VNĐ)');
        // $grid->column('cancellation_fee', trans('entity.gateway.cancellation_fee') . ' (VNĐ)');
        // $grid->column('charge_back_fee', trans('entity.gateway.charge_back_fee') . ' (VNĐ)');
        // $grid->column('rolling_reserve_days', trans('entity.gateway.rolling_reserve_days'));
        // $grid->column('rolling_reserve_percent', trans('entity.gateway.rolling_reserve_percent') . ' (%)');

        $grid->column('created_at', trans('entity.created_at'));

        $grid->column('updated_at', trans('entity.updated_at'));
        
        // $grid->actions(function (Grid\Displayers\Actions $actions) {
            // $actions->disableEdit();
            // $actions->disableView();
        // });

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
        $show = new Show(Device::findOrFail($id));
        $show->field('id', trans('entity.id'));
        $show->field('name', trans('Tên thiết bị'));
        $show->field('deviceCode', trans('deviceCode'));
        $show->field('address', trans('Địa chỉ'));
        $show->field('area.title', trans('Cụm loa'));
        // $show->field('areaId', trans('Cụm loa'));

        // $show->area(trans('Cụm loa'), function($area){
        //     $area->title();
        // });
        $show->divider();
        $show->field('lat', trans('Tọa độ Lat'));
        $show->field('lon', trans('Tọa độ Lon'));

        /*
        $show->html('Bản đồ')->as(function ($devices) {
            $devices = Device::all();
            return view('deviceMap')->render();
        })->badge()->style('font-size:16px;');
        */
        $show->title('Vị trí')->as(function () use ($id) {
            return view('deviceMap',['device'=>Device::findOrFail($id)])->render();
        })->badge(' w-100 h-400px p-0');


        $show->field('created_at', trans('entity.created_at'));
        $show->field('updated_at', trans('entity.updated_at'));


        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Device);
        $form->text('name', trans('Tên thiết bị'))->rules('required')->autofocus();
        $form->select('areaId', trans('Cụm loa'))->options(Area::selectOptions());
        $form->text('deviceCode', trans('Mã thiết bị'))->creationRules(['required', "unique:devices"])
        ->updateRules(['required', "unique:devices,deviceCode,{{id}}"]);
        $form->text('address', trans('Địa chỉ'))->rules('required');
        $form->text('lat', trans('Tọa độ Lat'))->rules('required');
        $form->text('lon', trans('Tọa độ Lon'))->rules('required');
        //$form->latlong('lat', 'lon', 'Position');

        // $form->disableReset();
        // $form->saved(function (Form $form) {
        //     // $formInfo = new Form(new DeviceInfo);
        //     // $formInfo->model()->id = $form->model()->id;
        //     // $infoModel = DeviceInfo::findOrFail($form->model()->id);
        //     // $infoModel->deviceCode = $form->model()->deviceCode;
        //     DeviceInfo::updateOrCreate(['id' => $form->model()->id], ['deviceCode' => $form->model()->deviceCode]);
        // });

        $form->saved(function (Form $form) {
            // $formInfo = new Form(new DeviceInfo);
            // $formInfo->model()->id = $form->model()->id;
            // $formInfo->model()->deviceCode = $form->model()->deviceCode;

            $infoModel = DeviceInfo::find($form->model()->id);

            if(empty($infoModel)) {
                $infoModel = new DeviceInfo();
                $infoModel->id = $form->model()->id;
                $infoModel->deviceCode = $form->model()->deviceCode;
                $infoModel->save();
            } else {
                $infoModel->deviceCode = $form->model()->deviceCode;
                $infoModel->update();
            }

            // if(empty($infoModel)) {
            //     $formInfo->model()->save();
            // } else {
                // $formInfo->model()->update();
            // }
        });

        $form->disableViewCheck();
        $form->disableEditingCheck();
        $form->disableCreatingCheck();

        return $form;
    }
}
