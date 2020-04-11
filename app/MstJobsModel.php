<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MstJobsModel extends Model
{
    public function jobProfile(){
        return $this->hasMany('App\JobProfilesModel');
    }

    public function testResult(){
        return $this->hasMany('App\TestResultModel');
    }
}
