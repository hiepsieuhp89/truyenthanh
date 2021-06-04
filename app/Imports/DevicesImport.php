<?php

namespace App\Imports;

use App\DeviceInfo;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithValidation;

class DevicesImport implements ToModel, SkipsOnFailure, SkipsOnError
{
    use Importable, SkipsErrors;
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new DeviceInfo([
            'deviceCode'     => $row[0],
            'status'    => $row[1],
            'volume' => $row[2],
            'is_playing' => $row[3],
            'created_at' => $row[4],
        ]);
    }
    public function onFailure(Failure ...$failures)
    {
        return null;
    }
    public function onError(\Throwable $e)
    {
        return null;
    }
}
