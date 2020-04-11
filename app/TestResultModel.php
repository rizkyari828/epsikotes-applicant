<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TestResultModel extends Model
{
    protected $table = 'psy_test_result';
    protected $primaryKey = 'TEST_RESULT_ID';
    protected $fillable = ['SCHEDULE_ID','JOB_ID','ACHIEVE_TOTAL_SCORE','IS_ACHIEVE_TOTAL_SCORE','HAS_MANDATORY','TOTAL_MANDATORY','TOTAL_ACHIEVE_MANDATORY','RECOMENDATION_BY_SYSTEM'];
    public $timestamps = false;

    public function schedules()
    {
        return $this->belongsTo('App\SchedulesModel','SCHEDULE_ID');
    }

    public function mstJobs()
    {
        return $this->belongsTo('App\MstJobsModel','JOB_ID');
    }

}
