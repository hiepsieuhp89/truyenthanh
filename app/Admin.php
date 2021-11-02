<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Admin_role;
use App\Area;
use Encore\Admin\Auth\Database\Administrator;


class Admin extends Administrator
{
  	//protected $table = "admin_users";

  	// public function role(){
   //      return $this->belongsTo(Admin_role::class,'user_id', 'id');
   //  }	
    public function area()
    {
        return $this->belongsTo(Area::class,'areaId');
    }
  	public function createProgram(){
  		return $this->hasMany(Program::class,'creatorId');
  	}
  	public function approveProgram(){
  		return $this->hasMany(Program::class,'approvedId');
  	}
}
