<?php

namespace App\Admin\Controllers;

use Request;

use App\Device;
use App\DeviceInfo;
use App\Area;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Log;
use App\Admin\Actions\DeviceInfo\StopPlay;
use App\Admin\Actions\DeviceInfo\RelayFirst;
use App\Admin\Actions\DeviceInfo\RelaySecond;
use Encore\Admin\Facades\Admin;

class DeviceInfoController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Giám sát thiết bị';
    public $path = '/admin/devicedata';

    public function index(Content $content)
    {
        if(Admin::user()->can('*') || Request::get('_scope_') == 'auth'){

            return $content

                ->title($this->title())

                ->description($this->description['index'] ?? trans('admin.list'))

                ->body($this->grid());
           
        }

        return redirect()->intended($this->path.'?_scope_=auth');
    }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DeviceInfo);

        $grid->disableCreateButton(); 

        $grid->disableBatchActions();   
         
        $grid->actions(function ($actions) {
            if (!(new Admin)->user()->can('*')) {
                $actions->disableDelete();
            }
        });
        // lấy thông tin thiết bị
        // $deviceStatus = $this->getDeviceStatus();
        // Log::info("device list " . $deviceStatus);

        $grid->filter(function($filter){

            $filter->scope('auth',trans('Giám sát thiết bị'))->whereHas('device', function ($query) {

                $query->wherein('areaId',explode(',',Admin::user()->areaId));
            });

            $filter->expand();

            $filter->disableIdFilter();

            $filter->like('name', trans('Tên thiết bị'));

            $filter->like('deviceCode', trans('Mã thiết bị'));

            $filter->equal('areaId', trans('Cụm loa'))->select((new Area())::selectOptions());
        });

        $grid->column('id', __('Id'));

        $grid->column('device.name', __('Tên'))->label()->style('font-size:16px;'); 

        $grid->column('deviceCode', __('Mã thiết bị'))->copyable();

        $grid->column('Dừng phát')->action(StopPlay::class);

        $grid->column('relay1', 'Relay 1')->action(RelayFirst::class);

        $grid->column('volume', __('Volume'))->editable('select', [1 => 'Mức 1', 2 => 'Mức 2', 3 => 'Mức 3', 4 => 'Mức 4', 5 => 'Mức 5', 6 => 'Mức 6', 7 => 'Mức 7', 8 => 'Mức 8', 9 => 'Mức 9', 10 => 'Mức 10', 11 => 'Mức 11', 12 => 'Mức 12', 13 => 'Mức 13', 14 => 'Mức 14', 15 => 'Mức 15']);
        
        $grid->column('ip', __('IP'))->editable();

        $states = [
            'off' => ['value' => 0, 'text' => 'Tắt', 'color' => 'danger'],
            'on' => ['value' => 1, 'text' => 'Bật', 'color' => 'primary'],
        ];
        $grid->column('status', 'Trạng thái')->switch($states);

        $grid->column('created_at', __('Created at'));

        $grid->column('updated_at', __('Updated at'));

        $grid->disableExport();

        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();

            // $actions->add(new StopPlay);

            // $actions->setupDeleteScript();
            // $actions->disableView();

             // append an action.
            //  $actions->append('<a href=""><i class="fa fa-eye"></i></a>');

            // prepend an action.
            // $actions->prepend('<a href=""><i class="fa fa-paper-plane"></i></a>');
        });

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
        $show = new Show(DeviceInfo::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('deviceCode', __('DeviceCode'));
        $show->field('status', __('Status'));
        $show->field('volume', __('Volume'));
        $show->field('ip', __('Ip'));
        $show->field('version', __('Version'));
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
        $form = new Form(new DeviceInfo);

        $form->text('deviceCode');

        $states = [
            'off' => ['value' => 0, 'text' => 'Tắt', 'color' => 'danger'],
            'on' => ['value' => 1, 'text' => 'Bật', 'color' => 'primary'],
        ];

        $form->switch('status','Trạng thái')->states($states)->default(0);

        $form->number('volume', __('Volume'))->default(5);

        $form->textarea('ip', __('Ip'));
        
        // $form->textarea('version', __('Version'));
        // $form->display('created_at', __('Created At'));
        // $form->display('updated_at', __('Updated At'));
        $form->saved(function ($form) {
            // $debug_export = var_export($form, true);
            // ob_start();
            // var_dump($form);
            // $debug_dump = ob_get_clean();
            // Log::info("device data " . $debug_dump);
            $this->setDeviceStatus(4,  $form->model()->deviceCode,  $form->model()->volume);

            $device = Device::where('deviceCode',$form->model()->deviceCode)->first();
            if($device !== NULL){
              $device->status = $form->model()->status;
              $device->save();
            }
        });

        return $form;
    }

    protected function setDeviceStatus($type, $deviceCode, $data) 
    {
        $curl = curl_init();

        // $dataRequest = '{"DataType":'.$type.',"Data":"{\"CommandItem_Ts\":[{\"DeviceID\":\"'.$deviceCode.'\",\"CommandSend\":\"{\\\"'.$data.'\\\":\\\"6\\\",\\\"PacketType\\\":17}\"}]}"}';

        // $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[{\"DeviceID\":\"123456789ABCDF4\",\"CommandSend\":\"{\\\\\\"Data\\\\\\":\\\\\\"8\\\\\\",\\\\\\"PacketType\\\\\\":17}\"}]}"}';
        $dataRequest = '{"DataType":'.$type.',"Data":"{\"CommandItem_Ts\":[{\"DeviceID\":\"'.$deviceCode.'\",\"CommandSend\":\"{\\\\\\"Data\\\\\\":\\\\\\"'.$data.'\\\\\\",\\\\\\"PacketType\\\\\\":17}\"}]}"}';
        
        $request = base64_encode($dataRequest);

        // echo "request " . $request;
        $urlRequest = "http://103.130.213.161:906/".$request;

        // admin_toastr('$urlRequest', 'info');

        // echo "XXX " . $urlRequest;
        Log::info($urlRequest);


        curl_setopt_array($curl, array(
          CURLOPT_URL => $urlRequest,
        //   CURLOPT_URL => "http://103.130.213.161:906/eyJEYXRhVHlwZSI6NCwiRGF0YSI6IntcIkNvbW1hbmRJdGVtX1RzXCI6W3tcIkRldmljZUlEXCI6XCIxMjM0NTY3ODlBQkNERjFcIixcIkNvbW1hbmRTZW5kXCI6XCJ7XFxcIkRhdGFcXFwiOlxcXCIxM1xcXCIsXFxcIlBhY2tldFR5cGVcXFwiOjE3fVwifV19In0=",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_CONNECTTIMEOUT => 20,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_FOLLOWLOCATION => false,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        
        // if ($err) {
        //   echo "cURL Error #:" . $err;
        // } else {
        //   echo $response;
        // } 
    }   

    protected function getDeviceStatus() 
    {
        $curl = curl_init();

        $dataRequest = "eyJEYXRhVHlwZSI6MjAsIkRhdGEiOiJHRVRfQUxMX0RFVklDRV9TVEFUVVMifQ==";
        
        curl_setopt_array($curl, array(
          CURLOPT_URL => "http://103.130.213.161:906/".$dataRequest,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_CONNECTTIMEOUT => 20,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_FOLLOWLOCATION => false,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);

        return $response;

        // if ($err) {
        //   echo "cURL Error #:" . $err;
        // } else {
        // //   file_put_contents("voices/".$fileVoice , $response);
        //   echo $response;
        // } 
    }   

    
}
