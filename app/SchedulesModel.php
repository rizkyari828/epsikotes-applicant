<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SchedulesModel extends Model
{
    protected $table = 'psy_schedules'; 
    protected $primaryKey = 'SCHEDULE_ID';

    public function ScheduleHistories()
    {
        return $this->hasMany('App\ScheduleHistoriesModel');
    }

    public function testCategories()
    {
        return $this->hasMany('App\TestCategoriesModel');
    }

    public function testQuestions()
    {
        return $this->hasMany('App\TestQuestionsModel');
    }

    public function testResult()
    {
        return $this->hasMany('App\TestResultModel');
    }

    public function Applicant()
    {
        return $this->belongsTo('App\ApplicantModel','CANDIDATE_ID');
    }
}
