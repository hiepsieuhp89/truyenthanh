<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class VideoController extends Controller
{
    public function link(Request $req){
        stream_context_set_default([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);
        $file = ($req->all())['url'];
        $head = array_change_key_case(get_headers($file, TRUE));
        $size = $head['content-length'];
        header('Content-Type: video/m3u8');
        header('Accept-Ranges: bytes');
        header('Content-Disposition: inline');
        header('Content-Length:' . $size);
        readfile($file);
    }
}
