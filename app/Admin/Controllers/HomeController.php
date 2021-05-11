<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;


class HomeController extends Controller
{
    public function index(Content $content)
    {
        return $content->title('Mạng lưới phát thanh kỹ thuật số')->body($this->show());
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
