<?php

namespace App\Admin\Controllers;

use Request;
use Helper;
use DB;
use Carbon\Carbon;

use App\Document;
use App\Admin\Actions\Document\Delete;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Log;
use Encore\Admin\Facades\Admin;

class DocumentController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Phát văn bản';
    public $path = '/docs';
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

    public function show($id, Content $content)
    {
        $document = Document::where('id',$id)->first();

        if(Admin::user()->can('*') || Request::get('_scope_') == 'auth' || $document->creatorId == Admin::user()->id)
            return $content
                ->title($this->title())
                ->description($this->description['show'] ?? trans('admin.show'))
                ->body($this->detail($id));

        abort(404);
    }

    // public function edit($id, Content $content)
    // {
    //     $document = Document::where('id',$id)->first();

    //     if(Admin::user()->can('*') || Request::get('_scope_') == 'auth' || $document->creatorId == Admin::user()->id)
    //         return $content
    //             ->title($this->title())
    //             ->description($this->description['edit'] ?? trans('admin.edit'))
    //             ->body($this->form()->edit($id));
    //     abort(404);

    //     //return redirect()->intended($this->path);
    // }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */

    protected function grid()
    {
        $grid = new Grid(new Document);       
        $grid->disablePagination();   
        $grid->disableExport();    
        $grid->disableColumnSelector();   
        $grid->actions(function($actions){
          $actions->disableEdit();
          $actions->disableDelete();
          $actions->add(new Delete);
        });
        $grid->batchActions(function($batch){
          $batch->disableDelete();
        });

        $grid->filter(function($filter){
            $filter->scope('auth',trans('Tài liệu'))->where('creatorId',Admin::user()->id);
            $filter->expand();
            $filter->disableIdFilter();
            $filter->like('name', trans('Tên bài'));
        });
        $grid->model()->orderBy('id', 'DESC');
        $grid->column('name', trans('Tên bài')); 

        $grid->column('fileVoice', 'Nội dung')->display(function ($fileVoice) {
            return "<audio controls><source src='".config('filesystems.disks.upload.url')."/$fileVoice' type='audio/mpeg'></audio>";
        });
        $grid->column('creator.name', trans('Người tạo')); 
        $grid->column('created_at', trans('entity.created_at'));
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
        $show = new Show(Document::findOrFail($id));
        $show->field('name', trans('Tên bài'));
        $show->field('content', trans('Nội dung'));
        $show->field('fileVoice', trans('File nghe'));
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
        $form = new Form(new Document);
        $form->text('name', trans('Tên bài'))->rules('required')->autofocus();
        $form->textarea('content', 'Nội dung')->rows(15)->rules('required');

        $form->select('volumeBooster', 'Tăng giảm âm lượng')->options($this->volume_step)->default(0);

        $form->select('voice', 'Chọn giọng nói')->options([
          'hn_female_maiphuong_news_48k-d' => 'HN - Mai Phương - Đọc báo nâng cao',
          'sg_female_thaotrinh_news_48k-d' => 'SG - Thảo Trinh - Đọc báo nâng cao',
          'hn_female_ngochuyen_news_48k-d' => 'HN - Ngọc Huyền - Đọc báo nâng cao',
          'hn_male_manhdung_news_48k-d' => 'HN - Mạnh Dũng - Đọc báo nâng cao',

        ])->default('hn_female_maiphuong_news_48k-d');

        $form->select('rate', 'Tốc độ đọc')->options([
          '0.6' => '0,6 lần',
          '0.8' => '0,8 lần',
          '1.0'=> '1 lần',
          '1.2' => '1,2 lần',
          '1.4' => '1,4 lần',
          '1.6' => '1,6 lần',
        ])->default('1.0');

        $form->disableViewCheck();
        $form->disableEditingCheck();
        $form->disableCreatingCheck();

        // create voice file
        // $fileName = md5($form->model()->content);
        // if (empty($form->model()->name)) {

        //     $form->model()->fileVoice = md5($form->model()->name).".mp3";
        //     $this->createVoice($form->model()->content, $form->model()->fileVoice);
        // }


        // Log::info(" 1 name  " . $form->model()->name);
        // Log::info("content  " . $form->model()->content);


        // $form->submitted(function (Form $form) {
        //     Log::info(" 2 name  " . $form->model()->name);
        //     Log::info("content  " . $form->model()->content);
        //     $form->model()->fileVoice = md5($form->model()->name).".mp3";
        //     // $this->createVoice($form->model()->content, $form->model()->fileVoice);
        // });

        // $form->saving(function (Form $form) {
        //     Log::info(" 3 name  " . $form->model()->name);
        //     Log::info("content  " . $form->model()->content);
        //     $form->model()->fileVoice = md5($form->model()->name).".mp3";
        //     // $this->createVoice($form->model()->content, $form->model()->fileVoice);
        // });
        $form->saving(function (Form $form) {
            $form->model()->creatorId = Admin::user()->id;
        });
        $form->saved(function (Form $form) {

            Log::info("Saved - name  " . $form->model()->name);

            Log::info("Saved - content  " . $form->model()->content);

            $file_temp = 'voices/'.md5($form->model()->content.(Carbon::now())).".mp3";

            $form->model()->fileVoice = $this->createVoice($form->model()->content, $file_temp, $form->model()->volumeBooster, $form->model()->voice, $form->model()->rate);

            //$form->model()->fileVoice = $file_temp;

            $form->model()->save();
        });

        return $form;
    }

    protected function createVoice($content, $fileVoice, $volumeBooster, $voice, $rate) 
    {
        ini_set('max_execution_time', 0);
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://tts.mobifone.ai/api/tts",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_CONNECTTIMEOUT => 0,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => false,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => http_build_query(array(
            "input_text" => $content,
            "app_id" => env('MOBIFONE_TEXT_2_SPEECH_APP_ID'),
            "key" => env('MOBIFONE_TEXT_2_SPEECH_KEY'),
            "time" => env('MOBIFONE_TEXT_2_SPEECH_TIME'),
            "voice" => $voice,
            "rate" => $rate,
            "user_id" => env('MOBIFONE_TEXT_2_SPEECH_USER_ID'),
            CURLOPT_HTTPHEADER => array(
              "Content-Type: application/x-www-form-urlencoded"
            ),
          ))
        ));
        $response = curl_exec($curl);

        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
        //   echo "cURL Error #:" . $err;
          Log::error("cURL Error #:" . $err);
        } else {

          Log::info(" Tạo file  " . $fileVoice);

          file_put_contents('uploads/'.$fileVoice , $response);

          $booster = (float)$volumeBooster . 'dB';

          $fileInputPath = $fileVoice;

          $fileOutputPath = 'voices/'.md5($fileInputPath).'.mp3';

          $cmd = 'ffmpeg -y -i ' . config('filesystems.disks.upload.path') . $fileInputPath.' -filter:a "volume='.$booster.'" ' . config('filesystems.disks.upload.path') . $fileOutputPath;

          exec($cmd);

          if(file_exists(config('filesystems.disks.upload.path') . $fileOutputPath)){
              unlink(config('filesystems.disks.upload.path') . $fileInputPath);
              return $fileOutputPath;
          }
          return $fileVoice;
        
          // move file
        } 
    }   
}