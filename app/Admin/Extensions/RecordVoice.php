<?php

namespace App\Admin\Extensions;

use Encore\Admin\Form\Field;

class RecordVoice extends Field
{
    protected $view = 'admin.record';

    protected static $css = [
        
    ];

    protected static $js = [
        'https://unpkg.com/mic-recorder-to-mp3@2.2.1/dist/index.js',
        'https://momentjs.com/downloads/moment-with-locales.js',
    ];

    public function render()
    {
        return parent::render();
    }
}
