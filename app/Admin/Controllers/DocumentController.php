<?php

namespace App\Admin\Controllers;

use App\Document;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Log;

class DocumentController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App\Document';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Document);       
        $grid->disablePagination();        
        $grid->disableBatchActions();    
        $grid->disableExport();    
        $grid->disableColumnSelector();   

        $grid->filter(function($filter){
            $filter->expand();
            $filter->disableIdFilter();
            $filter->like('name', trans('Tên bài'));
        });
        $grid->model()->orderBy('id', 'DESC');

        $grid->column('id', trans('entity.id'));
        $grid->column('name', trans('Tên bài')); 
        $grid->column('fileVoice', 'File')->display(function ($fileVoice) {
            return "<audio controls><source src='/$fileVoice' type='audio/mpeg'></audio>";
        });
        $grid->column('created_at', trans('entity.created_at'));
        $grid->column('updated_at', trans('entity.updated_at'));
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
        $show->field('id', trans('entity.id'));
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
        $form->textarea('content')->rows(15)->rules('required');

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

        $form->saved(function (Form $form) {
            Log::info("Saved - name  " . $form->model()->name);
            Log::info("Saved - content  " . $form->model()->content);
            $form->model()->fileVoice = md5($form->model()->name).".mp3";
            $this->createVoice($form->model()->content, $form->model()->fileVoice);
            $form->model()->save();
        });

        return $form;
    }

    protected function createVoice($content, $fileVoice) 
    {
        $curl = curl_init();
        // $fileVoice  = md5($fileName).".wav";
        Log::info(" Create file  " . $fileVoice);
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://tts.mobifone.ai/api/tts",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => false,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => http_build_query(array(
            "input_text" => $content,
            "audio_type" => "mp3",
            "app_id" => env("APP_ID","0a7ffab8a3e323713780f6f9"),
            "key" => env("MOBIFONE_TEXT_2_SPEECH_KEY","8cf61e5fef154dc3dc9f089fff4763a9"),
            "time" => "1608213189865",
            "voice" => "hn_female_ngochuyen_news_48k-d",
            "rate" => "1",
            "user_id" => env("MOBIFONE_TEXT_2_SPEECH_USER_ID","1633"),
             CURLOPT_HTTPHEADER => array("Content-Type: application/x-www-form-urlencoded"),
            ))
        ));
        
        $response = curl_exec($curl);
        dd($response);
        $err = curl_error($curl);
        
        curl_close($curl);
        
        if ($err) {
        //   echo "cURL Error #:" . $err;
          Log::error("cURL Error #:" . $err);
        } else {
          Log::info(" Tạo file  " . $fileVoice);
          file_put_contents($fileVoice , $response);
          // move file
        } 
    }   
}