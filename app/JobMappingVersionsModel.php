<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JobMappingVersionsModel extends Model
{
    //
    protected $table = 'psy_job_mapping_versions';
    protected $primaryKey = 'VERSION_ID';

    public function jobCategoryList(){
        return $this->hasMany('App\CategoryListModel');
    }

    public function jobProfile(){
        return $this->hasMany('App\JobProfileModel');
    }

    public function Narrations()
    {
        return $this->belongsTo('App\NarrationsModel','NARRATION_ID');
    }

    public function jobMappings()
    {
        return $this->belongsTo('JobMappingsModel','JOB_MAPPING_ID');
    }
}
