<?php
namespace App\Admin\Controllers;

use Request;

use App\Program;
use App\Device;
use App\DeviceInfo;
use App\Document;
use App\VoiceRecord;
use App\Schedule;

use App\Api;

use App\Admin\Actions\Program\Delete;
use App\Admin\Actions\Program\BatchDelete;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Layout\Content;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;
use Illuminate\Support\Facades\Log;
use Encore\Admin\Facades\Admin;

class ProgramController extends AdminController
{
    use Api;
    /**
     * Title for current resource.
     *
     * @var string
     */
    public $title = '';

    public $path = '/admin/programs';

    public $volume_step = [
        5 => '0.5 lần (Giảm volume)',
        10 => '1 lần',
        20 => '2 lần',
        30 => '3 lần',
        40 => '4 lần (Vỡ âm)',
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

    // public function show($id, Content $content)
    // {
    //     $program = Program::where('id', $id)->first();

    //     if (Admin::user()
    //         ->can('*') || Request::get('_scope_') == 'auth' || !isset($program->creatorId) || $program->creatorId == Admin::user()->id || $program->approvedId == Admin::user()
    //         ->id) return $content->title($this->title())
    //         ->description($this->description['show']??trans('admin.show'))
    //         ->body($this->detail($id));

    //     return redirect()->intended($this->path);
    // }

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
            //$filter->expand();
            $filter->disableIdFilter();

            $filter->scope('auth', trans('Chương trình'))
                ->where('creatorId', Admin::user()->id);

            $filter->equal('mode', 'Kiểu phát')
                ->select([1 => 'Trong ngày', 2 => 'Hàng ngày', 3 => 'Hàng tuần', 4 => 'Phát ngay']);

            $filter->equal('type', 'Loại phát sóng')
                ->select([1 => 'Bản tin', 2 => 'Tiếp sóng', 3 => 'Thu phát FM', 4 => 'Bản tin văn bản', 5 => 'File ghi âm']);

            $filter->like('name', 'Tên chương trình');

        });

        $grid->quickSearch(function ($model, $query)
        {
            $d = Device::select("deviceCode")->where('name', 'like', '%' . $query . '%')->get();
            $model->where('name', 'like', '%' . $query . '%')->orwhere('devices', 'like', '%' . $query . '%')->orwherein('devices', $d->toArray());

        })
            ->placeholder('Tên Chương trình / Thiết bị cần tìm');

        $grid->model()
            ->orderBy('id', 'DESC');

        //$grid->column('id', __('Id'))->sortable();
        $grid->column('name', __('Tên'))
            ->style("min-width:100px;")->expand(function ($model)
        {
            return new Table(['Người tạo', 'Người duyệt', 'Khung giờ phát', 'Ngày bắt đầu', 'Ngày kết thúc', 'Ngày tạo', 'Ngày cập nhật'], [[isset($model->creator) ? $model
                ->creator->name : "", isset($model->approver) ? $model
                ->approver->name : "", $model->time, $model->startDate, $model->endDate, $model
                ->created_at
                ->format('H:i:s -- d-m-Y') , $model
                ->updated_at
                ->format('H:i:s -- d-m-Y') , ]]);
        })
            ->sortable();

        $states = ['off' => ['value' => 1, 'text' => 'Chưa duyệt', 'color' => 'danger'], 'on' => ['value' => 2, 'text' => 'Đã duyệt', 'color' => 'success'], ];
        $grid->column('status', __('Trạng thái'))
            ->switch($states)->sortable();

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
        $grid->column('volumeBooster', __('Volume'))->display(function ($value)
        {
            return ((double)$value / 10) . ' lần';
        })->hide();
        $grid->column('replay', 'Phát liên tiếp')->display(function($value){
            return $value . ' lần';
        });
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

    // /**
    //  * Make a show builder.
    //  *
    //  * @param mixed $id
    //  * @return Show
    //  */
    // protected function detail($id)
    // {
    //     $model = Program::findOrFail($id);
    //     $show = new Show($model);

    //     //$show->field('id', __('Id'));
    //     $show->field('name', __('Tên chương trình'));
    //     $show->field('type', __('Loại phát sóng'))
    //         ->using(['1' => 'Bản tin', '2' => 'Tiếp sóng', '3' => 'Thu phát FM', '4' => 'Bản tin văn bản']);
    //     // $show->fileVoice()->as(function ($fileVoice) {
    //     //     if ($fileVoice != "")
    //     //     return "<{$fileVoice}>";
    //     // })->link();
    //     // $show->field('fileVoice', __('FileVoice'))->as(function($fileVoices){
    //     //     $html = '';
    //     //     foreach($fileVoices as $file){
    //     //         $html .= '<audio controls=""><source src="'.config('filesystems.disks.upload.url').trim($file,'"').'"></audio>';
    //     //     }
    //     //     return $html;
    //     // })->badge();
    //     $show->field('fileVoice', __('FileVoice'))->as(function () use ($model)
    //     {
    //         if ($model->type == 4) return $model
    //             ->document->fileVoice;
    //         if ($model->type == 1) return $model->fileVoice;
    //     })
    //         ->audio();

    //     // $show->field('priority', __('Priority'));
    //     $show->field('mode', __('Chế độ phát'))
    //         ->using(['1' => 'Trong ngày', '2' => 'Hàng ngày', '3' => 'Hàng tuần', '4' => 'Phát ngay']);

    //     $show->field('startDate', __('Ngày bắt đầu'));

    //     $show->field('endDate', __('Ngày kết thúc'));

    //     $show->field('time', __('Khung giờ phát'));

    //     $show->field('replay', 'Số lần lặp');

    //     $show->devices('Danh sách thiết bị phát')->as(function ($devices)
    //     {
    //         $html = '';
    //         foreach ($devices as $b)
    //         {
    //             $deviceinfo = DeviceInfo::where('deviceCode', $b)->first();
    //             $html .= isset($deviceinfo->device) ? "<pre style=\"margin:10px;\">{$deviceinfo
    //                 ->device->name}</pre>" : "NULL";
    //         }
    //         return $html;
    //     })->badge(' w-100 p-0 d-initial')
    //         ->style('font-size:16px;');

    //     // $show->field('days', __('Ngày phát'))->using(['2' => 'Thứ 2', '3' => ' Thứ 3', '4' => 'Thứ 4', '5' => 'Thứ 5', '6' => 'Thứ 6', '7' => 'Thứ 7', '8' => 'Chủ nhật']);
    //     // $show->field('devices', __('Danh sách loa'));
    //     $show->field('creatorId', __('Người tạo'))->as(function ($creator_id) use ($id)
    //     {
    //         $n = Program::find($id)
    //             ->creator->name ? Program::find($id)
    //             ->creator->name : "";
    //         return $n;
    //     });
    //     $show->field('approvedId', __('Người phê duyệt'))->as(function ($approver_id) use ($id)
    //     {
    //         $n = Program::find($id)
    //             ->approver->name ? Program::find($id)
    //             ->approver->name : "";
    //         return $n;
    //     });
    //     $show->field('created_at', __('Ngày tạo'));
    //     $show->field('updated_at', __('Ngày cập nhật'));

    //     return $show;
    // }

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
            // $form->file('fileVoice', 'Chọn file')->options([
            // 'previewFileType'=>'audio',
            // 'initialPreviewFileType'=>'audio',
            // ])->uniqueName();
            //$form->media('fileVoice', 'Chọn file')->path('files');
                $form->radio('file_mode', 'Chọn nguồn file')
                    ->options([
                // 1 => 'Chọn file có sẵn',
                2 => 'Tải lên file mới'])->when(2, function (Form $form)
                {

                    $form->file('fileVoice', 'Chọn file')->name(function ($file) {
                        return md5($file->getClientOriginalName()) . $file->guessExtension();
                    });

                    $form->select('volumeBooster', 'Tăng giảm Volume')
                        ->options($this->volume_step)
                        ->rules('required', ['required' => "Cần nhập giá trị"])
                        ->default(10);

                })
                    ->rules('required', ['required' => "Cần nhập giá trị"])
                    ->default(2);

        
                //$form->number('replay', 'Số lần lặp')->max(20)->min(1)->default(1);
                //$form->multipleFile('fileVoice', 'Chọn file');
                //$form->file('fileVoice', 'Chọn file')->uniqueName();
                //$form->multipleFile('fileVoice', 'Chọn file')->removable();
                
            })->when(2, function (Form $form) {

                if(Admin::user()->stream_key == '')
                    $kenh = [
                        'https://streaming1.vov.vn:8443/audio/vovvn1_vov1.stream_aac/playlist.m3u8' => 'VOV 1',
                        'https://streaming1.vov.vn:8443/audio/vovvn1_vov2.stream_aac/playlist.m3u8' => 'VOV 2',
                    ];
                else
                    $kenh = [
                        'https://streaming1.vov.vn:8443/audio/vovvn1_vov1.stream_aac/playlist.m3u8' => 'VOV 1',
                        'https://streaming1.vov.vn:8443/audio/vovvn1_vov2.stream_aac/playlist.m3u8' => 'VOV 2',
                        Admin::user()->stream_url => 'Phát trực tiếp (' . Admin::user()->stream_key . ')',
                    ];

                $form->radio('digiChannel', trans('Chọn kênh tiếp sóng'))->options($kenh)->rules('required', ['required' => "Cần nhập giá trị"]);
                
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
                // $form->number('interval', 'Thời gian mỗi lần phát liên tiếp (giây)')
                //     ->max(7200)
                //     ->min(1)
                //     ->default(30)
                //     ->rules('required', ['required' => "Cần nhập giá trị"]);


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
                // $form->number('interval', 'Thời gian mỗi lần phát liên tiếp (giây)')
                // ->max(7200)
                // ->min(1)
                // ->default(30)
                // ->rules('required', ['required' => "Cần nhập giá trị"]);

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

        $form->saving(function ($form)
        {
            $form->radioChannel = (double) $form->radioChannel;

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
                $booster = (float)$form->model()->volumeBooster / 10;
                
                $outputFile = 'files/' . md5($form->model()->fileVoice.$booster) . '.mp3';

                if($form->model()->fileVoice != $outputFile){

                    if (!file_exists(config('filesystems.disks.upload.path') . $outputFile) && file_exists(config('filesystems.disks.upload.path') . $form->model()->fileVoice)) {

                        $exec_to_convert_to_mp3 = 'ffmpeg -y -i ' . config('filesystems.disks.upload.path') . $form->model()->fileVoice . ' -filter:a "volume=' . $booster . '" ' . config('filesystems.disks.upload.path') . $outputFile;

                        exec($exec_to_convert_to_mp3);

                        if (file_exists(config('filesystems.disks.upload.path') . $outputFile)) {

                            if (file_exists(config('filesystems.disks.upload.path') . $form->model()
                                ->fileVoice))
                                unlink(config('filesystems.disks.upload.path') . $form->model()
                                    ->fileVoice);

                            $form->model()->fileVoice = $outputFile;

                            $form->model()->save();
                        }
                    } else {
                        if (file_exists(config('filesystems.disks.upload.path') . $form->model()
                            ->fileVoice))
                            unlink(config('filesystems.disks.upload.path') . $form->model()->fileVoice);

                        $form->model()->fileVoice = $outputFile;

                        $form->model()->save();
                    }   
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
                    if ($form->model()->status == 2)
                        $this->setPlaySchedule($form->model()->id, $form->model()->type, $devices , $form->model()->startDate, $form->model()->endDate, $form->model()->time, $songPath, $form->model()->replay, 30);
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

                    if ($form->model()->status == 2)
                        $this->setPlaySchedule($form->model()->id, $form->model()->type, implode(',', $form->model()
                        ->devices), $form->model()->startDate, $form->model()->endDate, $form->model()->time, $songPath, $form->model()->replay, 30);
                    else
                        $this->resetSchedule($devices, $form->model()->type);
                }
            }  
            // nếu phát đài FM
            if ($form->model()->type == 3)
            {

                if ($form->model()->status == 1) // nếu không duyệt
                $songPath = "";
                if ($form->model()->status == 2) // nếu duyệt
                $songPath = $form->model()->radioChannel;
                
                $this->setPlayFM($form->model()->type, implode(',', $form->model()->devices), $songPath);
                // if ($form->model()->mode == 4)
                // {

                //     (new Api())
                //         ->setPlayFM($form->model()->type, implode(',', $form->model()
                //         ->devices) , $songPath);
                // }
                // else
                // {
                //     (new Api())->setPlaySchedule($form->model()->type, implode(',', $form->model()
                //         ->devices) , $form->model()->startDate, $form->model()->endDate, $form->model()->time, $songPath, $form->model()->replay, 30);
                // }
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
                        ->devices) , $form->model()->startDate, $form->model()->endDate, $form->model()->time, $songPath, $form->model()->replay, 30);
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
                        ->devices), $form->model()->startDate, $form->model()->endDate, $form->model()->time, $songPath, $form->model()->replay, 30);
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
    public function setFileVoiceAttribute($fileVoice)
    {
        if (is_array($fileVoice))
        {
            $this->attributes['fileVoice'] = json_encode($fileVoice);
        }
    }
    public function getFileVoiceAttribute($fileVoice)
    {
        return json_decode($fileVoice, true);
    }
}