<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JobProfilesModel extends Model
{
    protected $table = 'psy_job_profiles';
    protected $primaryKey = 'JOB_PROFILE_ID';

    public function jobProfileScore(){
        return $this->hasMany('App\JobProfileScoreModel');
    }

    public function jobMappingVersions()
    {
        return $this->belongsTo('App\JobMappingVersionsModel','VERSION_ID');
    }

    public function mstJob()
    {
        return $this->belongsTo('App\JobMappingVersionsModel','JOB_ID');
    }
}
