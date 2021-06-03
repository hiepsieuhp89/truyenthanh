<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Encore\Admin\Layout\Content;
use App\Api;


class HomeController extends Controller
{
    use Api;
    public function index(Content $content)
    {
        return $content->title('Mạng lưới phát thanh kỹ thuật số')->body($this->show());
    }
    public function getDevicesStatus(){
        return response($this->getDevicesStatus());
    }
    protected function show()
    {
    	return view('allDevicesMap',["areaId" => 1]);
    }
    public function changeLanguage(Request $req){
        Session::put('lan', $req->lang);
        return response($req->lang);
    }
}
