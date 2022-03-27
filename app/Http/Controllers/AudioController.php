<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AudioController extends Controller
{
    // public $fileInput;
    // public $fileOutput;
    // public $increaseDb;
    // function __construct($a, $b, $c)
    // {
    //     $fileInput=$a;
    //     $fileInput=$b;
    //     $increaseDb=$c;
    // }
    public function Convert($fileInput, $fileOutput){
        $exec = sprintf('ffmpeg -y -i %s %s', $fileInput, $fileOutput);
        exec($exec);
        unlink($fileInput);
        return true;
    }
    public function IncreaseVolume($fileInput, $increaseDb, $fileOutput){
        $exec = sprintf('ffmpeg -y -i %s -filter:a "volume=%fdB" %s', $fileInput, $increaseDb, $fileOutput);
        exec($exec);
        unlink($fileInput);
        return true;
    }
    public function ReduceNoise($fileInput, $fileOutput){
        $exec = sprintf('ffmpeg -y -i %s -af "afftdn=nf=-25" %s', $fileInput, $fileOutput);
        exec($exec);
        unlink($fileInput);
        return true;
    }
}
