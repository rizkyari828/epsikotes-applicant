<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TestQuestionsModel extends Model
{
    protected $table = 'psy_test_questions';
    protected $primaryKey = 'TEST_QUESTION_ID';
    protected $fillable = ['TEST_CATEGORY_ID','QUESTION_SEQ','QUESTION_ID','EXAMPLE','TYPE_SUB_CATEGORY','TYPE_ANSWER','CREATED_DATE'];
    public $timestamps = false;
    
    public function testScore(){
        return $this->hasMany('App\TestScoreModel');
    }

    public function testChoices(){
        return $this->hasMany('App\TestChoicesModel');
    }
    
    public function testMemories(){
        return $this->hasMany('App\TestMemoriesModel');
    }

    public function testCategories()
    {
        return $this->belongsTo('App\TestCategoriesModel','TEST_CATEGORY_ID');
    }
    
    public function schedule()
    {
        return $this->belongsTo('App\SchedulesModel','SCHEDULE_ID');
    }
    
    public function queQuestion()
    {
        return $this->belongsTo('App\QuestionModel','QUESTION_ID');
    }
}
