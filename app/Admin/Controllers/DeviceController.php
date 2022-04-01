<?php

namespace App\Admin\Controllers;

use Request;

use App\Device;
use App\DeviceInfo;
use App\Area;

use App\Admin\Actions\Device\Delete;

use Encore\Admin\Controllers\AdminController;
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
    public $path = '/devices';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    public function map(){
        $devices = Device::join('device_infos', 'device_infos.deviceCode', '=', 'devices.deviceCode')
                ->select('devices.id','devices.name','devices.address','devices.lat','devices.lon','device_infos.status')->wherein('areaId', array_diff(explode(",", Admin::user()->areaId), array("")))->get();
                
        return response()->view('map',['devices'=>$devices])->header('Content-Type', 'text/xml');

    }
    // public function index(Content $content)
    // {
    //     if(Admin::user()->can('*') || Request::get('_scope_') == 'auth'){

    //         return $content

    //             ->title($this->title())

    //             ->description($this->description['index'] ?? trans('admin.list'))

    //             ->body($this->grid());
           
    //     }

    //     return redirect()->intended($this->path.'?_scope_=auth');
    // }
    public function edit($id, Content $content)
    {
        $device = Device::find($id);

        if (Admin::user()->can('*') || !$device ||in_array($device->areaId, explode(',',Admin::user()->areaId)) )

            return $content->title($this->title())
            ->description($this->description['edit']??trans('admin.edit'))
            ->body($this->form()
            ->edit($id));

        abort(404);
    }
    protected function grid()
    {
        $grid = new Grid((new Device));

        if(!Admin::user()->can('*'))
            $grid->model()->wherein('areaId',explode(',',Admin::user()->areaId));

        $grid->actions(function ($actions) {

            $actions->disableDelete();

            $actions->add(new Delete());

            // if (!Admin::user()->can('*')) {

            //     $actions->disableDelete();
            // }
        });
        // $grid->batchActions(function ($batch) {

        //     $batch->add(new BatchPlayMedia());

        //     if (!Admin::user()->can('*')) {

        //         $batch->disableDelete();

        //     }
        // });
        $grid->filter(function($filter){

            $filter->scope('auth',trans('Thiết bị'))->wherein('areaId',explode(',',Admin::user()->areaId));

            $filter->expand();

            $filter->disableIdFilter();

            $filter->like('name', trans('Tên thiết bị'));

            $filter->like('deviceCode', trans('Mã thiết bị'));

            $filter->equal('DeviceInfo.status', trans('Trạng thái'))->select([
                1 => "Đang hoạt động",
                0 => "Không hoạt động",
            ]);
            if(Admin::user()->can('*'))
                $filter->equal('areaId', trans('Cụm loa'))->select((new Area())::selectOptions());
        });

        $grid->model()->orderBy('id', 'DESC');

        $grid->column('id', trans('entity.id'))->hide();

        $grid->column('name', trans('admin.deviceName'))->display(function($name){

            return $this->DeviceInfo->status ? '<span class="label label-success">' . $name . '</span>':'<span class="label label-danger">' . $name . '</span>';

        })->style('font-size:16px;');  

        $grid->column('deviceCode', trans('admin.deviceCode'))->copyable(); 

        $grid->column('area.title', trans('Cụm loa'))->label(' label-primary')->style('font-size:16px;');   

        $grid->column('address', trans('admin.address'))->label(' label-default')->style('font-size:16px;');

        $grid->column('lat', trans('admin.latitude'))->label(' label-info')->style('font-size:16px;')->hide();  

        $grid->column('lon', trans('admin.longtitude'))->label(' label-info')->style('font-size:16px;')->hide();

        // $grid->column('DeviceInfo.status', trans('Trạng thái'))->display(function($value){
        //     if($value == 1) return "<b class=\"text-success\">Đang hoạt động</b>";
        //     return "<b class=\"text-danger\">Không hoạt động</b>";
        // });   

        // $grid->column('payment_fee', trans('entity.gateway.payment_fee') . ' (%)');
        // $grid->column('transaction_fee', trans('entity.gateway.transaction_fee') . ' (VNĐ)');
        // $grid->column('cancellation_fee', trans('entity.gateway.cancellation_fee') . ' (VNĐ)');
        // $grid->column('charge_back_fee', trans('entity.gateway.charge_back_fee') . ' (VNĐ)');
        // $grid->column('rolling_reserve_days', trans('entity.gateway.rolling_reserve_days'));
        // $grid->column('rolling_reserve_percent', trans('entity.gateway.rolling_reserve_percent') . ' (%)');

        $grid->column('created_at', trans('entity.created_at'))->hide();

        $grid->column('updated_at', trans('entity.updated_at'))->hide();
        
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

        $show->field('deviceCode', trans('Mã thiết bị'));

        $show->field('address', trans('Địa chỉ'));

        $show->field('area.title', trans('Cụm loa'));

        $show->divider();

        $show->field('Vị trí')->latlong('lat', 'lon', $height = 500, $zoom = 16);

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

        if(Admin::user()->can('*'))
            $form->select('areaId', trans('Cụm loa'))->options(Area::selectOptions());

        $form->text('deviceCode', trans('Mã thiết bị'))->creationRules(['required', "unique:devices"])
        ->updateRules(['required', "unique:devices,deviceCode,{{id}}"]);
        $form->text('address', trans('Địa chỉ'))->rules('required');

        $form->latlong('lat', 'lon', 'Vị trí')->height(500)->default(['lat' => 20.955835 , 'lng' => 105.7563658 ]);

        // $form->disableReset();
        // $form->saved(function (Form $form) {
        //     // $formInfo = new Form(new DeviceInfo);
        //     // $formInfo->model()->id = $form->model()->id;
        //     // $infoModel = DeviceInfo::findOrFail($form->model()->id);
        //     // $infoModel->deviceCode = $form->model()->deviceCode;
        //     DeviceInfo::updateOrCreate(['id' => $form->model()->id], ['deviceCode' => $form->model()->deviceCode]);
        // });
        $form->saving(function (Form $form) {
            if(!Admin::user()->can('*'))
                    $form->model()->areaId = Admin::user()->areaId    ;       
            $form->model()->status = 0;
        });

        $form->saved(function (Form $form) {
            // $formInfo = new Form(new DeviceInfo);
            // $formInfo->model()->id = $form->model()->id;
            // $formInfo->model()->deviceCode = $form->model()->deviceCode;

            $infoModel = DeviceInfo::where('deviceCode',$form->model()->deviceCode)->first();

            if(empty($infoModel)) {
                $infoModel = new DeviceInfo();
                $infoModel->deviceCode = $form->model()->deviceCode;
                $infoModel->status = $form->model()->status;
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
