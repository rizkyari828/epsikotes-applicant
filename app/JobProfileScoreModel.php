<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JobProfileScoreModel extends Model
{
    protected $table = 'psy_job_profile_score';
    protected $primaryKey = 'PROFILE_SCORE_ID';

    public function jobProfile()
    {
        return $this->belongsTo('App\JobProfileModel','JOB_PROFILE_ID');
    }

    public function queCategories()
    {
        return $this->belongsTo('App\QueCategoriesModel','CATEGORY_ID');
    }
}
