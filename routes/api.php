<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Lấy danh sách program
Route::get('programs', 'Api\ProgramController@index')->name('programs.index');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('test', function () {
    return response('Hello World', 200)
                ->header('Content-Type', 'text/plain');
});

Route::get('data/{seri}', function (Request $request) {
    
    $packetType = $request->input('packetType');
    $responseData = "";

    if ($packetType == 1) {
        $responseData = ['URLlist' => "'https://c1-ex-swe.nixcdn.com/NhacCuaTui997/TinhSauThienThuMuonLoi-VoDinhHieu-6262177.mp3','https://c1-ex-swe.nixcdn.com/NhacCuaTui994/AnhThanhNien-HuyR-6205741.mp3','https://c1-ex-swe.nixcdn.com/NhacCuaTui993/ThiepHongNguoiDung-X2X-6198689.mp3'"];
    } else if ($packetType == 2) {
        $responseData = ['PlayList' => ['song1'=>['DateStart'=>'2020-08-04','DateStop'=>'2020-08-04', 'PlayRepeatType' => '1', 'PlayType' => 1, "SongName" => "songName1", "TimeStart"=>"08:00:00","TimeStop"=>"10:30:25"]
                                      ,'song2'=>['DateStart'=>'2020-08-04','DateStop'=>'2020-08-04', 'PlayRepeatType' => '1', 'PlayType' => 1, "SongName" => "songName1", "TimeStart"=>"08:00:00","TimeStop"=>"10:30:25"]]
                        ];
    } else {
        $responseData = "No Data";
    }

    $responseData = '{"Data":"{\"URLlist\":[\"https://c1-ex-swe.nixcdn.com/NhacCuaTui997/TinhSauThienThuMuonLoi-VoDinhHieu-6262177.mp3\",\"https://c1-ex-swe.nixcdn.com/NhacCuaTui994/AnhThanhNien-HuyR-6205741.mp3\",\"https://c1-ex-swe.nixcdn.com/NhacCuaTui993/ThiepHongNguoiDung-X2X-6198689.mp3\",\"https://c1-ex-swe.nixcdn.com/NhacCuaTui997/NhoLamDay-NgocKara-6245082.mp3\"]}","PacketType":1}';

    $responseData = '{"Data":"102.7","PacketType":11}';

    $responseData = '{"Data":"No Data","PacketType":6}';

    return response($responseData, 200)
    ->header('Content-Type', 'text/plain');

});