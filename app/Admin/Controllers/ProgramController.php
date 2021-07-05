<?php
namespace App\Admin\Controllers;

use Request;
use Carbon\Carbon;

use App\Program;
use App\Device;
use App\DeviceInfo;
use App\Document;
use App\VoiceRecord;
use App\Api;

use App\Admin\Actions\Program\Delete;
use App\Admin\Actions\Program\BatchDelete;

use App\Http\Controllers\AudioController;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Layout\Content;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Widgets\Table;
use Illuminate\Support\Facades\Log;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\MessageBag;
use Encore\Admin\Actions\RowAction;

class ProgramController extends AdminController
{
    use Api;
    /**
     * Title for current resource.
     *
     * @var string
     */
    public $title = '';

    public $path = '/programs';

    public $volume_step = [
        0 => '0 dB',
        2 => '2 dB',
        4 => '4 dB',
        6 => '6 dB',
        8 => '8 dB',
        10 => '10 dB',
        12 => '12 dB',
        14 => '14 dB',
    ];
    public $programtype = [1 => 'Bản tin', 2 => 'Tiếp sóng', 3 => 'Thu phát FM', 4 => 'Bản tin văn bản', 5 => 'File ghi âm'];

    function __construct()
    {
        $this->title = trans('admin.program');
    }

    public function index(Content $content)
    {
        // ->body(view('admin.chartjs',[
        //         'programs' => Program::select(DB::raw('type, COUNT(type) as types'))->groupby('type')->get()
        //         ]))
        if (Admin::user()->can('*') || Request::get('_scope_') == 'auth')
        {

            return $content
                ->title($this->title())
                ->description($this->description['index']??trans('admin.list'))
                ->body($this->grid());

        }

        return redirect()->intended($this->path . '?_scope_=auth');

    }

    public function edit($id, Content $content)
    {
        $program = Program::where('id', $id)->first();

        if (Admin::user()
            ->can('*') || Request::get('_scope_') == 'auth' || !isset($program->creatorId) || $program->creatorId == Admin::user()->id || $program->approvedId == Admin::user()
            ->id) return $content->title($this->title())
            ->description($this->description['edit']??trans('admin.edit'))
            ->body($this->form()
            ->edit($id));

        return redirect()->intended($this->path);
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        $grid = new Grid((new Program));

        $grid->actions(function ($actions)
        {

            $actions->disableDelete();
            $actions->disableView();
            //$actions->disableEdit();
            if(Admin::user()->can('*'))
                $actions->add(new Delete);
        });

        $grid->batchActions(function ($batch)
        {

            $batch->disableDelete();
            if (Admin::user()->can('*'))
                $batch->add(new BatchDelete());
        });
        $grid->filter(function ($filter)
        {
            $filter->expand();
            $filter->disableIdFilter();

            $filter->scope('auth', trans('Chương trình'))
                ->where('creatorId', Admin::user()->id);

            $filter->equal('mode', 'Kiểu phát')
                ->select([1 => 'Trong ngày', 2 => 'Hàng ngày', 3 => 'Hàng tuần', 4 => 'Phát ngay']);

            $filter->equal('type', 'Loại phát sóng')
                ->select([1 => 'Bản tin', 2 => 'Tiếp sóng', 3 => 'Thu phát FM', 4 => 'Bản tin văn bản', 5 => 'File ghi âm']);

            $filter->like('name', 'Tên chương trình');

            $filter->between('endDate','Lịch phát')->date();

        });

        $grid->quickSearch(function ($model, $query)
        {
            $d = Device::select("deviceCode")->where('name', 'like', '%' . $query . '%')->get()->toArray();
            $d = array_map(function($value){
                return $value["deviceCode"];
            } ,$d);
            if(count($d) == 0)
                $model
                ->where('name', 'like', '%' . $query . '%')
                ->orwhere('devices', 'like', '%' . $query . '%')
                ->orwherein('devices', $d);
            else
                $model
                ->where('name', 'like', '%' . $query . '%')
                ->orwhere('devices', 'like', '%' . $query . '%')
                ->orwhere('devices', 'like', '%' . implode(',',$d) . '%')
                ->orwherein('devices', $d);
        })
            ->placeholder('Tên Chương trình / Thiết bị cần tìm');

        $grid->model()
            ->orderBy('id', 'DESC');

        //$grid->column('id', __('Id'))->sortable();
        $grid->column('name', __('Tên'))->display(function($name){

            return (new Carbon($this->endDate . ' 23:59:59')) > Carbon::now() ? '<span title="Chương trình hoạt động" class="label label-warning fs-12">'.$name.'</span>' : '<span title="Chương trình hết hoạt động" class="label label-default fs-12">'.$name.'</span>';

        })->style("min-width:100px;")->sortable();

        $states = [1 => false, 2 => true];

        $grid->column('status', __('Trạng thái'))->bool($states)->sortable();

        $grid->column('type', __('Loại phát sóng'))
            ->using($this->programtype)
            ->label(' label-primary')
            ->style('font-size:16px;')
            ->sortable();

        $grid->column('fileVoice', 'Nội dung/Kênh')->display(function ($fileVoice)
        {
            if ($this->type == 4 || $this->type == 1|| $this->type == 5){
                return "<audio controls><source src='" . config('filesystems.disks.upload.url') . $fileVoice . "' type='audio/wav'></audio>";
            }
            if ($this->type == 3){
                return '<a>'.$this->radioChannel.'</a>';
            }
            if($this->type == 2){
                $scope = [
                    'https://streaming1.vov.vn:8443/audio/vovvn1_vov1.stream_aac/playlist.m3u8' => 'VOV 1',
                    'https://streaming1.vov.vn:8443/audio/vovvn1_vov2.stream_aac/playlist.m3u8' => 'VOV 2',
                    Admin::user()->stream_url => 'Phát trực tiếp',
                ];
                
                $title = isset($scope[$this->digiChannel])? $scope[$this->digiChannel] : 'Phát trực tiếp';
                $d = '<a href="' . env('APP_URL') . '/admin/streams?url=' . $this->digiChannel . '">' . $title . '</a>';
                return $d;
            }
        });
        // $grid->column('volumeBooster', __('Volume'))->display(function ($value)
        // {
        //     return ((double)$value) . ' dB';
        // })->hide();
        $grid->column('mode', __('Kiểu phát'))
            ->using([1 => 'Trong ngày', 2 => 'Hàng ngày', 3 => 'Hàng tuần', 4 => 'Phát ngay'])
            ->label('default')
            ->style('font-size:16px;')
            ->sortable();

        $grid->column('startDate', __('Ngày bắt đầu'))
            ->sortable()
            ->hide();
        $grid->column('endDate', __('Ngày kết thúc'))
            ->hide();

        $grid->column('devices', __('Thiết bị phát'))->display(function ($a)
        {
            $html = '<div style="display:grid;">';
            foreach ($a as $b)
            {
                $deviceinfo = DeviceInfo::where('deviceCode', $b)->first();
                if (isset($deviceinfo->device)) $html .= '<span class="label label-success" style="margin: 1px;">' . $deviceinfo
                    ->device->name . '</span>';
            }
            return $html . '</div>';
        });
        $grid->column('id', __('Xem thêm'))->display(function(){
            return '';
        })->expand(function ($model){
            return new Table(['Người tạo', 'Người duyệt', 'Phát liên tiếp', 'Khung giờ phát', 'Ngày bắt đầu', 'Ngày kết thúc', 'Ngày tạo', 'Ngày cập nhật'], [
                [
                    isset($model->creator) ? '<b>'.$model->creator->name.'</b>' : "", 
                    isset($model->approver) ? '<b>'.$model->approver->name.'</b>' : "", 
                    $model->replay, 
                    $model->time, 
                    $model->startDate, 
                    $model->endDate, 
                    $model->created_at->format('H:i:s -- d-m-Y'),
                    $model->updated_at->format('H:i:s -- d-m-Y'),
                ]]);
        });
        $grid->column('time', __('Khung giờ phát'))
            ->hide();

        $grid->column('creator.name', __('Người tạo'))
            ->hide();
        $grid->column('approver.name', __('Người duyệt'))
            ->hide();

        $grid->column('created_at', __('Ngày tạo'))
            ->hide();
        $grid->column('updated_at', __('Ngày cập nhật'))
            ->hide();

        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Program);

        $form->text('name', __('Tên chương trình'))
            ->rules('required', ['required' => "Cần nhập giá trị"]);

        $form->radio('type', trans('Loại phát sóng'))
            ->options($this->programtype)->when(1, function (Form $form){
                $form->radio('file_mode', 'Chọn nguồn file')
                    ->options([
                // 1 => 'Chọn file có sẵn',
                2 => 'Tải lên file mới'])->when(2, function (Form $form)
                {

                    $form->file('fileVoice', 'Chọn file')->name(function ($file) {
                        return md5($file->getClientOriginalName()) . $file->guessExtension();
                    });

                    $form->select('volumeBooster', 'Tăng Volume (dB)')
                        ->options($this->volume_step)
                        ->rules('required', ['required' => "Cần nhập giá trị"])
                        ->default(0);

                })
                    ->rules('required', ['required' => "Cần nhập giá trị"])
                    ->default(2);
            })->when(2, function (Form $form) {

                if(Admin::user()->stream_key == '')
                    $kenh = [
                        'https://streaming1.vov.vn:8443/audio/vovvn1_vov1.stream_aac/playlist.m3u8' => 'VOV 1',
                        'https://streaming1.vov.vn:8443/audio/vovvn1_vov2.stream_aac/playlist.m3u8' => 'VOV 2',
                        'http://stream2.mobiradio.vn/vovradio/vovgthcm.stream/playlist.m3u8'=>'VOV GT Sài Gòn',
                    ];
                else
                    $kenh = [
                        'https://streaming1.vov.vn:8443/audio/vovvn1_vov1.stream_aac/playlist.m3u8' => 'VOV 1',
                        'https://streaming1.vov.vn:8443/audio/vovvn1_vov2.stream_aac/playlist.m3u8' => 'VOV 2',
                        'http://stream2.mobiradio.vn/vovradio/vovgthcm.stream/playlist.m3u8' => 'VOV GT Sài Gòn',
                        Admin::user()->stream_url => 'Phát trực tiếp (' . Admin::user()->stream_key . ')',
                    ];

                $form->select('digiChannel', trans('Chọn kênh tiếp sóng'))->options($kenh)->rules('required', ['required' => "Cần nhập giá trị"]);

                $form->number('duration','Thời lượng (phút)')->max(1440);
                
            })->when(3, function (Form $form){

                $form->text('radioChannel', 'Kênh')->rules(
                    'required|numeric', ['required' => "Cần nhập giá trị","numeric"=>"Cần nhập dạng số"]);

            })->when(4, function (Form $form){
                if(Admin::user()->can('*'))
                    $docs = Document::all()->pluck('name', 'id');
                else 
                    $docs = Document::where('creatorId', Admin::user()->id)->pluck('name', 'id');
            
                $form->select('document_Id', trans('Chọn file văn bản'))->options($docs);
                    
            })->when(5, function (Form $form) {
                if (Admin::user()->can('*'))
                    $records = VoiceRecord::all()->pluck('name', 'id');
                else
                    $records = VoiceRecord::where('creatorId', Admin::user()->id)->pluck('name', 'id');

                $form->select('record_Id', trans('Chọn file ghi âm'))->options($records);

            })->rules('required', ['required' => "Cần nhập giá trị"]);

        $form->divider(trans('Thời gian'));

        $form->radio('mode', trans('Kiểu phát'))
            ->options([

                '1' => 'Trong ngày', 
                '2' => 'Hàng ngày',
                // '3' => 'Hàng tuần',
                '4' => 'Phát ngay'

            ])->when(1, function (Form $form){

                $form->date('startDate', __('Ngày phát'))->rules('required',['required'=>"Cần nhập giá trị"]);

                $form->time('time', __('khung giờ phát'))->format('HH:mm:ss')->rules('required',['required'=>"Cần nhập giá trị"]);

                $form->divider(trans('Phát liên tiếp'));

                $form->number('replay', 'Số lần phát liên tiếp')
                    ->max(10)
                    ->min(1)
                    ->default(1)
                    ->rules('required', ['required' => "Cần nhập giá trị"]);
                $form->number('interval', 'Thời gian mỗi lần phát liên tiếp (giây)')
                    ->max(7200)
                    ->min(1)
                    ->default(30)
                    ->rules('required', ['required' => "Cần nhập giá trị"]);


            })->when(2, function (Form $form){

                $form->dateRange('startDate', 'endDate', __('Thời gian phát'))->rules('required',['required'=>"Cần nhập giá trị"]);

                $form->time('time', __('khung giờ phát'))
                    ->format('HH:mm:ss')->rules('required',['required'=>"Cần nhập giá trị"]);

                $form->divider(trans('Phát liên tiếp'));

                $form->number('replay', 'Số lần phát liên tiếp')
                    ->max(10)
                    ->min(1)
                    ->default(1)
                    ->rules('required',['required'=>"Cần nhập giá trị"]);
                $form->number('interval', 'Thời gian mỗi lần phát liên tiếp (giây)')
                ->max(7200)
                ->min(1)
                ->default(30)
                ->rules('required', ['required' => "Cần nhập giá trị"]);

                    // })->when(3, function (Form $form) {
                    //     $form->dateRange('startDate', 'endDate',__('Thời gian phát'));
                    //     // $form->time('time', __('khung giờ phát'))->format('HH:mm:ss')->rules('required');;
                    //     $form->checkbox('days', 'Chọn ngày')->options(['2' => 'Thứ 2', '3' => ' Thứ 3', '4' => 'Thứ 4', '5' => 'Thứ 5', '6' => 'Thứ 6', '7' => 'Thứ 7', '8' => 'Chủ nhật'])->canCheckAll();
                    //     $form->time('time', __('khung giờ phát'))->format('HH:mm:ss');
                    
            })->rules('required', ['required' => "Cần nhập giá trị"]);


        $form->divider(trans('Chọn loa phát'));

        if (Admin::user()->can('*')) 
            $device_auth = Device::join('device_infos', 'device_infos.deviceCode', '=', 'devices.deviceCode')
            ->WHERE("device_infos.status", 1)
            ->PLUCK('devices.name', 'devices.deviceCode');

        else 
            $device_auth = Device::join('device_infos', 'device_infos.deviceCode', '=', 'devices.deviceCode')
            ->WHERE("device_infos.status", 1)
            ->WHEREIN('devices.areaId', explode(',', Admin::user()->areaId))
            ->PLUCK('devices.name', 'devices.deviceCode');

        $form->listbox('devices', trans('Danh sách loa'))
            ->options($device_auth);
            //->rules('required',['required'=>"Cần nhập giá trị"]);

        $states = ['off' => ['value' => 1, 'text' => 'Chưa duyệt', 'color' => 'danger'], 'on' => ['value' => 2, 'text' => 'Đã duyệt', 'color' => 'success'], ];

        $form->switch('status', 'Phê duyệt')->states($states)->default(2);

        Log::info('User ID name ' . Admin::user()
            ->id);

        $form->model()->creatorId = Admin::user()->id;

        $form->submitted(function (Form $form) {
        });
        $form->saving(function ($form)
        {
        
            if (($form->status == "on" || $form->status == 1) && $form->mode != 4) {

                $songPath = $form->fileVoice ? $form->fileVoice->getPathName() : config('filesystems.disks.upload.path') . $form->model()->fileVoice;
                $devices = is_array($form->devices) ? implode(',', $form->devices) : ($form->devices ? $form->devices : implode(',',$form->model()->devices)); 

                $checkSchedule = $this->checkPlaySchedule(
                    $form->id ? $form->id : $form->model()->id, 
                    $form->type ? $form->type : $form->model()->type, 
                    $devices, 
                    $form->startDate ? $form->startDate : $form->model()->startDate, 
                    $form->endDate ? $form->endDate : $form->model()->endDate, 
                    $form->time ? $form->time : $form->model()->time, 
                    $songPath, 
                    $form->replay ? $form->replay : $form->model()->replay, 
                    $form->interval ? $form->interval : $form->model()->interval,
                    $form->duration ? $form->duration : $form->model()->duration
                );
                if (isset($checkSchedule['program'])) {
                    $error = new MessageBag([
                        'title'   => 'Xung đột chương trình',
                        'message' => sprintf(
                            'Bị trùng thời gian phát trên chương trình: <b>%s</b><br>- Tên thiết bị: <b>%s</b><br>- Lúc: <b>%s</b> ngày <b>%s</b> đến <b>%s</b><br><button class="btn btn-warning">Ghi đè chương trình</button>',
                            $checkSchedule['program']->program->name,
                            $checkSchedule['program']->device->name,
                            $checkSchedule['program']->time,
                            $checkSchedule['program']->startDate,
                            $checkSchedule['program']->endDate
                        )
                    ]);
                    //admin_toastr('Message...', 'success', ['timeOut' => 5000]);
                    return back()->with(compact('error'));
                }
            }

            if (($form->_method == "PUT" && $form->type == 1 && $form->model()->fileVoice == null) || ($form->_method != "PUT" && $form->type == 1 && $form->fileVoice == null)){
                $error = new MessageBag([
                    'title'   => 'Lỗi nhập liệu',
                    'message' => 'Cần chọn file phương tiện',
                ]);
                return back()->with(compact('error'));
            }

            $form->model()->radioChannel = $form->radioChannel ? (float) $form->radioChannel : $form->model()->radioChannel;

            $form->model()->creatorId = $form->model()->creatorId ? $form->model()->creatorId : Admin::user()->id;

            $form->model()->approvedId = $form->model()->approvedId ? $form->model()->approvedId : Admin::user()->id;
        });

        $form->saved(function ($form)
        {
            // dd($form->model()->fileVoice);
            // đoạn code xử lý file
            if ($form->model()->type == 1)
            {
                //convert to mp3
                if($form->_method != "PUT"){

                    $booster = (float)$form->model()->volumeBooster;

                    $inputFile = $form->model()->fileVoice;

                    $inputPath = config('filesystems.disks.upload.path') . $form->model()->fileVoice;

                    $outputFile = 'files/' . md5($form->model()->fileVoice . $booster) . '.mp3';

                    $outputPath = config('filesystems.disks.upload.path') . 'files/' . md5($form->model()->fileVoice . $booster) . '.mp3';

                    if ($inputFile != $outputFile) {

                        if (!file_exists($outputPath) && file_exists($inputPath)) {

                            $audioController = new AudioController();

                            $audioController->IncreaseVolume($inputPath, $booster, $outputPath);

                        } else {
                            if (file_exists($inputPath))
                                unlink($inputPath);
                        }
                    }

                    $form->model()->fileVoice = $outputFile;
                    $form->model()->volumeBooster = 0;
                    $form->model()->save();
                } 
                else if ($form->model()->volumeBooster != 0) {

                        $booster = (float)$form->model()->volumeBooster . 'dB';

                        $outputFile = 'files/' . md5($form->model()->fileVoice . $booster) . '.mp3';

                        if ($form->model()->fileVoice != $outputFile) {

                            if (!file_exists(config('filesystems.disks.upload.path') . $outputFile) && file_exists(config('filesystems.disks.upload.path') . $form->model()->fileVoice)) {

                                $exec_to_convert_to_mp3 = 'ffmpeg -y -i ' . config('filesystems.disks.upload.path') . $form->model()->fileVoice . ' -filter:a "volume=' . $booster . '" ' . config('filesystems.disks.upload.path') . $outputFile;

                                exec($exec_to_convert_to_mp3);
                            }
                        }
                        $form->model()->fileVoice = $outputFile;
                        $form->model()->volumeBooster = 0;
                        $form->model()->save();
                }    
            }
            if ($form->model()->type == 4) {
                $d = Document::where('id', $form->model()
                    ->document_Id)
                    ->first();

                if ($d !== NULL)
                {

                    $form->model()->fileVoice = $d->fileVoice;

                    $form->model()
                        ->save();
                }
            }
            if ($form->model()->type == 5) {
                $d =VoiceRecord::where('id', $form->model()
                    ->record_Id)
                    ->first();

                if ($d !== NULL) {

                    $form->model()->fileVoice = $d->fileVoice;

                    $form->model()
                        ->save();
                }
            }
            //kết thúc đoạn code xử lý file
            
            if($form->model()->mode == 1){
                $form->model()->endDate = $form->model()->startDate;
                $form->model()->save();
            }
            // nếu phát file phương tiện
            if ($form->model()->type == 1)
            {
                $songPath = $form->model()->fileVoice;
                $this->deleteSchedule($form->model());

                if ($form->model()->mode == 4){ // nếu phát ngay

                    if ($form->model()->status == 2)
                        $this->playOnline($form->model()->type, implode(',', $form->model()
                        ->devices) , $songPath);
                }
                else{ // nếu phát theo lịch
                    $devices = implode(',', $form->model()->devices);
                    if ($form->model()->status == 2){

                        $this->setPlaySchedule(
                            $form->model()->id, 
                            $form->model()->type, 
                            $devices, 
                            $form->model()->startDate, 
                            $form->model()->endDate, 
                            $form->model()->time, 
                            $songPath, 
                            $form->model()->replay, 
                            $form->model()->interval
                        );
                    }
                    else 
                        $this->resetSchedule($devices,$form->model()->type);
                    
                }
            }
            // nếu phát tiếp sóng
            if ($form->model()->type == 2) {

                $songPath = $form->model()->digiChannel;
                $this->deleteSchedule($form->model());

                if ($form->model()->mode == 4) { // nếu phát ngay
                    if ($form->model()->status == 2)
                        $this->playOnline($form->model()->type, implode(',', $form->model()
                            ->devices), $songPath);
                } else { // nếu phát theo lịch
                    $devices = implode(',', $form->model()->devices);
                    if ($form->model()->status == 2)
                        $this->setPlaySchedule(
                            $form->model()->id, 
                            $form->model()->type,
                            $devices, 
                            $form->model()->startDate, 
                            $form->model()->endDate, 
                            $form->model()->time, 
                            $songPath, 
                            $form->model()->replay, 
                            $form->model()->interval,
                            $form->model()->duration,
                        );
                    else
                        $this->resetSchedule($devices, $form->model()->type);
                }
            }  
            // nếu phát đài FM
            if ($form->model()->type == 3)
            {
                if ($form->model()->status == 2){ // nếu duyệt
                    $songPath = $form->model()->radioChannel;
                    $this->setPlayFM($form->model()->type, implode(',', $form->model()->devices), $songPath);
                }
            }
            // nếu phát file văn bản
            if ($form->model()->type == 4)
            {
                $docModel = Document::findOrFail($form->model()->document_Id);
                $songPath = $docModel->fileVoice;
                $this->deleteSchedule($form->model());

                if ($form->model()->mode == 4)
                {
                    if ($form->model()->status == 2)
                        $this->playOnline($form->model()->type, implode(',', $form->model()
                        ->devices) , $songPath);
                }
                else
                {
                    if ($form->model()->status == 2)
                        $this->setPlaySchedule($form->model()->id, $form->model()->type, implode(',', $form->model()
                        ->devices) , $form->model()->startDate, $form->model()->endDate, $form->model()->time, $songPath, $form->model()->replay, $form->model()->interval);
                    else
                        $this->resetSchedule($devices, $form->model()->type);
                }
            }
            // nếu phát ghi âm
            if ($form->model()->type == 5) {

                $voiceModel = VoiceRecord::findOrFail($form->model()->record_Id);
                $songPath = $voiceModel->fileVoice;
                $this->deleteSchedule($form->model());

                if ($form->model()->mode == 4) {
                    if ($form->model()->status == 2)
                        $this->playOnline($form->model()->type, implode(',', $form->model()
                            ->devices), $songPath);
                } else {
                    if ($form->model()->status == 2)
                        $this->setPlaySchedule($form->model()->id, $form->model()->type, implode(',', $form->model()
                        ->devices), $form->model()->startDate, $form->model()->endDate, $form->model()->time, $songPath, $form->model()->replay, $form->model()->interval);
                    else
                        $this->resetSchedule($devices, $form->model()->type);
                }
            }

            Log::info('Song name ' . $songPath);
            
        });

        $form->disableReset();
        $form->disableViewCheck();
        $form->disableEditingCheck();
        $form->disableCreatingCheck();

        return $form;
    }
}