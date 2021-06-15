<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Encore\Admin\Layout\Content;
use Encore\Admin\Facades\Admin;
use App\Api;
use App\Area;


class HomeController extends Controller
{
    use Api;
    public function index(Content $content)
    {
        return $content->title('Mạng lưới phát thanh kỹ thuật số')->body($this->show());
    }
    public function getDevicesStatus(){
        $curl = curl_init();

        $dataRequest = "eyJEYXRhVHlwZSI6MjAsIkRhdGEiOiJHRVRfQUxMX0RFVklDRV9TVEFUVVMifQ==";

        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://103.130.213.161:906/" . $dataRequest,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        $response = str_replace(':"{', ":{", $response);
        $response = str_replace(':"[{', ":[{", $response);
        $response = str_replace('"}"', "}", $response);
        $response = str_replace('"{"', "{", $response);
        $response = str_replace(']"}', "]}", $response);
        $response = json_decode($response, true);

        return $response;

    }
    protected function show()
    {
        $areaId = Admin::user()->can('*')? 1 : (array_diff(explode(",", Admin::user()->areaId), array("")))[0];
        $area = Area::find($areaId);
    	return view('allDevicesMap',["area" => $area]);
    }
    public function changeLanguage(Request $req){
        Session::put('lan', $req->lang);
        return response($req->lang);
    }
}
