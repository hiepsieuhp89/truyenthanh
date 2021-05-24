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
    ];

    public function render()
    {
        $this->script = <<<EOT
            const button = document.getElementById('record_btn');
            const recorder = new MicRecorder({
            bitRate: 128
            });

            button.addEventListener('click', startRecording);

            function startRecording() {
                recorder.start().then(() => {
                    document.querySelector('#playlist').innerHTML = '';
                    button.textContent = "Dừng ghi âm";
                    button.classList.toggle('btn-danger');
                    button.removeEventListener('click', startRecording);
                    button.addEventListener('click', stopRecording);
                }).catch((e) => {
                    console.error(e);
                });
            }

            function stopRecording() {
            recorder.stop().getMp3().then(([buffer, blob]) => {
                console.log(buffer, blob);
                const file = new File(buffer, 'record_'+ Date.now() +'.wav', {
                    type: blob.type,
                    lastModified: Date.now()
                });

                const player = new Audio(URL.createObjectURL(file));
                player.controls = true;

                document.querySelector('#playlist').appendChild(player);

                button.textContent = 'Nhấn để bắt đầu';
                button.classList.toggle('btn-danger');
                button.removeEventListener('click', stopRecording);
                button.addEventListener('click', startRecording);

                let container = new DataTransfer();
                let fileInputElement = document.querySelector('input[type="file"]');
                container.items.add(file);
                fileInputElement.files = container.files;

            }).catch((e) => {
                console.error(e);
            });
            }
        EOT;

        return parent::render();
    }
}
