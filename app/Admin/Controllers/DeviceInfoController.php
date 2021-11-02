<?php

namespace App\Admin\Controllers;

use Request;
use Carbon\Carbon;

use App\Device;
use App\DeviceInfo;
use App\Area;
use App\Schedule;
use App\Program;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Widgets\Table;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Log;
use App\Admin\Actions\DeviceInfo\StopPlay;
use App\Admin\Actions\DeviceInfo\BatchStopPlay;
use App\Admin\Actions\DeviceInfo\RelayFirst;
use Encore\Admin\Facades\Admin;

class DeviceInfoController extends AdminController
{
    
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Giám sát thiết bị';
    public $path = '/devicedata';

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
        // $schedules = Schedule::all();
        // foreach($schedules as $schedule){
        //     if(!Program::find($schedule->program_id)){
        //         $schedule->delete();
        //     }
        // }
        $grid = new Grid(new DeviceInfo);
        
        $grid->disableCreateButton(); 
        $grid->disableActions();
        
        // lấy thông tin thiết bị
        // $deviceStatus = $this->getDeviceStatus();
        // Log::info("device list " . $deviceStatus);
        $grid->batchActions(function($action){
            $action->disableDelete();
            $action->add(new BatchStopPlay());
        });
        $grid->filter(function($filter){

            $filter->scope('auth',trans('admin.deviceManager'))->whereHas('device', function ($query) {

                $query->wherein('areaId',explode(',',Admin::user()->areaId));
            });

            $filter->expand();

            $filter->disableIdFilter();

            $filter->where(function ($query) {

                $query->whereHas('device', function ($query) {

                    $query->where('name', 'like', "%{$this->input}%");

                });

            },trans('Tên thiết bị'));

            $filter->like('deviceCode', trans('Mã thiết bị'));

            $filter->equal('status', trans('Trạng thái'))->select([
                1 => "Đang hoạt động",
                0 => "Không hoạt động",
            ]);
            if(Admin::user()->can('*'))
                $filter->equal('device.areaId', trans('Cụm loa'))->select((new Area())::selectOptions());
        });

        $grid->model()->orderBy('status', 'DESC')->orderBy('id', 'DESC');
        //$grid->column('id', __('Id'));

        $grid->column('device.name', trans('admin.deviceName'))->display(function () {
            $name = isset($this->device->name) ? $this->device->name : '';
            if($this->status){
                if ($this->is_playing == 0)
                    return '<span class="label label-success">' . $name . '</span><i style="float:right;color: cornflowerblue;animation: scale-1-3 0.5s ease infinite;" class="fas fa-volume-up hidden"></i>';
                else
                    return '<span class="label label-success">' . $name . '</span><i style="float:right;color: cornflowerblue;animation: scale-1-3 0.5s ease infinite;" class="fas fa-volume-up"></i>';
            }
            else
                return '<span class="label label-danger">' . $name . '</span><i style="float:right;color: cornflowerblue;animation: scale-1-3 0.5s ease infinite;" class="fas fa-volume-up hidden"></i>';
        })->style('font-size:16px;');

        $grid->column('deviceCode', trans('admin.deviceCode'))->display(function(){return 'Sao chép';})->copyable();

        $grid->column('Dừng phát')->action(StopPlay::class);

        $grid->column('relay1', 'Relay 1')->action(RelayFirst::class);

        $grid->column('volume', trans('admin.volume'))->editable('select', [1 => 'Mức 1', 2 => 'Mức 2', 3 => 'Mức 3', 4 => 'Mức 4', 5 => 'Mức 5', 6 => 'Mức 6', 7 => 'Mức 7', 8 => 'Mức 8', 9 => 'Mức 9', 10 => 'Mức 10', 11 => 'Mức 11', 12 => 'Mức 12', 13 => 'Mức 13', 14 => 'Mức 14', 15 => 'Mức 15']);
        
        //$grid->column('ip', __('IP'))->editable()->hide();

        $states = [
            'off' => ['value' => 0, 'text' => 'Không hoạt động', 'color' => 'danger'],
            'on' => ['value' => 1, 'text' => 'Hoạt động', 'color' => 'primary'],
        ];

        $grid->column('turn_off_time','Thời gian bật tắt')->display(function () {
            return "Nhấn để xem";
        })->expand(function ($model) {
            return new Table(
                ['Thời gian bật gần nhất','Thời gian tắt gần nhất'], 
                [[$model->turn_on_time, $model->turn_off_time]]
            );
        });
        
        // ->display(function($value){
        //     if($value !== NULL){
        //         $diff = Carbon::create($value)->diff(Carbon::now());
        //         $year = $diff->y == 0 ? '': $diff->y.' năm ';
        //         $month = $diff->m == 0 ? '' : $diff->m . ' tháng ';
        //         $day = $diff->d == 0 ? '' : $diff->d . ' ngày ';
        //         $hour = $diff->h == 0 ? '' : $diff->h . ' giờ ';
        //         $minute= $diff->i == 0 ? '' : $diff->i . ' phút ';
        //         $f_time = $year . $month . $day . $hour . $minute;
        //         $f_time = trim($f_time)==''? 'Vài giây trước': $f_time . ' trước';
        //         return $f_time;
        //     }    
        //     return '';   
        // });

        $grid->column('id', trans('Xem lịch phát'))->display(function () {
            return "Nhấn để xem";
        })->expand(function ($model) {

            $schedules = Schedule::select('program_id','type','fileVoice','time','startDate','endDate','endTime')->where('deviceCode', $model->deviceCode)
            ->orderby('startDate','DESC')
            ->orderby('time', 'DESC')->get();

            $programtype = [1 => 'Bản tin', 2 => 'Tiếp sóng', 3 => 'Thu phát FM', 4 => 'Bản tin văn bản', 5 => 'File ghi âm'];

                $schedules = $schedules->map(function($schedule) use ($programtype){
                    if($schedule->type == 2){
                        $scope = [
                            'https://streaming1.vov.vn:8443/audio/vovvn1_vov1.stream_aac/playlist.m3u8' => 'VOV 1',
                            'https://streaming1.vov.vn:8443/audio/vovvn1_vov2.stream_aac/playlist.m3u8' => 'VOV 2',
                            Admin::user()->stream_url => 'Phát trực tiếp',
                        ];
                        $fv = isset($scope[$schedule->fileVoice]) ? $scope[$schedule->fileVoice] : 'Phát trực tiếp';
                    }                  
                    else
                        $fv = '<audio controls=""><source src="' . $schedule->fileVoice . '" type="audio/wav"></audio>';
                    
                    $program = Program::find($schedule->program_id)->name;

                    $program = (new Carbon($schedule->endDate . ' ' . $schedule->endTime)) > Carbon::now() ? '<span title="Chương trình hoạt động" class="label label-warning fs-12">'.$program.'</span>' : '<span title="Chương trình hết hoạt động" class="label label-default fs-12">'.$program.'</span>';

                    return [
                        'program' => $program,
                        'type' => '<span class="label label-primary fs-12">'.$programtype[$schedule->type].'</span>',
                        'fileVoice' => $fv,
                        'time' => $schedule->time.' - '.$schedule->endTime,
                        'startDate' => (new Carbon($schedule->startDate))->format('d-m-Y')
                    ];
                });

            return new Table(['Chương trình','Loại phát sóng','Nội dung', 'Thời gian', 'Ngày'], $schedules->toArray());
        });
        $grid->column('created_at', __('Created at'))->hide();

        $grid->column('updated_at', __('Updated at'))->hide();

        $grid->disableExport();

        return $grid;
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
