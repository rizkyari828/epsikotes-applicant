<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TestCategoriesModel extends Model
{
    protected $table = 'psy_test_categories';
    protected $primaryKey = 'TEST_CATEGORY_ID';
    protected $fillable = ['SCHEDULE_ID','CATEGORY_SEQ','CATEGORY_ID','SUB_CATEGORY_ID','IS_TEST_CATEGORY_ACTIVE','CATEGORY_START_DATE','CATEGORY_SUBMIT_DATE','SUM_RAWSCORE','STANDARD_SCORE','CREATION_DATE','LAST_UPDATE_DATE','CATEGORY_STATUS'];
    public $timestamps = false;

    public function testQuestion(){
        return $this->hasMany('App\TestQuestionModel');
    }

    public function schedules()
    {
        return $this->belongsTo('App\SchedulesModel','SCHEDULE_ID');
    }

    public function queCategories()
    {
        return $this->belongsTo('App\QueCategoriesModel','CATEGORY_ID');
    }

    public function queSubCategories()
    {
        return $this->belongsTo('App\QueSubCategoriesModel','SUB_CATEGORY_ID');
    }
}
