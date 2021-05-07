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

Admin::favicon(env("APP_URL").'/images/icon-s.png');
Admin::style('.w-100{width:100%;} .h-100{height:100%;} .h-400px{height:400px;} .p-0{padding:0;}');
Admin::js(env('APP_URL').'/js/custom.js');

//Encore\Admin\Form::forget(['map', 'editor']);

