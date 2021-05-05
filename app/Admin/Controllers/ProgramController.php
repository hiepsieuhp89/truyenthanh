<?php

namespace App\Admin\Controllers;
use DB;
use App\Program;
use App\Area;
use App\Device;
use App\DeviceInfo;
use App\Document;
use App\Admin\Actions\Post\BatchPlayAll;    
use Helper;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Layout\Content;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
Use Encore\Admin\Widgets\Table;

use Illuminate\Support\Facades\Log;
use Encore\Admin\Facades\Admin;

class ProgramController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    public $title = '';
    function __construct(){
      $this->title = trans('admin.program');
    }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    public function index(Content $content)
    {
        // ->body(view('admin.chartjs',[
        //         'programs' => Program::select(DB::raw('type, COUNT(type) as types'))->groupby('type')->get()
        //         ]))
        return $content
            ->title($this->title())
            ->description($this->description['index'] ?? trans('admin.list'))
            ->body($this->grid());
    }
    protected function grid()
    {

        $grid = new Grid(new Program);   
        $grid->batchActions(function ($batch) {
            $batch->add(new BatchPlayAll());
        });      
        $grid->filter(function($filter){
            //$filter->expand();
            //$filter->disableIdFilter();
            $filter->like('name', 'Tên chương trình');
        });

        $grid->quickSearch(function ($model, $query) {
            $d = Device::select("deviceCode")->where('name','like','%'.$query.'%')->get();
            $model->where('name','like','%'.$query.'%')->orwhere('devices','like','%'.$query.'%')->orwherein('devices',$d->toArray());

        })->placeholder('Tên Chương trình / Thiết bị cần tìm');

        $grid->model()->orderBy('id', 'DESC');
        $grid->column('id', __('Id'))->sortable();;
        $grid->column('name', __('Tên'))->style("min-width:100px;")->expand(function ($model) {
            return new Table(
                ['Người tạo', 'Người duyệt','Khung giờ phát','Ngày bắt đầu','Ngày kết thúc','Ngày tạo','Ngày cập nhật'],
                [   
                    [
                        isset($model->creator) ? $model->creator->name : "", 
                        isset($model->approver) ? $model->approver->name : "",
                        $model->time,
                        $model->startDate,
                        $model->endDate,
                        $model->created_at->format('H:i:s -- d-m-Y'),
                        $model->updated_at->format('H:i:s -- d-m-Y'),
                    ] 
                ]
            );
        });

        $states = [
            'off' => ['value' => 1, 'text' => 'Chưa duyệt', 'color' => 'danger'],
            'on' => ['value' => 2, 'text' => 'Đã duyệt', 'color' => 'success'],
        ];
        $grid->column('status', __('Trạng thái'))->switch($states);

        $grid->column('type', __('Loại phát sóng'))->using(['1' => 'Bản tin',
                                                            '2' => 'Tiếp sóng', 
                                                            '3' => 'Thu phát FM',
                                                            '4' => 'Bản tin văn bản'
                                                        ])->label(' label-primary')->style('font-size:16px;')->sortable();
                                                        
        $grid->column('document.fileVoice', 'File')->display(function ($fileVoiceDocs) {
            if ($this->type == 4) { // type voice
                return "<audio controls><source src='".env("APP_URL").$fileVoiceDocs."' type='audio/wav'></audio>";
            } 
            if ($this->type == 1) { // type media mp3
                return "<audio controls><source src='".config('filesystems.disks.upload.url').$this->fileVoice."' type='audio/wav'></audio>";
            } 
            if ($this->type == 2) {
                if ($this->digiChannel == '91') {
                    return 'VOV Giao thông HN';
                } else if ($this->digiChannel == '102.7') {
                    return 'VOV 2';
                } 
            } 
            if ($this->type == 3) {
                if ($this->digiChannel == '91') {
                    return 'VOV Giao thông HN';
                } else if ($this->digiChannel == '102.7') {
                    return 'VOV 2';
                } 
            } 
        });

        $grid->column('mode', __('Kiểu phát'))->using(['1' => 'Trong ngày',
                                                            '2' => 'Hàng ngày', 
                                                            '3' => 'Hàng tuần',
                                                            '4' => 'Phát ngay']);
        $grid->column('startDate', __('Ngày bắt đầu'))->sortable()->hide();
        $grid->column('endDate', __('Ngày kết thúc'))->hide();

        $grid->column('devices', __('Thiết bị phát'))->display(function($a){
            $html = '<div style="display:grid;">';
            foreach($a as $b){
                $deviceinfo = DeviceInfo::where('deviceCode',$b)->first();
                if(isset($deviceinfo->device))
                    $html .= '<span class="label label-success" style="margin: 1px;">'. $deviceinfo->device->name .'</span>';
            }
            return $html.'</div>';
        });

        $grid->column('time', __('Khung giờ phát'))->hide();

        $grid->column('creator.name', __('Người tạo'))->hide();
        $grid->column('approver.name', __('Người duyệt'))->hide();

        $grid->column('created_at', __('Ngày tạo'))->hide();
        $grid->column('updated_at', __('Ngày cập nhật'))->hide();

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            // $actions->disableEdit();
            // $actions->disableDelete();
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
        $model = Program::findOrFail($id);
        $show = new Show($model);

        $show->field('id', __('Id'));
        $show->field('name', __('Tên chương trình'));
        $show->field('type', __('Loại phát sóng'))->using(['1' => 'Bản tin',
                                                            '2' => 'Tiếp sóng', 
                                                            '3' => 'Thu phát FM',
                                                           '4' => 'Bản tin văn bản'
                                                         ]);
        // $show->fileVoice()->as(function ($fileVoice) {
        //     if ($fileVoice != "") 
        //     return "<{$fileVoice}>";
        // })->link();
        $show->field('fileVoice', __('FileVoice'))->link(config('filesystems.disks.upload.url').$model->fileVoice);

        // $show->field('fileVoice', __('FileVoice'))->link();
        // $show->field('fileVoice', __('FileVoice'))->link();

        $show->field('priority', __('Priority'));
        $show->field('mode', __('Chế độ phât'))->using(['1' => 'Trong ngày',
                                                        '2' => 'Hàng ngày', 
                                                        '3' => 'Hàng tuần',
                                                        '4' => 'Phát ngay'
                                                ]);

        $show->field('startDate', __('Ngày bắt đầu'));
        $show->field('endDate', __('Ngày kết thúc'));


        $show->devices('Danh sách thiết bị phát')->as(function ($devices) {
            $html = '';
            foreach($devices as $b){
                $deviceinfo = DeviceInfo::where('deviceCode',$b)->first();
                $html .= isset($deviceinfo->device) ? "<pre>{$deviceinfo->device->name}</pre>":"NULL";
            }
            return $html;
        })->badge(' w-100 p-0')->style('font-size:16px;');

        $show->field('time', __('Khung giờ phát'));

        // $show->field('days', __('Ngày phát'))->using(['2' => 'Thứ 2', '3' => ' Thứ 3', '4' => 'Thứ 4', '5' => 'Thứ 5', '6' => 'Thứ 6', '7' => 'Thứ 7', '8' => 'Chủ nhật']);
        // $show->field('devices', __('Danh sách loa'));

        $show->field('creatorId', __('Người tạo'));
        $show->field('approvedId', __('Người phê duyệt'));
        $show->field('created_at', __('Ngày tạo'));
        $show->field('updated_at', __('Ngày cập nhật'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Program);

        $form->text('name', __('Tên chương trình'))->rules('required',['required'=>"Cần nhập giá trị"]);
       
        $form->radio('type',trans('Loại phát sóng'))
                    ->options(['1' => 'Bản tin',
                               '2' => 'Tiếp sóng', 
                                '3' => 'Thu phát FM',
                                '4' => 'Bản tin văn bản'
                    ])->when(1, function (Form $form) {
                        // $form->file('fileVoice', 'Chọn file')->options([
                            // 'previewFileType'=>'audio',
                            // 'initialPreviewFileType'=>'audio',
                        // ])->uniqueName();
                        $form->file('fileVoice', 'Chọn file')->uniqueName();

                    })->when(2, function (Form $form) {
                        // $form->radio('digiChannel', trans('Chọn kênh tiếp sóng'))->options(['89' => 'VOV 89','90' => 'RADIO Hà Nội','91' => 'VOV Giao thông HN', '96' => 'XZONE FM']);
                        $form->radio('digiChannel', trans('Chọn kênh tiếp sóng'))->options(['91' => 'VOV Giao thông HN', '102.7' => 'VOV 2']);
                        $form->number('inteval', 'Thời lượng (Phút)')->rules('required',['required'=>"Cần nhập giá trị"]);

                    })->when(3, function (Form $form) {
                        $form->radio('radioChannel', trans('Chọn kênh thu phát'))->options(['91' => 'VOV Giao thông HN', '102.7' => 'VOV 2']);
                        $form->number('inteval', 'Thời lượng (Phút)')->rules('required',['required'=>"Cần nhập giá trị"]);

                    })->when(4, function (Form $form) {
                        $form->select('document_Id', trans('Chọn file văn bản'))->options(Document::all()->pluck('name', 'id'));
                    })->rules('required',['required'=>"Cần nhập giá trị"])->default(1);
                           
        $form->divider(trans('Thời gian'));

        $form->radio('mode',trans('Kiểu phát'))
                    ->options(['1' => 'Trong ngày',
                               '2' => 'Hàng ngày', 
                               '3' => 'Hàng tuần',
                               '4' => 'Phát ngay'
                    ])->when(1, function (Form $form) {
                        $form->date('startDate',__('Ngày phát'));
                        // $form->time('time', __('khung giờ phát'))->format('HH:mm:ss')->rules('required');;  
                        $form->time('time', __('khung giờ phát'))->format('HH:mm:ss');   
              
                    })->when(2, function (Form $form) {
                        $form->dateRange('startDate', 'endDate',__('Thời gian phát'));
                        $form->time('time', __('khung giờ phát'))->format('HH:mm:ss');   

                    })->when(3, function (Form $form) {
                        $form->dateRange('startDate', 'endDate',__('Thời gian phát'));
                        // $form->time('time', __('khung giờ phát'))->format('HH:mm:ss')->rules('required');;   
                        $form->checkbox('days', 'Chọn ngày')->options(['2' => 'Thứ 2', '3' => ' Thứ 3', '4' => 'Thứ 4', '5' => 'Thứ 5', '6' => 'Thứ 6', '7' => 'Thứ 7', '8' => 'Chủ nhật'])->canCheckAll();
                        $form->time('time', __('khung giờ phát'))->format('HH:mm:ss');   
                    })->default('1')->rules('required',['required'=>"Cần nhập giá trị"]);

        $form->divider(trans('Chọn loa phát'));

        $form->listbox('devices', trans('Danh sách loa'))->options(Device::all()->pluck('name', 'deviceCode'))->rules('required',['required'=>"Cần nhập giá trị"]);

        $states = [
            'off' => ['value' => 1, 'text' => 'Chưa duyệt', 'color' => 'danger'],
            'on' => ['value' => 2, 'text' => 'Đã duyệt', 'color' => 'success'],
        ];

        $form->switch('status','Phê duyệt')->states($states)->default(2);

        Log::info('User ID name ' . Admin::user()->id);
        $form->model()->creatorId = Admin::user()->id;

        $form->saving(function ($form) {
             $form->model()->creatorId = Admin::user()->id;
        });

        $form->saved(function ($form) {
            if ($form->model()->status == 2) {
                // gui lenh
                // $this->setPlayFM($form->model()->type, '123456789ABCDEF', $form->model()->digiChannel);
                $songPath = "";
                if ($form->model()->type == 1) {// nếu phát file phương tiện

                    $songPath = env("APP_URL").'/uploads/'.$form->model()->fileVoice;  

                    if ($form->model()->mode == 4) { // nếu phát ngay
                        $this->playOnline($form->model()->type, implode(',',$form->model()->devices),$songPath);   
                    } else { // nếu phát theo lịch
                        // $this->sendFileToDevice(implode(',',$form->model()->devices), $songPath);
                        // set schedule
                        $this->setPlaySchedule($form->model()->type, implode(',',$form->model()->devices), $form->model()->startDate, $form->model()->endDate, $form->model()->time, $songPath);    
                    } 

                } else if ($form->model()->type == 2) {
                    $songPath = $form->model()->digiChannel;
                    if ($form->model()->mode == 4) {
                        $this->playOnline($form->model()->type, implode(',',$form->model()->devices),$songPath);   
                    } else {
                        $this->setPlaySchedule($form->model()->type, implode(',',$form->model()->devices), $form->model()->startDate, $form->model()->endDate, $form->model()->time, $songPath);
                    }
                } else if ($form->model()->type == 3) {
                    $songPath = $form->model()->radioChannel;
                    if ($form->model()->mode == 4) {
                        $this->playOnline($form->model()->type, implode(',',$form->model()->devices),$songPath);   
                    } else {
                        $this->setPlaySchedule($form->model()->type, implode(',',$form->model()->devices), $form->model()->startDate, $form->model()->endDate, $form->model()->time, $songPath);
                    }
                } else if ($form->model()->type == 4) {
                    $docModel = Document::findOrFail($form->model()->document_Id);
                    $songPath = "http://truyenthanh.org.vn:8888/".$docModel->fileVoice;

                    // $this->sendFileToDevice(implode(',',$form->model()->devices), $songPath);
                    if ($form->model()->mode == 4) {
                        $this->playOnline($form->model()->type, implode(',',$form->model()->devices),$songPath);   
                    } else {
                        $this->setPlaySchedule($form->model()->type, implode(',',$form->model()->devices), $form->model()->startDate, $form->model()->endDate, $form->model()->time, $songPath);
                    }
                } 

                Log::info('Song name ' . $songPath);
 
                // setPlaySchedule($type, $deviceCode, $data, $startDate, $endDate, $startTime, $endTime, $songName) 

            }
        });

        $form->disableReset();
        $form->disableViewCheck();
        $form->disableEditingCheck();
        $form->disableCreatingCheck();

        return $form;
    }

    //{"DataType":4,"Data":"{\"CommandItem_Ts\":[{\"DeviceID\":\"123456789ABCDEF\",\"CommandSend\":\"{\\\"PacketType\\\":2,\\\"Data\\\":\\\"{\\\\\\\"PlayList\\\\\\\":[{\\\\\\\"SongName\\\\\\\":\\\\\\\"TraLaiEmLoiYeu-NhatTinhAnh_xwc9.mp3\\\\\\\",\\\\\\\"TimeStart\\\\\\\":\\\\\\\"16:48:13\\\\\\\",\\\\\\\"TimeStop\\\\\\\":\\\\\\\"19:48:13\\\\\\\",\\\\\\\"DateStart\\\\\\\":\\\\\\\"2020-11-16\\\\\\\",\\\\\\\"DateStop\\\\\\\":\\\\\\\"2020-11-16\\\\\\\",\\\\\\\"PlayType\\\\\\\":1,\\\\\\\"PlayRepeatType\\\\\\\":1},{\\\\\\\"SongName\\\\\\\":\\\\\\\"TinhAnhVanNhuThe-NhatTinhAnh_cxkg.mp3\\\\\\\",\\\\\\\"TimeStart\\\\\\\":\\\\\\\"20:48:13\\\\\\\",\\\\\\\"TimeStop\\\\\\\":\\\\\\\"21:48:13\\\\\\\",\\\\\\\"DateStart\\\\\\\":\\\\\\\"2020-11-16\\\\\\\",\\\\\\\"DateStop\\\\\\\":\\\\\\\"2020-11-16\\\\\\\",\\\\\\\"PlayType\\\\\\\":1,\\\\\\\"PlayRepeatType\\\\\\\":1}]}\\\"}\"}]}"}

    protected function setPlayFM($type, $deviceCode, $data) 
    {
        $curl = curl_init();

        // $dataRequest = '{"DataType":'.$type.',"Data":"{\"CommandItem_Ts\":[{\"DeviceID\":\"'.$deviceCode.'\",\"CommandSend\":\"{\\\"'.$data.'\\\":\\\"6\\\",\\\"PacketType\\\":17}\"}]}"}';
        if ($type == 2 || $type == 3) {
            $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[{\"DeviceID\":\"'.$deviceCode.'\",\"CommandSend\":\"{\\\"Data\\\":\\\"'.$data.'\\\",\\\"PacketType\\\":11}\"}]}"}';
        } else {
            $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[{\"DeviceID\":\"'.$deviceCode.'\",\"CommandSend\":\"{\\\\\"PacketType\\\\\":2,\\\\\"Data\\\\\":\\\\\"{\\\\\\\\\\\\\"PlayList\\\\\\\\\\\\\":[{\\\\\\\\\\\\\"SongName\\\\\\\\\\\\\":\\\\\\\\\\\\\"TraLaiEmLoiYeu-NhatTinhAnh_xwc9.mp3\\\\\\\\\\\\\",\\\\\\\\\\\\\"TimeStart\\\\\\\\\\\\\":\\\\\\\\\\\\\"16:48:13\\\\\\\\\\\\\",\\\\\\\\\\\\\"TimeStop\\\\\\\\\\\\\":\\\\\\\\\\\\\"19:48:13\\\\\\\\\\\\\",\\\\\\\\\\\\\"DateStart\\\\\\\\\\\\\":\\\\\\\\\\\\\"2020-11-16\\\\\\\\\\\\\",\\\\\\\\\\\\\"DateStop\\\\\\\\\\\\\":\\\\\\\\\\\\\"2020-11-16\\\\\\\\\\\\\",\\\\\\\\\\\\\"PlayType\\\\\\\\\\\\\":1,\\\\\\\\\\\\\"PlayRepeatType\\\\\\\\\\\\\":1}]}\\\\\"}\"}]}"}';
        }
        
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

    protected function setPlaySchedule($type, $deviceCode, $startDate, $endDate, $startTime, $songName) 
    {
        $curl = curl_init();

        // $dataRequest = '{"DataType":'.$type.',"Data":"{\"CommandItem_Ts\":[{\"DeviceID\":\"'.$deviceCode.'\",\"CommandSend\":\"{\\\"'.$data.'\\\":\\\"6\\\",\\\"PacketType\\\":17}\"}]}"}';
        // if ($type == 2 || $type == 3) {
        //     $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[{\"DeviceID\":\"'.$deviceCode.'\",\"CommandSend\":\"{\\\\\"Data\\\":\\\\\"'.$data.'\\\\\",\\\\\"PacketType\\\\\":11}\"}]}"}';
        // } else {
        
        $dataRequest = "";
        if ($type == 1 || $type == 4) {
            $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[{\"DeviceID\":\"'.$deviceCode.'\",\"CommandSend\":\"{\\\\\"PacketType\\\\\":2,\\\\\"Data\\\\\":\\\\\"{\\\\\\\\\\\\\"PlayList\\\\\\\\\\\\\":[{\\\\\\\\\\\\\"SongName\\\\\\\\\\\\\":\\\\\\\\\\\\\"'.$songName.'\\\\\\\\\\\\\",\\\\\\\\\\\\\"TimeStart\\\\\\\\\\\\\":\\\\\\\\\\\\\"'.$startTime.'\\\\\\\\\\\\\",\\\\\\\\\\\\\"TimeStop\\\\\\\\\\\\\":\\\\\\\\\\\\\"23:59:13\\\\\\\\\\\\\",\\\\\\\\\\\\\"DateStart\\\\\\\\\\\\\":\\\\\\\\\\\\\"'.$startDate.'\\\\\\\\\\\\\",\\\\\\\\\\\\\"DateStop\\\\\\\\\\\\\":\\\\\\\\\\\\\"'.$startDate.'\\\\\\\\\\\\\",\\\\\\\\\\\\\"PlayType\\\\\\\\\\\\\":2,\\\\\\\\\\\\\"PlayRepeatType\\\\\\\\\\\\\":1}]}\\\\\"}\"}]}"}';
        } else {
            // $endTime = $this->addMinutesToTime($startTime, 5);
            // Log::info('Start time ' .$startTime);
            // Log::info('End time ' .$endTime);
            $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[{\"DeviceID\":\"'.$deviceCode.'\",\"CommandSend\":\"{\\\\\"PacketType\\\\\":2,\\\\\"Data\\\\\":\\\\\"{\\\\\\\\\\\\\"PlayList\\\\\\\\\\\\\":[{\\\\\\\\\\\\\"SongName\\\\\\\\\\\\\":\\\\\\\\\\\\\"'.$songName.'\\\\\\\\\\\\\",\\\\\\\\\\\\\"TimeStart\\\\\\\\\\\\\":\\\\\\\\\\\\\"'.$startTime.'\\\\\\\\\\\\\",\\\\\\\\\\\\\"TimeStop\\\\\\\\\\\\\":\\\\\\\\\\\\\"23:59:13\\\\\\\\\\\\\",\\\\\\\\\\\\\"DateStart\\\\\\\\\\\\\":\\\\\\\\\\\\\"'.$startDate.'\\\\\\\\\\\\\",\\\\\\\\\\\\\"DateStop\\\\\\\\\\\\\":\\\\\\\\\\\\\"'.$startDate.'\\\\\\\\\\\\\",\\\\\\\\\\\\\"PlayType\\\\\\\\\\\\\":3,\\\\\\\\\\\\\"PlayRepeatType\\\\\\\\\\\\\":1}]}\\\\\"}\"}]}"}';
        }

        $request = base64_encode($dataRequest);

        // echo "request " . $request;
        $urlRequest = "http://103.130.213.161:906/".$request;

        // admin_toastr('$urlRequest', 'info');

        // echo "XXX " . $urlRequest;
        Log::info('Play schedule ' .$urlRequest);


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

    protected function sendFileToDevice($deviceCode, $songName) 
    {
        $curl = curl_init();

        $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[{\"DeviceID\":\"'.$deviceCode.'\",\"CommandSend\":\"{\\\\\"PacketType\\\\\":1,\\\\\"Data\\\\\":\\\\\"{\\\\\\\\\\\\\"URLlist\\\\\\\\\\\\\":[\\\\\\\\\\\\\"'.$songName.'\\\\\\\\\\\\\"]}\\\\\"}\"}]}"}';
        
        $request = base64_encode($dataRequest);

        // echo "request " . $request;
        $urlRequest = "http://103.130.213.161:906/".$request;

        Log::info('Send file ' . $urlRequest);

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
    }   

    protected function playOnline($type, $deviceCode, $songName) 
    {
        $curl = curl_init();
        $dataRequest = "";
        $deviceCode = explode(",",$deviceCode);

        $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[';

        if ($type == 1 || $type == 4) { // nếu phát ngay file pt


            foreach($deviceCode as $device){

                $dataRequest .= '{\"DeviceID\":\"'.$device.'\",\"CommandSend\":\"{\\\\\"Data\\\\\":\\\\\"{\\\\\\\\\\\\\"PlayRepeatType\\\\\\\\\\\\\":1,\\\\\\\\\\\\\"PlayType\\\\\\\\\\\\\":2,\\\\\\\\\\\\\"SongName\\\\\\\\\\\\\":\\\\\\\\\\\\\"'.$songName.'\\\\\\\\\\\\\"}\\\\\",\\\\\"PacketType\\\\\":5}\"},';
            }

        } else {

            $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[{\"DeviceID\":\"'.$deviceCode.'\",\"CommandSend\":\"{\\\\\"Data\\\\\":\\\\\"'.$songName.'\\\\\",\\\\\"PacketType\\\\\":11}\"}]}"}';
        }

        $dataRequest .= ']}"}';

        $request = base64_encode($dataRequest);

        // echo "request " . $request;
        $urlRequest = "http://103.130.213.161:906/".$request;

        Log::info('Phat ngay ' . $urlRequest);

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
    }   

    // function addMinutesToTime( $time, $plusMinutes ) {

    //     $time = DateTime::createFromFormat( 'HH:mm:ss', $time );
    //     $time->add( new DateInterval( 'PT' . ( (integer) $plusMinutes ) . 'M' ) );
    //     $newTime = $time->format( 'HH:mm:ss' );
    
    //     return $newTime;
    // }
}
