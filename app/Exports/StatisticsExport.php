<?php

namespace App\Exports;

use App\Statistic;
use Encore\Admin\Grid\Exporters\ExcelExporter;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StatisticsExport extends ExcelExporter implements ShouldAutoSize, FromQuery, WithMapping, WithColumnFormatting, WithStyles
{
    protected $fileName = 'Statistics.xlsx';

    protected $headings = [
        'Mã số', 
        'Mã thiết bị',
        'Kết nối', 
        'Tình trạng quạt',
        'Âm lượng',
        'Đang phát',
        'Đường dẫn',
        'Tần số Radio',
        'Thời gian thống kê bắt đầu',
        'Thời gian thống kê kết thúc',
    ];
    public function map($statistic): array
    {
        return [
            $statistic->id,
            $statistic->deviceCode,
            $statistic->status ? 'Kết nối' : 'Không kết nối',
            $statistic->fan_status ? 'Đang chạy' : 'Không chạy',
            $statistic->volume,
            $statistic->audio_out_state ? 'Đang phát' : 'Không phát',
            $statistic->play_url ? $statistic->play_url : '',
            $statistic->radio_frequency ? $statistic->radio_frequency : '',
            Date::dateTimeToExcel($statistic->created_at),
            Date::dateTimeToExcel($statistic->updated_at),
        ];
    }
    public function columnFormats(): array
    {
        return [
            'I' => 'dd/mm/yyyy hh:mm',
            'J' => 'dd/mm/yyyy hh:mm',
        ];
    }
    public function styles(Worksheet $sheet)
    {
        return [
            'B' => ['font' => ['bold' => true]],
        ];
    }
}
