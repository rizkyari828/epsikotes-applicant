<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JobMappingsModel extends Model
{
    protected $table = 'psy_job_mappings'; 
    protected $primaryKey = 'JOB_MAPPING_ID';

    public function jobMappingVersions(){
    	return $this->hasMany('JobMappingVersionsModel');
    }

    public function scheduleHistories(){
    	return $this->hasMany('ScheduleHistories');
    }
}
