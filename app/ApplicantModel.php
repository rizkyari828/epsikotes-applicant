<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApplicantModel extends Model
{
    protected $table = 'mst_applicant'; 
    protected $primaryKey = 'CANDIDATE_ID';
    public $timestamps = false;

    public function Schedules(){
    	return $this->hasMany('App\SchedulesModel');
    }
}
