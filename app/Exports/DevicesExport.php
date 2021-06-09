<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\DeviceInfo;
use App\Imports\DevicesImport;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

use Exception;

class DevicesExport implements FromCollection, ShouldAutoSize
{
    public $code;

    public function __construct($deviceCode){
        $this->code = $deviceCode;
    }
    // public function headings(): array
    // {
    //     return [
    //         'Mã thiết bị',
    //         'Trạng thái',
    //         'Âm lượng',
    //         'Đang phát',
    //         'Thời điểm',
    //     ];
    // }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        try{
            $pre = (new DevicesImport)->toCollection($this->code . '.xlsx', 'export');
        } catch (Exception $e) {
            $pre = new Collection();
        }
        if(!isset($pre))
            $pre = new Collection();

        $d = DeviceInfo::select('deviceCode', 'status', 'volume', 'is_playing')->where('deviceCode', $this->code)->get();

        foreach($d as $d_info){

            if ($d_info->status)
                $d_info->status = "Hoạt động";
            else
                $d_info->status = "Không hoạt động";

            $d_info->volume = 'Mức ' . $d_info->volume;

            if (!$d_info->is_playing)
                $d_info->is_playing = "Không phát";
            else
                $d_info->is_playing = "Đang phát";

            $d_info->created_at = Carbon::now();

            $pre->add($d_info);
        }
        return $pre;
    }
}
