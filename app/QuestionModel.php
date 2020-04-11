<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuestionModel extends Model
{
    protected $table = 'que_questions';
    protected $primaryKey = 'QUESTION_ID'; // Misal kita memakai nama id_kendaraan
    // public $incrementing = false; // defaultnya true
    public $timestamps = false;

    protected $guarded = array();

    public function ansChoices()
    {
        return $this->hasMany('App\AnsChoicesModel');
    }
    
    public function ansGroup()
    {
        return $this->hasMany('App\AnsGroupModel');
    }

    public function ansTextSeries()
    {
        return $this->hasMany('App\AnsTextSeriesModel');
    }

    public function testQuestions()
    {
        return $this->hasMany('App\TestQuestionsModel');
    }
    
    public function narations()
    {
        return $this->belongsTo('App\NarrationsModel','NARRATION_ID');
    }

    public function queSubCategoryVersions()
    {
        return $this->belongsTo('App\QueSubCategoryVersionsModel','VERISON_ID');
    }




}
