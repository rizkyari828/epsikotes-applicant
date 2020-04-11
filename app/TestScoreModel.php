<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TestScoreModel extends Model
{
    protected $table = 'psy_test_score';
    protected $primaryKey = 'TEST_SCORE_ID';
    protected $fillable = ['TEST_QUESTION_ID','ORIGINAL_ANSWER','RAW_SCORE','CREATION_DATE'];
    public $timestamps = false;

    public function testQuestion()
    {
        return $this->belongsTo('App\TestQuestionModel','TEST_QUESTION_ID');
    }
}
