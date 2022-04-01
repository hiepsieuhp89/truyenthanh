<?php

/**
 * Laravel-admin - admin builder based on Laravel.
 * @author z-song <https://github.com/z-song>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 * Encore\Admin\Form::forget(['map', 'editor']);
 *
 * Or extend custom form field:
 * Encore\Admin\Form::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */
Use Encore\Admin\Admin;
use Encore\Admin\Form;

Encore\Admin\Form::extend('media', \Encore\FileBrowser\FileBrowserField::class);
Encore\Admin\Form::extend('record', App\Admin\Extensions\RecordVoice::class);


Admin::favicon(env("APP_URL").'/images/icon-s.png');
Admin::style('
.w-100{width:100%;} 
.h-100{height:100%;} 
.h-400px{height:400px;} 
.fs-12{
  font-size:12px;
}
.fs-16{
  font-size:16px;
}
.p-0{padding:0;} 
.d-flex{display:flex;} 
.d-initial{display:initial;} 
.bootstrap-switch-handle-on, .bootstrap-switch-handle-off{
    white-space: nowrap;
}
body{
    font-family:system-ui;
}
@keyframes scale-1-3 {
  from {transform: scale(1);opacity:0.8;}
  to {transform: scale(1.3);opacity:1;}
}
');
Admin::js(env('APP_URL').'/js/custom.js');
Admin::js("https://kit.fontawesome.com/12065bbb1f.js");
Admin::js('https://cdn.jsdelivr.net/npm/@goongmaps/goong-js@1.0.9/dist/goong-js.js');
Admin::css('https://cdn.jsdelivr.net/npm/@goongmaps/goong-js@1.0.9/dist/goong-js.css');

Form::init(function (Form $form) {

    $form->disableEditingCheck();

    $form->disableCreatingCheck();

    $form->disableViewCheck();

    $form->tools(function (Form\Tools $tools) {
        $tools->disableDelete();
        $tools->disableView();
        $tools->disableList();
    });
});
//Admin::js('https://maps.googleapis.com/maps/api/js?key='. env('GOOGLE_API_KEY') .'&callback=initMap');