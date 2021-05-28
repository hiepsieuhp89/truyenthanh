<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Admin;
use App\Area;


class UpdateAreaAccount implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    public function findArea($id, $result)
    {

        $child_areas = Area::where('parent_id', $id)->get();

        if ($child_areas !== NULL) {

            foreach ($child_areas as $ca) {

                $result .= ',' . $ca->id;

                $result = $this->findArea($ca->id, $result);
            }
        }

        return $result;
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach (Admin::all() as $user) {
            $parent_area = (explode(',', $user->areaId))[0];
            $user->areaId = $this->findArea($parent_area, $parent_area);
            $user->save();
        }
    }
}
