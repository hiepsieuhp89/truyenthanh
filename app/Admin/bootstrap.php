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

Encore\Admin\Form::extend('media', \Encore\FileBrowser\FileBrowserField::class);
Encore\Admin\Form::extend('record', App\Admin\Extensions\RecordVoice::class);


Admin::favicon(env("APP_URL").'/images/icon-s.png');
Admin::style('
.w-100{width:100%;} 
.h-100{height:100%;} 
.h-400px{height:400px;} 
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

// Admin::script('
// 	if(document.URL.indexOf("#")==-1){
//         // Set the URL to whatever it was plus "#".
//         url = document.URL+"#";
//         location = "#";

//         //Reload the page
//         location.reload(true);
//     }'
// );

//Encore\Admin\Form::forget(['map', 'editor']);

